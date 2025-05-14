<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangYong extends Model
{
    //
    protected $table = 'changyongs';
    protected $fillable = [
        'owner_id',
        'price'
    ];

}
