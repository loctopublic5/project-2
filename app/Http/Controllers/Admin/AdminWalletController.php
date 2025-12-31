<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Services\Customer\WalletService;
use App\Http\Requests\Customer\RefundRequest;
use App\Http\Resources\Customer\TransactionResource;

class AdminWalletController
{
    
    use ApiResponse;
    
    public function __construct(protected WalletService $walletService){}

    // API: Hoàn tiền (Refund)
    public function refund(RefundRequest $request){
        try{
            // 1. Lấy data validate
            $data = $request->validated();
            
            // 2. Tìm User cần hoàn tiền
            $targetUser = User::find($data['user_id']);

            // 3. Gọi Service hoàn tiền
            // Service sẽ lo việc Transaction, Locking, Cộng tiền
            $refundTrans = $this->walletService->refund($targetUser, $data);

            // 4. Trả về kết quả
            return $this->success(
                new TransactionResource($refundTrans),
                'Đã hoàn tiền thành công cho user');

        }catch (Exception $e){
            return $this->error($e->getMessage(), 400);}
    }
}
