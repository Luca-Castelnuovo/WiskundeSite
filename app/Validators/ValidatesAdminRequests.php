<?php

namespace App\Validators;

use App\Models\User;
use Illuminate\Http\Request;

trait ValidatesAdminRequests
{
    /**
     * Validate user info update.
     *
     * @param Request $request
     * @param User    $user
     */
    protected function validateUpdate(Request $request)
    {
        $this->validate($request, [
            'name' => 'sometimes|max:50|alpha_num',
            'email' => 'sometimes|max:255|email|unique:users,email',
            'password' => 'sometimes|min:8',
            'role' => 'sometimes|string|in:student,teacher',
            'verified' => 'sometimes|boolean',
        ]);
    }

    /**
     * Validate revoke request input.
     *
     * @param Request $request
     */
    protected function validateRevoke(Request $request)
    {
        $this->validate($request, [
            'session_uuid' => 'required|size:36|alpha_dash',
        ]);
    }
}
