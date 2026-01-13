<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Mazer</title>
    
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/auth.css') }}"> </head>

<body>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="#"><img src="{{ asset('admin_assets/assets/compiled/svg/logo.svg') }}" alt="Logo"></a>
                    </div>
                    
                    <h1 class="auth-title">Đăng nhập.</h1>
                    <p class="auth-subtitle mb-5">Chào mừng quay lại với hệ thống quản trị.</p>

                    <div id="error-alert" class="alert alert-danger d-none" role="alert">
                        </div>

                    <form id="login-form">
                        
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" id="email" class="form-control form-control-xl" placeholder="Username / Email">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password" class="form-control form-control-xl" placeholder="Password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>

                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" value="" id="flexCheckDefault">
                            <label class="form-check-label text-gray-600" for="flexCheckDefault">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>

                        <button type="submit" id="btn-login" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            Đăng nhập
                        </button>
                    </form>
                    
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">Bạn quên mật khẩu? <a href="#" class="font-bold">Khôi phục</a>.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('admin_assets/js/pages/login.js') }}"></script> 
</body>

</html>