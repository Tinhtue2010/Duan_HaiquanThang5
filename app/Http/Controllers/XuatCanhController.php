<?php

namespace App\Http\Controllers;

use App\Exports\ToKhaiXuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanh;
use App\Models\ThuyenTruong;
use App\Models\XuatCanh;
use App\Models\XuatCanhChiTietSua;
use App\Models\XuatCanhSua;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Services\XuatCanhService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class XuatCanhController extends Controller
{
    protected $xuatCanhService;

    public function __construct(XuatCanhService $xuatCanhService)
    {
        $this->xuatCanhService = $xuatCanhService;
    }
    public function danhSachToKhai()
    {
        $xuatCanhs = XuatCanh::orderBy('ma_xuat_canh', 'desc')->get();
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $xuatCanhs = XuatCanh::where('ma_doanh_nghiep', $this->xuatCanhService->getDoanhNghiepHienTai()->ma_doanh_nghiep)
                ->orderBy('ma_xuat_canh', 'desc')
                ->get();
        }
        return view('xuat-canh.quan-ly-xuat-canh', ['xuatCanhs' => $xuatCanhs]);
    }

    public function themToKhai()
    {
        if (Auth::user()->loai_tai_khoan !== "Doanh nghiệp") {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
        $thuyenTruongs = ThuyenTruong::all()->pluck("ten_thuyen_truong");

        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

        return view('xuat-canh.them-to-khai-xuat-canh', [
            'PTVTXuatCanhs' => PTVTXuatCanh::all(),
            'doanhNghiep' => $doanhNghiep,
            'thuyenTruongs' => $thuyenTruongs,
        ]);
    }



    public function themXuatCanhSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                $xuatCanh = $this->xuatCanhService->themXuatCanh($request);
                $rowsData = json_decode($request->rows_data, true);

                foreach ($rowsData as $row) {
                    $xuatHang = XuatHang::find($row['so_to_khai_xuat']);
                    $xuatHang->trang_thai = "11";
                    $xuatHang->save();
                    $this->xuatCanhService->themChiTietXuatCanh($xuatCanh->ma_xuat_canh, $row['so_to_khai_xuat']);
                }

                $thuyenTruongs = ThuyenTruong::pluck("ten_thuyen_truong")->toArray();
                if (!in_array($request->ten_thuyen_truong, $thuyenTruongs)) {
                    ThuyenTruong::insert([
                        'ten_thuyen_truong' => $request->ten_thuyen_truong,
                    ]);
                }

                $this->themTienTrinh($xuatCanh->ma_xuat_canh, "tạo tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);

                return redirect()
                    ->route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh])
                    ->with('alert-success', 'Thêm tờ khai mới thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in themXuatCanhSubmit: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinXuatCanh($ma_xuat_canh)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        if (XuatCanh::find($ma_xuat_canh)) {
            $xuatCanh = XuatCanh::find($ma_xuat_canh);
            $chiTiets = XuatCanhChiTiet::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet.so_to_khai_xuat')
                ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->where('ma_xuat_canh', $ma_xuat_canh)
                ->select(
                    'xuat_hang.*',
                    DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
                )
                ->groupBy(
                    'xuat_hang.so_to_khai_xuat',
                    'xuat_hang.ma_loai_hinh',
                    'xuat_hang.ngay_dang_ky',
                    'xuat_hang.ngay_xuat_canh',
                    'xuat_hang.ten_doan_tau',
                    'xuat_hang.trang_thai',
                    'xuat_hang.ghi_chu',
                    'xuat_hang.ma_cong_chuc',
                    'xuat_hang.ma_doanh_nghiep',
                    'xuat_hang.phuong_tien_vt_nhap',
                    'xuat_hang.ten_phuong_tien_vt',
                    'xuat_hang.tong_so_luong',
                    'xuat_hang.created_at',
                    'xuat_hang.updated_at',
                )
                ->get();
        }
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();

        return view('xuat-canh.thong-tin-xuat-canh', compact('xuatCanh', 'chiTiets', 'congChucs')); // Pass data to the view
    }
    public function xemYeuCauSuaXuatCanh($ma_xuat_canh)
    {
        $xuatCanhSua = XuatCanhSua::where('ma_xuat_canh', $ma_xuat_canh)->first();
        $chiTietSuas = XuatCanhChiTietSua::where('ma_yeu_cau', $xuatCanhSua->ma_yeu_cau)->get();

        $xuatCanh = XuatCanh::find($ma_xuat_canh);
        $chiTiets = XuatCanhChiTiet::join('xuat_hang', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_canh_chi_tiet.ma_xuat_canh', $ma_xuat_canh)
            ->select(
                'xuat_hang.*',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.trang_thai',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.phuong_tien_vt_nhap',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.updated_at',
            )
            ->get();

        $chiTietSuas = XuatCanhChiTietSua::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet_sua.so_to_khai_xuat')
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('ma_yeu_cau', $xuatCanhSua->ma_yeu_cau)
            ->select(
                'xuat_hang.*',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.trang_thai',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.phuong_tien_vt_nhap',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.updated_at',
            )
            ->get();

        return view('xuat-canh.xem-sua-xuat-canh', compact('xuatCanh', 'xuatCanhSua', 'chiTiets', 'chiTietSuas')); // Pass data to the view
    }

    public function duyetXuatCanh(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
            $xuatCanh->trang_thai = "2";
            $xuatCanh->ma_cong_chuc = $request->ma_cong_chuc;
            $xuatCanh->ngay_duyet = now();
            $xuatCanh->save();


            $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                ->where('xuat_canh.ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->select('xuat_hang.*')
                ->get();

            foreach ($xuatHangs as $xuatHang) {
                $this->xuatCanhService->xuLyDuyetPhieuXuat($xuatHang);
            }

            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "đã duyệt tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);


            DB::commit();
            session()->flash('alert-success', 'Duyệt tờ khai thành công!');
            return redirect()->route('xuat-canh.quan-ly-xuat-canh');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetXuatCanh: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function duyetThucXuat(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
            $xuatCanh->trang_thai = "3";
            $xuatCanh->save();

            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "đã duyệt thực xuất tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);

            DB::commit();
            session()->flash('alert-success', 'Duyệt thực xuất tờ khai thành công!');
            return redirect()->route('xuat-canh.quan-ly-xuat-canh');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetThucXuat: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function suaXuatCanh($ma_xuat_canh)
    {
        $xuatCanh = XuatCanh::find($ma_xuat_canh);
        $thuyenTruongs = ThuyenTruong::all()->pluck("ten_thuyen_truong");
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

        $doanhNghieps = XuatCanh::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.ma_xuat_canh', 'xuat_canh.ma_xuat_canh')
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet.so_to_khai_xuat')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'xuat_hang.ma_doanh_nghiep')
            ->where('xuat_canh.ma_xuat_canh', $ma_xuat_canh)
            ->select('doanh_nghiep.*')
            ->distinct()
            ->get();
        $chiTiets = XuatCanhChiTiet::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet.so_to_khai_xuat')
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('ma_xuat_canh', $ma_xuat_canh)
            ->select(
                'xuat_hang.*',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.trang_thai',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.phuong_tien_vt_nhap',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.updated_at',
            )
            ->get();
        $tongSoLuong = $chiTiets->sum('tong_so_luong_xuat');
        return view('xuat-canh.sua-to-khai-xuat-canh', data: compact('xuatCanh', 'thuyenTruongs', 'doanhNghiep', 'doanhNghieps', 'chiTiets', 'tongSoLuong')); // Pass the data to the view
    }

    public function suaXuatCanhSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
            if ($xuatCanh->trang_thai == '2') {
                $xuatCanh->trang_thai = '4';
                $xuatCanh->save();
            }
            $xuatCanhSua = $this->xuatCanhService->themXuatCanhSua($request, $xuatCanh);
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                $xuatHang = XuatHang::find($row['so_to_khai_xuat']);
                $this->xuatCanhService->themChiTietXuatCanhSua($xuatCanhSua, $xuatHang);
            }

            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "yêu cầu sửa tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);

            DB::commit();
            if ($xuatCanh->trang_thai == '1') {
                $this->duyetSuaXuatCanh($xuatCanhSua->ma_yeu_cau);
            }
            session()->flash('alert-success', 'Thêm sửa tờ khai xuất cảnh thành công!');
            return redirect()->route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $request->ma_xuat_canh]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in suaXuatCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function duyetSuaXuatCanh($ma_yeu_cau, Request $request = null)
    {
        try {
            DB::beginTransaction();
            $xuatCanhSua = XuatCanhSua::find($ma_yeu_cau);
            $xuatCanh = XuatCanh::find($xuatCanhSua->ma_xuat_canh);

            $this->xuatCanhService->suaXuatCanh($ma_yeu_cau, $xuatCanh->trang_thai);
            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "duyệt yêu cầu sửa tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);
            DB::commit();
            session()->flash('alert-success', 'Sửa tờ khai xuất cảnh thành công!');
            return redirect()->route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaXuatCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }



    public function huyYeuCauSuaXuatCanh(Request $request, $ma_yeu_cau)
    {
        try {
            DB::beginTransaction();
            $xuatCanhSua = XuatCanhSua::find($ma_yeu_cau);
            $xuatCanh = XuatCanh::find($xuatCanhSua->ma_xuat_canh);

            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "hủy yêu cầu sửa tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);

            $xuatCanh->trang_thai = 2;
            $xuatCanh->ghi_chu = $request->ghi_chu;
            $xuatCanh->save();

            $xuatCanhSua->delete();
            XuatCanhChiTietSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
            session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
            DB::commit();
            return redirect()->route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauSua: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function themTienTrinh($ma_xuat_canh, $noi_dung)
    {
        $processedSoToKhaiNhap = [];
        $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
            ->where('xuat_canh.ma_xuat_canh', $ma_xuat_canh)
            ->select('xuat_hang.*')
            ->get();

        foreach ($xuatHangs as $xuatHang) {
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();

            foreach ($xuatHangConts as $xuatHangCont) {
                if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                    if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                        $congChuc = $this->xuatCanhService->getCongChucHienTai();
                        foreach ($xuatHangConts as $xuatHangCont) {
                            $this->xuatCanhService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức " . $noi_dung, $congChuc->ma_cong_chuc);
                        }
                    } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                        foreach ($xuatHangConts as $xuatHangCont) {
                            $this->xuatCanhService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp " . $noi_dung, '');
                        }
                    }
                    $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap;
                }
            }
        }
    }


    public function yeuCauHuyXuatCanh(Request $request)
    {
        $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
        if ($xuatCanh) {

            if ($xuatCanh->trang_thai == '1') {
                $this->xuatCanhService->huyXuatCanhFunc($request->ma_xuat_canh, $request->ghi_chu, "Doanh nghiệp", '');
            } elseif ($xuatCanh->trang_thai == '2') {
                $xuatCanh->trang_thai = '5';
            }
            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "yêu cầu hủy tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);

            $xuatCanh->ghi_chu = $request->ghi_chu;
            $xuatCanh->save();
            session()->flash('alert-success', 'Yêu cầu hủy phiếu xuất thành công!');
        }
        return redirect()->back();
    }

    public function thuHoiYeuCauHuyXuatCanh(Request $request)
    {
        $xuatCanh = XuatCanh::find($request->ma_xuat_canh);

        if ($xuatCanh) {
            if ($xuatCanh->trang_thai == '4') {
                $xuatCanh->trang_thai = '1';
            } elseif ($xuatCanh->trang_thai == '5') {
                $xuatCanh->trang_thai = '2';
            }

            $this->themTienTrinh($xuatCanh->ma_xuat_canh, "thu hồi yêu cầu hủy tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh);


            $xuatCanh->ghi_chu = $request->ghi_chu;
            $xuatCanh->save();
            session()->flash('alert-success', 'Thu hồi yêu cầu hủy thành công!');
        }
        return redirect()->back();
    }

    public function huyXuatCanh(Request $request)
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $this->xuatCanhService->huyXuatCanhFunc($request->ma_xuat_canh, $request->ghi_chu, "Cán bộ công chức", '');
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $this->xuatCanhService->huyXuatCanhFunc($request->ma_xuat_canh, $request->ghi_chu, "Doanh nghiệp", '');
        }
        session()->flash('alert-success', 'Hủy tờ khai xuất cảnh thành công!');
        return redirect()->back();
    }

    public function exportToKhaiXuatCanh(Request $request)
    {
        // $export = new ToKhaiXuatCanh(
        //     $request->ma_xuat_canh, 
        //     $request->ten_thuyen_truong, 
        //     $request->ma_doanh_nghiep
        // );

        // // Export as PDF using mPDF
        // $response = Excel::download($export, 'Tờ khai xuất cảnh.pdf', \Maatwebsite\Excel\Excel::MPDF);

        // // Set header to inline for browser display
        // $response->headers->set('Content-Disposition', 'inline; filename="Tờ khai xuất cảnh.pdf"');

        // return $response;
        $fileName = 'Tờ khai xuất cảnh.xlsx';
        return Excel::download(new ToKhaiXuatCanh($request->ma_xuat_canh), $fileName);
    }


    public function thayDoiCongChucXuatCanh(Request $request)
    {
        XuatCanh::find($request->ma_xuat_canh)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }
    public function getPhieuXuats(Request $request)
    {
        if (!$request->ma_xuat_canh) {
            $xuatHangs = $this->xuatCanhService->getXuatHangDaDuyet($request->so_ptvt_xuat_canh);
        } else {
            $xuatHangs = $this->xuatCanhService->getXuatHangDaDuyetSua($request->so_ptvt_xuat_canh, $request->ma_xuat_canh);
        }

        return response()->json(['xuatHangs' => $xuatHangs]);
    }
    public function getDoanhNghiepsTrongCacPhieu(Request $request)
    {
        $doanhNghieps = XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '2')
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('xuat_hang.ngay_dang_ky', today())
                        ->orWhereDate('xuat_hang.ngay_dang_ky', today()->subDay());
                } else {
                    $query->whereDate('xuat_hang.ngay_dang_ky', today());
                }
            })
            ->select('doanh_nghiep.*');
        if ($request->ma_xuat_canh) {
            $chiTiets = XuatCanh::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.ma_xuat_canh', 'xuat_canh.ma_xuat_canh')
                ->where('xuat_canh.ma_xuat_canh', $request->ma_xuat_canh)
                ->pluck('so_to_khai_xuat')->unique()->values();

            $doanhNghieps2 = XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->whereIn('xuat_hang.so_to_khai_xuat', $chiTiets)
                ->where(function ($query) {
                    if (now()->hour < 9) {
                        $query->whereDate('xuat_hang.ngay_dang_ky', today())
                            ->orWhereDate('xuat_hang.ngay_dang_ky', today()->subDay());
                    } else {
                        $query->whereDate('xuat_hang.ngay_dang_ky', today());
                    }
                })
                ->select('doanh_nghiep.*');
            $doanhNghieps = $doanhNghieps->union($doanhNghieps2)
                ->groupBy('xuat_hang.ma_doanh_nghiep')
                ->get();
        } else {
            $doanhNghieps = XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $request->so_ptvt_xuat_canh)
                ->where('xuat_hang.trang_thai', '2')
                ->where(function ($query) {
                    if (now()->hour < 9) {
                        $query->whereDate('xuat_hang.ngay_dang_ky', today())
                            ->orWhereDate('xuat_hang.ngay_dang_ky', today()->subDay());
                    } else {
                        $query->whereDate('xuat_hang.ngay_dang_ky', today());
                    }
                })
                ->select('doanh_nghiep.*')
                ->distinct()
                ->get();
        }


        return response()->json(['doanhNghieps' => $doanhNghieps]);
    }

    public function getXuatCanhs(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = XuatCanh::query()
                ->select([
                    'xuat_canh.*', // Select all columns from xuat_canh
                    'doanh_nghiep.ten_doanh_nghiep', // Specific columns from doanh_nghiep
                    'ptvt_xuat_canh.ten_phuong_tien_vt' // Specific columns from ptvt_xuat_canh
                ])
                ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'xuat_canh.ma_doanh_nghiep')
                ->join('ptvt_xuat_canh', 'ptvt_xuat_canh.so_ptvt_xuat_canh', 'xuat_canh.so_ptvt_xuat_canh');
            if ($user->loai_tai_khoan === "Doanh nghiệp") {
                $query->where('xuat_canh.ma_doanh_nghiep', function ($subquery) use ($user) {
                    $subquery->select('ma_doanh_nghiep')
                        ->from('doanh_nghiep')
                        ->where('ma_tai_khoan', $user->ma_tai_khoan)
                        ->limit(1);
                });
            }
            $query->orderBy('xuat_canh.ma_xuat_canh', 'desc');

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('xuat_canh.ma_xuat_canh', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(xuat_canh.ngay_dang_ky, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('doanh_nghiep.ten_doanh_nghiep', 'LIKE', "%{$search}%")
                                ->orWhere('ptvt_xuat_canh.ten_phuong_tien_vt', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($xuatHang) {
                    return '';
                })
                ->editColumn('ngay_dang_ky', function ($xuatCanh) {
                    return Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($xuatCanh) {
                    return $xuatCanh->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('ten_phuong_tien_vt', function ($xuatCanh) {
                    return $xuatCanh->ten_phuong_tien_vt ?? 'N/A';
                })
                ->addColumn('action', function ($xuatCanh) {
                    return '<a href="' . route('xuat-canh.thong-tin-xuat-canh', $xuatCanh->ma_xuat_canh) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('trang_thai', function ($xuatHang) {
                    $status = trim($xuatHang->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đang chờ duyệt', 'class' => 'text-primary'],
                        '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Đã duyệt thực xuất', 'class' => 'text-success'],
                        '4' => ['text' => 'Doanh nghiệp xin sửa', 'class' => 'text-warning'],
                        '5' => ['text' => 'Doanh nghiệp xin hủy', 'class' => 'text-danger'],
                        '6' => ['text' => 'Chấp nhận hủy', 'class' => 'text-danger'],
                        '7' => ['text' => 'Từ chối hủy', 'class' => 'text-danger'],
                        '0' => ['text' => 'Đã hủy', 'class' => 'text-danger'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->rawColumns(['trang_thai', 'action']) // Allows HTML in status & action columns
                ->make(true);
        }
    }
}
