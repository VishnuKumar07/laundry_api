<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorAddress extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'vendor_addresses';

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
         'company_image' => 'array',
    ];

    protected $appends = ['company_image_urls'];

    public function getCompanyImageUrlsAttribute()
    {
        if (empty($this->company_image)) {
            return [];
        }
        $images = [];
        foreach ($this->company_image as $image) {
            if (!$image) {
                continue;
            }
            $image = str_replace('\\', '/', $image);
            $image = str_ireplace('uploads/Vendor/', 'uploads/vendor/', $image);
            if (str_starts_with($image, 'uploads/')) {
                $images[] = asset($image);
            } else {
                $images[] = asset('uploads/vendor/company_image/' . $image);
            }
        }

        return $images;
    }



    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
