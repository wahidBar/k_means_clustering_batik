<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     *
     * @var User|null $user
     */
    public function handle(Request $request, Closure $next, $role)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'You must login.');
        }

        if (!$user->isRole($role)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
