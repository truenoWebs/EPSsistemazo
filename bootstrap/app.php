<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- AÑADE ESTA LÍNEA
        apiPrefix: 'api',                  // <--- (OPCIONAL PERO RECOMENDADO) Esto asegura el prefijo /api
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aquí es donde se configura el middleware para el grupo 'api',
        // como Laravel Sanctum si lo usas para autenticación de API.
        // Ejemplo (si usas Sanctum más adelante):
        // $middleware->validateCsrfTokens(except: [
        //     'api/*', // Si tus rutas API no usan sesiones/CSRF
        // ]);
        // $middleware->append(EnsureFrontendRequestsAreStateful::class); // Para Sanctum SPA
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
