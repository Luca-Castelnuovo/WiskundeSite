<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Mail\Mailable;

class ProductStateUpdate extends Mailable
{
    public $user;
    public $products;
    public $order;

    /**
     * Create a new message instance.
     *
     * @param User    $user
     * @param Product $products
     * @param Order   $order
     */
    public function __construct($user, $products, $order)
    {
        $this->user = $user;
        $this->products = $products;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'mail.OrderConfirmation',
            [
                'orderInfoURL' => config('app.domain').'/orders/'.$this->order->id,
            ]
        );
    }
}
