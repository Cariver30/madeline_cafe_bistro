<?php



use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['mobile.api']]
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'mobile.api' => \App\Http\Middleware\MobileTokenAuth::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LogSlowRequests::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\LogSlowRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Puedes agregar excepciones globales aquí si es necesario.
    })
    ->create();
