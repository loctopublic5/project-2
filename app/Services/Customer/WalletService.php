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

    public function getHistory($userId, $filters = [], $perPage = 10){
        $query = WalletTransaction:: where('user_id', $userId);
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
    public function processPayment(User $user, Array $data){
        // BƯỚC 1: PRE-CHECK (Tối ưu hiệu năng)
        // Check "nhẹ" trước khi mở Transaction nặng nề
        if ($user->wallet->balance < $data['amount']){
            throw new InsufficientBalanceException("Số dư không đủ (Pre-check)");}

        DB::beginTransaction();
        try{
            // BƯỚC 2: LOCKING (Pessimistic Lock)
            // Khóa ví lại, không ai được sửa lúc này
            $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

            // BƯỚC 3: DOUBLE CHECK (SỐNG CÒN)
            // Tại sao? Vì giữa lúc Pre-check (Bước 1) và lúc Lock (Bước 2),
            // có thể transaction khác đã trừ sạch tiền rồi.
            if ($wallet->balance < $data['amount']){
                throw new InsufficientBalanceException("Số dư không đủ (Real-time Check)");}
            
            $code = $this->generateUniqueCode(WalletTransaction::class, 'code', 'PAY');

            // BƯỚC 4: EXECUTION (Trừ tiền)
            $wallet->balance -= $data['amount'];
            $wallet->save();

            // BƯỚC 5: LOGGING
            $trans = WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'code'         => $code,
                'type'         => 'payment',
                'amount'       => -($data['amount']), // Số âm
                'status'       => 'success', // Payment nội bộ thì Success luôn
                'reference_id' => $data['order_id'],
                'description'  => !empty($data['note']) ? "Thanh toán: " . $data['note'] : "Thanh toán đơn hàng"
            ]);

            DB::commit();
            return $trans;

            } catch (Exception $e){
                DB::rollBack();
                throw $e;
            }

    }

    public function refund(User $user, Array $data){

    DB::beginTransaction();
        try{
            // 1. Lock Ví (Bắt buộc)
            // Bất kỳ thao tác ghi nào vào balance đều phải Lock để tránh Race Condition
            $wallet = UserWallet::where('user_id', $user->id)->lockForUpdate()->first();

            // 2. Logic nghiệp vụ (Optional)
            // Có thể check xem số tiền hoàn có lớn hơn giá trị đơn gốc không?
            // (Cần query Order Service - nhưng ở đây ta bỏ qua cho đơn giản)

            $code = $this->generateUniqueCode(WalletTransaction::class, 'code', 'REF');

            // 3. Cộng tiền lại
            $wallet->balance += $data['amount'];
            $wallet->save();

            // 4. Log Transaction Refund
            $trans = WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'code'         => $code,
                'type'         => 'refund',
                'amount'       => $data['amount'], // Số dương
                'status'       => 'success',
                'reference_id' => $data['original_order_id'], // Truy vết đơn nào
                'description'  => !empty($data['reason']) ? "Hoàn tiền: " . $data['reason'] : "Hoàn tiền hủy đơn"
            ]);

            DB::commit();
            return $trans;

            } catch(Exception $e){
                DB::rollBack();
                throw $e;
            }
    }


    /**
     * Logic Nạp tiền
     */
    // Method 1: Tạo yêu cầu (Bước này chưa cộng tiền)
    public function createDepositRequest(User $user, Array $data){    // 1. Validate logic cơ bản
        if ($data['amount'] <= 0){throw new Exception("Số tiền không hợp lệ");}

        // 2. Tạo Transaction PENDING
        $code = $this->GenerateUniqueCode(
            WalletTransaction::class,
            'code',
            'DEP',
        );
        $transaction = WalletTransaction::create([
            'wallet_id' => $user->wallet->id,
            'type'      => 'deposit',
            'amount'    => $data['amount'],
            'status'    => 'pending', // Chờ duyệt
            'code'      => $code,
            'descrption'=> $data['description'] ?? 'Nạp tiền vào ví'
        ]);
    
        return $transaction;
    }

    // Method 2: Duyệt (Hàm này có thể dùng cho Admin hoặc Mock Auto)
    public function forceApprove( $transactionId){
        DB::beginTransaction();
        try{
            // 1. Lock Transaction
            $trans = WalletTransaction::where('id', $transactionId)->lockForUpdate()->first();
        
            if ($trans->status !== 'pending'){ throw new Exception("Giao dịch không hợp lệ");}

            // 2. Lock Ví & Cộng tiền
            $wallet = UserWallet::where('id', $trans->wallet_id)->lockForUpdate()->first();
            $wallet->balance += $trans->amount;
            $wallet->save();

            // 3. Update Status
            $trans->status = 'success';
            $trans->save();
        
        DB::commit();
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
?>