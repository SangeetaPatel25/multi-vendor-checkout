<?php

use App\Exceptions\Business\EmptyCartException;
use App\Exceptions\Business\InsufficientStockException;
use App\Exceptions\Business\OrderNotFoundForCustomerException;
use App\Exceptions\Business\PaymentStateException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldReturnJson = function (Request $request): bool {
            return $request->expectsJson() || $request->is('api/*');
        };

        $jsonError = function (Request $request, string $message, int $status, array $errors = []) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return response()->json([
                'status_code' => $status,
                'success' => false,
                'message' => $message,
                'errors' => (object) $errors,
            ], $status);
        };

        $exceptions->render(function (ValidationException $e, Request $request) use ($jsonError) {
            return $jsonError(
                $request,
                'Validation failed.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->errors()
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($jsonError) {
            return $jsonError($request, 'Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($jsonError) {
            return $jsonError($request, 'You are not authorized to perform this action.', Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (EmptyCartException $e, Request $request) use ($jsonError) {
            return $jsonError($request, $e->getMessage(), Response::HTTP_BAD_REQUEST);
        });

        $exceptions->render(function (InsufficientStockException $e, Request $request) use ($jsonError) {
            return $jsonError($request, $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (PaymentStateException $e, Request $request) use ($jsonError) {
            return $jsonError($request, $e->getMessage(), Response::HTTP_BAD_REQUEST);
        });

        $exceptions->render(function (OrderNotFoundForCustomerException $e, Request $request) use ($jsonError) {
            return $jsonError($request, $e->getMessage(), Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($jsonError, $shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                return $jsonError(
                    $request,
                    $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()],
                    $e->getStatusCode()
                );
            }

            return $jsonError(
                $request,
                config('app.debug') ? $e->getMessage() : 'Server error.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        });
    })->create();
