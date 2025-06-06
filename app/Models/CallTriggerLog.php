<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallTriggerLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'from_status' => 'object',
        'to_status' => 'object'
    ];

    public function watcher() {
        return $this->belongsTo(User::class, 'watcher_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function call_history() {
        return $this->belongsTo(CallHistory::class, 'call_id');
    }
    public function assigneddriver() {
        return $this->belongsTo(User::class, 'assgined_driver_id');
    }
}
