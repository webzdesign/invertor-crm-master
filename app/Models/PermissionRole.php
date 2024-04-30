<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "permission_role";
    
    protected $fillable = [
        'permission_id',
        'role_id'
    ];
}
