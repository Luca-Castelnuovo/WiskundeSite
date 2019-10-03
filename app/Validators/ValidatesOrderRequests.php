<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesOrderRequests
{
    /**
     * Validate creation of new order
     *
     * @param  Request $request
     */
    protected function validateCreate(Request $request)
    {
        $this->validate($request, [
            'item'  => 'required'
        ]);
    }
}
