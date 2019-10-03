<?php

namespace App\Http\Controllers;

use App\Helpers\HttpStatusCodes;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class GeneralController extends Controller {

    /**
     * Return required params for clients
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(
            [
                'time' => Carbon::now()->toDateTimeString(),
                'captcha_public_key' => config('captcha.public_key'),
                'jwt_public_key' => config('tokens.access_token.public_key')
            ],
            HttpStatusCodes::SUCCESS_OK
        );
    }
}