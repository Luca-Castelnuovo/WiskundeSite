<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JWTHelper
{
    /**
     * Create access_token.
     *
     * @param array $data
     *
     * @return string
     */
    public static function create($data)
    {
        $head = [
            'iss' => config('tokens.access_token.iss'),
            'iat' => time(),
            'exp' => time() + config('tokens.access_token.ttl'),
        ];

        array_push($payload, $data);
        $payload = Arr::collapse([$head, $data]);

        return JWT::encode(
            $payload,
            config('tokens.access_token.private_key'),
            config('tokens.access_token.algorithm')
        );
    }

    /**
     * Decode and validate JWT.
     *
     * @param string $access_token
     *
     * @return bool
     */
    public static function decode($access_token)
    {
        if (!$access_token) {
            throw new Exception('access_token not provided');
        }

        try {
            $credentials = JWT::decode(
                $access_token,
                config('tokens.access_token.public_key'),
                [config('tokens.access_token.algorithm')]
            );
        } catch (ExpiredException $error) {
            throw new Exception('access_token has expired');
        } catch (Exception $error) {
            throw new Exception('access_token invalid');
        }

        return $credentials;
    }
}
