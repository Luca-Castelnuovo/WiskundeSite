<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'img_url',
        'price',
        'is_healthy',
        'tags',
        'recommended_addons'
    ];

    /**
     * The fields that will be transformed by Carbon.
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
        'created_at',
        'updated_at',
    ];

    /**
     * Convert JSON to array
     * @var array
     */
    protected $casts = [
        'tags' => 'array', // Will converted to (Array)
        'recommended_addons' => 'array', // Will converted to (Array)
    ];

    /**
     * Get all the tags from the product.
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}
