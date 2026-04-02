<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // Redirect unauthenticated users to admin login instead of default 'login' route
        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Requirements: 17.1 — Render 404 page via Inertia
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->header('X-Inertia')) {
                return Inertia::render('Errors/404')
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            return Inertia::render('Errors/404')
                ->toResponse($request)
                ->setStatusCode(404);
        });

        // Requirements: 17.2 — Render 500 page via Inertia (no stack trace)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof NotFoundHttpException) {
                return null; // handled above
            }

            // Only intercept in non-testing environments to avoid breaking tests
            if (app()->environment('testing')) {
                return null;
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($statusCode >= 500) {
                return Inertia::render('Errors/500')
                    ->toResponse($request)
                    ->setStatusCode(500);
            }

            return null;
        });
    })->create();
