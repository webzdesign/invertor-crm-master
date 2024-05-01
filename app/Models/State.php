<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    public function scopeActive($query) {
        return $query->where('status', '1');
    }

    public function country() {
        return $this->belongsTo(Country::class);        
    }

    public function cities() {
        return $this->hasMany(City::class, 'state_id', 'id');
    }
}
