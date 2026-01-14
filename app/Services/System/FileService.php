<?php

namespace App\Services\System;

// Import các Class cần thiết (Tôi để sẵn để bạn đỡ phải tra namespace)
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileService
{
    /**
     * Xử lý upload file trung tâm
     * * @param UploadedFile $file: Object file lấy từ request
     * @param string $targetType: Tên Model (VD: App\Models\User)
     * @param int $targetId: ID của đối tượng
     * @return File: Trả về Model File vừa tạo
     */
    public function upload(UploadedFile $file, string $targetType, int $targetId)
    {
        // --- BƯỚC 1: XỬ LÝ TÊN FILE (NAMING) ---
        $fileName = $file->hashName();


        // --- BƯỚC 2: ĐỊNH NGHĨA THƯ MỤC (PATH) ---
        $folderPath = 'uploads/' .date('Y/m');


        // --- BƯỚC 3: LƯU FILE VẬT LÝ (STORAGE) ---
        $savedPath = Storage::disk('public')->putFileAs($folderPath,$file,$fileName);

        
        // TODO: Kiểm tra nếu $savedPath bị false hoặc rỗng (lưu lỗi) thì ném Exception báo lỗi ngay.
        if(!$savedPath){
            throw new Exception("Lỗi hệ thống: Không thể lưu file vào ổ đĩa.");
        }


        // --- BƯỚC 4: LƯU DATABASE (MODEL) ---
        $fileRecord = File::create([
            'disk' => 'public',
            'path' => $savedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);

        // --- BƯỚC 5: RETURN ---
        return $fileRecord;
    }

    /**
     * Helper: Xóa file (Dọn dẹp)
     * Khi xóa User/Product thì nên xóa luôn file đính kèm
     */
    public function delete(File $file){
        //Xóa file vật lý
        if(Storage::disk($file->disk)->exists($file->path)){
            Storage::disk($file->disk)->delete($file->path);
        }

        //Xóa record trong DB
        return $file->delete();
    }

}