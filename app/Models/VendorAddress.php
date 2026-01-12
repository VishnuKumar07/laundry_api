<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAddress extends Model
{
    use HasFactory;

    protected $table = 'vendor_addresses';

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
