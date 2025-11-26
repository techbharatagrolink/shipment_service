<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'shipment_id', 'courier_name', 'awb_code', 'status', 'order_data'
    ];

    protected $casts = [
        'order_data' => 'array',
    ];
}
