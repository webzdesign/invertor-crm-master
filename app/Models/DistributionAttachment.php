<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DistributionAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function getDocAttribute()
    {
        return asset('storage/distribution-docs/' . $this->name);
    }
}
