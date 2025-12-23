<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver{

    /**
     * Xử lý khi TẠO MỚI (Created Event)
     * @param Model $model Đối tượng vừa được tạo (VD: Product vừa insert xong)
     */
    public function created(Model $model){
        // BƯỚC 1: Thu thập thông tin
        // Lấy tất cả thuộc tính của record mới tạo
        $newValues = $model->getAttributes();

        if (isset($newValues['password'])) {
            $newValues['password'] = '***';
        }

                // BƯỚC 2: Lưu vào bảng audit_logs
        AuditLog::create ([
            'user_id'      => Auth::id(),
            'action'       => 'create',
            'table_name'   => $model->getTable(),
            'record_id'    => $model->getKey(),
            'old_values'   => null,
            'new_values'   => $newValues,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * Xử lý khi CẬP NHẬT (Updated Event)
     * @param Model $model Đối tượng vừa được update
     */
    public function updated(Model $model): void{
        // BƯỚC 1: Tìm các trường bị thay đổi
        // getDirty() trả về mảng: ['tên_cột' => 'giá_trị_mới']
        $dirty = $model->getDirty(); 
        
        $oldValues = [];
        $newValues = [];
        foreach ($dirty as $key => $value){
            // BƯỚC 2: Bỏ qua các trường nhạy cảm & timestamps
            // Dùng hàm in_array để kiểm tra key có nằm trong danh sách đen không
            if ($key === 'password') {
                 $oldValues[$key] = '***'; // Che mật khẩu cũ
                 $newValues[$key] = '***'; // Che mật khẩu mới
            }else if (in_array($key, [ 'remember_token', 'updated_at'])){
                continue;
            } else {
            
            // BƯỚC 3: Lấy giá trị Cũ và Mới
            // $value trong vòng lặp chính là giá trị mới rồi, không cần getAttribute lại
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $value;
            }
        }
        
        // BƯỚC 4: Chỉ ghi log nếu thực sự có dữ liệu quan trọng thay đổi
        // Dùng !empty() để kiểm tra mảng
        if(!empty($newValues)){
            AuditLog::create ([
                'user_id'    => Auth::id(),
                'action'     => 'update',
                'table_name' => $model->getTable(),
                'record_id'  => $model->getKey(),
                'old_values' => $oldValues, 
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Xử lý khi XÓA (Cả Soft Delete và Force Delete đều chạy vào đây)
     */
    public function deleted(Model $model):void{
        // Mặc định action là soft_delete
        $action = 'soft_delete';

        // Kiểm tra xem Model này có dùng SoftDeletes không VÀ có đang bị Force Delete không?
        // Hàm isForceDeleting() trả về true nếu dùng $model->forceDelete()
        if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()){
            $action ='force_delete';
        }

        // Ghi Log
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action, // 'soft_delete' hoặc 'force_delete'
            'table_name' => $model->getTable(),
            'record_id'  => $model->getKey(),

            // Nếu Force Delete: Lưu lại toàn bộ dữ liệu cũ để nhỡ may cần khôi phục bằng tay
            // Nếu Soft Delete: Cũng lưu lại để tiện tra cứu nhanh
            'old_values' => $model->attributesToArray(),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    /**
     * Xử lý khi KHÔI PHỤC (Chỉ dành cho Soft Delete)
     * Khi gọi $model->restore()
     */
    public function restored(Model $model): void{
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'restore',
            'table_name' => $model->getTable(),
            'record_id'  => $model->getKey(),
            'old_values' => ['deleted_at' => $model->getOriginal('deleted_at')], // Trước đó nó có ngày xóa
            'new_values' => ['deleted_at' => null],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
?>