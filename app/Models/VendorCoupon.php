<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorCoupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'images' => 'array',
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    protected $appends = ['image_urls'];

    public function getImageUrlsAttribute()
    {
        if (empty($this->images)) {
            return [];
        }

        return collect($this->images)->map(fn ($img) =>
            asset('uploads/vendor/coupons/' . $img)
        )->toArray();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
