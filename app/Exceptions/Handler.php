<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle custom exceptions
        if ($e instanceof InsufficientStockException ||
            $e instanceof PaymentException ||
            $e instanceof UnauthorizedActionException) {
            return $e->render($request);
        }

        // Handle validation exceptions for AJAX requests
        if ($e instanceof ValidationException && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }

        // Handle authentication exceptions
        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException($request, $e);
        }

        // Handle authorization exceptions
        if ($e instanceof AccessDeniedHttpException) {
            return $this->handleAuthorizationException($request, $e);
        }

        // Handle 404 exceptions
        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(Request $request, AuthenticationException $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'error_type' => 'unauthenticated'
            ], 401);
        }

        return redirect()->guest(route('login'))
            ->with('error', 'Please log in to access this page.');
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(Request $request, AccessDeniedHttpException $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
                'error_type' => 'unauthorized'
            ], 403);
        }

        return response()->view('errors.unauthorized', [
            'message' => 'You are not authorized to perform this action.'
        ], 403);
    }

    /**
     * Handle 404 exceptions
     */
    protected function handleNotFoundHttpException(Request $request, NotFoundHttpException $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found.',
                'error_type' => 'not_found'
            ], 404);
        }

        return response()->view('errors.404', [
            'message' => 'The page you are looking for could not be found.'
        ], 404);
    }
}
