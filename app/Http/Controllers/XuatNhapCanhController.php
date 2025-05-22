<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use App\Models\PTVTXuatCanh;
use App\Models\ChuHang;
use App\Models\NhapCanh;
use App\Models\XuatNhapCanh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class XuatNhapCanhController extends Controller
{
    public function danhSachXNC()
    {
        return view('xuat-nhap-canh.quan-ly-xnc');
    }

    public function themXNC()
    {
        return view('xuat-nhap-canh.them-xnc', [
            'PTVTXuatCanhs' => PTVTXuatCanh::where('trang_thai', '2')->get(),
            'chuHangs' => ChuHang::all(),
        ]);
    }

    public function themXNCSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $xuatNhapCanh = XuatNhapCanh::create([
                    'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
                    'ngay_them' => now(),
                    'so_the' => $request->so_the,
                    'is_hang_lanh' => $request->is_hang_lanh,
                    'is_hang_nong' => $request->is_hang_nong,
                    'ma_chu_hang' => $request->ma_chu_hang,
                    'so_luong_may' => $request->so_luong_may,
                    'tong_trong_tai' => $request->tong_trong_tai,
                    'thoi_gian_nhap_canh' => $request->thoi_gian_nhap_canh,
                    'thoi_gian_xuat_canh' => $request->thoi_gian_xuat_canh,
                    'ma_cong_chuc' => $this->getCongChucHienTai()->ma_cong_chuc,
                    'ghi_chu' => $request->ghi_chu,
                ]);
                return redirect()
                    ->route('xuat-nhap-canh.thong-tin-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc])
                    ->with('alert-success', 'Thêm theo dõi xuất nhập cảnh thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in themXuatNhapCanhSubmit: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinXNC($ma_xnc)
    {
        if (XuatNhapCanh::find($ma_xnc)) {
            $xuatNhapCanh = XuatNhapCanh::find($ma_xnc);
        }
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();

        return view('xuat-nhap-canh.thong-tin-xnc', compact('xuatNhapCanh', 'congChucs')); // Pass data to the view
    }

    public function huyXNC(Request $request)
    {
        XuatNhapCanh::find($request->ma_xnc)->delete();
        session()->flash('alert-success', 'Hủy theo dõi xuất nhập cảnh thành công!');
        return redirect()->route('xuat-nhap-canh.danh-sach-xnc');
    }
    public function suaXNC($ma_xnc)
    {
        $xuatNhapCanh = XuatNhapCanh::find($ma_xnc);
        return view('xuat-nhap-canh.sua-xnc', [
            'PTVTXuatCanhs' => PTVTXuatCanh::where('trang_thai', '2')->get(),
            'chuHangs' => ChuHang::all(),
            'xuatNhapCanh' => $xuatNhapCanh,
        ]);
    }
    public function suaXNCSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatNhapCanh = XuatNhapCanh::find($request->ma_xnc);
            XuatNhapCanh::find($request->ma_xnc)->update([
                'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
                'so_the' => $request->so_the,
                'is_hang_lanh' => $request->is_hang_lanh,
                'is_hang_nong' => $request->is_hang_nong,
                'ma_chu_hang' => $request->ma_chu_hang,
                'so_luong_may' => $request->so_luong_may,
                'tong_trong_tai' => $request->tong_trong_tai,
                'thoi_gian_nhap_canh' => $request->thoi_gian_nhap_canh,
                'thoi_gian_xuat_canh' => $request->thoi_gian_xuat_canh,
                'ghi_chu' => $request->ghi_chu,
            ]);
            DB::commit();
            session()->flash('alert-success', 'Sửa theo dõi xuất nhập cảnh thành công!');
            return redirect()->route('xuat-nhap-canh.thong-tin-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in suaXuatNhapCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function thayDoiCongChucXNC(Request $request)
    {
        XuatNhapCanh::find($request->ma_xnc)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }

    public function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    public function getXNCs(Request $request)
    {
        if ($request->ajax()) {
            $query = XuatNhapCanh::query()->select(
                'xuat_nhap_canh.*',
                'chu_hang.ten_chu_hang',
                'ptvt_xuat_canh.ten_phuong_tien_vt',
                'cong_chuc.ten_cong_chuc'
            )
                ->orderBy('ma_xnc', 'desc')
                ->join('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
                ->join('chu_hang', 'xuat_nhap_canh.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
                ->join('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc');

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('xuat_nhap_canh.ma_xnc', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(xuat_nhap_canh.ngay_them, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('chu_hang.ten_chu_hang', 'LIKE', "%{$search}%")
                                ->orWhere('ptvt_xuat_canh.ten_phuong_tien_vt', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($xuatNhapCanh) {
                    return '';
                })
                ->editColumn('ngay_them', function ($xuatNhapCanh) {
                    return Carbon::parse($xuatNhapCanh->ngay_them)->format('d-m-Y');
                })
                ->addColumn('ten_chu_hang', function ($xuatNhapCanh) {
                    return $xuatNhapCanh->ten_chu_hang ?? 'N/A';
                })
                ->addColumn('ten_phuong_tien_vt', function ($xuatNhapCanh) {
                    return $xuatNhapCanh->ten_phuong_tien_vt ?? 'N/A';
                })
                ->addColumn('thoi_gian_nhap_canh', function ($xuatNhapCanh) {
                    return $xuatNhapCanh->thoi_gian_nhap_canh ?? 'N/A';
                })
                ->addColumn('thoi_gian_xuat_canh', function ($xuatNhapCanh) {
                    return $xuatNhapCanh->thoi_gian_xuat_canh ?? 'N/A';
                })
                ->addColumn('ten_cong_chuc', function ($xuatNhapCanh) {
                    return $xuatNhapCanh->ten_cong_chuc ?? 'N/A';
                })
                ->addColumn('action', function ($xuatNhapCanh) {
                    return '<a href="' . route('xuat-nhap-canh.thong-tin-xnc', $xuatNhapCanh->ma_xnc) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('loai_hang', function ($xuatNhapCanh) {
                    if ($xuatNhapCanh->is_hang_lanh == 1) {
                        return 'Hàng lạnh';
                    } else {
                        return 'Hàng nóng';
                    }
                })
                ->rawColumns(['loai_hang', 'action'])
                ->make(true);
        }
    }
}
