<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function from_user() {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function to_user() {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function assigned_user() {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

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

    public function ostatus()
    {
        return $this->belongsTo(CallTaskStatus::class, 'status_id');
    }

    public function tstatus()
    {
        return $this->hasMany(CallChangeCallHistoryStatusTrigger::class, 'call_id')->orderBy('id', 'DESC');
    }

    public function userchanges()
    {
        return $this->hasMany( CallChangeCallHistoryUser::class, 'call_id')->orderBy('id', 'DESC');
    }
}
