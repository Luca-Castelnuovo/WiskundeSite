<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'verified',
        'reset_password_token',
        'verify_email_token',
    ];

    /**
     * The fields that will be transformed by Carbon.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'reset_password_token',
        'verify_email_token',
    ];

    /**
     * Get all the refresh_tokens by the user.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get all the orders by the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all the products by the user.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
