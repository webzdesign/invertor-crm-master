<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getUserProfileAttribute() {
        if (!empty(trim($this->profile)) && file_exists(public_path("storage/user-profiles/{$this->profile}"))) {
            return asset("storage/user-profiles/{$this->profile}");
        }

        return asset('assets/images/profile.png');
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class,UserRole::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class,PermissionRole::class,'role_id');
    }

    public function hasPermission($per)
    {
        if (in_array(1,auth()->user()->roles->pluck("id")->all())) {
            return (bool) true;
        } else {
            if (session()->has('userPermissionsLists') && session()->has('userPermissionsListsTime')) {
                if (session()->get('userPermissionsListsTime') > time()) {
                    $userPermissionsLists = session()->get('userPermissionsLists');
                } else {
                    $userPermissionsLists = User::select('permissions.slug', 'users.id')->join('user_roles', 'users.id', '=', 'user_roles.user_id')->join('roles', 'user_roles.role_id', '=', 'roles.id')->join('permission_roles', 'roles.id', '=', 'permission_roles.role_id')->join('permissions', 'permission_roles.permission_id', '=', 'permissions.id')->where('users.id', auth()->user()->id)->pluck('id', 'slug')->toArray();

                    $permissionExpiry = time() + (10 * 60);

                    session()->put('userPermissionsLists', $userPermissionsLists);
                    session()->put('userPermissionsListsTime', $permissionExpiry);
                }
            } else {
                $userPermissionsLists = User::select('permissions.slug', 'users.id')->join('user_roles', 'users.id', '=', 'user_roles.user_id')->join('roles', 'user_roles.role_id', '=', 'roles.id')->join('permission_roles', 'roles.id', '=', 'permission_roles.role_id')->join('permissions', 'permission_roles.permission_id', '=', 'permissions.id')->where('users.id', auth()->user()->id)->pluck('id', 'slug')->toArray();

                $permissionExpiry = time() + (10 * 60);

                session()->put('userPermissionsLists', $userPermissionsLists);
                session()->put('userPermissionsListsTime', $permissionExpiry);
            }

            return (bool) isset($userPermissionsLists[$per]);
        }
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
