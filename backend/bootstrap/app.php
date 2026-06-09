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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \App\Http\Middleware\LocalizationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'Database connection or query error.'
                    : 'Lỗi truy vấn cơ sở dữ liệu.',
            ], 500);
        });

        $exceptions->render(function (\PDOException $e) {
            return response()->json([
                'error' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'Database connection failed.'
                    : 'Không thể kết nối đến cơ sở dữ liệu.',
            ], 500);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json([
                'error' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'The requested resource was not found.'
                    : 'Tài nguyên yêu cầu không tồn tại.',
            ], 404);
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'error' => false,
                'message' => $e->getMessage(),
            ], 401);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json([
                'error' => false,
                'message' => app()->getLocale() === 'en'
                    ? 'You do not have permission to perform this action.'
                    : 'Bạn không có quyền thực hiện hành động này.',
            ], 403);
        });

        $exceptions->render(function (\Throwable $e) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = ($status !== 500 || config('app.debug'))
                ? $e->getMessage()
                : (app()->getLocale() === 'en' ? 'Internal server error.' : 'Đã xảy ra lỗi hệ thống.');

            return response()->json([
                'error' => false,
                'message' => $message,
            ], $status);
        });
    })->create();
