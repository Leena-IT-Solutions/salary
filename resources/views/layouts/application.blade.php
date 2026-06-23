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
<body>
    <div id="app">
        <pwa-install></pwa-install>
        <div class="container-fluid h-full bg-white p-2 p-md-3">
            <div class="h-100 rounded-4 shadow border border-2 overflow-auto">

                <div class="row align-items-top p-0 m-0 h-100">

                    <div class="col-12 bg-primary d-block d-lg-none sticky-top" style="height: 60px;">

                        <div class="row align-items-center h-100 px-3">

                            <div class="col d-flex align-items-center gap-2">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 32px; height: 32px; overflow: hidden;">
                                    <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <h3 class="text-light p-0 m-0 text-uppercase">Payroll</h3>
                            </div>
                            <div class="col-auto text-end text-light">
                                <button 
                                class="btn btn-clear btn-lg border-0 text-light"
                                type="button" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#offcanvasExample" 
                                aria-controls="offcanvasExample">
                                    <i class="bi bi-list fs-1"></i>
                                </button>
                            </div>
                            
                        </div>
                
                    </div>

                    <div class="col-auto bg-primary d-none d-lg-block h-auto">
                        <div style="width:300px;" class="">
                        
                            <div class="row align-items-top">
                                <div class="col-12 pt-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 40px; height: 40px; overflow: hidden;">
                                            <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <h3 class="text-light p-0 m-0 text-uppercase">Payroll</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="py-3">
                                <app-navigation></app-navigation>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="col p-3 bg-danger h-auto">
                        @yield('content')
                    </div>

                </div>

                <div class="offcanvas offcanvas-start p-3" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
                    <div class="card bg-primary rounded-4 h-100">

                        <div class="card-header">
                            <div class="row align-items-top h-100">
                                <div class="col d-flex align-items-center gap-2 py-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 32px; height: 32px; overflow: hidden;">
                                        <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <h5 class="text-light p-0 m-0 text-uppercase">Payroll</h5>
                                </div>
                                <div class="col-auto text-end text-light">
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <app-navigation></app-navigation>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
