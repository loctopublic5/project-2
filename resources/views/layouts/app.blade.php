<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Retail E-commerce')</title>

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">

    <link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/owl.carousel/assets/owl.carousel.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/pages/css/components.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/pages/css/style-shop.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/corporate/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/corporate/css/style-responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/corporate/css/themes/red.css') }}" rel="stylesheet" id="style-color">
    <link href="{{ asset('assets/corporate/css/custom.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="ecommerce">

    <div class="pre-header">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-6 additional-shop-info">
                    <ul class="list-unstyled list-inline">
                        <li><i class="fa fa-phone"></i><span>+1 456 6717</span></li>
                        <li><i class="fa fa-envelope-o"></i><span>info@retail-b2c.com</span></li>
                    </ul>
                </div>
                <div class="col-md-6 col-sm-6 additional-nav">
                    <ul class="list-unstyled list-inline pull-right" id="auth-section"></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="header">
        <div class="container">
            <a class="site-logo" href="/"><img src="{{ asset('assets/template/corporate/img/logos/logo-shop-red.png') }}" alt="Metronic Shop"></a>
            <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>

            <div class="top-cart-block">
                <div class="top-cart-info">
                    <a href="javascript:void(0);" class="top-cart-info-count" id="cart-count">0 items</a>
                    <a href="javascript:void(0);" class="top-cart-info-value" id="cart-total">0 đ</a>
                </div>
                <i class="fa fa-shopping-cart"></i>
            </div>

            <div class="header-navigation">
                <ul id="category-menu"></ul>
            </div>
        </div>
    </div>

    <main class="main">
        <div class="container">
            <div class="row margin-bottom-40">
                @yield('content')
            </div>
        </div>
    </main>

    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="copyright">2026 © Retail E-commerce System. Developed by Dev A.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-migrate.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/owl.carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/zoom/jquery.zoom.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.pack.js') }}"></script>

    <script src="{{ asset('assets/corporate/scripts/layout.js') }}"></script>
    <script>
        jQuery(document).ready(function() {
            // Kiểm tra an toàn để không vỡ trang nếu file nạp chậm
            if (typeof Layout !== 'undefined') {
                Layout.init();    
                Layout.initOWL();
                Layout.initImageZoom(); 
            }
        });
    </script>

    @stack('scripts')
</body>
</html>