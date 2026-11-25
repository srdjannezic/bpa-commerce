<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GuestCart
{
    public const KEY = 'guest_cart_key';

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        if (! $session->has(self::KEY)) {
            $session->put(self::KEY, Str::uuid()->toString());
        }

        return $next($request)->setCache(['private' => true, 'no_store' => true]);
    }
}
