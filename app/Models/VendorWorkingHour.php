<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorWorkingHour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'day',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
