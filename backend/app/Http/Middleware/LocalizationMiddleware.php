<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');

        if ($locale && in_array($locale, ['vi', 'en'])) {
            App::setLocale($locale);
        } else {
            App::setLocale('vi'); // Default to Vietnamese
        }

        return $next($request);
    }
}
