<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;

class HandleTokenMismatch
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            return redirect()->route('home-page')->with('error', 'Your session has expired. Please try again.');
        }
    }
}