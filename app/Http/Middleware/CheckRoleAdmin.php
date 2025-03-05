<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoleAdmin
{
    public function handle(Request $request, Closure $next)
    {   
        if(!$request->user()){
            return redirect('dang-nhap');
        }
        // Check if the user is authenticated and has the correct role
        elseif ($request->user() && $request->user()->loai_tai_khoan !== "Admin") {
            // Redirect or handle as needed if the role does not match
            return redirect('dang-nhap');
        }

        return $next($request);  // Proceed to the next middleware/route
    }
}