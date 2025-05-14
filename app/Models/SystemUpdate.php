<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $table = 'system_update';
    protected $fillable = [
        'title',          // 更新標題
    ];

    // 依照日期排序（最新的優先）
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
