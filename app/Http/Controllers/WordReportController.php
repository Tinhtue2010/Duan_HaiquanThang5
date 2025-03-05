<?php

namespace App\Http\Controllers;

use App\Exports\Word\BaoCaoHangHoaXuatNhapKhau;
use App\Exports\Word\InPhieuChuyenContainer;
use App\Exports\Word\InPhieuChuyenTau;
use App\Exports\Word\InPhieuChuyenTauCont;
use App\Exports\Word\InPhieuKiemTraHang;
use Illuminate\Http\Request;

class WordReportController extends Controller
{
    public function baoCaoHangHoaXuatNhapKhau(Request $request)
    {
        $baoCaoHangHoaXuatNhapKhau = new BaoCaoHangHoaXuatNhapKhau();
        return $baoCaoHangHoaXuatNhapKhau->baoCaoHangHoaXuatNhapKhau($request);
    }
    public function inPhieuChuyenTau($ma_yeu_cau)
    {
        $inPhieuChuyenTau = new InPhieuChuyenTau();
        return $inPhieuChuyenTau->inPhieuChuyenTau($ma_yeu_cau);
    }

    public function inPhieuChuyenTauCont($ma_yeu_cau)
    {
        $inPhieuChuyenTauCont = new InPhieuChuyenTauCont();
        return $inPhieuChuyenTauCont->inPhieuChuyenTauCont($ma_yeu_cau);
    }

    public function inPhieuChuyenContainer($ma_yeu_cau)
    {
        $inPhieuChuyenContainer = new InPhieuChuyenContainer();
        return $inPhieuChuyenContainer->inPhieuChuyenContainer($ma_yeu_cau);
    }

    public function inPhieuKiemTraHang($ma_yeu_cau)
    {
        $inPhieuKiemTraHang = new InPhieuKiemTraHang();
        return $inPhieuKiemTraHang->inPhieuKiemTraHang($ma_yeu_cau);
    }
}
