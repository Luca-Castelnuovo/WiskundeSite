<?php

namespace App\Http\Controllers;

use App\Helpers\HttpStatusCodes;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class GeneralController extends Controller
{
    /**
     * Return required params for clients.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->respondSuccess(
            'https://docs.wiskundesite.nl',
            HttpStatusCodes::SUCCESS_OK,
            [
                'time' => Carbon::now()->toDateTimeString(),
                'captcha_public_key' => config('captcha.public_key'),
                'jwt_public_key' => config('tokens.access_token.public_key'),
            ]
        );
    }
}
