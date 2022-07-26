<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApartmentRentalCertificationException extends Exception
{
    protected $message = 'Apartment rental certification failed.';

    protected $statusCode = Response::HTTP_FORBIDDEN;

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
