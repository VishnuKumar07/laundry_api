<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorServiceType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_service_type';

    protected $fillable = [
        'vendor_id',
        'service_id',
        'delivery_type_id',
        'avg_delivery_days',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }
}
