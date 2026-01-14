<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'disk',
        'path',
        'mime_type',
        'size',
        'target_type',
        'target_id'
    ];

    /**
     * Định nghĩa quan hệ đa hình ngược
     * Giúp từ File -> tìm ra được nó thuộc về User hay Product
     */
    public function target()
    {
        // Cú pháp: return $this->morphTo();
        // Nó sẽ tự động dùng 'target_type' và 'target_id' để tìm cha
        return $this->morphTo();
    }


    /**
     * Accessor: Tự động lấy full URL của ảnh
     * Khi gọi $file->url sẽ ra link http://...
     */
    public function getUrlAttribute()
    {
        // Nếu disk là public, dùng hàm url() để tạo link public
        if ($this->disk === 'public') {
            return Storage::url($this->path);
        }
        
        // Nếu là s3 hay local thì logic khác (tạm thời return path)
        return $this->path;
    }
}