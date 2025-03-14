<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e) {
            if (request()->expectsJson()) {
                return $this->handleApiException($e);
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $exception->errors(),
                ], 422);
            }
        }
    
        return parent::render($request, $exception);
    }

    private function handleApiException(Throwable $e): JsonResponse
    {
        $statusCode = 500;
        $response = [
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ];

        if ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $response['message'] = 'Resource not found';
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $response['message'] = 'Validation failed';
            $response['errors'] = $e->errors();
        } elseif ($e instanceof NotFoundHttpException) {
            $statusCode = 404;
            $response['message'] = 'Route not found';
        } elseif ($e instanceof TooManyRequestsHttpException) {
            $statusCode = 429;
            $response['message'] = 'Too many requests';
        }

        if (config('app.debug')) {
            $response['trace'] = $e->getTrace();
        }

        Log::error('API Error', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => request()->all()
        ]);

        return response()->json($response, $statusCode);
    }
} 