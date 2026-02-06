<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_products';

    protected $guarded = ['id'];

    public function prices()
    {
        return $this->hasMany(VendorProductPrice::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return asset('uploads/vendor/products/' . $this->image);
    }

}
