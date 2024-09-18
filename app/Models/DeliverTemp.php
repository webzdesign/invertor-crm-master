<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliverTemp extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
