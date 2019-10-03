<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reset_password_token',
        'verify_email_token'
    ];

    /**
     * The fields that will be transformed by Carbon
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'reset_password_token',
        'verify_email_token'
    ];

    /**
     * Get all the refresh_tokens by the user.
     */
    public function refreshTokens()
    {
        return $this->hasMany(Session::class);
    }
}
