<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role_id != 1) {
            abort(403, 'غير مسموح لك بالدخول إلى هذه الصفحة');
        }

        return $next($request);
    }
}
