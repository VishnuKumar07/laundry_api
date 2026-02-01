<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorTermsCondition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_terms_conditions';
    protected $guarded = ['id'];
}
