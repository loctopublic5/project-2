<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/auth.css') }}">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* 2. CSS cho nút xem mật khẩu */
        .password-toggle { z-index: 10; cursor: pointer; }
    </style>
</head>

<body>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="#"><img src="{{ asset('admin_assets/assets/compiled/svg/logo.svg') }}" alt="Logo"></a>
                    </div>
                    
                    <h1 class="auth-title">Đăng nhập.</h1>
                    <p class="auth-subtitle mb-5">Chào mừng quay lại với hệ thống.</p>

                    <form id="login-form">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" id="email" class="form-control form-control-xl" placeholder="Email / Tên đăng nhập">
                            <div class="form-control-icon"><i class="bi bi-person"></i></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password" class="form-control form-control-xl" placeholder="Mật khẩu">
                            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                            
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye text-muted" id="toggleIcon"></i>
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
                        <p class="text-gray-600">Chưa có tài khoản? <a href="{{ route('admin.register') }}" class="font-bold">Đăng ký ngay</a>.</p>
                        <p><a class="font-bold" href="{{ route('admin.password.request') }}">Quên mật khẩu?</a>.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right"></div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('admin_assets/js/axios-config.js') }}"></script>
    <script src="{{ asset('admin_assets/js/pages/login.js') }}"></script> 

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>