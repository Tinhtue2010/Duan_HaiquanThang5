<?php

namespace App\Http\Controllers;

use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PTVanTaiController extends Controller
{
    public function danhsachPTVTXC()
    {
        $data = PTVTXuatCanh::where('trang_thai', 1)->orderBy('so_ptvt_xuat_canh', 'desc')->first();
        return view('ptvt-xuat-canh.danh-sach-ptvt-xc', data: compact(var_name: 'data'));
    }
    public function themPTVTXC()
    {
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        return view('ptvt-xuat-canh.them-to-khai-ptvt-xc', data: compact(var_name: 'doanhNghiep'));
    }
    public function themPTVTXCSubmit(Request $request)
    {
        $trang_thai = 2;
        $ma_doanh_nghiep = null;
        if (PTVTXuatCanh::where('ten_phuong_tien_vt', $request->ten_phuong_tien_vt)->where('trang_thai', 2)->exists()) {
            session()->flash('alert-danger', 'Trùng tên tàu trong hệ thống');
            return redirect()->back();
        }

        if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp') {
            $trang_thai = 1;
            $ma_doanh_nghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
        }

        PTVTXuatCanh::insert([
            'ten_phuong_tien_vt' => $request->ten_phuong_tien_vt,
            'quoc_tich_tau' => $request->quoc_tich_tau,
            'cang_den' => $request->cang_den,
            'ten_thuyen_truong' => $request->ten_thuyen_truong,
            'so_giay_chung_nhan' => $request->so_giay_chung_nhan,
            'draft_den' => $request->draft_den,
            'dwt_den' => $request->dwt_den,
            'loa_den' => $request->loa_den,
            'breadth_den' => $request->breadth_den,
            'draft_roi' => $request->draft_roi,
            'dwt_roi' => $request->dwt_roi,
            'loa_roi' => $request->loa_roi,
            'breadth_roi' => $request->breadth_roi,
            'ma_doanh_nghiep' => $ma_doanh_nghiep,
            'trang_thai' => $trang_thai,
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

    public function duyetPTVTXC(Request $request)
    {
        $PTVTXC = PTVTXuatCanh::find($request->so_ptvt_xuat_canh);
        $PTVTXC->trang_thai = "2";
        $PTVTXC->save();
        session()->flash('alert-success', 'Duyệt tờ khai thành công!');
        return redirect()->route('phuong-tien-vt.danh-sach-ptvt-xc');
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
            'draft_den' => $request->draft_den,
            'dwt_den' => $request->dwt_den,
            'loa_den' => $request->loa_den,
            'breadth_den' => $request->breadth_den,
            'draft_roi' => $request->draft_roi,
            'dwt_roi' => $request->dwt_roi,
            'loa_roi' => $request->loa_roi,
            'breadth_roi' => $request->breadth_roi,
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
    public function getPTVTs(Request $request)
    {
        if ($request->ajax()) {
            $query = PTVTXuatCanh::orderBy('so_ptvt_xuat_canh', 'desc')
                ->leftJoin('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'ptvt_xuat_canh.ma_doanh_nghiep')
                ->select([
                    'ptvt_xuat_canh.*',
                    'doanh_nghiep.ten_doanh_nghiep',
                ]);
            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('ptvt_xuat_canh.so_ptvt_xuat_canh', 'LIKE', "%{$search}%")
                                ->orWhere('doanh_nghiep.ten_doanh_nghiep', 'LIKE', "%{$search}%")
                                ->orWhere('ptvt_xuat_canh.ten_phuong_tien_vt', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($PTVT) {
                    return '';
                })
                ->addColumn('ten_doanh_nghiep', function ($PTVT) {
                    return $PTVT->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('ten_phuong_tien_vt', function ($PTVT) {
                    return $PTVT->ten_phuong_tien_vt ?? 'N/A';
                })
                ->addColumn('ten_thuyen_truong', function ($PTVT) {
                    return $PTVT->ten_thuyen_truong ?? 'N/A';
                })
                ->addColumn('quoc_tich_tau', function ($PTVT) {
                    return $PTVT->quoc_tich_tau ?? 'N/A';
                })
                ->addColumn('action', function ($PTVT) {
                    return '<a href="' . route('phuong-tien-vt.thong-tin-ptvt-xc', $PTVT->so_ptvt_xuat_canh) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('trang_thai', function ($PTVT) {
                    $status = trim($PTVT->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đang chờ duyệt', 'class' => 'text-primary'],
                        '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Đã duyệt thực xuất', 'class' => 'text-success'],
                        '4' => ['text' => 'Doanh nghiệp xin sửa', 'class' => 'text-warning'],
                        '5' => ['text' => 'Doanh nghiệp xin hủy', 'class' => 'text-danger'],
                        '0' => ['text' => 'Đã hủy', 'class' => 'text-danger'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->rawColumns(['trang_thai', 'action'])
                ->make(true);
        }
    }
}
