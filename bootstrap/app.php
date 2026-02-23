<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\SetApiLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API yanıtlarındaki hata mesajlarını Türkçe'ye çevir
        $exceptions->respond(function (Response $response, \Throwable $e, Request $request): Response {
            if ($request->is('api/*') && $response instanceof JsonResponse) {
                $data = $response->getData(true);
                if (isset($data['message']) && is_string($data['message'])) {
                    $data['message'] = __($data['message']) ?: $data['message'];
                    $response->setData($data);
                }
            }
            return $response;
        });
    })->create();
