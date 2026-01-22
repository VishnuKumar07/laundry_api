<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerFavoriteVendor extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customer_favorite_vendors';

    protected $fillable = [
        'customer_id',
        'vendor_id',
    ];
}
