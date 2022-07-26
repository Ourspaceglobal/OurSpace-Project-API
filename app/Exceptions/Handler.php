<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e instanceof \Spatie\QueryBuilder\Exceptions\InvalidIncludeQuery) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_NOT_ACCEPTABLE)
                        ->withMessage('Invalid query')
                        ->build();
                }

                if ($e instanceof \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_REQUEST_ENTITY_TOO_LARGE)
                        ->withMessage(preg_replace('(`.*`\\s)', '', $e->getMessage()))
                        ->build();
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode($e->getStatusCode())
                        ->withMessage($e->getMessage() ?: 'Route not found')
                        ->build();
                }

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_UNAUTHORIZED)
                        ->withMessage($e->getMessage())
                        ->build();
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_FORBIDDEN)
                        ->withMessage($e->getMessage())
                        ->build();
                }

                if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_UNAUTHORIZED)
                        ->withMessage('You are not permitted to perform this action.')
                        ->build();
                }

                if ($e instanceof \Yabacon\Paystack\Exception\ApiException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode(Response::HTTP_BAD_REQUEST)
                        ->withMessage($e->getMessage())
                        ->build();
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return ResponseBuilder::asError(100)
                        ->withHttpCode($e->getStatusCode())
                        ->withMessage($e->getMessage())
                        ->build();
                }
            }
        });
    }
}
