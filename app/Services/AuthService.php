<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthService
{
    /**
     * Logic Đăng Ký
     * Input: Dữ liệu đã validate từ Controller ($request->validated())
     */
    public function register(array $data): array
    {
        // 1. Bắt đầu Transaction để đảm bảo tính toàn vẹn (ACID)
        // Keyword: DB::beginTransaction();
        DB::beginTransaction();

        try {
            // 2. Hash mật khẩu
            // Keyword: $data['password'] = Hash::make(...);
            $data['password'] = Hash::make($data['password']);
            // 3. Tạo User vào bảng `users`
            // Keyword: User::create($data);
            // $user = ...
            $user = User::create($data);
            // 4. Lấy Role 'customer' từ bảng `roles` dựa vào slug
            // Keyword: Role::where('slug', 'customer')->first();
            // Lưu ý: Nếu không tìm thấy role thì phải ném lỗi hoặc tạo default.
            $role = Role::where('slug','customer')->first();
            if (! $role) {
                throw new Exception("Hệ thống chưa cấu hình vai trò 'customer'. Vui lòng liên hệ Admin.");
            }
            // 5. Gán Role cho User (Bảng trung gian `user_roles`)
            // Keyword: $user->roles()->attach($role->id);
            $user->roles()->attach($role->id);
            $token = $user->createToken('auth_token')->plainTextToken;
            // 6. Commit Transaction (Lưu chính thức)
            // Keyword: DB::commit();
            DB::commit();
            return [
            'user' => $user,
            'access_token' => $token,
            'roles' => ['customer'], 
            ];
            

        } catch (Exception $e) {
            // 7. Nếu có lỗi -> Rollback (Hoàn tác mọi thứ)
            // Keyword: DB::rollBack();
            DB::rollBack();
            throw $e; // Ném lỗi ra để Controller xử lý tiếp
        }
    }

    /**
     * Logic Đăng Nhập
     * Input: ['email' => '...', 'password' => '...']
     */
    public function login(array $credentials): array
    {
        // 1. Tìm User theo email
        // Keyword: User::where('email', ...)->first();
        // $user = ...
        $user= User::where('email',$credentials['email'])->first();
        // 2. Kiểm tra User có tồn tại VÀ Mật khẩu có khớp không
        // Keyword: !$user || !Hash::check(...)
        if(!$user || !Hash::check($credentials['password'], $user->password)){
            throw ValidationException::withMessages(['email' => ['Thông tin đăng nhập không đúng.'],]);
        }
        // Nếu sai -> Ném lỗi ValidationException (để trả về 401/422 chuẩn)
        // throw ValidationException::withMessages(['email' => 'Thông tin đăng nhập không đúng.']);

        // 3. Kiểm tra User có đang hoạt động không (cột `is_active`) [cite: 13]
        // Nếu $user->is_active == false -> Throw Exception 'Tài khoản đã bị khóa'
        if(!$user->is_active){
            throw ValidationException::withMessages(['email' => ['Tài khoản của bạn đã bị khóa.'],
            ]);
        }
        // 4. Tạo Token (Sanctum)
        // Keyword: $token = $user->createToken('auth_token')->plainTextToken;
        $token = $user->createToken('auth_token')->plainTextToken;
        // 5. Lấy danh sách Role (Eager Loading) để trả về cho Frontend
        // Keyword: $user->load('roles');
        // $roles = $user->roles->pluck('slug');
        $user->load('roles');
        $roles = $user->roles->pluck('slug');
        // 6. Trả về mảng dữ liệu
        return [
            'user' => $user,
            'access_token' => $token,
            'roles' => $roles, // Trả về list roles (VD: ['customer'])
            'token_type' => 'Bearer',
        ];
    }
}