<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">RETAIL B2C</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="/products">Sản phẩm</a></li>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDrop" data-bs-toggle="dropdown">
                            {{ Auth::user()->full_name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile">Ví: {{ number_format(Auth::user()->wallet_balance ?? 0) }} đ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#">Đăng xuất</a></li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="/login">Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Đăng ký</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>