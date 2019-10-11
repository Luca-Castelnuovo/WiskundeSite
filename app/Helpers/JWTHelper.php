<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Arr;

class JWTHelper
{
    /**
     * Create access_token.
     *
     * @param string $type
     * @param int    $expires
     * @param array  $data
     *
     * @return string
     */
    public static function create($type, $expires, $data)
    {
        $head = [
            'iss' => config('tokens.jwt_token.iss'),
            'iat' => time(),
            'exp' => time() + $expires,
            'type' => $type,
        ];

        $payload = Arr::collapse([$head, $data]);

        return JWT::encode(
            $payload,
            config('tokens.jwt_token.private_key'),
            config('tokens.jwt_token.algorithm')
        );
    }

    /**
     * Decode and validate JWT.
     *
     * @param string $access_token
     * @param string $type
     *
     * @return bool
     */
    public static function decode($access_token, $type)
    {
        if (!$access_token) {
            throw new Exception('JWT not provided');
        }

        try {
            $credentials = JWT::decode(
                $access_token,
                config('tokens.jwt_token.public_key'),
                [config('tokens.jwt_token.algorithm')]
            );

            if ($credentials->type !== $type) {
                throw new Exception('JWT: Token type invalid');
            }
        } catch (ExpiredException $error) {
            throw new Exception('JWT: Token has expired');
        } catch (Exception $error) {
            throw new Exception('JWT: Token is invalid');
        }

        return $credentials;
    }
}
