<?php

namespace App\Models;

use App\Traits\UUIDS;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use UUIDS;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'refresh_token_hash',
        'refresh_token_hash_old',
        'refresh_token_expires',
    ];

    /**
     * The fields that will be transformed by Carbon.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'refresh_token_expires',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'updated_at',
        'refresh_token_hash',
        'refresh_token_hash_old',
    ];

    /**
     * Define relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
