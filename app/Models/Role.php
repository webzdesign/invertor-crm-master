<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory,SoftDeletes;

    public $guarded = [];

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class);
    }

    public function hasPermissionTo($permission)
    {
        return $this->permissions->contains('name', $permission->name);
    }

    public function givePermissionTo($permission)
    {
        return $this->permissions()->save($permission);
    }

    public function revokePermissionTo($permission)
    {
        return $this->permissions()->detach($permission);
    }

    public function syncPermissions($permissions)
    {
        return $this->permissions()->sync($permissions);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hasUser($user)
    {
        return $this->users->contains('id', $user->id);
    }

    public function giveUser($user)
    {
        return $this->users()->save($user);
    }

    public function revokeUser($user)
    {
        return $this->users()->detach($user);
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

    public function scopeExceptSuperAdmin($query)
    {
        return $query->where('id', '!=', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
