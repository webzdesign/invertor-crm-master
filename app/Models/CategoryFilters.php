<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CategoryFilters extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'category_filters';

    protected $guarded = [];

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
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function options()
    {
        return $this->hasMany(CategoryFilterOptions::class, 'category_filter_id', 'id');
    }
}
