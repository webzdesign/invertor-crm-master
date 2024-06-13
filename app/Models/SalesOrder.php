<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function addedby()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    public function updatedby()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'name' => '-',
        ]);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'so_id', 'id');
    }

    public function total()
    {
        return $this->items()->sum('amount') ?? 0;
    }

    public function ostatus()
    {
        return $this->belongsTo(SalesOrderStatus::class, 'status');
    }

    public function tstatus()
    {
        return $this->hasMany(ChangeOrderStatusTrigger::class, 'order_id')->orderBy('id', 'DESC');
    }

    public function task()
    {
        return $this->hasMany(AddTaskToOrderTrigger::class, 'order_id')->where('executed', 1)->orderBy('id', 'DESC');
    }

    public function userchanges()
    {
        return $this->hasMany(ChangeOrderUser::class, 'order_id')->orderBy('id', 'DESC');
    }
}
