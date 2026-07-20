<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected string $redirectTo = '/admin/dashboard';

    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->guard()->attempt($this->credentials($request), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectTo);
        }

        throw ValidationException::withMessages([
            'email' => [__('These credentials do not match our records.')],
        ]);
    }

    protected function credentials(Request $request): array
    {
        return [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => 1,
        ];
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }
}
