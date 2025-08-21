<?php 

namespace App\Exceptions;


use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

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
     * Render exceptions into HTTP responses.
     */
    public function render($request, Throwable $exception)
    {
        // Global custom message for authorization failures
        if ($exception instanceof AuthorizationException) {
            return redirect()->back()
                             ->withErrors('You do not have permission to perform this action.');
        }

        return parent::render($request, $exception);
    }
}
