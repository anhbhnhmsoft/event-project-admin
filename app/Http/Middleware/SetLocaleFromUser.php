<?php

namespace App\Http\Middleware;

use App\Utils\Constants\Language;
use Laravel\Sanctum\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromUser
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Language::VI->value;

        $user = $request->user();
        if (!$user) {
            $authHeader = $request->header('Authorization');
            if (is_string($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
                $plainToken = substr($authHeader, 7);
                $accessToken = PersonalAccessToken::findToken($plainToken);
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                }
            }
        }
        if ($user && !empty($user->lang)) {
            $locale = $user->lang;
        } else {
            $filter = $request->input('locate');
            if (in_array($filter, [Language::VI->value, Language::EN->value], true)) {
                $locale = $filter;
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}


