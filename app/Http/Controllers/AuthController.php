<?php

namespace App\Http\Controllers;

use App\Helpers\CaptchaHelper;
use App\Helpers\JWTHelper;
use App\Mail\RegisterConfirmationMail;
use App\Mail\RequestResetPasswordMail;
use App\Models\Session;
use App\Models\User;
use App\Validators\ValidatesAuthRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ValidatesAuthRequests;

    /**
     * Authenticate User,
     * Returns access_token and refresh_token.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = User::whereEmail($request->get('email'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return $this->respondError(
                'email or password is invalid',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        if ($user->verify_email_token) {
            return $this->respondError(
                'account not active',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $session = new Session();

        $refresh_token = Str::random(config('tokens.refresh_token.length'));
        $session->user_id = $user->id;
        $session->refresh_token_hash = Hash::make($refresh_token);
        $session->refresh_token_expires = time() + config('tokens.refresh_token.ttl');

        $session->save();

        $access_token = $access_token = JWTHelper::create(
            'auth',
            config('tokens.access_token.ttl'),
            [
                'sub' => $session->user_id,
                'session_uuid' => $session->id,
                'role' => 'student',
            ]
        );

        return $this->respondSuccess(
            'login successful',
            'SUCCESS_OK',
            [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
            ]
        );
    }

    /**
     * Refresh access_token
     * Returns access_token and refresh_token.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        // $this->validateRefresh($request);

        $refresh_token = $request->get('refresh_token');
        $session = Session::findOrFail($request->get('session_uuid'));

        dd(Hash::check($refresh_token, $session->refresh_token_hash));

        if (!Hash::check($refresh_token, $session->refresh_token_hash)) {
            return $this->respondError(
                'session invalid',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        if ($session->refresh_token_expires->isPast()) {
            return $this->respondError(
                'session expired',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        if (Hash::check($refresh_token, $session->refresh_token_hash_old)) {
            $session->delete();

            return $this->respondError(
                'token theft detected',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $new_refresh_token = Str::random(config('tokens.refresh_token.length'));
        $session->refresh_token_hash_old = $session->refresh_token_hash;
        $session->refresh_token_hash = Hash::make($new_refresh_token);
        $session->refresh_token_expires = time() + config('tokens.refresh_token.ttl');

        $session->save();

        $access_token = JWTHelper::create(
            'auth',
            config('tokens.access_token.ttl'),
            [
                'sub' => $session->user_id,
                'session_uuid' => $session->id,
                'role' => 'student',
            ]
        );

        return $this->respondSuccess(
            'session refreshed',
            'SUCCESS_OK',
            [
                'access_token' => $access_token,
                'refresh_token' => $new_refresh_token,
            ]
        );
    }

    /**
     * Revoke refresh_token
     * Returns 204.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $session = Session::findOrFail($request->session_uuid);

        $session->delete();

        return $this->respondSuccess(
            'logout successful',
            'SUCCESS_OK'
        );
    }

    /**
     * Register account
     * Sends email
     * Returns user model.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $this->validateRegister($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return $this->respondError(
                'invalid captcha',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $verify_mail_token = Str::random(config('tokens.verify_mail_token.length'));
        $verify_mail_token_JWT = JWTHelper::create(
            'verify_email',
            config('tokens.verify_mail_token.ttl'),
            [
                'sub' => $user->id,
                'token' => $verify_mail_token,
            ]
        );

        $user->verify_email_token = $verify_mail_token;
        $user->save();

        // Mail::to($request->get('email'))->send(new RegisterConfirmationMail($user, $verify_mail_token_JWT));

        return $this->respondSuccess(
            'registration successful',
            'SUCCESS_CREATED',
            $user
        );
    }

    /**
     * Request Reset
     * Sends email
     * Returns 204.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function requestResetPassword(Request $request)
    {
        $this->validateRequestPasswordReset($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return $this->respondError(
                'invalid captcha',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $user = User::whereEmail($request->get('email'))->get();

        if (!$user) {
            // Fake success
            return $this->respondSuccess(
                'reset requested',
                'SUCCESS_OK'
            );
        }

        $reset_password_token = Str::random(config('tokens.reset_password_token.length'));
        $reset_password_token_JWT = JWTHelper::create(
            'reset_password',
            config('tokens.reset_password_token.ttl'),
            [
                'sub' => $user->id,
                'token' => $reset_password_token,
            ]
        );

        $user->reset_password_token = $reset_password_token;
        $user->save();

        Mail::to($request->get('email'))->send(new RequestResetPasswordMail($user, $reset_password_token_JWT));

        return $this->respondSuccess(
            'reset requested',
            'SUCCESS_OK'
        );
    }

    /**
     * Confirm Reset
     * Returns 204.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $this->validatePasswordReset($request);

        $reset_password_token = $request->get('reset_password_token');

        try {
            $credentials = JWTHelper::decode($reset_password_token, 'reset_password');
        } catch (Exception $error) {
            return $this->respondError(
                $error->getMessage(),
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $user = User::findOrFail($credentials->sub);

        if (!$user->reset_password_token) {
            return $this->respondError(
                'password reset not active',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        if ($user->reset_password_token !== $credentials->token) {
            return $this->respondError(
                'invalid reset token',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        $user->password = Hash::make($request->get('password'));
        $user->reset_password_token = null;
        $user->save();

        return $this->respondSuccess(
            'reset confirmed',
            'SUCCESS_OK'
        );
    }

    /**
     * Verify email
     * Returns 204.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $this->validateVerifyEmailToken($request);

        $verify_email_token = $request->get('verify_email_token');

        try {
            $credentials = JWTHelper::decode($verify_email_token, 'verify_email');
        } catch (Exception $error) {
            return $this->respondError(
                $error->getMessage(),
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $user = User::findOrFail($credentials->sub);

        if (!$user->verify_email_token) {
            return $this->respondError(
                'email already activated',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        if ($user->verify_email_token !== $credentials->token) {
            return $this->respondError(
                'invalid verification token',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        $user->verify_email_token = null;
        $user->save();

        return $this->respondSuccess(
            'verification successfull',
            'SUCCESS_OK'
        );
    }
}
