<?php

namespace App\Http\Middleware;

use App\Utils\Constants\RoleUser;
use Closure;
use Filament\Panel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomerAccessCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $path = $request->path();

        if ($user->role == RoleUser::CUSTOMER->value ) {
            auth()->logout();
            abort(403);
        }

        return $next($request);
    }
}
