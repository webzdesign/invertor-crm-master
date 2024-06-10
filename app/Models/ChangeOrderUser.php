<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChangeOrderUser extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function status() {
        return $this->belongsTo(SalesOrderStatus::class, 'status_id', 'id');
    }

    public function mainstatus() {
        return $this->belongsTo(SalesOrderStatus::class, 'status_id');
    }

    public function trigger()
    {
        return $this->belongsTo(Trigger::class, 'trigger_id');
    }
}
