<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;

Route::get('/', function () {
    return view('layouts.app'); // Test khung layout
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        Route::resource('products', AdminProductController::class)
            ->middleware([
                'index'   => 'permission:products,view',
                'create'  => 'permission:products,create',
                'store'   => 'permission:products,create',
                'edit'    => 'permission:products,update',
                'update'  => 'permission:products,update',
                'destroy' => 'permission:products,delete',
            ]);
    });

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::resource('products', AdminProductController::class);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

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

// Route này chỉ dùng để dev, giúp trình duyệt nhớ bạn là User 1
Route::get('/force-login', function () {
    Auth::loginUsingId(1);
    return "Đã đăng nhập thành công với User ID 1. Hãy thử lại route test.";
});
Route::get('/admin/products/delete', function(){
    return 'Xóa sản phẩm thành công';
})->middleware('permission:products,delete');

Route::get('/test-view', function () {
    // Nhớ fake login user 1 trước
    Auth::loginUsingId(1); 
    return view('test-gate');
});