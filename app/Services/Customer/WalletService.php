<?php 
namespace App\Services\Customer;


use Exception;
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
        if ($filters['type']){
            $query->where('type', $filters['type']);
        }
        // Filter theo thời gian
        if ($filters['start_date']){
            $query->whereDate('create_at', '>=', $filters['start_date']);
        }
        // Sắp xếp mới nhất trước
        $query->orderBy('created_at', 'DESC');
        return $query->paginate($perPage);
    }

    /**
 * @param int $userId
 * @param float $amount: Số tiền cần trừ
 * @param string $orderCode: Mã đơn hàng (Để log reference)
 */
    public function deductForPayment($userId, $amount, $orderCode){
    
    DB::beginTransaction();
        try{
            // 1. Lock ví để không ai sửa đổi trong lúc này
            $wallet = UserWallet::where('user_id', $userId)->lockForUpdate()->first();

            // 2. Validate Số dư
            if ($wallet->balance < $amount){
                throw new InsufficientBalanceException("Số dư không đủ để thanh toán.");
            }
            // 3. Trừ tiền
            $wallet->balance -= $amount;
            $wallet->save();

            // 4. Ghi log giao dịch (Audit Trail)
            WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'user_id'      => $userId,
                'type'         => 'payment',    // Loại: Thanh toán
                'amount'       => -($amount),      // Số âm
                'reference_id' => $orderCode,    // Gắn với đơn hàng nào
                'description'  => "Thanh toán đơn hàng " . $orderCode,
                'status'       => 'success'
            ]);

            DB::commit();
            return True;

            } catch (Exception $e){
            DB::rollBack();
            throw $e;
            }
    }

    public function refundOrder($userId, $amount, $orderCode, $reason){
    
    DB::beginTransaction();
        try{
            $wallet = UserWallet::where('user_id', $userId)->lockForUpdate()->first();

            // Cộng lại tiền
            $wallet->balance += $amount;
            $wallet->save();

            // Ghi log hoàn tiền
            WalletTransaction::create([
                'wallet_id'    => $wallet->id,
                'user_id'      => $userId,
                'type'         => 'refund',     // Loại: Hoàn tiền
                'amount'       => $amount,       // Số dương
                'reference_id' => $orderCode,
                'description'  => "Hoàn tiền đơn " . $orderCode . ": " . $reason,
                'status'       => 'success'
            ]);

            DB::commit();
            return true;

            } catch (Exception $e){
            DB::rollBack();
            throw $e;
            }
    }


    /**
     * Logic Nạp tiền
     */
    public function requestDeposit($user, $amount, $description){

        // 1. Validation Logic
        if ($amount <= 0){
            throw new Exception("Số tiền nạp phải lớn hơn 0");
        }
        // 2. Lấy ví của User
        // Vì ta đã chọn tạo ví lúc Register, nên bước này luôn lấy được ví
        $wallet = $user->wallet; 

        if ($wallet->status == 'locked'){
            throw new Exception("Ví đang bị khóa, không thể nạp tiền");
        }
        // 3. Tạo Transaction (Ghi nhận ý định)
        $code = $this->generateUniqueCode(
            WalletTransaction::class, // Model cần check
            'code',                   // Cột cần check
            'DEP',                    // Prefix
            8                         // Độ dài ngẫu nhiên
        );
        $transaction = WalletTransaction::create([
            'wallet_id'    => $wallet->id, 
            'type'         => 'deposit',   
            'amount'       => $amount,     
            'status'       => 'pending',   // Quan trọng: Chờ xử lý [cite: 58]
            'reference_id' =>  $code,   // VD: DEP-20231224 [cite: 49]
            'description'  => $description
        ]);

    // 4. Return
    return $transaction;
    }


    public function approveTransaction($transactionId){

    DB::beginTransaction();
    try{
        // 1. Tìm Transaction
        $transaction = WalletTransaction::find($transactionId);

        // 2. [CRITICAL] Kiểm tra trạng thái (Double Spending Guard)
        // Nếu giao dịch này đã thành công hoặc thất bại trước đó -> DỪNG NGAY
        if ($transaction->status != 'pending'){
            throw new Exception("Giao dịch này đã được xử lý rồi!");
        }
        // 3. Lấy ví và KHÓA DÒNG (Pessimistic Locking)
        // Câu lệnh SQL tương ứng: SELECT * FROM user_wallets WHERE id = ... FOR UPDATE
        // Các tiến trình khác muốn sửa ví này sẽ phải chờ tại dòng này
        $wallet = UserWallet::where('id', $transaction->wallet_id)->lockForUpdate()->first();

        // 4. Cộng tiền (Update Balance)
        $wallet->balance += $transaction->amount;
        $wallet->save();

        // 5. Cập nhật trạng thái Transaction
        $transaction->status = 'success';
        $transaction->save();

        // 6. Ghi Audit Log (Nếu có bảng AuditLogs)
        // Log lại việc Admin nào đã duyệt giao dịch này

        DB::commit();

        return $transaction;

    } catch (Exception $e){
        DB::rollBack();
        throw $e; }
    }
}
?>