<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Metronic Shop')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    {{-- LỖ 1: Dành cho các thẻ Meta SEO & Social (Open Graph) --}}
    @yield('meta_tags')

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
    
    <link rel="stylesheet" href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    
    {{-- Plugins dùng chung --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/owl.carousel/assets/owl.carousel.css') }}">

    {{-- LỖ 2: Dành cho các Plugin đặc thù của từng trang (Ví dụ: Uniform) --}}
    @yield('extra_plugins')

    <link rel="stylesheet" href="{{ asset('assets/pages/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/pages/css/style-shop.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/corporate/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/corporate/css/style-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/corporate/css/themes/red.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/corporate/css/custom.css') }}">
</head>
<body class="ecommerce">

    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    {{-- SCRIPTS CHUNG: Trang nào cũng phải có --}}
    <script src="{{ asset('assets/plugins/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/corporate/scripts/layout.js') }}"></script>

    {{-- Axios & SweetAlert2 (Dùng chung cho cả Search Header và Checkout) --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('admin_assets/js/axios-config.js') }}"></script>

    {{-- Header Logic (Search, New Products): Luôn cần để Header chạy --}}
    <script src="{{ asset('assets/js/pages/Header/Navigative.js') }}"></script>
    <script src="{{ asset('assets/js/pages/Header/new.js') }}"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init(); // Khởi tạo chung
        });
    </script>

    {{-- Nơi đục lỗ cho JS riêng của từng trang --}}
    @stack('extra_scripts')

    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <script>
            alert("{{ session('success') }}");
        </script>
    @endif

</body>
</html>