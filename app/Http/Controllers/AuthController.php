<?php

namespace App\Http\Controllers;

use App\Helpers\CaptchaHelper;
use App\Helpers\JWTHelper;
use App\Helpers\UtilsHelper;
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
            $verify_mail_token_JWT = $this->generateJWT(
                'verify_email',
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
        $session->token = UtilsHelper::generateRandomToken();

        $session->save();

        $access_token = $this->generateJWT(
            'access',
            $user->id,
            $session->id,
            ['role' => $user->role]
        );
        $refresh_token = $this->generateJWT(
            'refresh',
            $session->id,
            $session->token
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
        $user = User::findOrFail($session->user_id);

        if ($credentials->token !== $session->token) {
            $session->delete();

            // TODO: log error

            return $this->respondError(
                'token theft detected',
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $session->token = UtilsHelper::generateRandomToken();
        $session->save();

        $access_token = $this->generateJWT(
            'access',
            $user->id,
            $session->id,
            ['role' => $user->role]
        );
        $refresh_token = $this->generateJWT(
            'refresh',
            $session->id,
            $session->token
        );

        return $this->respondSuccess(
            'session refreshed',
            'SUCCESS_OK',
            [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
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

        $verify_mail_token = UtilsHelper::generateRandomToken();
        $verify_mail_token_JWT = $this->generateJWT(
            'verify_email',
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

        $reset_password_token = UtilsHelper::generateRandomToken();
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
            $user = $this->verifyJWT(
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
            $this->verifyJWT(
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
     * Generate JWT's.
     *
     * @param string $type
     * @param string $sub
     * @param string $token
     * @param array  $additional_data
     *
     * @return string
     */
    private function generateJWT($type, $sub, $token, $additional_data = null)
    {
        $data = [
            'sub' => $sub,
            'token' => $token,
        ];

        if ($additional_data) {
            $data += $additional_data;
        }

        $ttl = config("tokens.{$type}_token.ttl");

        return JWTHelper::create($type, $ttl, $data);
    }

    /**
     * Verification helper.
     *
     * @param string $token
     * @param string $type
     *
     * @return User $user
     */
    private function verifyJWT($token, $type)
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
