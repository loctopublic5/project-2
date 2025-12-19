<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return view('home');
});

Route::post('/register', function(){
    return 'thank yyou!';
});

Route::get('/test-rbac', function () {
    
    // 1. Giả lập lấy ra 1 user (Ví dụ user có id = 1)
    // Keyword: User::find(1);
    $user = User::find(1);

    // Kiểm tra xem tìm thấy user không, nếu không thì báo lỗi
    if (!$user) return 'Không tìm thấy User ID 1';

    // 2. Thử gọi hàm hasPermissionTo bạn vừa viết
    // Giả sử ta check quyền: resource 'products', action 'create'
    $check = $user->hasPermissionTo('products', 'create');

    // 3. Xuất kết quả ra màn hình
    // Dùng hàm dd() (Dump and Die) của Laravel để xem kết quả nhanh
    dd([
        'User Name' => $user->full_name, // Cột full_name theo ERD [cite: 13]
        'Roles' => $user->roles->pluck('name'), // Lấy tên các role
        'Has Permission (products.create)?' => $check ? 'CÓ' : 'KHÔNG'
    ]);
});