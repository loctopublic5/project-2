<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Customer\ProfileService;
use App\Http\Resources\Admin\UserResource;
use App\Http\Requests\Customer\UpdateAvatarRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;

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
    public function updateInfo(UpdateProfileRequest $request, $id)
{
    try {
        $data = $request->validated();
        $updatedUser = $this->profileService->updateInfo($id, $data);

        return $this->success(
            new UserResource($updatedUser),
            'Cập nhật thông tin tài khoản thành công!'
        );
    } catch (Exception $e) {
        return $this->error('Có lỗi xảy ra: ' . $e->getMessage(), 500);
    }
}

public function triggerResetPassword(Request $request)
{
    try {
        $user = User::findOrFail($request->user_id);
        
        // Tạo URL dẫn đến route 'password.request' (quên mật khẩu)
        // Kèm theo email để user không phải nhập lại
        $resetUrl = route('password.request') . '?email=' . urlencode($user->email);

        return $this->success([
            'redirect_url' => $resetUrl
        ], 'Đang chuyển hướng đến trang đặt lại mật khẩu...');
    } catch (Exception $e) {
        return $this->error($e->getMessage(), 500);
    }
}
}