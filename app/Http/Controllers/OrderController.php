<?php

namespace App\Http\Controllers;

use App\Validators\ValidatesOrderRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;

class OrderController extends Controller
{
    use ValidatesOrderRequests;

    /**
     * Create order.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function new(Request $request)
    {
        $this->validateCreate($request);

        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
            'description' => 'My first API payment',
            'redirectUrl' => 'https://files.lucacastelnuovo.nl/general/images/hacked.gif',
            'webhookUrl' => 'https://enh6gp136zyzu.x.pipedream.net',
        ]);

        $payment = Mollie::api()->payments()->get($payment->id);

        return $this->respondSuccess(
            'order created',
            'SUCCESS_OK',
            [
                'link' => $payment->getCheckoutUrl(),
            ]
        );
    }
}
