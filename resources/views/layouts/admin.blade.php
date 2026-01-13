<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Mazer Admin</title>

    <script>
        (function() {
            const token = localStorage.getItem('admin_token');
            if (!token) {
                // Lưu lại URL hiện tại để redirect lại sau khi login (nếu muốn nâng cao sau này)
                window.location.href = '/admin/login';
            }
        })();
    </script>

    <link rel="shortcut icon" href="{{ asset('admin_assets/assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/compiled/css/iconly.css') }}">

    @stack('styles')
</head>

<body>
    <div id="app">
        @include('admin.partials.sidebar')
        <div id="main">
            @include('admin.partials.header')

            <div class="page-heading">
                <h3>@yield('title')</h3>
            </div>

            <div class="page-content">
                @yield('content')
            </div>
            
            @include('admin.partials.footer')
        </div>
    </div>

    <script src="{{ asset('admin_assets/assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('admin_assets/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('admin_assets/assets/compiled/js/app.js') }}"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('admin_assets/js/axios-config.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('btn-logout');
            
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function(e) {
                    e.preventDefault(); 
                    
                    if(confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                        // UX: Đổi text nút
                        const originalHtml = logoutBtn.innerHTML;
                        logoutBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Đang xử lý...</span>';
                        // Nếu là thẻ a thì đổi text hơi khác chút, nhưng logic ok

                        try {
                            // Gọi API Logout
                            await axios.post('/api/v1/auth/logout'); 
                        } catch (error) {
                            console.warn("Lỗi API Logout (Token có thể đã hết hạn), vẫn tiến hành xóa local.");
                        } finally {
                            // Xóa sạch Token & User
                            localStorage.removeItem('admin_token');
                            localStorage.removeItem('admin_user');

                            // Redirect về Login
                            window.location.href = '/admin/login';
                        }
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>