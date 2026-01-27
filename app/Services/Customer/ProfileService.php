<?php
namespace App\Services\Customer;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function updateAvatar($userId, $file)
    {
        $user = User::findOrFail($userId);

        DB::beginTransaction();
        try {
            if ($file) {
                // 1. Xóa ảnh cũ nếu tồn tại trong disk 'public'
                // Giả sử avatar_url lưu path tương đối như: uploads/avatars/2024/05/name.jpg
                if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                    Storage::disk('public')->delete($user->avatar_url);
                }

                // 2. Đặt tên file và đường dẫn theo format Y/m giống Product
                $fileName = $file->hashName();
                $folderPath = 'uploads/avatars/' . date('Y/m');

                // 3. Lưu file vật lý
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                
                // 4. Cập nhật vào Database
                $user->update([
                    'avatar_url' => $path
                ]);
            }

            DB::commit();
            return $user->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateInfo($id, array $data)
{
    $user = User::findOrFail($id);

    DB::beginTransaction();
    try {
        // Chỉ cập nhật các trường được phép
        $user->update([
            'full_name' => $data['full_name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? $user->phone,
        ]);

        DB::commit();
        return $user->refresh();
    } catch (Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
}