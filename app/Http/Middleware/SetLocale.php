<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['vi', 'en', 'zh', 'ko'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            abort(404);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
