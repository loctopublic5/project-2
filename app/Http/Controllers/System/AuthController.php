<?php

namespace App\Http\Controllers\System;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\System\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Exceptions\BusinessException;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\StoreRegisterRequest;

class AuthController
{
    use ApiResponse;
    public function __construct(protected AuthService $auth_service) {}

    public function register(StoreRegisterRequest $request){
        try{
            $data = $request->validated();
            $result = $this->auth_service->register($data);
            return $this->success(new AuthResource($result), 'Đăng ký thành công!', 201);
        } catch(BusinessException $e){
            return $this->error($e->getMessage(), 401);
        } catch(Exception $e){
            return $this->error($e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request)
{
    try {
        // 1. Lấy data sạch (đã validate xong ở lớp Request)
        $credentials = $request->validated(); 

        // 2. Gọi Service (Giao việc)
        $result = $this->auth_service->login($credentials);

        // 3. Trả về thành công 
        return $this->success(new AuthResource($result), 'Bạn đã Đăng nhập thành công!', 200);

    } catch (BusinessException $e) {
        // 4. Bắt lỗi logic nghiệp vụ (VD: Sai pass, khóa acc)
        // Trả về code 401 hoặc 400 tùy message
        return $this->error($e->getMessage(), 401);
    } catch (Exception $e) {
        // 5. Bắt lỗi hệ thống không mong muốn (500)
        return $this->error($e->getMessage(), 500);
    }
}

    public function logout(Request $request){
        $user = $request->user();
        $this->auth_service->logout($user);
        return $this->success(null, 'Đăng xuất thành công.', 200);
    }

    public function forgotPassword(Request $request)
    {
    
    // 1. Validate Input
    // $request->validate(['email' => 'required|email']);
    $request->validate(['email'=> 'required|email']);
    // 2. Gọi Service
    // $this->authService->forgotPassword($request->email);
    $this->auth_service->forgotPassword($request->email);
    // 3. Trả về JSON Success
    return $this->success(null, 'Vui lòng kiểm tra email để lấy lại mật khẩu.',200);
    }

    public function resetPassword(ResetPasswordRequest $request){
        $data = $request->validated();
        $this->auth_service->resetPassword(
            $data['email'],
            $data['token'],
            $data['password']
        );
        return response()->json([
            'status' => true,
            'message' => 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay bây giờ.'
        ]);
    }
}
