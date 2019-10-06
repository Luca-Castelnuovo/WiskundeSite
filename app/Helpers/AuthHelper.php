<?php

namespace App\Helpers;

use App\Models\Session;

class AuthHelper
{
    /**
     * Revoke all refresh_tokens associated with a user.
     *
     * @param $user_id
     *
     * @return mixed
     */
    public static function revokeAllRefreshTokens($user_id)
    {
        $sessions = Session::where('user_id', $user_id);

        return $sessions->delete();
    }

    /**
     * Create access_token
     * 1. Set Payload
     * 2. Sign
     * 3. Return JWT.
     *
     * @param $session_uuid
     * @param $user_id
     *
     * @return string
     */
    private static function createAccessToken($session_uuid, $user_id)
    {
        return JWTHelper::create(
            'auth',
            config('tokens.access_token.ttl'),
            [
                'sub' => $user_id,
                'session_uuid' => $session_uuid,
                'role' => 'student', // TODO: implement role-based auth system
            ]
        );
    }
}
