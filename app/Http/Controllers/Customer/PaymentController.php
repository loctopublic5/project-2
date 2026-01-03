<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Models\Order;
use App\Traits\apiResponse;
use App\Services\Customer\WalletService;
use App\Http\Requests\Customer\PaymentRequest;
use App\Exceptions\InsufficientBalanceException;
use App\Http\Resources\Customer\TransactionResource;

class PaymentController
{
    use apiResponse;
    
    // Inject cả 2 Service hoặc Model cần thiết
    public function __construct(protected WalletService $walletService){}
    
    public function payByWallet(PaymentRequest $request){
        try{
            $user = $request->user();
            $data = $request->validated();

            // 1. TÌM ĐƠN HÀNG (Security Check)
            // Phải đảm bảo đơn hàng đó là của chính user này
            $order = Order::where('id', $data['order_id'])->where('user_id', $user->id)->first();

            if(!$order){ throw new Exception("Đơn hàng không tồn tại", 404);}

            // 2. CHECK TRẠNG THÁI ĐƠN
            if ($order->payment_status === 'paid'){
                throw new Exception("Đơn hàng này đã được thanh toán rồi", 400);}
            
            if ($order->status === 'cancelled'){
                throw new Exception("Đơn hàng này đã bị hủy", 400);}

            // 3. CHUẨN BỊ DỮ LIỆU THANH TOÁN
            // Quan trọng: Lấy amount từ DB (order->total), KHÔNG lấy từ request
            $paymentData = [
                'amount'   => $order->total_amount, 
                'order_id' => $order->id,
                'note'     => $data['note'] ?? "Thanh toán đơn hàng " . $order->code
            ];

            // 4. GỌI WALLET SERVICE TRỪ TIỀN
            // Hàm này đã có Transaction, Lock, Double Check như ta đã viết
            $transaction = $this->walletService->processPayment($user, $paymentData);

            // 5. UPDATE TRẠNG THÁI ĐƠN HÀNG (Post-Payment Action)
            // Nếu bước 4 không lỗi, nghĩa là tiền đã trừ. Giờ ta update đơn.
            $order->payment_status = 'paid';
            $order->payment_method = 'wallet';
            $order->save();

            // 6. TRẢ VỀ KẾT QUẢ
            return $this->success(
                [
                'order_code'=> $order->code,
                'remaining_balance'=> $user->wallet->balance, // Số dư còn lại
                'transaction'=> new TransactionResource($transaction)
                ],
                'Thanh toán thành công'
            );

        }catch (InsufficientBalanceException $e){
            // Bắt riêng lỗi thiếu tiền để FE hiển thị popup nạp tiền
            return $this->error($e->getMessage(), 400);

        }catch (Exception $e){
            return $this->error($e->getMessage(), 500);}

    }
}
