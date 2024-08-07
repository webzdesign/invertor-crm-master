<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function scopeSeen($query) {
        return $query->where('read', true);
    }

    public function scopeUnseen($query) {
        return $query->where('read', false);
    }
}
