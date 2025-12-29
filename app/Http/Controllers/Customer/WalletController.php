<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\BusinessException;
use App\Services\Customer\WalletService;
use App\Http\Requests\Customer\DepositRequest;
use App\Http\Resources\Customer\WalletResource;
use App\Http\Resources\Customer\TransactionResource;

class WalletController
{
    use ApiResponse;

    public function __construct(protected WalletService $walletService){}

    public function getBalance(Request $request){
        try{
            $userId = Auth::id();
            $result = $this->walletService->checkBalance($userId);
            return $this->success(new WalletResource($result), 'Ví của bạn:', 200);
        } catch (BusinessException $e) {
        // 4. Bắt lỗi logic nghiệp vụ 
        // Trả về code 401 hoặc 400 tùy message
        return $this->error($e->getMessage(), 400);
        } catch (Exception $e) {
        // 5. Bắt lỗi hệ thống không mong muốn (500)
        return $this->error($e->getMessage(), 500);
        }
    }

    public function history(Request $request){
        try{
            $userId = Auth::id();
            $fillter = $request->all();
            $result = $this->walletService->getHistory($userId,$fillter, 10);
            return $this->success(new TransactionResource($result), 'Lịch sử giao dịch của bạn:', 200);
        } catch (BusinessException $e) {
        // 4. Bắt lỗi logic nghiệp vụ 
        // Trả về code 401 hoặc 400 tùy message
        return $this->error($e->getMessage(), 400);
        } catch (Exception $e) {
        // 5. Bắt lỗi hệ thống không mong muốn (500)
        return $this->error($e->getMessage(), 500);
        }
    }

}
