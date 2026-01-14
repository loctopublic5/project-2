<?php 
namespace App\Services\Customer;


use Exception;
use App\Models\User;
use App\Models\UserWallet;
use App\Traits\HasUniqueCode;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientBalanceException;

class WalletService{
    use HasUniqueCode;

    public function checkBalance($userId)
    {
        // Vì đã có Observer, ta mặc định ví phải tồn tại.
        // Dùng findByUserId hoặc quan hệ $user->wallet
        $wallet = UserWallet::where('user_id', $userId)->first();

        // Phòng thủ: Nếu lỡ (rất hiếm) ví chưa có thì báo lỗi hệ thống
        if (!$wallet) {
            // Hoặc có thể gọi logic tạo ví khẩn cấp ở đây (Fallback)
            throw new Exception("Lỗi dữ liệu: Tài khoản chưa được kích hoạt ví.");
        }

        if ($wallet->status == 'locked') {
            throw new Exception("Ví của bạn đang bị khóa.");
        }

        return $wallet->balance;
    }

    public function getHistory($userId, $filters = [], $perPage = 10)
    {
        // BƯỚC 1: Tìm ví của User trước để lấy wallet_id
        $wallet = UserWallet::where('user_id', $userId)->first();

        // Nếu user chưa có ví -> Trả về danh sách rỗng luôn (tránh lỗi)
        if (!$wallet) {
            // Trả về một LengthAwarePaginator rỗng
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        // BƯỚC 2: Query Transaction theo 'wallet_id' (Đúng theo ERD)
        $query = WalletTransaction::where('wallet_id', $wallet->id);

        // Filter theo loại: 'deposit', 'payment', 'refund'
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter theo thời gian
        if (!empty($filters['start_date'])){
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // Sắp xếp mới nhất trước
        $query->orderBy('created_at', 'DESC');

        return $query->paginate($perPage);
    }

    /**
     * @param User $user
     * @param array $data ['amount', 'order_id', 'note']
     */
    public function processPayment(User $user, array $data)
    {
        // 1. PRE-CHECK
        // Load relation nếu chưa có để tránh lỗi null pointer
        if (!$user->relationLoaded('wallet')) {
            $user->load('wallet');
        }
        
        if (!$user->wallet || $user->wallet->balance < $data['amount']) {
            throw new InsufficientBalanceException("Số dư không đủ (Pre-check)");
        }

        DB::beginTransaction();
        try {
            // 2. LOCKING
            $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

            // 3. DOUBLE CHECK
            if ($wallet->balance < $data['amount']) {
                throw new InsufficientBalanceException("Số dư không đủ (Real-time Check)");
            }

            // 4. EXECUTION
            $wallet->balance -= $data['amount'];
            $wallet->save();

            // 5. LOGGING
            $trans = WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'type'         => 'payment',
                'amount'       => -($data['amount']), // Lưu số âm
                'status'       => 'success',
                'reference_id' => $data['order_id'], // Map Order ID vào đây
                'description'  => !empty($data['note']) ? "Thanh toán: " . $data['note'] : "Thanh toán đơn hàng #" . $data['order_id']
            ]);

            DB::commit();
            return $trans;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function refund(User $user, array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Lock Ví
            $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

            // 2. Cộng tiền
            $wallet->balance += $data['amount'];
            $wallet->save();

            // 3. Log Refund
            // FIX: Không sinh code thừa, dùng original_order_id làm reference
            $trans = WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'type'         => 'refund',
                'amount'       => $data['amount'], // Số dương
                'status'       => 'success',
                'reference_id' => $data['original_order_id'], 
                'description'  => !empty($data['reason']) ? "Hoàn tiền: " . $data['reason'] : "Hoàn tiền hủy đơn"
            ]);

            DB::commit();
            return $trans;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hàm hoàn tiền dành riêng cho quy trình Hủy đơn (Chạy trong Transaction cha)
     */
    public function refundForOrder(User $user, array $data)
    {
        // 1. Lock Ví (Vẫn cần lock để đảm bảo tính đúng đắn)
        $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

        // 2. Cộng tiền
        $wallet->balance += $data['amount'];
        $wallet->save();

        // 3. Log Refund
        WalletTransaction::create([
            'wallet_id'    => $wallet->id,
            'type'         => 'refund',
            'amount'       => $data['amount'], // Số dương
            'status'       => 'success',
            'reference_id' => $data['original_order_id'], 
            'description'  => !empty($data['reason']) ? "Hoàn tiền: " . $data['reason'] : "Hoàn tiền hủy đơn"
        ]);

        return true;
    }


    /**
     * Logic Nạp tiền
     */
    // Method 1: Tạo yêu cầu (Bước này chưa cộng tiền)
    public function createDepositRequest(User $user, array $data)
    {
        if ($data['amount'] <= 0) {
            throw new Exception("Số tiền không hợp lệ");
        }

        // Tạo mã nạp tiền (DEP-XXXX)
        // Lưu ý: Sửa GenerateUniqueCode -> generateUniqueCode (camelCase)
        $depositCode = $this->generateUniqueCode('reference_id', 'DEP', 8);

        // FIX: Đổi 'code' thành 'reference_id' và sửa typo 'descrption'
        $transaction = WalletTransaction::create([
            'wallet_id'    => $user->wallet->id, // Đảm bảo $user->wallet đã tồn tại
            'type'         => 'deposit',
            'amount'       => $data['amount'],
            'status'       => 'pending',
            'reference_id' => $depositCode, // Lưu mã DEP vào cột reference_id
            'description'  => $data['description'] ?? 'Nạp tiền vào ví'
        ]);
    
        return $transaction;
    }

    public function forceApprove($transactionId)
    {
        DB::beginTransaction();
        try {
            // 1. Lock Transaction
            $trans = WalletTransaction::where('id', $transactionId)->lockForUpdate()->first();
        
            if (!$trans || $trans->status !== 'pending') { 
                throw new Exception("Giao dịch không tồn tại hoặc không hợp lệ");
            }

            // 2. Lock Ví & Cộng tiền
            $wallet = UserWallet::where('id', $trans->wallet_id)->lockForUpdate()->first();
            
            // Phòng hờ ví bị xóa
            if (!$wallet) throw new Exception("Ví không tồn tại");

            $wallet->balance += $trans->amount;
            $wallet->save();

            // 3. Update Status
            $trans->status = 'success';
            $trans->save();
        
            DB::commit();
            return $trans;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hàm trừ tiền dành riêng cho Order Transaction.
     * Tận dụng checkBalance để validate trạng thái ví.
     */
    public function deductBalanceForOrder(User $user, float $amount, string $orderReference, string $note = '')
    {
        // 1. TẬN DỤNG HELPER (REUSE)
        // Gọi checkBalance để validate:
        // - Ví có tồn tại không? (Nếu không -> Exception)
        // - Ví có bị khóa không? (Nếu có -> Exception)
        // - Lấy số dư hiện tại để Pre-check
        $currentBalance = $this->checkBalance($user->id);

        // 2. PRE-CHECK BALANCE (FAIL FAST)
        // Kiểm tra nhanh trước khi phải lock DB (Tối ưu hiệu năng)
        if ($currentBalance < $amount) {
            throw new InsufficientBalanceException("Số dư ví không đủ để thanh toán.");
        }

        // 3. LOCKING (BẮT BUỘC TRONG TRANSACTION)
        // Dù đã check ở trên, ta vẫn phải lockForUpdate để tránh Race Condition 
        // (Ví dụ: Vừa check xong thì có 1 giao dịch khác trừ tiền)
        $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

        // 4. DOUBLE CHECK (REAL-TIME)
        // Kiểm tra lại lần cuối sau khi đã giữ khóa
        if ($wallet->balance < $amount) {
            throw new InsufficientBalanceException("Giao dịch thất bại: Số dư không đủ.");
        }

        // 5. EXECUTION (Trừ tiền)
        $wallet->balance -= $amount;
        $wallet->save();

        // 6. LOGGING
        // Lưu ý: reference_id là mã đơn hàng (Order Code) để dễ tra cứu
        WalletTransaction::create([
            'wallet_id'    => $wallet->id,
            'type'         => 'payment',
            'amount'       => -($amount), // Số âm
            'status'       => 'success',
            'reference_id' => $orderReference, 
            'description'  => $note ?: "Thanh toán đơn hàng " . $orderReference
        ]);

        return true;
    }
}
?>