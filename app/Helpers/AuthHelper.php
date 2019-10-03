<?php

namespace App\Helpers;

use Exception;
use App\Models\Session;
use App\Mail\TokenTheftMail;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthHelper {

    /**
     * Issue access credentials
     * 1. Create refresh_token
     * 2. Create access_token
     * 3. Return credentials
     *
     * @param $user_id
     * @param $user_ip
     * @param $user_agent
     *
     * @return array
     */
    public static function login($user_id, $user_ip, $user_agent)
    {
        $session = AuthHelper::createRefreshToken(
            $user_id,
            $user_ip,
            $user_agent
        );

        $access_token = AuthHelper::createAccessToken(
            $session->session_uuid,
            $user_id,
            $user_ip
        );

        return [
            'access_token' => $access_token,
            'refresh_token' => $session->refresh_token,
        ];
    }

    /**
     * Revoke refresh_token
     * 1. Revoke refresh_token
     *
     * @param $user_id
     * @param $session_uuid
     *
     * @return mixed
     */
    public static function logout($user_id, $session_uuid)
    {
        // check if user owns session
        $session = Session::where([
            'id' => $session_uuid,
            'user_id' => $user_id
        ])->first();

        if (!$session) {
            return false;
        }

        return AuthHelper::revokeRefreshToken($session_uuid);
    }

    /**
     * Refresh access credentials
     * 1. Validate refresh_token
     * 2. Update refresh_token
     * 3. Create new access_token
     * 4. Return new credentials
     *
     * @param $session_uuid
     * @param $refresh_token
     * @param $user_ip
     * @param $user_agent // This is intended to show the user the type of device logged in
     *
     * @return array|bool
     */
    public static function refresh($session_uuid, $refresh_token, $user_ip, $user_agent)
    {
        $user_id = AuthHelper::validateRefreshToken($session_uuid, $refresh_token);

        if (!$user_id) {
            return false;
        }

        $session = AuthHelper::updateRefreshToken(
            $session_uuid,
            $user_id,
            $user_ip,
            $user_agent
        );

        $access_token = AuthHelper::createAccessToken(
            $session->session_uuid,
            $user_id,
            $user_ip
        );

        return [
            'access_token' => $access_token,
            'refresh_token' => $session->refresh_token,
        ];
    }

    /**
     * Revoke all refresh_tokens associated with a user
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
     * Revoke a specific refresh_token-
     *
     * @param $session_uuid
     *
     * @return mixed
     */
    private static function revokeRefreshToken($session_uuid)
    {
        $session = Session::findOrFail($session_uuid);
        return $session->delete();
    }

    /**
     * Parses Authorization header
     *
     * @param $header_value
     *
     * @return bool|string
     */
    public static function parseAuthHeader($header_value)
    {
        if (!Str::startsWith($header_value, 'Bearer ')) {
            return false;
        }

        return Str::replaceFirst(
            'Bearer ',
            '',
            $header_value
        );
    }

    /**
     * Create access_token
     * 1. Set Payload
     * 2. Sign
     * 3. Return JWT
     *
     * @param $session_uuid
     * @param $user_id
     * @param $user_ip
     *
     * @return string
     */
    private static function createAccessToken($session_uuid, $user_id, $user_ip)
    {
        $payload = [
            'iss' => config('tokens.access_token.iss'),
            'sub' => $user_id,
            'sub_ip' => $user_ip,
            'session_uuid' => $session_uuid,
            'iat' => time(),
            'exp' => time() + config('tokens.access_token.ttl'),
        ];

        return JWT::encode(
            $payload,
            config('tokens.access_token.private_key'),
            config('tokens.access_token.algorithm')
        );
    }

    /**
     * Create refresh_token and session
     * 1. Create Session
     * 2. Set token
     * 3. Hash token
     * 4. Session is saved
     * 5. Return refresh_token and session_uuid
     *
     * @param $user_id
     * @param $user_ip
     * @param $user_agent
     *
     * @return object
     */
    private static function createRefreshToken($user_id, $user_ip, $user_agent)
    {
        $session = new Session();

        $refresh_token = Str::random(config('tokens.refresh_token.length'));
        $refresh_token_hash = Hash::make($refresh_token);
        $refresh_token_expires = time() + config('tokens.refresh_token.ttl');

        $session->user_id = $user_id;
        $session->user_ip = $user_ip;
        $session->user_agent = $user_agent;

        $session->refresh_token_hash = $refresh_token_hash;
        $session->refresh_token_hash_old = null;
        $session->refresh_token_expires = $refresh_token_expires;

        $session->save();

        return (object) [
            'refresh_token' => $refresh_token,
            'session_uuid' => $session->id,
        ];
    }

    /**
     * Update refresh_token
     * 1. Search Session
     * 2. Update token
     * 3. Hash token
     * 4. Set old refresh_token_hash to refresh_token_hash_old
     * 5. Session is saved
     * 6. Return refresh_token and session_uuid
     *
     * @param $session_uuid
     * @param $user_id
     * @param $user_ip
     * @param $user_agent
     *
     * @return string
     */
    private static function updateRefreshToken($session_uuid, $user_id, $user_ip, $user_agent)
    {
        $session = Session::findOrFail($session_uuid);

        $refresh_token = Str::random(config('tokens.refresh_token.length'));
        $refresh_token_hash = Hash::make($refresh_token);
        $refresh_token_expires = time() + config('tokens.refresh_token.ttl');

        $session->user_id = $user_id;
        $session->user_ip = $user_ip;
        $session->user_agent = $user_agent;

        $session->refresh_token_hash_old = $session->refresh_token_hash;
        $session->refresh_token_hash = $refresh_token_hash;
        $session->refresh_token_expires = $refresh_token_expires;

        $session->save();

        return (object) [
            'refresh_token' => $refresh_token,
            'session_uuid' => $session->id,
        ];
    }

    /**
     * Validate access_token
     * 1. Check if empty
     * 2. Check if has expired
     * 3. Check signature
     * 4. Check IP
     * 5. Return JWT payload
     *
     * @param $access_token
     * @param $user_ip
     *
     * @return object
     */
    public static function validateAccessToken($access_token, $user_ip)
    {
        if (!$access_token) {
            return (object) [
                'error' => 'access_token not provided',
                'http' => HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED,
            ];
        }

        try {
            $credentials = JWT::decode(
                $access_token,
                config('tokens.access_token.public_key'),
                [config('tokens.access_token.algorithm')]
            );
        } catch (ExpiredException $error) {
            return (object) [
                'error' => 'access_token has expired',
                'http' => HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED,
            ];
        } catch (Exception $error) {
            return (object) [
                'error' => 'access_token invalid',
                'http' => HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED,
            ];
        }

        if ($credentials->sub_ip !== $user_ip) {
            return (object) [
                'error' => 'access_token invalid',
                'http' => HttpStatusCodes::CLIENT_ERROR_UNAUTHORIZED,
            ];
        }

        return $credentials;
    }

    /**
     * Validate refresh_token
     * 1. Find session
     * 2. Check if refresh_token was stolen
     * 3. Check if token is valid
     * 4. Check if token has expired
     * 5. Return success
     *
     * @param $session_uuid
     * @param $refresh_token
     *
     * @return int
     */
    private static function validateRefreshToken($session_uuid, $refresh_token)
    {
        $session = Session::findOrFail($session_uuid);

        // Check if refresh_token was stolen (https://hackernoon.com/the-best-way-to-securely-manage-user-sessions-91f27eeef460)
        if (
            isset($session->refresh_token_hash_old) &&
            Hash::check($refresh_token, $session->refresh_token_hash_old)
        ) {
            AuthHelper::revokeRefreshToken($session_uuid);
            Mail::to(config('app.mail'))->send(new TokenTheftMail($session_uuid));

            return false;
        }

        // Check if refresh_token is valid
        if (!Hash::check($refresh_token, $session->refresh_token_hash)) {
            return false;
        }

        // Check if session hasn't expired
        if ($session->refresh_token_expires->isPast()) {
            return false;
        }

        return $session->user_id;
    }
}