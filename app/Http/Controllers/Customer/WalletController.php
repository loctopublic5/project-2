<?php

namespace App\Http\Controllers\Customer;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\Customer\WalletService;
use App\Http\Requests\Customer\DepositRequest;
use App\Http\Resources\Customer\WalletResource;
use App\Http\Resources\Customer\TransactionResource;

class WalletController
{
    use ApiResponse;

    public function __construct(protected WalletService $walletService){}

    public function deposit(DepositRequest $request){

    try{
        // Bước 1: Lấy dữ liệu sạch từ Request
        // (Amount, Description đã được validate ở lớp Request)
        $data = $request->validated();
        $user = $request->user();

        // Bước 2: Tạo yêu cầu nạp (Status: PENDING)
        // Gọi Service: createDepositRequest($user, $data)
        $transaction = $this->walletService->createDepositRequest($user, $data);

        // Bước 3: [MOCK LOGIC] Tự động duyệt ngay lập tức
        // Bình thường bước này nằm ở WebhookController hoặc AdminController.
        // Nhưng vì demo, ta gọi luôn hàm duyệt tại đây.
        $this->walletService->forceApprove($transaction->id);

        // Bước 4: Lấy số dư mới nhất để trả về cho FE cập nhật UI
        // (Optional: Frontend có thể tự cộng, nhưng Backend trả về là chuẩn nhất)
        $newBalance = $this->walletService->checkBalance($user->id);

        // Bước 5: Trả về Response chuẩn
        // Format: { status: true, message: "...", data: { balance: ... } }
        return $this->success(
            [
                'balance' => $newBalance,
                'transaction_code' => $transaction->code
            ], 
            "Nạp tiền thành công (Demo Mode)"
        );

    }catch (Exception $e){
        // Bước 6: Xử lý lỗi
        // Nếu lỗi ở Bước 2 (Validation logic) hoặc Bước 3 (DB Error)
        // Trait ApiResponse sẽ lo việc format lỗi này
        return $this->error($e->getMessage(), 400);
    }
    }

    public function getMe(Request $request){
    try{
        $user = $request->user();

        // 1. Lấy số dư (Service Logic)
        $balance = $this->walletService->checkBalance($user->id);

        // 2. Lấy lịch sử giao dịch (Có phân trang & Filter)
        $filters = $request->all(); // Truyền params như ?type=deposit&page=1
        $history = $this->walletService->getHistory($user->id, $filters);

        // 3. Chuẩn hóa dữ liệu trả về (Data Shaping)
        $responseData = [
            'balance' => $balance,
            // Wrap history qua Resource Collection để format đẹp (có màu sắc, label)
            'history' => TransactionResource::collection($history)->response()->getData(true)
        ];

        // 4. Trả về
        RETURN $this->success($responseData, "Lấy thông tin ví thành công");

    }catch (Exception $e){
        return $this->error($e->getMessage());
        }
    }
}
