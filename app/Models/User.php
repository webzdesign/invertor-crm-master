<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
// use App\Models\Scopes\ApprovedScope;


class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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


    protected static function booted()
    {
        static::addGlobalScope('ApprovedScope', function (Builder $builder) {

            $builder->where('status', '!=',2);

        });

    }

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

    public static function getUserRoles() {
        return auth()->user()->roles->pluck('id')->toArray();
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function userpermission()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
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
            $rolePermissions = User::withoutGlobalScope('ApprovedScope')->select('permissions.slug', 'users.id')
                                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                                    ->join('permission_role', 'roles.id', '=', 'permission_role.role_id')
                                    ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
                                    ->where('users.id', auth()->user()->id)
                                    ->pluck('id', 'slug')
                                    ->toArray();

            $userPermissions = User::withoutGlobalScope('ApprovedScope')->select('permissions.slug', 'users.id')
                                    ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
                                    ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                                    ->where('users.id', auth()->user()->id)
                                    ->pluck('id', 'slug')
                                    ->toArray();


            if (isset($rolePermissions[$per]) && isset($userPermissions[$per])) {
                return true;
            }

            return false;
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

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getEncidAttribute()
    {
        return encrypt($this->id);
    }

    public static function isAdmin() {
        return in_array(1, auth()->user()->roles->pluck('id')->toArray());
    }

    public static function isSeller() {
        return in_array(Role::where('slug', 'seller')->first()->id ?? 0, auth()->user()->roles->pluck('id')->toArray());
    }

    public static function isDriver() {
        return in_array(Role::where('slug', 'driver')->first()->id ?? 0, auth()->user()->roles->pluck('id')->toArray());
    }

    public static function isSellerManager() {
        return in_array(Role::where('slug', 'seller-manager')->first()->id ?? 0, auth()->user()->roles->pluck('id')->toArray());
    }
}
