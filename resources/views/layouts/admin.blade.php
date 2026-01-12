<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Mazer Admin</title>

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
    
    @stack('scripts')
</body>

</html>