<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // Display registration page
    public function registration(){
        return view('manage-login.Registration');
    }

    // Handle registration process
    public function postregistration(Request $request){
        $request->validate([
            'name' => 'required|string|max:255|unique:login_record,name',
            'email' => 'required|string|email|max:255|unique:login_record,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Prepare data for user creation
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password); 
        $user = User::create($data);
        if (!$user) {
            return redirect(route('manage-login.Registration'))->with("error", "Registration failed, please try again");
        }
        return redirect(route('login'))->with("success", "Successfully registered, please log in");
    }

    // Display login page
    public function login() {
        if (Auth::check()) {
            return redirect(route('manage-note.IndexNote'));
        }
        return view('manage-login.Login');
    }

    // Handle login process
    public function postlogin(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('name', 'password');

        // Unique throttle key based on username and IP address
        $key = 'login:' . Str::transliterate(
            Str::lower($request->input('name'))
        ) . '|' . $request->ip();

        // Check whether login attempts have exceeded the limit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);

            $request->session()->regenerate();

            return redirect()->route('manage-note.IndexNote')->with('success', 'Logged in successfully');
        }

        // Record failed login attempt (60-second decay)
        RateLimiter::hit($key, 60);

        return back()->with('error','Login credentials do not match our records');
    }
    
    // Log user out
    public function logout(Request $request){
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'))->with('success', 'Successfully logged out');
    }
}
