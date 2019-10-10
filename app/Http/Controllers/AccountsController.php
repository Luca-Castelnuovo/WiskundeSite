<?php

namespace App\Http\Controllers;

use App\Mail\DeleteAccountMail;
use App\Models\Session;
use App\Models\User;
use App\Validators\ValidatesAccountsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountsController extends Controller
{
    use ValidatesAccountsRequests;

    /**
     * Add Request to class.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * View user.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = $this->user();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $user->toArray()
        );
    }

    /**
     * Update user
     * Returns updated user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $user = $this->user();

        $this->validateUpdate($request, $user);

        $user->update([
            'name' => $request->get('name', $user->name),
            'email' => $request->get('email', $user->email),
            'password' => $request->has('password') ? Hash::make($request->input('password')) : $user->password,
        ]);

        $user->save();

        return $this->respondSuccess(
            'account updated',
            'SUCCESS_OK',
            $user->toArray()
        );
    }

    /**
     * Delete user
     * Revokes all sessions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $user = $this->user();

        Session::whereUser_id($user->id)->delete();
        Mail::to($user->email)->send(new DeleteAccountMail($user));

        $user->delete();

        return $this->respondSuccess(
            'account deleted',
            'SUCCESS_OK'
        );
    }

    /**
     * Show sessions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showSessions(Request $request)
    {
        $user = $this->user();
        $refresh_tokens = $user->refreshTokens();
        $refresh_tokens_output = $refresh_tokens->get()->toArray();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $refresh_tokens_output
        );
    }

    /**
     * Revoke an session.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function revoke(Request $request)
    {
        $this->validateRevoke($request);

        $revokable_session_uuid = $request->get('session_uuid');
        $revokable_session = Session::findOrFail($revokable_session_uuid);

        if ($revokable_session_uuid === $request->session_uuid) {
            return $this->respondError(
                'can\'t revoke current session',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        if ($revokable_session->user_id !== $request->user_id) {
            return $this->respondError(
                'session not found',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        $revokable_session->delete();

        return $this->respondSuccess(
            'session_revoked',
            'SUCCESS_OK'
        );
    }

    /**
     * Get user from JWT.
     *
     * @return User
     */
    protected function user()
    {
        return User::findOrFail($this->request->user_id);
    }
}
