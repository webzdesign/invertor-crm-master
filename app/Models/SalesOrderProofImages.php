<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesOrderProofImages extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public $table = 'sales_order_proof_images';

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function getDocAttribute()
    {
        return asset('storage/so-price-change-agreement/' . $this->name);
    }
}
