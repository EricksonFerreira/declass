<?php

use App\Http\Middleware\CheckPermissaoPorRota;
use App\Http\Middleware\ExigirPagamentoPlanoMiddleware;
use App\Http\Middleware\ForcarTrocaSenhaMiddleware;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            ResolveTenant::class,
            ForcarTrocaSenhaMiddleware::class,
            ExigirPagamentoPlanoMiddleware::class,
            CheckPermissaoPorRota::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
