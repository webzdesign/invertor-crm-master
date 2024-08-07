<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        $validate_admin = User::withoutGlobalScope('ApprovedScope')->where('email',$request->email)->first();
        if ($validate_admin && Hash::check($request->password, $validate_admin->password)) {
            if($validate_admin->status == 2){
                return back()->withErrors([
                    'email' => 'Your account approval is pending. Please contact the administrator.',
                ])->onlyInput('email');
            } else if($validate_admin->status == 0) {
                return back()->withErrors([
                    'email' => 'Your account has been disabled. Please contact the administrator.',
                ])->onlyInput('email');
            }
        }
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (in_array(3, auth()->user()->roles->pluck('id')->toArray())) {
                if (auth()->user()->hasPermission('sales-orders.view')) {
                    return redirect()->route('sales-orders.index');                    
                }
                return redirect()->intended('dashboard');
            }

            return redirect()->intended('dashboard');
        }
        return back()->withErrors([
            'email' => 'Credentials does not match.',
        ])->onlyInput('email');

    }
}
