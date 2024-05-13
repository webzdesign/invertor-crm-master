<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }
}
