<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class BlockedAccountException extends Exception
{
    protected $message = 'Your account is blocked. Contact the administrators.';

    protected $statusCode = Response::HTTP_LOCKED;

    /**
     * Render the response.
     *
     * @param Request $request
     * @return mixed
     */
    public function render(Request $request)
    {
        if ($request->is('api/*')) {
            return ResponseBuilder::asError(102)
                ->withHttpCode($this->statusCode)
                ->withMessage($this->getMessage())
                ->build();
        }
    }
}
