<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesAuthRequests
{
    /**
     * Validate login request input.
     *
     * @param Request $request
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|max:255|email',
            'password' => 'required',
        ]);
    }

    /**
     * Validate refresh request input.
     *
     * @param Request $request
     */
    protected function validateRefresh(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required',
        ]);
    }

    /**
     * Validate register request input.
     *
     * @param Request $request
     */
    protected function validateRegister(Request $request)
    {
        $this->validate($request, [
            'captcha_response' => 'required|max:512|alpha_dash',
            'name' => 'required|max:50|alpha_num',
            'email' => 'required|max:255|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|string|in:student,teacher',
        ]);
    }

    /**
     * Validate password reset request input.
     *
     * @param Request $request
     */
    protected function validateRequestPasswordReset(Request $request)
    {
        $this->validate($request, [
            'captcha_response' => 'required|max:512|alpha_dash',
            'email' => 'required|email',
        ]);
    }

    /**
     * Validate password reset request input.
     *
     * @param Request $request
     */
    protected function validatePasswordReset(Request $request)
    {
        $this->validate($request, [
            'reset_password_token' => 'required',
            'password' => 'required|min:8',
        ]);
    }

    /**
     * Validate verify email request input.
     *
     * @param Request $request
     */
    protected function validateVerifyEmailToken(Request $request)
    {
        $this->validate($request, [
            'verify_email_token' => 'required',
        ]);
    }
}
