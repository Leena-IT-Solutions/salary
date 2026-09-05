<?php

ini_set('pcre.jit', '0');

// Self-heal stale bootstrap cache if any cached package provider class is missing
$packagesCache = __DIR__ . '/cache/packages.php';
if (file_exists($packagesCache)) {
    $cached = @include $packagesCache;
    if (is_array($cached)) {
        foreach ($cached as $pkg) {
            foreach ($pkg['providers'] ?? [] as $provider) {
                if (!class_exists($provider)) {
                    @unlink($packagesCache);
                    if (file_exists(__DIR__ . '/cache/services.php')) {
                        @unlink(__DIR__ . '/cache/services.php');
                    }
                    break 2;
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
