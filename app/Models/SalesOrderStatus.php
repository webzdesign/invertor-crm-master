<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesOrderStatus extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
