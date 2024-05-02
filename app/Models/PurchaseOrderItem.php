<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id', 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id', 'product_id');
    }
}
