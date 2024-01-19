<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        // print_r($user);
        // die;
        if (auth()->user() && auth()->user()->role == 'user') {
            # code...
            return $next($request);
        }
        return response()->json([
            'success' => false,
            'Message' => 'Unauthorized'
        ]);
    }
}
