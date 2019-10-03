<?php

namespace App\Validators;

use Illuminate\Http\Request;

trait ValidatesAuthRequests
{
    /**
     * Validate login request input
     *
     * @param  Request $request
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|max:255|email',
            'password' => 'required',
        ]);
    }

    /**
     * Validate refresh request input
     *
     * @param  Request $request
     */
    protected function validateRefresh(Request $request)
    {
        $this->validate($request, [
            'session_uuid'  => 'required|size:36|alpha_dash',
            'refresh_token' => 'required|size:256|alpha_num'
        ]);
    }

    /**
     * Validate register request input
     *
     * @param  Request $request
     */
    protected function validateRegister(Request $request)
    {
        $this->validate($request, [
            'captcha_response'  => 'required',
            'name'              => 'required|max:50|alpha_num',
            'email'             => 'required|max:255|email|unique:users,email',
            'password'          => 'required|min:8',
        ]);
    }

    /**
     * Validate password reset request input
     *
     * @param  Request $request
     */
    protected function validateRequestPasswordReset(Request $request)
    {
        $this->validate($request, [
            'captcha_response'  => 'required',
            'email'             => 'required|email|exists:users,email'
        ]);

        // TODO: add response "Password reset completed" or something as not to show if a user exists.
    }

    /**
     * Validate password reset request input
     *
     * @param  Request $request
     */
    protected function validatePasswordReset(Request $request)
    {
        $token_length = config('tokens.reset_password_token.length');

        $this->validate($request, [
            'reset_password_token'  => "required|size:{$token_length}|exists:users,reset_password_token",
            'password'              => 'required'
        ]);
    }

    /**
     * Validate verify email request input
     *
     * @param  Request $request
     */
    protected function validateVerifyEmailToken(Request $request)
    {
        $token_length = config('tokens.verify_mail_token.length');

        $this->validate($request, [
            'verify_email_token'    => "required|size:{$token_length}|exists:users,verify_email_token",
        ]);
    }
}