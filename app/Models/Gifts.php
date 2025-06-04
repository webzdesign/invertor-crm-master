<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Gifts extends Model
{
    public $guarded = [];
    use HasFactory, SoftDeletes;
    protected $table = 'gifts';

     public function category()
    {
        return $this->belongsTo(Category::class, 'category_id','id');
    }
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
}
