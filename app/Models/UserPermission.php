<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_permissions";

    protected $fillable = [
        'user_id',
        'role_id',
    ];
}
