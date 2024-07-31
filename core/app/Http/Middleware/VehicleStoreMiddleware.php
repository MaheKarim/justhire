<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VehicleStoreMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) {
        $user = auth()->user();
        if (!$user->store) {
            $notify[] = ['error', 'Before proceeding, it is necessary to create the store'];
            return back()->withNotify($notify);
        }
        if ($user->store != Status::STORE_APPROVED) {
            $notify[] = ['error', 'You can\'t add vehicles until the store approved'];
            return back()->withNotify($notify);
        }
        return $next($request);
    }
}
