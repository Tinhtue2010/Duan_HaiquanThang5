<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRoleCongChucDoanhNghiep
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            if ($request->user()->loai_tai_khoan == "Cán bộ công chức" || $request->user()->loai_tai_khoan == "Doanh nghiệp" || $request->user()->loai_tai_khoan == "Lãnh đạo" || $request->user()->loai_tai_khoan == "Admin") {
                return $next($request);
                
            }
        }

        return redirect('dang-nhap');
    }
}
