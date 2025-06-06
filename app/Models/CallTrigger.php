<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallTrigger extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function currentstatus() {
        return $this->belongsTo(CallTaskStatus::class, 'status_id', 'id');
    }

    public function nextstatus() {
        return $this->belongsTo(CallTaskStatus::class, 'next_status_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
