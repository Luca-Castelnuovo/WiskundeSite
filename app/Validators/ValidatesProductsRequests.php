<?php

namespace App\Validators;

use App\Models\Product;
use Illuminate\Http\Request;

trait ValidatesProductsRequests
{
    /**
     * Validate creation of new product.
     *
     * @param Request $request
     */
    protected function validateCreate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|regex:/[a-zA-Z\h]+/|unique:products,name',
            'price' => 'required|between:0,100|numeric',
            'subject' => 'required|max:255|string',
            'class' => 'required|max:255|string',
            'method' => 'required|max:255|string',
        ]);
    }

    /**
     * Validate updating of existing product.
     *
     * @param Request $request
     * @param Product $product
     */
    protected function validateUpdate(Request $request, Product $product)
    {
        if ($product->name === $request->input('name')) {
            $name_rule = 'sometimes|max:255|regex:/[a-zA-Z\h]+/';
        } else {
            $name_rule = 'sometimes|max:255|regex:/[a-zA-Z\h]+/|unique:products,name';
        }

        $this->validate($request, [
            'name' => $name_rule,
            'price' => 'sometimes|between:0,100|numeric',
            'subject' => 'sometimes|max:255|string',
            'class' => 'sometimes|max:255|string',
            'method' => 'sometimes|max:255|string',
            'state' => 'sometimes|string|in:accepted,under_review,denied',
            'reason' => 'sometimes',
        ]);
    }
}
