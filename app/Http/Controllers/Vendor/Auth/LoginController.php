<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Vendor\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:vendor')->except('logout');
    }

    protected function guard()
    {
        return Auth::guard('vendor');
    }

    public function showLoginForm()
    {
        return view('vendor.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->guard()->attempt($this->credentials($request), $request->boolean('remember'))) {
            $user = $this->guard()->user();
            $vendor = $user?->vendor;

            if (! $vendor || ! $vendor->is_active || ! $vendor->is_approved) {
                $this->guard()->logout();

                throw ValidationException::withMessages([
                    'email' => [__('general.vendor_account_unavailable')],
                ]);
            }

            $request->session()->regenerate();

            return redirect()->to($this->vendorHome($request));
        }

        throw ValidationException::withMessages([
            'email' => [__('These credentials do not match our records.')],
        ]);
    }

    /**
     * Prefer a prior vendor URL; never follow an admin (or other) intended URL.
     */
    protected function vendorHome(Request $request): string
    {
        $intended = $request->session()->pull('url.intended');
        $path = is_string($intended) ? (parse_url($intended, PHP_URL_PATH) ?: '') : '';

        if ($path !== '' && preg_match('#(^|/)vendor(/|$)#', $path)) {
            return $intended;
        }

        return route('vendor.dashboard.index');
    }

    /**
     * @return array<string, mixed>
     */
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

        return redirect()->route('vendor.auth.login');
    }
}
