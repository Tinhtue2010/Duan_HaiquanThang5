<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoleCongChuc
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and has the correct role
        if ($request->user() && $request->user()->loai_tai_khoan !== "Cán bộ công chức") {
            // Redirect or handle as needed if the role does not match
            return redirect('dang-nhap');
        }

        return $next($request);  // Proceed to the next middleware/route
    }
}