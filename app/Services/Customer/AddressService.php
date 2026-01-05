<?php
namespace App\Services\Customer;

use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class AddressService{
    public function createAddress($userId,$data){
        return DB::transaction(function() use($userId,$data){
            // 1. Logic UX: Nếu đây là địa chỉ ĐẦU TIÊN của user
            // Thì BẮT BUỘC nó phải là default (Dù FE có gửi false lên cũng kệ)
            $count = UserAddress::where('user_id', $userId)->count();
            if($count === 0){
                $data['is_default'] = true;
            }

            // 2. Logic "Truất ngôi": Nếu input yêu cầu set default
            if(isset($data['is_default']) && $data['is_default'] === true){
                // Reset toàn bộ địa chỉ cũ của user này về false
                UserAddress::where('user_id', $userId)
                            ->update(['is_default' => false]);
            }

            // 3. Tạo mới an toàn
            return UserAddress::create([
                'user_id' => $userId,
                'recipient_name' => $data['recipient_name'],
                'phone'          => $data['phone'],
                'province_id'    => $data['province_id'],
                'district_id'    => $data['district_id'],
                'ward_id'        => $data['ward_id'],
                'address_detail' => $data['address_detail'],
                'is_default'     => $data['is_default'] ?? false,
                'is_active'      => true,
            ]);
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

    public function updateAddress(int $userId, int $addressId, array $data)
    {
    return DB::transaction(function () use ($userId, $addressId, $data) {
        // 1. Tìm địa chỉ và đảm bảo nó thuộc về user này
        $address = UserAddress::where('user_id', $userId)->findOrFail($addressId);

        // 2. Xử lý logic "Tranh ngôi" (Set Default)
        if (isset($data['is_default']) && $data['is_default'] === true) {
            // Nếu user muốn set cái này làm mặc định -> Reset tất cả cái KHÁC về false
            // Lưu ý: where('id', '!=', $addressId) để tránh update thừa chính nó
            UserAddress::where('user_id', $userId)
                        ->where('id', '!=', $addressId)
                        ->update(['is_default' => false]);
        }
        
        // *Edge Case (Nâng cao): Nếu user cố tình set is_default = false cho thằng đang là default?
        // Thường chúng ta sẽ CHẶN hoặc LỜ ĐI. Quy tắc là: 
        // "Muốn bỏ default cũ thì hãy set default cho cái mới. Không được để trống ngôi vua."
        // Đoạn code dưới đây sẽ ép logic đó (nếu frontend gửi false cho thằng đang true, ta bỏ qua field đó)
        if ($address->is_default && isset($data['is_default']) && $data['is_default'] === false) {
             unset($data['is_default']); // Không cho phép tắt default thủ công
        }

        // 3. Thực hiện Update
        $address->update($data);

        return $address;
        });
    }
}
