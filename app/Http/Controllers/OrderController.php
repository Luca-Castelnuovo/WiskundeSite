<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Validators\ValidatesOrderRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;

class OrderController extends Controller
{
    use ValidatesOrderRequests;

    /**
     * Show order.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function status(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== $request->user_id) {
            return $this->respondError(
                'model not found',
                'CLIENT_ERROR_NOT_FOUND'
            );
        }

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $order->toArray()
        );
    }

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

        $product_ids = $request->get('products');
        $products = Product::findOrFail($product_ids);

        $price = $products->sum('price');
        $order = Order::create([
            'products' => $product_ids,
            'price' => $price,
            'user_id' => $request->user_id,
            'payment_id' => 'UNKOWN',
            'state' => 'open',
        ]);

        $priceString = number_format($price, 2, '.', '');
        $description = config('mollie.order_prefix').'#'.time();
        $redirectURL = config('mollie.redirectURL').$order->id;
        $webhookURL = config('mollie.webhookURL');
        $payment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => config('mollie.currency'),
                'value' => $priceString,
            ],
            'description' => $description,
            'redirectUrl' => $redirectURL,
            'webhookUrl' => $webhookURL,
        ]);

        $order->payment_id = $payment->id;
        $order->save();
        $payment = $this->getPayment($payment->id);

        return $this->respondSuccess(
            'order initialised',
            'SUCCESS_OK',
            [
                'payment_url' => $payment->getCheckoutUrl(),
            ]
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

        $payment_id = $request->get('id');

        try {
            $payment = $this->getPayment($payment_id);
        } catch (Exception $error) {
            return $this->respondSuccess(
                'order updated',
                'SUCCESS_OK'
            );
        }

        $order = Order::wherePayment_id($payment_id)->first();

        if (!$order) {
            // TODO: log error

            return $this->respondError(
                'order not found',
                'CLIENT_ERROR_NOT_FOUND'
            );
        }

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
     * @param string $id
     */
    protected function getPayment($id)
    {
        return Mollie::api()->payments()->get($id);
    }
}
