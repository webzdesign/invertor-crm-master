<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\User;
use Closure;

class ModuleAccessor
{
    public function handle(Request $request, Closure $next, $module): Response
    {
        if (auth()->check()) {
            if ($request->user()->roles->where('id', 1)->count()) {
                return $next($request);
            } else {

                $roleHasPermission = User::withoutGlobalScope('ApprovedScope')->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->join('permission_role', 'roles.id', '=', 'permission_role.role_id')
                ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
                ->where('users.id', auth()->user()->id)
                ->where('permissions.slug', $module)
                ->where('users.status', '1')
                ->where('roles.status', '1')
                ->exists();

                $userHasPermission = User::withoutGlobalScope('ApprovedScope')->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('users.id', auth()->user()->id)
                ->where('permissions.slug', $module)
                ->exists();

                if ($roleHasPermission && $userHasPermission) {
                    return $next($request);
                } else {
                    abort(403);
                }
            }
        } else {
            abort(403);
        }
    }
}
