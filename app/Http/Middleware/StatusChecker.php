<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {

            $isUserRoleActive = true;
            $user = $request->user()->load('roles');
            if ($user->status == 0 || $user->roles->contains('status', 0)) {
                $isUserRoleActive = false;
            }

            if($isUserRoleActive){
                return $next($request);
            } else {

                $email = $request->user()->email;
                auth()->logout();
                return redirect()->route('login')->withErrors(['email' => 'Your account has been disabled. Please contact the administrator.'])->withInput(['email' => $email]);
            }

        } catch (\Exception $e) {
            abort(404, 'Something Went Wrong');
        }
    }
}
