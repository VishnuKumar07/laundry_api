<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_anniversary' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->hasOne(CustomerAddress::class);
    }

    public function vendorRatings()
    {
        return $this->hasMany(VendorRating::class);
    }

    public function favoriteVendors()
    {
        return $this->belongsToMany(
            Vendor::class,
            'customer_favorite_vendors'
        )->withTimestamps();
    }


}
