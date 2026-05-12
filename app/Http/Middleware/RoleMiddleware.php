<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            $access = in_array($role, $roles);
            if (!$access) {
                return $this->errorResponse('unauthorized' , [] , 403);
            }
        }
        return $next($request);
    }
}
