<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Return generic json response with the given data.
     *
     * @param array $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function respond($data, $statusCode = 'SUCCESS_OK', $headers = [])
    {
        return response($data, $statusCode, $headers);
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
