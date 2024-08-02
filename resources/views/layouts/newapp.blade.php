<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('head')

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">

        <div class="my_layout align-items-top">

            <div class="my_layout_sidebar bg-primary shadow">

                <div class="row border-bottom align-items-center m-0">
                    <div class="col">
                        <h4 class="text-uppercase text-light fw-bold m-0 p-0 px-3 py-2 py-lg-3">Payroll</h4>
                    </div>
                    <div class="col-auto d-block d-lg-none">
                        <button 
                        class="btn btn-clear btn-lg border-0 text-light"
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#offcanvasExample" 
                        aria-controls="offcanvasExample">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>

                <div class="container d-none d-lg-block overflow-auto">
                    <app-navigation></app-navigation>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
                
            </div>

            <div class="my_layout_main">
                @yield('content')
            </div>

        </div>

        <!-- This is sidebar -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
            <div class="card bg-primary h-100">

                <div class="card-header">
                    <div class="row align-items-top h-100">
                        <div class="col">
                            <h5 class="text-light p-0 m-0 text-uppercase">Payroll</h5>
                        </div>
                        <div class="col-auto text-end text-light">
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <div class="card-body overflow-auto">
                    <app-navigation></app-navigation>
                </div>

            </div>
        </div>

    </div>
</body>
</html>