<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $guarded = [];

    public function scopeCredit($builder): void
    {
        $builder->where('transaction_type', 0);
    }

    public function scopeDebit($builder): void
    {
        $builder->where('transaction_type', 1);
    }
}
