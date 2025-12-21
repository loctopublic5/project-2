<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;

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
            $data['last_login_at'] = now();
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
            throw ValidationException::withMessages(['email' => ['Thông tin đăng nhập không đúng.']]);
        }
        // Nếu sai -> Ném lỗi ValidationException (để trả về 401/422 chuẩn)
        // throw ValidationException::withMessages(['email' => 'Thông tin đăng nhập không đúng.']);

        // 3. Kiểm tra User có đang hoạt động không (cột `is_active`) [cite: 13]
        // Nếu $user->is_active == false -> Throw Exception 'Tài khoản đã bị khóa'
        if(!$user->is_active){
            throw ValidationException::withMessages(['email' => ['Tài khoản của bạn đã bị khóa.'],
            ]);
        }
        $user->last_login_at = now(); 
        $user->save();
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
    
    /**
     * Logout logic
    * Input: User Object
    * Output: bool (hoặc void)
    */
    public function logout(User $user): bool
    {   
    // Lấy token hiện tại
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        // Kiểm tra chắc chắn token tồn tại trước khi xóa (phòng trường hợp gọi logout khi chưa login - dù middleware đã chặn rồi nhưng cứ chắc cốp)
        if ($token) {
            return $token->delete();
        }
        return false;
    }


    public function forgotPassword(string $email): bool
    {
    // 1. Tìm User theo email trong bảng users
    $user = User::where('email', $email)->first();
    
    // 2. Check: Nếu không thấy user -> Throw Exception ("Email không tồn tại")
    // (Dev mode thì throw lỗi, Product thì nên return true giả để chống User Enumeration)
    if (!$user) {
    // Trả về lỗi 404 nhưng có message tiếng Việt
    throw new Exception("Không tìm thấy người dùng nào với email: " . $email, 404);
}
    // 3. Xóa token cũ của email này (nếu có) để tránh rác trong bảng `password_reset_tokens`
    DB::table('password_reset_tokens')->where('email', $email)->delete();
    // 4. Tạo Token ngẫu nhiên (chuỗi Raw)
    $token = Str::random(60);
    // 5. Lưu vào DB `password_reset_tokens`
    // Cần lưu: 'email', 'token' (Nhớ Hash::make($token)), 'created_at' (now())
    DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => Hash::make($token),
        'created_at' => NOW(),
    ]);
    // 6. Gửi Mail
    // Truyền $token (Raw - chưa hash) vào Mailable
    Mail::to($email)->send(new ResetPasswordMail($token));
    return true;
    }
}