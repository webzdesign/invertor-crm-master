<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Deliver extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function order() {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
