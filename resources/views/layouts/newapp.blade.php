<!DOCTYPE html>
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

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script>
        window.User = {!! json_encode(Auth::user()) !!};
    </script>
</head>
<body class="bg-light">
    <div id="app">
        <pwa-install></pwa-install>

        <div class="my_layout">

            <!-- Sidebar -->
            <aside class="my_layout_sidebar d-none d-lg-flex flex-column shadow-premium">
                <div class="sidebar-header border-bottom border-white border-opacity-10 py-4 px-4 mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="logo-box bg-transparent rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 40px; height: 40px; overflow: hidden;">
                            <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h4 class="text-white fw-bold m-0 letter-spacing-1">PAYROLL</h4>
                    </div>
                </div>

                <div class="sidebar-content flex-grow-1 overflow-auto custom-scrollbar">
                    <app-navigation></app-navigation>
                </div>

                <div class="sidebar-footer p-4 border-top border-white border-opacity-10">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-white rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold shadow-sm" style="width: 40px; height: 40px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="user-info overflow-hidden">
                            <p class="text-white fw-semibold m-0 text-truncate">{{ Auth::user()->name }}</p>
                            <p class="text-white text-opacity-50 small m-0 text-truncate">{{ Auth::user()->role }}</p>
                        </div>
                    </div>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </aside>

            <!-- Main Content Area -->
            <div class="my_layout_main d-flex flex-column">
                
                <!-- Top Navbar for Mobile -->
                <nav class="navbar d-lg-none bg-primary shadow-sm px-3">
                    <div class="container-fluid">
                        <span class="navbar-brand text-white fw-bold d-flex align-items-center gap-2">
                            <div class="bg-transparent rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 32px; height: 32px; overflow: hidden;">
                                <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            PAYROLL
                        </span>
                        <button class="btn btn-link text-white p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample">
                            <i class="bi bi-list fs-1"></i>
                        </button>
                    </div>
                </nav>

                <main class="flex-grow-1">
                    @yield('content')
                </main>
            </div>

        </div>

        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start bg-primary" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
            <div class="offcanvas-header border-bottom border-white border-opacity-10 py-4">
                <h5 class="offcanvas-title text-white fw-bold d-flex align-items-center gap-2" id="offcanvasExampleLabel text-uppercase">
                    <div class="bg-transparent rounded-circle d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 32px; height: 32px; overflow: hidden;">
                        <img src="/logo192.png" alt="Salary Manager Logo" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    PAYROLL
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <app-navigation></app-navigation>
            </div>
        </div>

    </div>

    <style>
        .letter-spacing-1 { letter-spacing: 1px; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    </style>

</body>
</html>