<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealerRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'status',
        'admin_note',
        'approved_at',
    ];

    protected $casts = [
    'approved_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
