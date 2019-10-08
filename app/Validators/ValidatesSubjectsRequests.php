<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesSubjectsRequests
{
    /**
     * Validate creation of new product.
     *
     * @param Request $request
     */
    protected function validateCreate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|alpha|unique:subjects,name',
        ]);
    }

    /**
     * Validate updating of existing product.
     *
     * @param Request $request
     * @param $subject
     */
    protected function validateUpdate(Request $request, $subject)
    {
        if ($subject->name === $request->input('name')) {
            $name_rule = 'sometimes|max:255|alpha';
        } else {
            $name_rule = 'sometimes|max:255|alpha|unique:subjects,name';
        }

        $this->validate($request, [
            'name' => $name_rule,
        ]);
    }
}
