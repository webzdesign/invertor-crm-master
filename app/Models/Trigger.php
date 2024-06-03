<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Trigger extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function currentstatus() {
        return $this->belongsTo(SalesOrderStatus::class, 'status_id', 'id');        
    }

    public function nextstatus() {
        return $this->belongsTo(SalesOrderStatus::class, 'next_status_id', 'id');        
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
