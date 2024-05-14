<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DistributionItem extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function dist() {
        return $this->belongsTo(Distribution::class, 'distribution_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromdriver()
    {
        return $this->belongsTo(User::class, 'from_driver');
    }

    public function todriver()
    {
        return $this->belongsTo(User::class, 'to_driver');
    }
}
