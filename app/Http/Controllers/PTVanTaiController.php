<?php

namespace App\Http\Controllers;

use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PTVanTaiController extends Controller
{
    public function danhsachPTVTXC()
    {
        $data = PTVTXuatCanh::where('trang_thai',1)->orderBy('so_ptvt_xuat_canh', 'desc')->get();
        return view('ptvt-xuat-canh.danh-sach-ptvt-xc', data: compact(var_name: 'data'));
    }
    public function themPTVTXC()
    {
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        return view('ptvt-xuat-canh.them-to-khai-ptvt-xc', data: compact(var_name: 'doanhNghiep'));
    }
    public function themPTVTXCSubmit(Request $request)
    {
        if(PTVTXuatCanh::where('ten_phuong_tien_vt',$request->ten_phuong_tien_vt)->exists()){
            session()->flash('alert-danger', 'Trùng tên tàu');
            return redirect()->back();
        }
        PTVTXuatCanh::insert([
            'ten_phuong_tien_vt' => $request->ten_phuong_tien_vt,
            'quoc_tich_tau' => $request->quoc_tich_tau,
            'cang_den' => $request->cang_den,
            'ten_thuyen_truong' => $request->ten_thuyen_truong,
            'so_giay_chung_nhan' => $request->so_giay_chung_nhan,
            'draft' => $request->draft,
            'dwt' => $request->dwt,
            'loa' => $request->loa,
            'breadth' => $request->breadth,
        ]);

        return redirect()
            ->route('phuong-tien-vt.danh-sach-ptvt-xc')
            ->with('alert-success', 'Thêm xuồng mới thành công!');
    }
    public function thongTinPTVTXC($so_ptvt_xuat_canh)
    {
        $phuong_tien_vt = PTVTXuatCanh::find($so_ptvt_xuat_canh);
        return view('ptvt-xuat-canh.thong-tin-ptvt-xc', compact('phuong_tien_vt')); // Pass data to the view
    }
    public function suaPTVTXC($so_ptvt_xuat_canh)
    {
        $phuong_tien_vt = PTVTXuatCanh::find($so_ptvt_xuat_canh);
        return view('ptvt-xuat-canh.sua-to-khai-ptvt-xc', compact('phuong_tien_vt')); // Pass data to the
    }

    public function suaPTVTXCSubmit(Request $request)
    {
        $PTVTXC = PTVTXuatCanh::find($request->so_ptvt_xuat_canh);
        $PTVTXC->update([
            'ten_phuong_tien_vt' => $request->ten_phuong_tien_vt,
            'quoc_tich_tau' => $request->quoc_tich_tau,
            'cang_den' => $request->cang_den,
            'ten_thuyen_truong' => $request->ten_thuyen_truong,
            'so_giay_chung_nhan' => $request->so_giay_chung_nhan,
            'draft' => $request->draft,
            'dwt' => $request->dwt,
            'loa' => $request->loa,
            'breadth' => $request->breadth,
        ]);
        session()->flash('alert-success', 'Cập nhật thành công');
        return redirect()->route('phuong-tien-vt.thong-tin-ptvt-xc', ['so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh]);
    }
    public function huyPTVTXC(Request $request)
    {
        $PTVTXC = PTVTXuatCanh::find($request->so_ptvt_xuat_canh);
        $PTVTXC->update([
            'trang_thai' => 0,
        ]);
        session()->flash('alert-success', 'Cập nhật thành công');
        return redirect()->route('phuong-tien-vt.thong-tin-ptvt-xc', ['so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh]);
    }

}
