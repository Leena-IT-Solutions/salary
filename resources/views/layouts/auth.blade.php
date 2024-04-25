<!doctype html>
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


        <div class="container h-full">
            <div class="row h-100 justify-content-center align-items-center">

                <div class="col-11 col-md-8 col-lg-7 col-xl-6 col-xxl-5">

                    <div class="container text-center mb-4">
                        <a class="btn btn-clear border-0" href="/">
                            <div style="width:100px;" class="mx-auto">
                                <div class="image image-s image-cover">
                                    <img src="/images/logo.png" alt="">
                                </div>
                            </div>
                            <h1>{{ config('app.name', 'Laravel') }}</h1>
                        </a>
                    </div>

                    <div class="container shadow rounded px-4 py-5 mb-4">
                        @yield('content')
                    </div>

                    <div class="container text-center">
                        <a class="btn btn-clear border-0" target="_blank" href="https://leenaitsolutions.com">Powered by <strong>LITS</strong></a>
                        &copy; {{ date('Y') }}
                    </div>

                </div>

            </div>
        </div>

        
    </div>
</body>
</html>