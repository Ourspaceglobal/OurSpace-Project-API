<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class InsufficientWalletFundsException extends Exception
{
    protected $message = 'Insufficient funds in your wallet';

    protected $statusCode = Response::HTTP_PAYMENT_REQUIRED;

    /**
     * Render the response.
     *
     * @param Request $request
     * @return mixed
     */
    public function render(Request $request)
    {
        if ($request->is('api/*')) {
            return ResponseBuilder::asError(100)
                ->withHttpCode($this->statusCode)
                ->withMessage($this->getMessage())
                ->build();
        }
    }
}
