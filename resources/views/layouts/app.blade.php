<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Metronic Shop')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
    {{-- CSS Global Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    
    {{-- Page Level Plugin Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/owl.carousel/assets/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/pages/css/animate.css') }}">

    {{-- Theme Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/pages/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/pages/css/slider.css') }}">
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

    {{-- JavaScripts --}}
    <script src="{{ asset('assets/plugins/jquery.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/jquery-migrate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>      
    <script src="{{ asset('assets/corporate/scripts/back-to-top.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.pack.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/owl.carousel/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/zoom/jquery.zoom.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-touchspin/bootstrap.touchspin.js') }}" type="text/javascript"></script>

    <script src="{{ asset('assets/corporate/scripts/layout.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/pages/scripts/bs-carousel.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            Layout.init();    
            Layout.initOWL();
            Layout.initImageZoom();
            Layout.initTouchspin();
            // Layout.initTwitter(); // Bỏ comment nếu bạn có dùng twitter widget
        });
    </script>

    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <script>
            alert("{{ session('success') }}");
        </script>
    @endif

</body>
</html>