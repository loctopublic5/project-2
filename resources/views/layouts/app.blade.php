<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Shop')</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/pages/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/owl.carousel/assets/owl.carousel.css') }}">

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

{{-- JS --}}
<script src="{{ asset('assets/plugins/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/fancybox/source/jquery.fancybox.pack.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/owl.carousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/pages/scripts/app.js') }}"></script>
<script src="{{ asset('assets/pages/scripts/shop-index.js') }}"></script>

<script>
    jQuery(document).ready(function () {
        if ($(".fancybox-button").length) {
            $(".fancybox-button").fancybox({
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: true,
                helpers: {
                    title: { type: 'inside' },
                    buttons: {}
                }
            });
        }
    });
</script>

<script>
    jQuery(document).ready(function() {
        App.init();
        App.initBxSlider();
    });
</script>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

</body>
</html>
