<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AdminAuthService $auth) {}

    public function login(LoginRequest $request): RedirectResponse
    {
        $loginId = Str::random(48);
        $token = $this->auth->login($request->validated(), $loginId);
        $request->session()->regenerate(true);
        $request->session()->put(AdminAuthService::SESSION_KEY, $loginId);

        return redirect()->route('admin.products.index')->cookie(
            AdminAuthService::COOKIE,
            $token,
            AdminAuthService::LIFETIME,
            '/admin',
            null,
            $request->isSecure() || config('session.secure'),
            true,
            false,
            'lax',
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(AdminAuthService::SESSION_KEY);
        $request->session()->regenerate(true);

        return redirect()->route('admin.login')->withoutCookie(AdminAuthService::COOKIE, '/admin');
    }
}
