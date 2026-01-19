<?php
namespace App\Services\Customer;

use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class AddressService{
    public function createAddress($userId, $data) {
    return DB::transaction(function() use($userId, $data) {
        // 1. Chuẩn hóa giá trị boolean ngay từ đầu
        $isDefault = isset($data['is_default']) ? filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN) : false;

        // 2. Kiểm tra nếu là địa chỉ đầu tiên
        $count = UserAddress::where('user_id', $userId)->count();
        if($count === 0) {
            $isDefault = true;
        }

        // 3. Nếu thằng này là mặc định, reset những thằng khác
        if($isDefault === true) {
            UserAddress::where('user_id', $userId)->update(['is_default' => false]);
        }

        // 4. Tạo mới
        return UserAddress::create([
            'user_id'        => $userId,
            'recipient_name' => $data['recipient_name'],
            'phone'          => $data['phone'],
            'province_id'    => $data['province_id'],
            'district_id'    => $data['district_id'],
            'ward_id'        => $data['ward_id'],
            'address_detail' => $data['address_detail'],
            'is_default'     => $isDefault, // Sử dụng biến đã chuẩn hóa
            'is_active'      => true,
        ]);
    });
    }

    public function updateAddress(int $userId, int $addressId, array $data)
    {
        return DB::transaction(function () use ($userId, $addressId, $data) {
            // 1. Tìm địa chỉ (IDOR Protection)
            $address = UserAddress::where('user_id', $userId)->findOrFail($addressId);

            // 2. Chuẩn hóa Boolean (Safe Casting)
            // Chuyển mọi thể loại "1", "true", 1 về true/false chuẩn của PHP
            $wantsToBeDefault = isset($data['is_default']) 
                                ? filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN) 
                                : null;

            // 3. Xử lý Logic "Tranh ngôi" (Set Default)
            if ($wantsToBeDefault === true) {
                // Nếu muốn làm vua -> Truất ngôi tất cả thằng khác
                UserAddress::where('user_id', $userId)
                    ->where('id', '!=', $addressId)
                    ->update(['is_default' => false]);
            }

            // 4. Xử lý Edge Case: "Vua không được tự thoái vị"
            // Nếu đang là Default mà user gửi lên false -> Lờ đi (Xóa khỏi data update)
            // Hoặc nếu user không gửi is_default -> Cũng không sao, Eloquent không update field đó
            if ($address->is_default && $wantsToBeDefault === false) {
            unset($data['is_default']); 
            }

            // 5. Cập nhật dữ liệu
            // Gán lại giá trị chuẩn boolean vào data để Eloquent lưu cho đúng (tránh lưu string "true")
            if (isset($data['is_default'])) {
                $data['is_default'] = $wantsToBeDefault;
            }
            
            $address->update($data);

            return $address;
        });
    }

    public function deleteAddress(int $userId, int $addressId)
    {
        return DB::transaction(function () use ($userId, $addressId) {
            $address = UserAddress::where('user_id', $userId)->findOrFail($addressId);
            $wasDefault = $address->is_default;

            // 1. Xóa địa chỉ
            $address->delete();

            // 2. Nếu vừa xóa địa chỉ mặc định -> Phải tìm cái thay thế
            if($wasDefault){
                // Lấy địa chỉ được update gần nhất còn lại
            $newDefault = UserAddress::where('user_id', $userId)
                                        ->orderBy('updated_at', 'desc')
                                        ->first();
            
            // Nếu còn địa chỉ khác -> Set nó làm default mới
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
            }
        });
    }

    public function getUserAddresses(int $userId)
    {
    return UserAddress::where('user_id', $userId)
        // Ưu tiên 1: Đưa thằng Default lên đầu (is_default = 1 xếp trước 0)
        ->orderByDesc('is_default') 
        // Ưu tiên 2: Các địa chỉ mới sửa gần đây xếp tiếp theo
        ->orderByDesc('updated_at')
        ->get();
    }

    // Hàm lấy chi tiết 1 cái (để hiện lên Form sửa)
    public function getAddressDetail(int $userId, int $addressId)
    {
        // Quan trọng: Phải check user_id để tránh user A xem trộm địa chỉ user B
        return UserAddress::where('user_id', $userId)->findOrFail($addressId);
    }

    
}
