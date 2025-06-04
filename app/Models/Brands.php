<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Brands extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'brands' ;
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

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id','id');
    }
}
