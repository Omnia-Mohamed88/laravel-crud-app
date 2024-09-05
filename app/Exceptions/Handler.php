<?php
namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'You must be logged in to access this resource.'
                ], 401);
            }
        }

        if ($exception instanceof RouteNotFoundException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Route Not Found',
                    'message' => 'The route you are trying to access does not exist.'
                ], 404);
            }
        }

        if ($exception instanceof ModelNotFoundException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Model Not Found',
                    'message' => 'The model you are trying to access does not exist.'
                ], 404);
            }
        }

        return parent::render($request, $exception);
    }
}
