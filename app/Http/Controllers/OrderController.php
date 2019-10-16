<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Validators\ValidatesOrderRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mollie\Laravel\Facades\Mollie;

class OrderController extends Controller
{
    use ValidatesOrderRequests;

    /**
     * All orders.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $orders = Order::whereUserId($request->user_id)->get();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['orders' => $orders]
        );
    }

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
        $products = Product::findOrFail($product_ids)->whereState('accepted')->where('user_id', '!=', $request->user_id)->get();

        if (!$products) {
            Log::critical('User has illegall items in cart');

            return $this->respondError(
                'products can\'t be purchased',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

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
        $redirectURL = config('mollie.redirect_url').$order->id;
        $webhookURL = config('mollie.webhook_url');
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

        $order = Order::wherePaymentId($payment_id)->first();

        if (!$order) {
            Log::critical('Order not connected to payment_id: '.$payment_id);

            return $this->respondError(
                'order not found',
                'CLIENT_ERROR_NOT_FOUND'
            );
        }

        $order->state = $payment->status;
        $order->save();

        if ('paid' === $order->state) {
            $user = User::findOrFail($order->user_id);
            $products = Product::findOrFail($order->products);

            Mail::to($user->email)->send(new OrderConfirmation($user, $products, $order));
        }

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
