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
        if ($request->user()->roles->where('id', 1)->count()) {
            return $next($request);
        } else {

            $hasPermission = User::join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('permission_roles', 'roles.id', '=', 'permission_roles.role_id')
            ->join('permissions', 'permission_roles.permission_id', '=', 'permissions.id')
            ->where('users.id', auth()->user()->id)
            ->where('permissions.slug', $module)
            ->exists();

            if ($hasPermission) {
                return $next($request);
            } else {
                abort(403);
            }
        }
    }
}
