<?php

namespace App\Http\Controllers;

use App\Mail\DeleteAccountMail;
use App\Models\User;
use App\Validators\ValidatesAdminRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountsController extends Controller
{
    use ValidatesAdminRequests;

    /**
     * Show user.
     *
     * @return JsonResponse
     */
    public function all()
    {
        $users = User::all();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['users' => $users]
        );
    }

    /**
     * Show user.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showProducts($id)
    {
        $products = User::findOrFail($id)->products();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['products' => $products->get()]
        );
    }

    /**
     * Show sessions.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showSessions($id)
    {
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
     *
     * @return JsonResponse
     */
    public function revokeSession(Request $request)
    {
        $this->validateRevoke($request);

        Session::findOrFail($request->get('session_uuid'))->delete();

        return $this->respondSuccess(
            'user session revoked',
            'SUCCESS_OK'
        );
    }
}
