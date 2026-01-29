<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorProductPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_product_prices';

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(VendorProduct::class);
    }
}
