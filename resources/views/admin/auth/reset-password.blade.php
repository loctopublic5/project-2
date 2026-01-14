<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-toggle { z-index: 10; cursor: pointer; }
        /* Style cho ô nhập code nổi bật hơn */
        #token { letter-spacing: 4px; font-weight: bold; text-align: center; font-size: 1.2rem; }
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
                    <h1 class="auth-title">Xác thực & Đổi mật khẩu.</h1>
                    <p class="auth-subtitle mb-4">Nhập mã 6 số chúng tôi vừa gửi vào email của bạn.</p>

                    <form id="reset-form">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" id="email" class="form-control form-control-xl" readonly style="background-color: #f2f2f2;">
                            <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" id="token" class="form-control form-control-xl" placeholder="Nhập mã 6 số (VD: 123456)" maxlength="6">
                            <div class="form-control-icon"><i class="bi bi-key"></i></div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password" class="form-control form-control-xl" placeholder="Mật khẩu mới">
                            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" onclick="togglePassword('password', this)">
                                <i class="bi bi-eye text-muted"></i>
                            </div>
                        </div>

                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password_confirmation" class="form-control form-control-xl" placeholder="Xác nhận mật khẩu">
                            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" onclick="togglePassword('password_confirmation', this)">
                                <i class="bi bi-eye text-muted"></i>
                            </div>
                        </div>

                        <button type="submit" id="btn-reset" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            Xác nhận đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('admin_assets/js/axios-config.js') }}"></script>
    <script src="{{ asset('admin_assets/js/pages/reset-password.js') }}"></script>
    
    <script>
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            const icon = el.querySelector('i');
            if(input.type === 'password') {
                input.type = 'text'; icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password'; icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>