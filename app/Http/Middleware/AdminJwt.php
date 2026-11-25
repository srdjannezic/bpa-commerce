<?php

namespace App\Http\Middleware;

use App\Services\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminJwt
{
    public function __construct(private AdminAuthService $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $admin = $this->auth->resolve(
            $request->cookie(AdminAuthService::COOKIE),
            $request->session()->get(AdminAuthService::SESSION_KEY),
        );

        if ($admin === null) {
            return redirect()->route('admin.login')->withoutCookie(AdminAuthService::COOKIE, '/admin');
        }

        $request->attributes->set('admin', $admin);

        return $next($request)->setCache(['private' => true, 'no_store' => true]);
    }
}
