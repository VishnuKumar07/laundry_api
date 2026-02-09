<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
    ];

    protected $appends = ['image_urls'];

    public function getImageUrlsAttribute()
    {
        if (empty($this->images)) {
            return [];
        }

        return collect($this->images)->map(function ($img) {
            return asset('uploads/vendor/products/' . $img);
        })->toArray();
    }

    public function prices()
    {
        return $this->hasMany(VendorProductPrice::class);
    }
}

