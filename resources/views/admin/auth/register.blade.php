<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành viên</title>
    
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* CSS cho nút xem mật khẩu */
        .cursor-pointer { cursor: pointer; }
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
                    <h1 class="auth-title">Đăng ký.</h1>
                    
                    <form id="register-form">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" id="full_name" class="form-control form-control-xl" placeholder="Họ và tên đầy đủ">
                            <div class="form-control-icon"><i class="bi bi-person"></i></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" id="email" class="form-control form-control-xl" placeholder="Email">
                            <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password" class="form-control form-control-xl" placeholder="Mật khẩu">
                            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                            
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye text-muted"></i>
                            </div>
                            
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password_confirmation" class="form-control form-control-xl" placeholder="Nhập lại mật khẩu">
                            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                            
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" onclick="togglePassword('password_confirmation', this)">
                                <i class="bi bi-eye text-muted"></i>
                            </div>

                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" id="btn-register" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            Đăng ký ngay
                        </button>
                    </form>

                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>Đã có tài khoản? <a href="{{ route('admin.login') }}" class="font-bold">Đăng nhập</a>.</p>
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
    <script src="{{ asset('admin_assets/js/pages/register.js') }}"></script>

    <script>
        function togglePassword(inputId, toggleIconDiv) {
            const input = document.getElementById(inputId);
            const icon = toggleIconDiv.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash'); // Đổi icon thành mắt gạch chéo
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>