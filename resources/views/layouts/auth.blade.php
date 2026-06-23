<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/logo192.png">

    @yield('head')

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Salary Manager">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-primary bg-gradient text-white">
    <div id="app">
        <pwa-install></pwa-install>

        <div class="container min-vh-100 d-flex align-items-center justify-content-center">
            <div class="row w-100 justify-content-center">

                <div class="col-11 col-md-8 col-lg-6 col-xl-5 col-xxl-4">

                    <div class="text-center mb-5" data-aos="zoom-in">
                        <div class="logo-box bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-premium overflow-hidden" style="width: 80px; height: 80px; overflow: hidden;">
                            <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h1 class="fw-bold text-uppercase letter-spacing-2 m-0">{{ config('app.name', 'PAYROLL') }}</h1>
                        <p class="text-white text-opacity-75 small mt-1">Advanced Salary Management System</p>
                    </div>

                    <div class="card bg-white rounded-2xl shadow-lg border-0 px-4 py-5 mb-4 text-dark" data-aos="fade-up" data-aos-delay="200">
                        @yield('content')
                    </div>

                    <div class="text-center text-white text-opacity-75 small" data-aos="fade-up" data-aos-delay="400">
                        <a class="text-white text-decoration-none fw-semibold" target="_blank" href="https://leenaitsolutions.com">Powered by LITS</a>
                        <span class="mx-2 opacity-25">|</span>
                        &copy; {{ date('Y') }}
                    </div>

                </div>

            </div>
        </div>
        
    </div>

    <style>
        .letter-spacing-2 { letter-spacing: 2px; }
        .bg-gradient { background: linear-gradient(135deg, $primary 0%, darken($primary, 20%) 100%) !important; }
    </style>
</body>
</html>