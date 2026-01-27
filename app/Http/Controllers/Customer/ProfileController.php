<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Customer\ProfileService;
use App\Http\Resources\Customer\UserResource;
use App\Http\Requests\Customer\UpdateAvatarRequest;

class ProfileController extends Controller
{
    use ApiResponse;
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function updateAvatar(UpdateAvatarRequest $request, $id)
{
    try {
        // Ưu tiên dùng $id từ tham số URL nếu bạn muốn truyền từ JS
        // Hoặc dùng auth()->id() để bảo mật hơn. Ở đây tôi dùng $id cho khớp ý bạn
        $userId = $id; 
        
        // Lấy file trực tiếp từ $request
        $file = $request->file('avatar');

        // Gọi Service xử lý logic
        $updatedUser = $this->profileService->updateAvatar($userId, $file);

        return $this->success(
            new UserResource($updatedUser),
            'Cập nhật ảnh đại diện thành công!'
        );
    } catch (Exception $e) {
        return $this->error($e->getMessage(), 500);
    }
}
}