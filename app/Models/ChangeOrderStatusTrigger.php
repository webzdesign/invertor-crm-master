<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ChangeOrderStatusTrigger extends Model
{
    use HasFactory, SoftDeletes;

    public $guarded = [];

    public function mainstatus() {
        return $this->belongsTo(SalesOrderStatus::class, 'status_id');
    }

    public function adder()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'name' => '-',
        ]);
    }
}
