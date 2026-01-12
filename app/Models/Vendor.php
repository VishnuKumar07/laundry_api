<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendors';

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_incorporation' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->hasOne(VendorAddress::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }
}
