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
        'item_id',
        'order_type',
        'status',
        'order_amount',
        'item_lists',
    ];

    protected $casts = [
        'item_lists'=>'array',
    ];
}
