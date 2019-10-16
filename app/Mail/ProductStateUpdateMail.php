<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Mail\Mailable;

class ProductStateUpdateMail extends Mailable
{
    public $user;
    public $product;
    public $state;
    public $reason;

    /**
     * Create a new message instance.
     *
     * @param User       $user
     * @param Product    $product
     * @param string     $state
     * @param null|mixed $reason
     */
    public function __construct($user, $product, $state, $reason = null)
    {
        $this->user = $user;
        $this->product = $product;
        $this->state = $state;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'mail.ProductStateUpdate',
            [
                'viewProductURL' => config('app.domain').'/products/'.$this->product->id,
                'state' => $this->state,
                'reason' => $this->reason,
            ]
        );
    }
}
