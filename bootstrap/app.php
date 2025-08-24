<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Task\Providers\TaskServiceProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.',
                    'status' => false,
                ], 404);
            }
        });
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });

    })->withProviders([
        CoreServiceProvider::class,
        AuthServiceProvider::class,
        TaskServiceProvider::class,
        L5Swagger\L5SwaggerServiceProvider::class,
    ])->booted(function () {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('login', function (Request $request) {
            return [Limit::perMinute(10)->by($request->ip()), Limit::perMinute(10)->by($request->input('email'))];
        });
    })->create();
