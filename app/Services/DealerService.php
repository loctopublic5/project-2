<?php

namespace App\Services;

use Exception;
use App\Models\Role; 
use App\Models\DealerRequest;
use App\Mail\DealerApprovedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DealerService
{
    /**
     * Logic duyệt yêu cầu nâng cấp đại lý
     * @param int $requestId ID của yêu cầu
     * @param string $newStatus Trạng thái mới ('approved' hoặc 'rejected')
     * @param string|null $note Ghi chú của Admin (có thể null)
     * @return DealerRequest Trả về object đã update
     */
    public function updateDealerStatus(int $requestId, string $newStatus, ?string $note)
    {
        // --- BƯỚC 1: TÌM DỮ LIỆU ---
        // Hãy tìm DealerRequest theo $requestId.
        // Gợi ý: Dùng hàm `findOrFail` để nếu không thấy nó tự báo lỗi 404 luôn.
        // CODE CỦA BẠN Ở ĐÂY:
        $request = DealerRequest::findOrFail($requestId);

        

        // --- BƯỚC 2: KIỂM TRA ĐIỀU KIỆN (GUARD CLAUSE) ---
        // Kiểm tra xem trạng thái hiện tại của $request có phải là 'pending' không.
        // Nếu KHÔNG phải 'pending' -> Ném ra một `new Exception("Thông báo lỗi...")`.
        // CODE CỦA BẠN Ở ĐÂY:
        if(!$request->status !== 'pending'){
            throw new Exception("Yêu cầu này đã được xử lý trước đó (" . $request->status . ").", 400);
        }

        // --- BƯỚC 3: BẮT ĐẦU TRANSACTION ---
        // Mở giao dịch DB để đảm bảo an toàn dữ liệu.
        // CODE CỦA BẠN Ở ĐÂY:
        DB::beginTransaction();

        try {
            // --- BƯỚC 4: CẬP NHẬT TRẠNG THÁI ---
            // Update bảng dealer_requests với các thông tin:
            // - status: theo biến $newStatus
            // - admin_note: theo biến $note
            // - approved_at: Logic 3 ngôi -> Nếu $newStatus là 'approved' thì lấy thời gian hiện tại (now()), nếu không thì null.
            // CODE CỦA BẠN Ở ĐÂY:
            $request->update([
                'status' => $newStatus,
                'admin_note' => $note,
                'approved_at' => ($newStatus ===  'approved') ? now() :null,
            ]);


            // --- BƯỚC 5: XỬ LÝ LOGIC NẾU LÀ 'APPROVED' ---
            if ($newStatus === 'approved') {
                // 5.1. Lấy User từ request này ra (thông qua relationship user)
                $user = $request->user;

                // 5.2. Tìm Role có slug là 'dealer' trong database (Model Role)
                $dealerRole = Role::where('slug', 'dealer')->first();

                // 5.3. Kiểm tra nếu tìm thấy Role thì mới gán
                if ($dealerRole) {
                    // 5.4. Gán role cho user. 
                    // Gợi ý: Dùng relationship roles() của user. 
                    // Dùng hàm `syncWithoutDetaching([$id])` để thêm role mà không xóa các role cũ.
                     // CODE CỦA BẠN Ở ĐÂY:
                    $user->roles()->syncWithoutDetaching([$dealerRole->id]) ;

                } else {
                    // Log lỗi nếu không tìm thấy role dealer trong DB (để dev biết đường sửa)
                    Log::error("Không tìm thấy Role 'dealer' trong bảng roles");
                }
            }

            // --- BƯỚC 6: LƯU CHÍNH THỨC (COMMIT) ---
            // Xác nhận transaction thành công.
            // CODE CỦA BẠN Ở ĐÂY:
            DB::commit();

            try{
                if ($newStatus = 'approved' && $request->user && $request->user->email){
                    Mail::to($request->user->email)->send(new DealerApprovedMail($request->user));
                }
            }catch (Exception $e) {
                Log::error("Gửi mail thất bại: " . $e->getMessage());
            }
            // --- BƯỚC 7: TRẢ VỀ KẾT QUẢ ---
            return $request;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}