<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function addedby()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    public function updatedby()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'name' => '-',
        ]);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'so_id', 'id');
    }

    public function total()
    {
        return $this->items()->sum('amount') ?? 0;
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'customer_country');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'customer_state');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'customer_city');
    }
}
