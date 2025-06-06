<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallChangeCallHistoryUser extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function status() {
        return $this->belongsTo(CallTaskStatus::class, 'status_id', 'id');
    }

    public function mainstatus() {
        return $this->belongsTo(CallTaskStatus::class, 'status_id', 'id');
    }

    public function trigger()
    {
        return $this->belongsTo(CallTrigger::class, 'trigger_id');
    }
}
