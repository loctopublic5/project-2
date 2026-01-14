<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Admin Dashboard</title>
    
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/auth.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="#"><img src="{{ asset('admin_assets/assets/compiled/svg/logo.svg') }}" alt="Logo"></a>
                    </div>
                    <h1 class="auth-title">Quên mật khẩu?</h1>
                    <p class="auth-subtitle mb-5">Nhập email của bạn và chúng tôi sẽ gửi link khôi phục mật khẩu.</p>

                    <form id="forgot-form">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" id="email" class="form-control form-control-xl" placeholder="Email">
                            <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" id="btn-send" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            Gửi Link Khôi Phục
                        </button>
                    </form>

                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>Nhớ mật khẩu rồi? <a href="{{ route('login') }}" class="font-bold">Đăng nhập</a>.</p>
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
    <script src="{{ asset('admin_assets/js/pages/forgot-password.js') }}"></script>
</body>
</html>