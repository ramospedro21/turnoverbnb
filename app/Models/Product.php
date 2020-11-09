<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{        
    use SoftDeletes;

    public const PER_PAGE = 16;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'quantity',
        'sku',
    ];

    public function logs()
    {
        return $this->hasMany('App\Models\ProductLog', 'product_id', 'id');
    }
}
