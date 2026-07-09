<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Keep default behavior available.
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->is('api')) {
            return $this->prepareApiResponse($exception);
        }

        return parent::render($request, $exception);
    }

    protected function prepareApiResponse(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->errors(),
                $exception->status
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error($exception->getMessage() ?: 'Forbidden.', [], 403);
        }

        if ($exception instanceof HttpExceptionInterface) {
            return ApiResponse::error(
                $exception->getMessage() ?: SymfonyResponse::$statusTexts[$exception->getStatusCode()] ?? 'Error',
                [],
                $exception->getStatusCode()
            );
        }

        return ApiResponse::error(
            $exception->getMessage() ?: 'Server Error',
            [],
            500
        );
    }
}
