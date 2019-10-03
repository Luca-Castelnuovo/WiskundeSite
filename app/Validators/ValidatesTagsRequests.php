<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesTagsRequests
{
    /**
     * Validate creation of new product
     *
     * @param  Request $request
     */
    protected function validateCreate(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required|max:255|alpha|unique:tags,name',
            'color' => 'required|max:255|alpha_num'
        ]);
    }

    /**
     * Validate updating of existing product
     *
     * @param  Request $request
     * @param $tag
     */
    protected function validateUpdate(Request $request, $tag)
    {
        if ($tag->name === $request->input('name')) {
            $name_rule = 'sometimes|max:255|alpha';
        } else {
            $name_rule = 'sometimes|max:255|alpha|unique:tags,name';
        }

        $this->validate($request, [
            'name'  => $name_rule,
            'color' => 'sometimes|max:255|alpha_num'
        ]);
    }
}
