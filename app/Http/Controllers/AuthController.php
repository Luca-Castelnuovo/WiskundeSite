<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\CaptchaHelper;
use App\Helpers\HttpStatusCodes;
use App\Mail\RegisterConfirmationMail;
use App\Mail\RequestResetPasswordMail;
use App\Models\User;
use App\Validators\ValidatesAuthRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ValidatesAuthRequests;

    /**
     * Login user and return tokens.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return $this->respond([
                'errors' => [
                    'email or password' => ['is invalid'],
                ],
            ], HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED);
        }

        if (null !== $user->verify_email_token) {
            return $this->respond([
                'errors' => [
                    'account' => ['not active'],
                ],
            ], HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED);
        }

        return response()->json(
            AuthHelper::login($user->id),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Refreshes the access_token.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        $this->validateRefresh($request);

        return response()->json(
            AuthHelper::refresh(
                $request->get('session_uuid'),
                $request->get('refresh_token')
            ),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Revoke the refresh_token.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        if (!AuthHelper::logout($request->user_id, $request->session_uuid)) {
            throw new ModelNotFoundException();
        }

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Register account.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $this->validateRegister($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return response()->json(
                [
                    'error' => 'invalid captcha',
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $verify_mail_token = Str::random(config('tokens.verify_mail_token.length'));
        $verify_mail_token_hash = Hash::make($verify_mail_token);

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'verify_email_token' => $verify_mail_token_hash,
        ]);

        Mail::to($request->get('email'))->send(new RegisterConfirmationMail($user, $verify_mail_token));

        return response()->json(
            $user,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    /**
     * Request an reset password email.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function requestResetPassword(Request $request)
    {
        $this->validateRequestPasswordReset($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return response()->json(
                [
                    'error' => 'invalid captcha',
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $user = User::where(
            'email',
            $request->get('email')
        )->get();

        $reset_password_token = Str::random(config('tokens.reset_password_token.length'));
        $reset_password_token_hash = Hash::make($reset_password_token);
        $user->reset_password_token = $reset_password_token_hash;

        $user->save();

        Mail::to($request->get('email'))->send(new RequestResetPasswordMail($user, $reset_password_token));

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Confirm a password reset.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $this->validatePasswordReset($request);

        $reset_password_token_hash = Hash::make($request->get('reset_password_token'));

        $user = User::where(
            'reset_password_token',
            $reset_password_token_hash
        )->first();

        if (null === $user) {
            return response()->json(
                [
                    'error' => 'invalid reset token',
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $user->password = Hash::make($request->get('password'));

        $user->reset_password_token = null;

        $user->save();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Verify user email and activate account.
     *
     * @param Request $request
     *
     * @throws
     *
     * @return JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $this->validateVerifyEmailToken($request);

        $verify_email_token_hash = Hash::make($request->get('verify_email_token'));

        $user = User::where(
            'verify_email_token',
            $verify_email_token_hash
        )->first();

        dd($user, $verify_email_token_hash);

        if (null === $user) {
            return response()->json(
                [
                    'error' => 'invalid verification token',
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $user->verify_email_token = null;

        $user->save();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
