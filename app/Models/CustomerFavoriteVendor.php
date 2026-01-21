<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFavoriteVendor extends Model
{
    protected $table = 'customer_favorite_vendors';

    protected $fillable = [
        'customer_id',
        'vendor_id',
    ];
}
