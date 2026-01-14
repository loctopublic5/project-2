<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Resources\Admin\UserResource;
use App\Services\Dashboard\UserService;
use Illuminate\Http\Request;
use Exception;

class AdminUserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // 1. Danh sách Users
    public function index(Request $request)
    {
        $users = $this->userService->getUsers($request->all());
        
        return UserResource::collection($users)->additional([
            'status' => true,
            'message' => 'Lấy danh sách người dùng thành công'
        ]);
    }

    // 2. Chi tiết User
    public function show($id)
    {
        try {
            $user = $this->userService->getUserDetail($id);
            return response()->json([
                'status' => true,
                'data' => new UserResource($user)
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy người dùng'], 404);
        }
    }

    // 3. Cập nhật trạng thái (Block/Unblock)
    public function updateStatus(UpdateUserStatusRequest $request, $id)
    {
        try {
            $user = $this->userService->updateStatus($id, $request->is_active);
            
            $statusText = $user->is_active ? 'Mở khóa' : 'Đã khóa';
            return response()->json([
                'status' => true,
                'message' => "Tài khoản {$user->full_name} đã được {$statusText}",
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 4. Analytics (Dashboard Data)
    public function analytics()
    {
        $data = $this->userService->getAnalytics();

        // Transform collection bên trong data analytics sang Resource để ẩn thông tin nhạy cảm
        return response()->json([
            'status' => true,
            'message' => 'Lấy dữ liệu thống kê thành công',
            'data' => [
                'stats' => $data['stats'],
                'top_spenders' => UserResource::collection($data['top_spenders']),
                'new_customers' => UserResource::collection($data['new_customers_list']),
            ]
        ]);
    }
}