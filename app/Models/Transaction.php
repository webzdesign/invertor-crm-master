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

    public function scopeSeller($builder): void
    {
        $builder->whereHas('user.roles', fn ($query) => ($query->whereIn('roles.id', [2,6])));
    }

    public function scopeDriver($builder): void
    {
        $builder->whereHas('user.roles', fn ($query) => ($query->where('roles.id', 3)));
    }

    public function scopeAdmin($builder): void
    {
        $builder->whereHas('user.roles', fn ($query) => ($query->where('roles.id', 1)));
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');        
    }

    public function order() {
        return $this->belongsTo(SalesOrder::class, 'so_id');        
    }
}
