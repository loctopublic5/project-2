<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Customer\CartService;
use App\Http\Resources\Cart\CartResource;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Resources\Cart\CartItemResource;
use App\Http\Requests\Cart\UpdateCartItemRequest;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(protected CartService $cartService){}
    
    /**
     * GET /customer/cart
     * Lấy thông tin giỏ hàng của user hiện tại
     */
    public function index(Request $request){
        try{
            $userId = $request->user()->id;
            $params = [
                'voucher_code' => $request->query('voucher_code'),
                'address_id'   => $request->query('address_id')
            ];

            $data = $this->cartService->getCartDetail($userId, $params);

            return $this->success(new CartResource($data), 'Lấy thông tin giỏ hàng thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * POST /customer/cart
     * Thêm sản phẩm vào giỏ hàng
     */
    public function store(AddToCartRequest $request){
        try{
            $userId = $request->user()->id;
            $data = $request->validated();

            $result = $this->cartService->addToCart(
                $userId,
                $data['product_id'],
                $data['quantity'],
                $data['options'] ?? []
            );
            return $this->success(new CartItemResource($result), 'Thêm sản phẩm vào giỏ hàng thành công');

        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * PUT /customer/cart/{itemId}
     * Cập nhật số lượng / options của 1 item trong giỏ hàng
     */
    public function update(UpdateCartItemRequest $request, $id){
        try{
            $userId = $request->user()->id;
            $data = $request->validated();
            
            $result = $this->cartService->updateCartItem(
                $userId,
                $id,
                $data['quantity'] ?? null, 
                $data['options'] ?? null
            );
            return $this->success(new CartItemResource($result), 'Cập nhật giỏ hàng thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * DELETE /customer/cart/{itemId}
     * Xóa 1 sản phẩm khỏi giỏ hàng
     */
    public function destroy(Request $request, $id){
        try{
            $userId = $request->user()->id;
            $this->cartService->removeItem($userId, $id);
            return $this->success(null, 'Xóa sản phẩm khỏi giỏ hàng thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * DELETE /customer/cart
     * Xóa tất cả sản phẩm khỏi giỏ hàng
     */
    public function clear(Request $request){
        try{
            $userId = $request->user()->id;
            $this->cartService->clearSelectedItems($userId);
            return $this->success(null, 'Xóa tất cả sản phẩm khỏi giỏ hàng thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
