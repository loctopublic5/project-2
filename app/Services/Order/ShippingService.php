<?php 
namespace App\Services\Order;
use Illuminate\Support\Str;

class ShippingService{

    // Định nghĩa bảng giá cứng (Hardcoded Config)
    protected $fees = [
        'INNER_CITY' => 15000, // HN, HCM
        'MAJOR_CITY' => 25000, // ĐN, HP...
        'REMOTE'     => 35000  // Mặc định
    ];

    /**
     * Hàm tính phí vận chuyển
     * @param mixed $addressData: Có thể là ID tỉnh thành hoặc Tên tỉnh thành
     */
    public function calculateShippingFee($addressData){

        // BƯỚC 1: CHUẨN HÓA DỮ LIỆU ĐẦU VÀO
        // Check input: Nếu truyền vào là array thì lấy key 'city', nếu là string thì dùng luôn
        // Dùng toán tử ?? '' để tránh lỗi nếu key không tồn tại
        $rawCity = is_array($addressData) ? ($addressData['city'] ?? ''): $addressData;

        // [SENIOR TRICK]: Dùng Str::slug để chuẩn hóa tiếng Việt
        // Ví dụ: "Hà Nội" -> "ha-noi", "TP. Hồ Chí Minh" -> "tp-ho-chi-minh"
        $citySlug = Str::slug($rawCity);

        // BƯỚC 2: LOGIC TÌM KIẾM (MATCHING)
        // Dùng str_contains để bắt dính chính xác hơn (VD: "thanh-pho-ha-noi" vẫn dính "ha-noi")

        // Case 1: Nội thành trọng điểm
        if (Str::contains($citySlug, ['ha-noi', 'ho-chi-minh', 'saI-gon'])){
            return $this->fees['INNER_CITY'];
        }
        // Case 2: Thành phố lớn
        else if (Str::contains($citySlug, ['da nang', 'hai phong', 'can tho'])){
            return $this->fees['MAJOR_CITY'];
        }
        // Case 3: Các tỉnh còn lại (Fallback)
        else{
            return $this->fees['REMOTE'];
        }
    }

}
?>