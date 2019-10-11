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
            $verify_mail_token_JWT = $this->generate_email_token(
                $user->id,
                $user->verify_email_token
            );

            Mail::to($user->email)->send(new RegisterConfirmationMail(
                $user,
                $verify_mail_token_JWT
            ));

            return $this->respondError(
                'account not active',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $session = new Session();
        $session->user_id = $user->id;
        $session->save();

        $refresh_token = $this->generate_refresh_token($session->id);
        $access_token = $this->generate_access_token(
            $session->id,
            $session->user_id
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
        $this->validateRefresh($request);

        $refresh_JWT = $request->get('refresh_token');

        try {
            $credentials = JWTHelper::decode($refresh_JWT, 'refresh');
        } catch (Exception $error) {
            return $this->respondError(
                $error->getMessage(),
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $session = Session::findOrFail($credentials->sub);
        $json_credentials = json_encode($credentials, JSON_UNESCAPED_SLASHES);

        if (Hash::check($json_credentials, $session->credentials_hash_old)) {
            $session->delete();

            return $this->respondError(
                'token theft detected',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }
        $session->credentials_hash_old = Hash::make($json_credentials);
        $session->save();

        $new_refresh_token = $this->generate_refresh_token($session->id);
        $access_token = $this->generate_access_token(
            $session->id,
            $session->user_id
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
        Session::findOrFail($request->session_uuid)->delete();

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
        $verify_mail_token_JWT = $this->generate_email_token(
            $user->id,
            $verify_mail_token
        );

        $user->verify_email_token = $verify_mail_token;
        $user->save();

        Mail::to($user->email)->send(new RegisterConfirmationMail(
            $user,
            $verify_mail_token_JWT
        ));

        return $this->respondSuccess(
            'registration successful',
            'SUCCESS_CREATED',
            $user->toArray()
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

        $user = User::whereEmail($request->get('email'))->first();

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

        Mail::to($user->email)->send(new RequestResetPasswordMail(
            $user,
            $reset_password_token_JWT
        ));

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

        try {
            $user = $this->verify_token(
                $request->get('reset_password_token'),
                'reset_password'
            );
        } catch (Exception $error) {
            return $this->respondError(
                $error->getMessage(),
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $user->password = Hash::make($request->get('password'));
        $user->save();

        return $this->respondSuccess(
            'reset sucessfull',
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

        try {
            $this->verify_token(
                $request->get('verify_email_token'),
                'verify_email'
            );
        } catch (Exception $error) {
            return $this->respondError(
                $error->getMessage(),
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        return $this->respondSuccess(
            'verification successfull',
            'SUCCESS_OK'
        );
    }

    /**
     * Generate access_token.
     *
     * @param string $session_uuid
     * @param int    $user_id
     * @param string $role
     *
     * @return string;
     */
    protected function generate_access_token($session_uuid, $user_id, $role = 'student')
    {
        return JWTHelper::create(
            'auth',
            config('tokens.access_token.ttl'),
            [
                'sub' => $user_id,
                'session_uuid' => $session_uuid,
                'role' => $role,
            ]
        );
    }

    /**
     * Generate refresh_token.
     *
     * @param string $session_uuid
     *
     * @return string
     */
    protected function generate_refresh_token($session_uuid)
    {
        return JWTHelper::create(
            'refresh',
            config('tokens.refresh_token.ttl'),
            ['sub' => $session_uuid]
        );
    }

    /**
     * Genereate email verification token.
     *
     * @param int    $user_id
     * @param string $token
     *
     * @return string
     */
    protected function generate_email_token($user_id, $token)
    {
        return JWTHelper::create(
            'verify_email',
            config('tokens.verify_mail_token.ttl'),
            [
                'sub' => $user_id,
                'token' => $token,
            ]
        );
    }

    /**
     * Verification helper.
     *
     * @param string $token
     * @param string $type
     *
     * @return User $user
     */
    protected function verify_token($token, $type)
    {
        $credentials = JWTHelper::decode($token, $type);
        $user = User::findOrFail($credentials->sub);

        $db_column = $type.'_token';

        if (!$user->{$db_column} || $user->{$db_column} !== $credentials->token) {
            throw new Exception('token invalid');
        }

        $user->{$db_column} = null;
        $user->save();

        return $user;
    }
}
