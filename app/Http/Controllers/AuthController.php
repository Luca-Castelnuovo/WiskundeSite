<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\RegisterConfirmationMail;
use App\Mail\RequestResetPasswordMail;
use App\Helpers\AuthHelper;
use App\Helpers\CaptchaHelper;
use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesAuthRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller {
    use ValidatesAuthRequests;

    /**
     * Login user and return tokens
     *
     * @param Request $request
     *
     * @return mixed
     *
     * @throws
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return $this->respond([
                'errors' => [
                    'email or password' => ['is invalid'],
                ]
            ], HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED);
        }

        if ($user->verify_email_token !== null) {
            return $this->respond([
                'errors' => [
                    'account' => ['not active'],
                ]
            ], HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED);
        }

        return response()->json(
            AuthHelper::login(
                $user->id,
                $request->ip(),
                $request->header('user-agent')
            ),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Refreshes the access_token
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function refresh(Request $request) {
        $this->validateRefresh($request);

        return response()->json(
            AuthHelper::refresh(
                $request->get('session_uuid'),
                $request->get('refresh_token'),
                $request->ip(),
                $request->header('user-agent')
            ),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Revoke the refresh_token
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function logout(Request $request) {
        if (!AuthHelper::logout($request->user_id, $request->session_uuid)) {
            throw new ModelNotFoundException();
        }

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Register account
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function register(Request $request) {
        $this->validateRegister($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return response()->json(
                [
                    'error' => 'invalid captcha'
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'verify_email_token' => Str::random(config('tokens.verify_mail_token.length'))
        ]);
      
        Mail::to($request->get('email'))->send(new RegisterConfirmationMail($user));

        return response()->json(
            $user,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    /**
     * Request an reset password email
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws
     */
    public function requestResetPassword(Request $request) {
        $this->validateRequestPasswordReset($request);

        if (!CaptchaHelper::validate($request->get('captcha_response'))) {
            return response()->json(
                [
                    'error' => 'invalid captcha'
                ],
                HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED
            );
        }

        $user = User::where(
            'email',
            $request->get('email')
        )->get();

        $user->reset_password_token = Str::random(config('tokens.reset_password_token.length'));

        $user->save();

        Mail::to($request->get('email'))->send(new RequestResetPasswordMail($user));

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }

    /**
     * Confirm a password reset
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws
     */
    public function resetPassword(Request $request) {
        $this->validatePasswordReset($request);

        $user = User::where(
            'reset_password_token',
            $request->get('reset_password_token')
        )->first();

        $user->password = Hash::make($request->get('password'));
        
        $user->reset_password_token = null;

        $user->save();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }


    /**
     * Verify user email and activate account
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws
     */
    public function verifyEmail(Request $request) {
        $this->validateVerifyEmailToken($request);

        $user = User::where(
            'verify_email_token',
            $request->get('verify_email_token')
        )->first();
        
        $user->verify_email_token = null;
        
        $user->save();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
