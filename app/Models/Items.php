<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    //
    protected $table = 'items';
    protected $fillable = [
        'item_name',
        'item_name_en',
        'item_name_jp',
        'item_price',
        'item_stock',
        'is_r18',
        'item_img_url',
        'item_type_id',
        'event_id',
        'owner_id'
    ];

    public function itemType()
    {

    }
    public function itemSet()
    {
        return  $this->hasMany(ItemSets::class)
            ->whereJsonContains('item_set_list', $this->id);
    }
}
