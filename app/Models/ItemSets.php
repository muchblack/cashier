<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ItemSets extends Model
{
    //
    protected $table = 'item_sets';
    protected $fillable = [
        'owner_id',
        'event_id',
        'item_set_name',
        'item_set_name_en',
        'item_set_name_jp',
        'item_set_price',
        'item_set_stock',
        'item_set_list',
    ];

    protected $casts = [
        'item_set_list' => 'array',
    ];

    public function items()
    {
        return $this->belongsToMany(Items::class)
            ->whereIn('items.id', function($query) {
                $query->select(DB::raw('json_unquote(json_extract(item_set_list), "$[*]"))'))
                    ->from($this->getTable())
                    ->where('id', $this->id);
            });
    }

}
