<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallChangeCallHistoryStatusTrigger extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function mainstatus() {
        return $this->belongsTo(CallTaskStatus::class, 'status_id');
    }

    public function oldstatus() {
        return $this->belongsTo(CallTaskStatus::class, 'current_status_id');
    }

    public function call_history() {
        return $this->belongsTo(CallHistory::class, 'call_id');
    }

    public function adder()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'name' => '-',
        ]);
    }

    public function trigger()
    {
        return $this->belongsTo(CallTrigger::class, 'trigger_id');
    }
}
