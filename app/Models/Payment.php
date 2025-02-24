<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $table = 'payments';
    protected $fillable = [
        'owner_id',
        'payment_name',
        'payment_name_en',
        'payment_name_jp',
    ];
}
