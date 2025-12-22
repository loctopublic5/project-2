<?php

namespace App\Http\Controllers;

use Exception;
use App\Traits\ApiResponse;
use App\Services\DealerService;
use App\Http\Requests\Customer\PostDealerRequestRequest;
use App\Http\Resources\Customer\PostDealerRequestResource;
use App\Http\Requests\Admin\UpdateDealerRequestRequest; 
use App\Http\Resources\Admin\UpdateDealerRequestResource;

class DealerRequestController
{
    use ApiResponse;
    protected $dealerService;
    public function __construct(DealerService $dealerService)
    {
        $this->dealerService = $dealerService;
    }

    /**
     * [USER] Gửi yêu cầu nâng cấp
     * POST /api/v1/user/dealer-request
     */
    public function store(PostDealerRequestRequest $request){
        try{
            $user = $request->user();

            $dealerRequest = $this->dealerService->registerForUpgrade($user);
            return $this->success(new PostDealerRequestResource($dealerRequest));
        } catch(Exception $e){
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * [ADMIN] Duyệt hoặc Từ chối yêu cầu
     * PUT /api/v1/admin/dealer-requests/{id}
     */
    public function updateStatus(UpdateDealerRequestRequest $request, $id){
        try{
            $validData = $request->validated();
            $updatedRequest =  $this->dealerService->updateDealerStatus(
                $id,
                $validData['status'],
                $validData['admin_note'] ?? null
            );
            return $this->success(new UpdateDealerRequestResource($updatedRequest));
        } catch(Exception $e){
            $statusCode = $e->getCode() == 404 ? 404 : 400;
            return $this->error($e->getMessage(),$statusCode);
        }
    }
}

?>