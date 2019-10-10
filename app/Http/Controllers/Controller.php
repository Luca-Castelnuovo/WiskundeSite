<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private $status_codes = [
        'SUCCESS_OK' => 200,
        'SUCCESS_CREATED' => 201,
        'SUCCESS_NO_CONTENT' => 204,
        'CLIENT_ERROR_BAD_REQUEST' => 400,
        'CLIENT_ERROR_UNAUTHORIZED' => 401,
        'CLIENT_ERROR_FORBIDDEN' => 403,
        'CLIENT_ERROR_NOT_FOUND' => 404,
        'METHOD_NOT_ALLOWED' => 405,
        'CLIENT_ERROR_CONFLICT' => 409,
        'CLIENT_ERROR_UNPROCESSABLE_ENTITY' => 422,
    ];

    /**
     * Return generic json response with the given data.
     *
     * @param array $data
     * @param int   $statusCode
     * @param array $headers
     * @param mixed $statusMessage
     *
     * @return JsonResponse
     */
    protected function respond($statusCode, $statusMessage, $data = null, $headers = [])
    {
        $httpStatus = $this->status_codes[$statusCode];

        if ($data) {
            $statusMessage = array_merge($statusMessage, $data);
        }

        return response($statusMessage, $httpStatus, $headers);
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
        $data = ['message' => $message];

        return $this->respond($statusCode, $data, $additionalData);
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
        $data = ['error' => $message];

        return $this->respond($statusCode, $data, $additionalData);
    }
}
