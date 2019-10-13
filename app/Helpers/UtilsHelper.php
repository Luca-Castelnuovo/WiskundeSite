<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class UtilsHelper
{
    /**
     * Generate random token.
     *
     * @return string
     */
    public static function generateRandomToken()
    {
        return Str::random(config('tokens.jwt_token.length'));
    }
}
