<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
    public function create(Request $request)
    {
        $this->validateCreate($request);

        $value = '10.00';

        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => config('mollie.currency'),
                'value' => $value,
            ],
            'description' => 'My first API payment',
            'redirectUrl' => config('mollie.redirectUrl'),
            'webhookUrl' => config('mollie.webhookUrl'),
        ]);

        $payment = $this->getPayment($request->id);

        // TODO: insert into order model

        return $this->respondSuccess(
            'order initialised',
            'SUCCESS_OK',
            [
                'link' => $payment->getCheckoutUrl(),
            ]
        );
    }

    /**
     * Show order.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $order = Order::findOrFail($id);

        return $this->respondSuccess(
            'order initialised',
            'SUCCESS_OK',
            $order
        );
    }

    /**
     * Webhook for order.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function webhook(Request $request)
    {
        $this->validateWebhook($request);

        $payment = $this->getPayment($request->id);

        dd($payment); // TODO: check if equals null on invalid id

        if (!$payment) {
            return $this->respondSuccess(
                'order updated',
                'SUCCESS_OK'
            );
        }

        $payment_id = $payment->metadata->order_id;
        $order = Order::wherePayment_id($payment_id)->first();
        $order->state = $payment->status;
        $order->save();

        return $this->respondSuccess(
            'order updated',
            'SUCCESS_OK'
        );
    }

    /**
     * Get Payment.
     *
     * @param [type] $id
     */
    protected function getPayment($id)
    {
        return Mollie::api()->payments()->get($id);
    }
}
