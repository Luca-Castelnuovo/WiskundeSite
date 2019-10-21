<?php

namespace App\Http\Controllers;

use App\Mail\DeleteAccountMail;
use App\Models\Session;
use App\Models\User;
use App\Validators\ValidatesAccountsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        $this->middleware('authorization:teacher.admin', ['only' => [
            'showProducts',
        ]]);
    }

    /**
     * View user.
     *
     * @return JsonResponse
     */
    public function show()
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
     * @return JsonResponse
     */
    public function delete()
    {
        $user = $this->user();

        $user->sessions()->delete();
        Mail::to($user->email)->send(new DeleteAccountMail($user));

        $user->delete();

        return $this->respondSuccess(
            'account deleted',
            'SUCCESS_OK'
        );
    }

    /**
     * Show products.
     *
     * @return JsonResponse
     */
    public function showProducts()
    {
        $products = $this->user()->products();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['products' => $products->get()]
        );
    }

    /**
     * Show orders.
     *
     * @return JsonResponse
     */
    public function showOrders()
    {
        $orders = $this->user()->orders();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['products' => $orders->get()]
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
        $sessions = $this->user()->sessions()->get();

        $sessions = $sessions->map(function ($session) use ($request) {
            $session->current = $session->id === $request->session_uuid;

            return $session;
        });

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['sessions' => $sessions]
        );
    }

    /**
     * Revoke an session
     * Can't be current session.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function revokeSession(Request $request)
    {
        $this->validateRevoke($request);

        $session_uuid = $request->get('session_uuid');

        if ($session_uuid === $request->session_uuid) {
            return $this->respondError(
                'can\'t revoke current session',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        $session = Session::whereId($session_uuid)->whereUserId($request->user_id)->firstOrFail();
        $session->delete();

        return $this->respondSuccess(
            'session revoked',
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
