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
}
