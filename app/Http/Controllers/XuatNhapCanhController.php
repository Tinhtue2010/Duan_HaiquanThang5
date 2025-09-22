<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use App\Models\PTVTXuatCanh;
use App\Models\ChuHang;
use App\Models\NhapCanh;
use App\Models\XuatNhapCanh;
use App\Models\XuatNhapCanhSua;
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

    public function quanLyYeuCauSuaXNC()
    {
        $XNCs = XuatNhapCanh::orderBy('ma_xnc', 'desc')
            ->whereIn('xuat_nhap_canh.trang_thai', [3, 4])
            ->join('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->join('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->select('xuat_nhap_canh.*', 'ptvt_xuat_canh.ten_phuong_tien_vt', 'cong_chuc.ten_cong_chuc')
            ->get();
        return view('xuat-nhap-canh.quan-ly-xnc-sua', compact('XNCs'));
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
                    'so_luong_may' => $request->so_luong_may,
                    'tong_trong_tai' => $request->tong_trong_tai,
                    'ma_chu_hang' => $request->ma_chu_hang,
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
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();

        return view('xuat-nhap-canh.thong-tin-xnc', compact('xuatNhapCanh', 'congChucs')); // Pass data to the view
    }

    public function huyXNC(Request $request)
    {
        $xnc = XuatNhapCanh::find($request->ma_xnc);
        if ($xnc->trang_thai == 1) {
            if (!Carbon::parse($xnc->ngay_them)->lt(Carbon::today()->subDays(2))) {
                $xnc->delete();
            } else {
                $xnc->trang_thai = 4;
                $xnc->save();
            }
        } else {
            if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức') {
                $xnc->trang_thai = 1;
                $xnc->save();
            } else {
                $xnc->delete();
            }
        }

        session()->flash('alert-success', 'Hủy theo dõi xuất nhập cảnh thành công!');
        if (Auth::user()->loai_tai_khoan == 'Admin') {
            return redirect()->route('xuat-nhap-canh.quan-ly-yeu-cau-sua-xnc');
        } else {
            return redirect()->route('xuat-nhap-canh.danh-sach-xnc');
        }
    }
    public function thuHoiYeuCauHuyXNC(Request $request)
    {
        $xnc = XuatNhapCanh::find($request->ma_xnc);
        $xnc->trang_thai = 1;
        $xnc->save();

        session()->flash('alert-success', 'Thu hồi hủy theo dõi xuất nhập cảnh thành công!');
        if (Auth::user()->loai_tai_khoan == 'Admin') {
            return redirect()->route('xuat-nhap-canh.quan-ly-yeu-cau-sua-xnc');
        } else {
            return redirect()->route('xuat-nhap-canh.danh-sach-xnc');
        }
    }


    public function suaXNC($ma_xnc)
    {
        $xuatNhapCanh = XuatNhapCanh::find($ma_xnc);
        return view('xuat-nhap-canh.sua-xnc', [
            'PTVTXuatCanhs' => PTVTXuatCanh::where('trang_thai', '2')->get(),
            'xuatNhapCanh' => $xuatNhapCanh,
            'chuHangs' => ChuHang::all(),
        ]);
    }
    public function suaXNCSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatNhapCanh = XuatNhapCanh::find($request->ma_xnc);
            if (!Carbon::parse($xuatNhapCanh->ngay_them)->lt(Carbon::today()->subDays(2))) {
                XuatNhapCanh::find($request->ma_xnc)->update([
                    'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
                    'so_the' => $request->so_the,
                    'is_hang_lanh' => $request->is_hang_lanh,
                    'is_hang_nong' => $request->is_hang_nong,
                    'so_luong_may' => $request->so_luong_may,
                    'tong_trong_tai' => $request->tong_trong_tai,
                    'ma_chu_hang' => $request->ma_chu_hang,
                    'thoi_gian_nhap_canh' => $request->thoi_gian_nhap_canh,
                    'thoi_gian_xuat_canh' => $request->thoi_gian_xuat_canh,
                    'ghi_chu' => $request->ghi_chu,
                ]);
            } else {
                XuatNhapCanhSua::create([
                    'ma_xnc' => $request->ma_xnc,
                    'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
                    'so_the' => $request->so_the,
                    'is_hang_lanh' => $request->is_hang_lanh,
                    'is_hang_nong' => $request->is_hang_nong,
                    'so_luong_may' => $request->so_luong_may,
                    'tong_trong_tai' => $request->tong_trong_tai,
                    'ma_chu_hang' => $request->ma_chu_hang,
                    'thoi_gian_nhap_canh' => $request->thoi_gian_nhap_canh,
                    'thoi_gian_xuat_canh' => $request->thoi_gian_xuat_canh,
                    'ghi_chu' => $request->ghi_chu,
                    'ngay_them' => $xuatNhapCanh->ngay_them,
                    'ma_cong_chuc' => $xuatNhapCanh->ma_cong_chuc,
                ]);
                XuatNhapCanh::find($request->ma_xnc)->update([
                    'trang_thai' => 3
                ]);
            }

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


    public function xemYeuCauSuaXNC($ma_xnc)
    {
        $xuatNhapCanh = XuatNhapCanh::join('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->join('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('ma_xnc', $ma_xnc)
            ->first();

        $xuatNhapCanhSua = XuatNhapCanhSua::where('ma_xnc', $ma_xnc)
            ->orderBy('ma_yeu_cau', 'desc')
            ->join('ptvt_xuat_canh', 'xuat_nhap_canh_sua.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->join('cong_chuc', 'xuat_nhap_canh_sua.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        return view('xuat-nhap-canh.xem-yeu-cau-sua-xnc', [
            'xuatNhapCanh' => $xuatNhapCanh,
            'xuatNhapCanhSua' => $xuatNhapCanhSua,
        ]);
    }

    public function duyetYeuCauSuaXNC($ma_yeu_cau)
    {
        $xuatNhapCanhSua = XuatNhapCanhSua::find($ma_yeu_cau);
        $ma_xnc = $xuatNhapCanhSua->ma_xnc;
        $xuatNhapCanh = XuatNhapCanh::find($ma_xnc);

        $xuatNhapCanh->update([
            'so_ptvt_xuat_canh' => $xuatNhapCanhSua->so_ptvt_xuat_canh,
            'so_the' => $xuatNhapCanhSua->so_the,
            'is_hang_lanh' => $xuatNhapCanhSua->is_hang_lanh,
            'is_hang_nong' => $xuatNhapCanhSua->is_hang_nong,
            'ma_chu_hang' => $xuatNhapCanhSua->ma_chu_hang,
            'so_luong_may' => $xuatNhapCanhSua->so_luong_may,
            'tong_trong_tai' => $xuatNhapCanhSua->tong_trong_tai,
            'thoi_gian_nhap_canh' => $xuatNhapCanhSua->thoi_gian_nhap_canh,
            'thoi_gian_xuat_canh' => $xuatNhapCanhSua->thoi_gian_xuat_canh,
            'ghi_chu' => $xuatNhapCanhSua->ghi_chu,
            'trang_thai' => 1,
        ]);

        XuatNhapCanhSua::find($ma_yeu_cau)->delete();

        session()->flash('alert-success', 'Duyệt yêu cầu sửa thành công!');
        return redirect()->route('xuat-nhap-canh.thong-tin-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc]);
    }
    public function huyYeuCauSuaXNC($ma_yeu_cau)
    {
        $ma_xnc = XuatNhapCanhSua::find($ma_yeu_cau)->ma_xnc;
        $xuatNhapCanh = XuatNhapCanh::find($ma_xnc);
        $xuatNhapCanh->trang_thai = 1;
        $xuatNhapCanh->save();
        XuatNhapCanhSua::where('ma_yeu_cau', operator: $ma_yeu_cau)->delete();
        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('xuat-nhap-canh.thong-tin-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc]);
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
                'ptvt_xuat_canh.ten_phuong_tien_vt',
                'cong_chuc.ten_cong_chuc'
            )
                ->orderBy('ma_xnc', 'desc')
                ->join('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
                ->join('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc');

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('xuat_nhap_canh.ma_xnc', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(xuat_nhap_canh.ngay_them, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('ptvt_xuat_canh.ten_phuong_tien_vt', 'LIKE', "%{$search}%")
                                ->orWhere('xuat_nhap_canh.so_the', 'LIKE', "%{$search}%");
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
                ->editColumn('trang_thai', function ($xuatHang) {
                    $status = trim($xuatHang->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Yêu cầu sửa', 'class' => 'text-warning'],
                        '4' => ['text' => 'Yêu cầu hủy', 'class' => 'text-danger'],
                    ];

                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->rawColumns(['loai_hang', 'trang_thai', 'action'])
                ->make(true);
        }
    }
}
