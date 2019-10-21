<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class UtilsHelper
{
    /**
     * Generate random token.
     *
     * @return string
     */
    public static function generateRandomToken()
    {
        return Str::random(config('tokens.jwt_token.length'));
    }

    /**
     * Check if user owns product.
     *
     * @param int     $user_id
     * @param Product $product
     *
     * @return bool
     */
    public static function productOwnedByUser($user_id, $product)
    {
        if ($user_id === $product->user_id) {
            return true;
        }

        $order = Order::whereUserId($user_id)
            ->whereState('paid')
            ->whereJsonContains('products', [(int) $product->id])
            ->first()
        ;

        return (bool) $order;
    }
}
