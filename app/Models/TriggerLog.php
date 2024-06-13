<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriggerLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['from_status' => 'object', 'to_status' => 'object'];

    public function watcher() {
        return $this->belongsTo(User::class, 'watcher_id');
    }
}
