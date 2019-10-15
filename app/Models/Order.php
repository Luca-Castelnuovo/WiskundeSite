<?php

namespace App\Models;

use App\Traits\UUIDS;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
        'products',
        'price',
        'user_id',
        'payment_id',
        'state',
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
        'created_at',
        'payment_id',
        'user_id',
        'id',
    ];

    /**
     * Convert JSON to array.
     *
     * @var array
     */
    protected $casts = [
        'products' => 'array',
    ];

    /**
     * Define relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
