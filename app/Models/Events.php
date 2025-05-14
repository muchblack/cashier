<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    //
    protected $table = 'events';
    protected $fillable = [
        'owner_id',
        'event_name',
        'event_date',
    ];
}
