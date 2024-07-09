<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesOrderStatus extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function scopeSystem($query)
    {
        return $query->where('type', 0);
    }

    public static function scopeCustom($query)
    {
        return $query->where('type', 1);
    }

    public function orders()
    {
        return $this->belongsTo(SalesOrder::class, 'status', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(AddTaskToOrderTrigger::class, 'status_id');
    }

    public function changeuser()
    {
        return $this->hasMany(ChangeOrderUser::class, 'status_id');
    }

    public function changestatus()
    {
        return $this->hasMany(ChangeOrderStatusTrigger::class, 'status_id');
    }

    public function scopeSequence($query) {
        return $query->orderByRaw("
            CASE
                WHEN slug = 'closed-win' THEN 3
                WHEN slug = 'scammer' THEN 2
                ELSE 1
            END, sequence
        ");
    }
}
