<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesOrderRequests
{
    /**
     * Validate creation of new order.
     *
     * @param Request $request
     */
    protected function validateCreate(Request $request)
    {
        $this->validate($request, [
            'products' => 'required|array',
            'products.*' => 'required|integer|distinct',
        ]);
    }

    /**
     * Validate webhook.
     *
     * @param Request $request
     */
    protected function validateWebhook(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string',
        ]);
    }
}
