<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Order\AddressService;
use App\Http\Resources\UserAddressResource;
use App\Http\Requests\Customer\SaveAddressRequest;


class AddressController extends Controller
{
    use ApiResponse;
    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    /**
     * GET /api/addresses
     * Lấy danh sách địa chỉ (Default lên đầu)
     */
    public function index(Request $request)
    {
        try{
            $userId = $request->user()->id;
            $addresses = $this->addressService->getUserAddresses($userId);

            // Trả về Collection Resource
            $resource = UserAddressResource::collection($addresses);
            return $this->success($resource, 'Lấy danh sách thành công !');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * POST /api/addresses
     * Thêm mới
     */
    public function store(SaveAddressRequest $request)
    {
        try {
            $userId = $request->user()->id;
            // $request->validated() trả về mảng dữ liệu sạch sau khi qua rules
            $address = $request->validated();

            $result = $this->addressService->createAddress($userId,$address);
            return $this->success(new UserAddressResource($result), 'Tạo thành công địa chỉ.', 201);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * PUT /api/addresses/{id}
     * Cập nhật thông tin
     */
    public function update(SaveAddressRequest $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $addressId = $id;
            $address = $request->validated();

            $result = $this->addressService->updateAddress($userId,$addressId,$address);
            return $this->success(new UserAddressResource($result), 'Cập nhật thành công');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * PATCH /api/addresses/{id}/default
     * API nhanh để set mặc định
     */
    public function setDefault(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            // Tái sử dụng hàm update, chỉ gửi mỗi field is_default
            $address = $this->addressService->updateAddress($userId, $id, ['is_default' => true]);

            return $this->success(new UserAddressResource($address), 'Đã đặt làm địa chỉ mặc định.');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * DELETE /api/addresses/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $this->addressService->deleteAddress($userId, $id);

            return $this->success(null, 'Xóa địa chỉ thành công.');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
