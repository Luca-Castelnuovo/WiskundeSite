<?php

namespace App\Validators;

use App\Models\User;
use Illuminate\Http\Request;

trait ValidatesAccountsRequests
{
    /**
     * Validate user info update
     *
     * @param  Request $request
     * @param  User $user
     */
    protected function validateUpdate(Request $request, User $user)
    {
        if ($user->email === $request->input('email')) {
            $email_rule = 'email';
        } else {
            $email_rule = 'sometimes|max:255|email|unique:users,email';
        }

        $this->validate($request, [
            'name'      => 'sometimes|max:50|alpha_num',
            'email'     => $email_rule,
            'password'  => 'sometimes|min:8',
        ]);
    }

    /**
     * Validate revoke request input
     *
     * @param  Request $request
     */
    protected function validateRevoke(Request $request)
    {
        $this->validate($request, [
            'session_uuid'  => 'required|size:36|alpha_dash',
        ]);
    }
}