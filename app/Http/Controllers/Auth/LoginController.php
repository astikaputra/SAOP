<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';
    
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('throttle:10,1')->only('login');
    }
    
    /**
     * Customize redirect based on user role
     */
    protected function authenticated(Request $request, $user)
    {
        // Redirect based on role
        switch ($user->role) {
            case 'LOKET_STAFF':
                return redirect()->route('loket.index');
            case 'ADMIN':
                return redirect()->route('admin.dashboard');
            case 'DRIVER':
                return redirect()->route('driver.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
}