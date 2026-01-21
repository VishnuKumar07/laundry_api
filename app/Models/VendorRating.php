<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorRating extends Model
{
    protected $fillable = [
        'vendor_id',
        'customer_id',
        'rating',
        'review',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
