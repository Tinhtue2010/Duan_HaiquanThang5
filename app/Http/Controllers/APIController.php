<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChuHang;
use App\Models\LoaiHang;
use App\Models\LoaiHinh;
use App\Models\HaiQuan;
use App\Models\PTVTXuatCanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class APIController extends Controller
{
    public function getChuHang()
    {
        return response()->json(ChuHang::all());
    }
    public function getLoaiHang()
    {
        return response()->json(LoaiHang::all());
    }
    public function getLoaiHinh()
    {
        return response()->json(LoaiHinh::all());
    }
    public function getHaiQuan()
    {
        return response()->json(HaiQuan::all());
    }
    public function getPTVT()
    {
        return response()->json(PTVTXuatCanh::all());
    }
}
