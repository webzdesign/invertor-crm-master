<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallTaskStatus extends Model
{
    use HasFactory, SoftDeletes;

    const status1 = 1; // Call The New Client
    const status2 = 2; // New Client

    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function scopeSystem($query)
    {
        return $query->where('type', 0);
    }

    public static function scopeCustom($query)
    {
        return $query->where('type', 1);
    }

    public function scopeSequence($query) {
        return $query->orderByRaw("
            CASE
                WHEN slug = 'closed-win' THEN 5
                WHEN slug = 'duplicate' THEN 4
                WHEN slug = 'closed-loss' THEN 3
                WHEN slug = 'scammer' THEN 2
                ELSE 1
            END, sequence
        ");
    }
}
