<?php

use App\Http\Middleware\ConvertCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->append(ConvertCase::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (QueryException $e){
            Log::error(''. $e->getMessage());
            return response()->json([
                'error' => 'Database error. Please try again later.',
            ], 500);
        });
        $exceptions->render(function (ModelNotFoundException $e){
            Log::error(''. $e->getMessage());
            return response()->json([
                'error' => 'Resource not found',
            ], 404);
        });
        $exceptions->render(function (AccessDeniedHttpException $e){
            Log::error(''. $e->getMessage());
            return response()->json([
                'error' => 'You do not have permission to perform this action.',
            ], 403);
        });
    })->create();
