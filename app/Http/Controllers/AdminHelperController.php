<?php

namespace App\Http\Controllers;

use App\Models\XuatHangCont;
use App\Models\PTVTXuatCanh;
use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\TheoDoiHangHoa;
use App\Models\XuatHang;
use App\Models\HangTrongCont;
use App\Models\XuatNhapCanhSua;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AdminHelperController extends Controller
{
    public function fixTheoDoiXD($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::where('so_to_khai_nhap', $so_to_khai_nhap)->first();
        $hangHoas = HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->get();
        $xuatHangConts = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();
        $theoDoiHangHoas = TheoDoiHangHoa::where('theo_doi_hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
            ->join('hang_hoa', 'theo_doi_hang_hoa.ma_hang', '=', 'hang_hoa.ma_hang')
            ->get();
        return view('fixing.fix-theo-doi-xd', compact('xuatHangConts', 'hangHoas', 'nhapHang', 'theoDoiHangHoas'));
    }


    public function cloneXuatHang(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatHangCont = XuatHangCont::find($request->ma_xuat_hang_cont);
            $hangTrongCont = HangTrongCont::find($request->ma_hang_cont);

            XuatHangCont::insert([
                'so_to_khai_xuat' => $xuatHangCont->so_to_khai_xuat,
                'so_to_khai_nhap' => $xuatHangCont->so_to_khai_nhap,
                'ma_hang_cont' => $request->ma_hang_cont,
                'so_luong_xuat' => $request->so_luong_xuat,
                'so_luong_ton' => 0,
                'so_container' => $xuatHangCont->so_container,
                'phuong_tien_vt_nhap' => $xuatHangCont->phuong_tien_vt_nhap,
                'so_seal_cuoi_ngay' => $xuatHangCont->so_seal_cuoi_ngay,
                'tri_gia' => $xuatHangCont->tri_gia
            ]);
            $theoDoi = TheoDoiHangHoa::where('so_to_khai_nhap', $xuatHangCont->so_to_khai_nhap)
                ->where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHangCont->so_to_khai_xuat)
                ->first();
            TheoDoiHangHoa::create([
                'so_to_khai_nhap' => $theoDoi->so_to_khai_nhap,
                'ma_hang' => $hangTrongCont->ma_hang,
                'thoi_gian' => $theoDoi->thoi_gian,
                'so_luong_xuat' => $request->so_luong_xuat,
                'so_luong_ton' => 0,
                'phuong_tien_cho_hang' => $theoDoi->phuong_tien_cho_hang,
                'cong_viec' => $theoDoi->cong_viec,
                'phuong_tien_nhan_hang' => $theoDoi->phuong_tien_nhan_hang,
                'so_container' => $theoDoi->so_container,
                'so_seal' => $theoDoi->so_seal,
                'ma_cong_chuc' => $theoDoi->ma_cong_chuc,
                'ghi_chu' => $theoDoi->ghi_chu,
                'ma_yeu_cau' => $theoDoi->ma_yeu_cau,
            ]);
            DB::commit();
            session()->flash('alert-success', 'Sửa theo dõi xuất nhập cảnh thành công!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in cloneXuatHang: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function xoaXuatHang(Request $request)
    {
        try {
            DB::beginTransaction();

            XuatHangCont::find($request->ma_xuat_hang_cont)->delete();

            DB::commit();
            session()->flash('alert-success', 'Sửa theo dõi xuất nhập cảnh thành công!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in xoaXuatHang: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function updateSLHienTai(Request $request)
    {
        try {
            DB::beginTransaction();

            HangTrongCont::find($request->ma_hang_cont)->update([
                'so_luong' => $request->so_luong
            ]);

            DB::commit();
            session()->flash('alert-success', 'Sửa theo dõi xuất nhập cảnh thành công!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in xoaXuatHang: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function quanLyThucXuat()
    {
        $xuatHangs = XuatHang::where('trang_thai', 13)
            ->selectRaw('DATE(xuat_hang.updated_at) as updated_date, xuat_hang.*, cong_chuc.*')
            ->groupBy(DB::raw('DATE(xuat_hang.updated_at)'), 'xuat_hang.ma_cong_chuc')
            ->join('cong_chuc', 'xuat_hang.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->orderBy(DB::raw('DATE(xuat_hang.updated_at)'), 'desc')
            ->get();
        return view('quan-ly-khac.danh-sach-thuc-xuat', compact('xuatHangs'));
    }
    
}
