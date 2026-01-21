<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. SAFETY CHECK (Phòng thủ)
        // Dù Service đã eager load, nhưng lỡ sản phẩm bị Soft Delete hoặc lỗi data
        // thì $this->product có thể null. Check để tránh crash API 500.
        $product = $this->product;

        if (!$product) {
            return [
                'item_id' => $this->id,
                'is_error' => true,
                'error_message' => 'Sản phẩm không còn tồn tại.',
                'product_info' => null,
                // ... trả về null các trường khác để FE không bị gãy
            ];
        }

        // 2. LOGIC GIÁ (Price Logic)
        // Cast về float để đảm bảo FE nhận số, không phải string "10000.00"
        $realPrice = (float) (($product->sale_price && $product->sale_price > 0) 
                        ? $product->sale_price 
                        : $product->price);
        
        $originalPrice = (float) $product->price;

        // 3. LOGIC TỒN KHO & TRẠNG THÁI (Advanced Stock Check)
        $stockError = null;
        
        // Case A: Sản phẩm bị Admin ẩn/ngừng kinh doanh
        if (!$product->is_active) {
            $stockError = 'Sản phẩm đã ngừng kinh doanh.';
        } 
        // Case B: Kho không đủ số lượng khách muốn mua
        elseif ($product->stock_qty < $this->quantity) {
            $stockError = "Kho chỉ còn {$product->stock_qty} sản phẩm.";
        }
        // Case C: Kho hết sạch (0)
        elseif ($product->stock_qty <= 0) {
            $stockError = "Sản phẩm đang tạm hết hàng.";
        }

        return [
            'item_id'      => $this->id,
            'product_id'   => $this->product_id,
            
            'product_info' => [
                'name'   => $product->name,
                'slug'   => $product->slug,
                'sku'    => $product->sku,
                'avatar' => $this->getAvatarUrl($product),
            ],

            'price'        => $realPrice,
            'old_price'    => $originalPrice,
            
            'quantity'     => (int) $this->quantity,
            'options'      => $this->options ?? [], // Luôn trả về mảng, tránh null
            'selected'     => (bool) $this->selected,
            
            'line_total'   => $realPrice * $this->quantity,
            
            // --- CỜ BÁO HIỆU CHO FE (QUAN TRỌNG) ---
            // Nếu có error -> FE sẽ hiện viền đỏ hoặc disable checkbox/nút checkout
            'is_error'      => !is_null($stockError),
            'error_message' => $stockError,
            
            // Thông tin kho để FE limit input số lượng (Max = stock_qty)
            'max_qty'       => (int) $product->stock_qty,
        ];
    }
    private function getAvatarUrl($product) {
    // Eager load quan hệ 'images' để lấy file
    $image = $product->images->first(); 
    if ($image && $image->path) {
        // Trả về URL đầy đủ. Theo ERD lưu tại public/storage/upload 
        return asset('storage/' . $image->path);
    }
    return asset('admin_assets/assets/compiled/jpg/1.jpg');
}
}