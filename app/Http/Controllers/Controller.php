<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private $status_codes = [
        'SUCCESS_OK' => 200,
        'SUCCESS_CREATED',
        'SUCCESS_NO_CONTENT',
        'CLIENT_ERROR_BAD_REQUEST',
        'CLIENT_ERROR_UNAUTHORIZED',
        'CLIENT_ERROR_FORBIDDEN',
        'CLIENT_ERROR_NOT_FOUND',
        'METHOD_NOT_ALLOWED',
        'CLIENT_ERROR_CONFLICT',
        'CLIENT_ERROR_UNPROCESSABLE_ENTITY',
    ];

    /**
     * Return generic json response with the given data.
     *
     * @param array $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function respond($data, $statusCode, $headers = [])
    {
        $httpStatus = $this->status_codes[$statusCode];

        return response($data, $httpStatus, $headers);
    }

    /**
     * Respond with success.
     *
     * @param string $message
     * @param int    $statusCode
     * @param array  $additionalData
     *
     * @return JsonResponse
     */
    protected function respondSuccess($message, $statusCode, $additionalData = null)
    {
        $data = Arr::collapse([['message' => $message], $additionalData]);

        return $this->respond($data, $statusCode);
    }

    /**
     * Respond with error.
     *
     * @param string $message
     * @param int    $statusCode
     * @param array  $additionalData
     *
     * @return JsonResponse
     */
    protected function respondError($message, $statusCode, $additionalData = null)
    {
        $data = Arr::collapse([['error' => $message], $additionalData]);

        return $this->respond($data, $statusCode);
    }
}
