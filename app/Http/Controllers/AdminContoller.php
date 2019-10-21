<?php

namespace App\Http\Controllers;

use App\Mail\DeleteAccountMail;
use App\Models\Session;
use App\Models\User;
use App\Validators\ValidatesAdminRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    use ValidatesAdminRequests;

    /**
     * Define authorization.
     */
    public function __construct()
    {
        $this->middleware('authorization:admin');
    }

    /**
     * Show user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $users = User::all();

        $users = $users->map(function ($user) use ($request) {
            $user->current = $user->id === $request->user_id;

            return $user;
        });

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['users' => $users]
        );
    }

    /**
     * Show user.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $user = User::findOrFail($id);

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
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $user = User::findOrFail($id);

        $this->validateUpdate($request, $user);

        $user->update([
            'name' => $request->get('name', $user->name),
            'email' => $request->get('email', $user->email),
            'password' => $request->has('password') ? Hash::make($request->input('password')) : $user->password,
            'role' => $request->get('role', $user->role),
            'verified' => $request->get('verified', $user->verified),
        ]);

        $user->save();

        return $this->respondSuccess(
            'user updated',
            'SUCCESS_OK',
            $user->toArray()
        );
    }

    /**
     * Delete user
     * Revokes all sessions.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $user = User::findOrFail($id);
        $user->sessions()->delete();

        Mail::to($user->email)->send(new DeleteAccountMail($user));

        $user->delete();

        return $this->respondSuccess(
            'user deleted',
            'SUCCESS_OK'
        );
    }

    /**
     * Show products.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function showProducts(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $products = User::findOrFail($id)->products();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['products' => $products->get()]
        );
    }

    /**
     * Show orders.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function showOrders(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $orders = User::findOrFail($id)->orders();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['orders' => $orders->get()]
        );
    }

    /**
     * Show sessions.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function showSessions(Request $request, $id)
    {
        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $sessions = User::findOrFail($id)->sessions();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['sessions' => $sessions->get()]
        );
    }

    /**
     * Revoke an session.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function revokeSession(Request $request, $id)
    {
        $this->validateRevoke($request);

        if ($this->checkID($request, $id)) {
            return $this->checkID($request, $id);
        }

        $session_uuid = $request->get('session_uuid');

        $session = Session::whereId($session_uuid)->whereUserId($id)->firstOrFail();
        $session->delete();

        return $this->respondSuccess(
            'user session revoked',
            'SUCCESS_OK'
        );
    }

    /**
     * Validate target isn't current account.
     *
     * @param Request $request
     * @param string  $id_request
     *
     * @return null|JsonResponse
     */
    protected function checkID(Request $request, $id_request)
    {
        if ($id_request == $request->user_id) {
            return $this->respondError(
                'can\'t perform actions on your own account',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }
    }
}
