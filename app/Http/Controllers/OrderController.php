<?php

namespace App\Http\Controllers;

use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesOrderRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class OrderController extends Controller {
    use ValidatesOrderRequests;

    /**
     * Create a new order
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function new(Request $request)
    {
        $this->validateCreate($request);

        return response()->json(
            [
                'error' => 'function not implemented'
            ],
            HttpStatusCodes::CLIENT_ERROR_BAD_REQUEST
        );
    }
}