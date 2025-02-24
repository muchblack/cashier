<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    //
    protected $table = 'orders';
    protected $fillable = [
        'owner_id',
        'event_id',
        'order_type',
        'status',
        'preorder_name',
        'payment_id',
        'order_amount',
        'item_lists',
        'item_quantities',
        'order_desc',
    ];

    protected $casts = [
        'item_lists'=>'array',
        'item_quantities'=>'array',
    ];
}
