<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Rules\UserValidation;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

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
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /* public function __construct()
    {
        $this->middleware('guest')->except('logout');
    } */

    public function login(Request $request)
    {


        $credentials = $request->validate([
            'user' => ['required', new UserValidation],
            'password' => ['required'],
        ]);

        $username = User::where('username', $request->user)->exists();
        $email = User::where('email', $request->user)->exists();

        $name = null;
        if($username) { $name = "username"; }
        if($email) { $name = "email"; }

        $user= [
            $name => $request->user,
            "password" => $request->password
        ];

 
        if (Auth::attempt($user)) {
            $request->session()->regenerate();
 
            return redirect()->intended('home');
        }
 
        return back()->withErrors([
            'user' => 'The provided credentials do not match our records.',
        ])->onlyInput('user');
    }
}
