<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\Request;

class GateHelper
{
    public static function product_show(Request $request, $product)
    {
        if ('admin' === $request->role) {
            return;
        }

        if ($product->user_id === $request->user_id) {
            return;
        }

        if ('accepted' !== $product->state) {
            throw new Exception('product can\'t be accessed while '.$product->state);
        }
    }

    public static function product_open(Request $request, $product)
    {
        if ('admin' === $request->role) {
            return;
        }

        if ('denied' === $product->state) {
            throw new Exception('product can\'t be accessed while denied');
        }

        if ($product->user_id === $request->user_id) {
            return;
        }

        if ('accepted' !== $product->state) {
            throw new Exception('product can\'t be accessed while '.$product->state);
        }

        if (!UtilsHelper::productOwnedByUser($request->user_id, $product)) {
            throw new Exception('product not purchased');
        }
    }

    public static function product_update(Request $request, $product)
    {
        if ('admin' === $request->role) {
            return;
        }

        if ($product->user_id !== $request->user_id) {
            throw new Exception('product is not owned by you');
        }

        if ('denied' === $product->state) {
            throw new Exception('product can\'t be accessed while denied');
        }

        if ($request->get('state')) {
            throw new Exception('product state can\'t be updated by teachers');
        }
    }

    public static function product_delete(Request $request, $product)
    {
        if ('admin' === $request->role) {
            return;
        }

        if ($product->user_id !== $request->user_id) {
            throw new Exception('product is not owned by you');
        }
    }
}
