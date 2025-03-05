<?php

namespace App\Http\Controllers;

use App\Exports\ToKhaiXuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanh;
use App\Models\ThuyenTruong;
use App\Models\XuatCanh;
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
                $so_ptvt_xuat_canh = $request->so_ptvt_xuat_canh;

                $xuatCanh = $this->xuatCanhService->themXuatCanh($request);
                $xuatHangs = $this->xuatCanhService->getXuatHangDaDuyet($so_ptvt_xuat_canh);

                $processedSoToKhaiNhap = []; // Store processed `so_to_khai_nhap`

                foreach ($xuatHangs as $xuatHang) {
                    $xuatHang->trang_thai = "Đã chọn phương tiện xuất cảnh";
                    $xuatHang->save();
                    $this->xuatCanhService->themChiTietXuatCanh($xuatCanh, $xuatHang);

                    $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                        ->select('so_to_khai_nhap')
                        ->distinct()
                        ->get();

                    foreach ($xuatHangConts as $xuatHangCont) {
                        if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                            $this->xuatCanhService->themTienTrinh(
                                $xuatHangCont->so_to_khai_nhap,
                                "Doanh nghiệp tạo tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh,
                                ''
                            );
                            $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap; // Mark as processed
                        }
                    }
                }


                $thuyenTruongs = ThuyenTruong::pluck("ten_thuyen_truong")->toArray();
                if (!in_array($request->ten_thuyen_truong, $thuyenTruongs)) {
                    ThuyenTruong::insert([
                        'ten_thuyen_truong' => $request->ten_thuyen_truong,
                    ]);
                }


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
        $congChucs = CongChuc::where('is_chi_xem',0)->get();
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
                    'xuat_hang.lan_xuat_canh',
                    'xuat_hang.ngay_dang_ky',
                    'xuat_hang.ngay_xuat_canh',
                    'xuat_hang.ten_doan_tau',
                    'xuat_hang.trang_thai',
                    'xuat_hang.ghi_chu',
                    'xuat_hang.ma_cong_chuc',
                    'xuat_hang.so_seal_cuoi_ngay',
                    'xuat_hang.ma_doanh_nghiep',
                    'xuat_hang.phuong_tien_vt_nhap',
                    'xuat_hang.ten_phuong_tien_vt',
                    'xuat_hang.tong_so_luong',
                    'xuat_hang.created_at',
                    'xuat_hang.updated_at',
                )
                ->get();
        }
        $congChucs = CongChuc::where('is_chi_xem',0)->get();

        //else {
        //     $xuatHang = XuatHangSecond::find($so_to_khai_xuat);
        //     $xuatHangs = XuatHangSecond::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)->get();
        //     $hangHoaRows = $this->xuatHangService->getThongTinPhieuXuatHang($so_to_khai_xuat, 'second');
        // }

        return view('xuat-canh.thong-tin-xuat-canh', compact('xuatCanh', 'chiTiets', 'congChucs')); // Pass data to the view
    }

    public function duyetXuatCanh(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
            $xuatCanh->trang_thai = "Đã duyệt";
            $xuatCanh->ma_cong_chuc = $request->ma_cong_chuc;
            $xuatCanh->ngay_duyet = now();
            $xuatCanh->save();

            $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                ->where('xuat_canh.ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->select('xuat_hang.*')
                ->get();

            foreach ($xuatHangs as $xuatHang) {
                $this->xuatCanhService->xuLyDuyetPhieuXuat($xuatHang, $request);
            }

            $processedSoToKhaiNhap = []; // Track processed `so_to_khai_nhap`

            foreach ($xuatHangs as $xuatHang) {
                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                    ->select('so_to_khai_nhap')
                    ->distinct()
                    ->get();

                foreach ($xuatHangConts as $xuatHangCont) {
                    if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                        $this->xuatCanhService->themTienTrinh(
                            $xuatHangCont->so_to_khai_nhap,
                            "Cán bộ công chức đã duyệt tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh,
                            $this->xuatCanhService->getCongChucHienTai()->ma_cong_chuc
                        );
                        $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap; // Mark as processed
                    }
                }
            }


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
            $xuatCanh->trang_thai = "Đã duyệt thực xuất";
            $xuatCanh->save();

            $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                ->where('xuat_canh.ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->select('xuat_hang.*')
                ->get();

            foreach ($xuatHangs as $xuatHang) {
                $this->xuatCanhService->xuLyDuyetThucXuat($xuatHang, $request);
            }

            $processedSoToKhaiNhap = []; // Track processed `so_to_khai_nhap`

            foreach ($xuatHangs as $xuatHang) {
                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                    ->select('so_to_khai_nhap')
                    ->distinct()
                    ->get();

                foreach ($xuatHangConts as $xuatHangCont) {
                    if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                        $this->xuatCanhService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức đã duyệt thực xuất tờ khai xuất cảnh số " . $xuatCanh->ma_xuat_canh, $this->xuatCanhService->getCongChucHienTai()->ma_cong_chuc);
                        $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap; // Mark as processed
                        $this->xuatCanhService->kiemTraXuatHetHang($xuatHangCont->so_to_khai_nhap);
                    }
                }
            }

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
                'xuat_hang.lan_xuat_canh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.trang_thai',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.so_seal_cuoi_ngay',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.phuong_tien_vt_nhap',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.updated_at',
            )
            ->get();

        return view('xuat-canh.sua-to-khai-xuat-canh', data: compact('xuatCanh', 'thuyenTruongs', 'doanhNghiep', 'doanhNghieps', 'chiTiets')); // Pass the data to the view
    }

    public function suaXuatCanhSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
            $xuatCanh->ten_thuyen_truong = $request->ten_thuyen_truong;
            $xuatCanh->ma_doanh_nghiep_chon = $request->ma_doanh_nghiep_chon;
            $xuatCanh->save();

            $thuyenTruongs = ThuyenTruong::pluck("ten_thuyen_truong")->toArray();
            if (!in_array($request->ten_thuyen_truong, $thuyenTruongs)) {
                ThuyenTruong::insert([
                    'ten_thuyen_truong' => $request->ten_thuyen_truong,
                ]);
            }

            DB::commit();
            session()->flash('alert-success', 'Thêm sửa tờ khai xuất cảnh thành công!');
            return redirect()->route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $request->ma_xuat_canh]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in suaXuatCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function yeuCauHuyXuatCanh(Request $request)
    {
        $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
        if ($xuatCanh) {
            $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                ->where('xuat_canh.ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->select('xuat_hang.*')
                ->get();

            if ($xuatCanh->trang_thai == 'Đang chờ duyệt') {
                $xuatCanh->trang_thai = 'Doanh nghiệp xin hủy (Chờ duyệt)';
            } elseif ($xuatCanh->trang_thai == 'Đã duyệt') {
                $xuatCanh->trang_thai = 'Doanh nghiệp xin hủy (Đã duyệt)';
            }

            $processedSoToKhaiNhap = []; // Track processed `so_to_khai_nhap`

            foreach ($xuatHangs as $xuatHang) {
                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                    ->select('so_to_khai_nhap')
                    ->distinct()
                    ->get();

                foreach ($xuatHangConts as $xuatHangCont) {
                    if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                        $this->xuatCanhService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp yêu cầu hủy tờ khai xuất cảnh số " . $request->ma_xuat_canh, '');
                        $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap; // Mark as processed
                    }
                }
            }

            $xuatCanh->ghi_chu = $request->ghi_chu;
            $xuatCanh->save();
            session()->flash('alert-success', 'Yêu cầu hủy phiếu xuất thành công!');
        }
        return redirect()->back();
    }

    public function thuHoiYeuCauHuyXuatCanh(Request $request)
    {
        $xuatCanh = XuatCanh::find($request->ma_xuat_canh);
        $xuatHangs = XuatCanhChiTiet::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet.so_to_khai_xuat')
            ->where('ma_xuat_canh', $xuatCanh->ma_xuat_canh)
            ->select('xuat_hang.*')
            ->get();

        if ($xuatCanh) {
            if ($xuatCanh->trang_thai == 'Doanh nghiệp xin hủy (Chờ duyệt)') {
                $xuatCanh->trang_thai = 'Đang chờ duyệt';
            } elseif ($xuatCanh->trang_thai == 'Doanh nghiệp xin hủy (Đã duyệt)') {
                $xuatCanh->trang_thai = 'Đã duyệt';
            }

            $processedSoToKhaiNhap = []; // Track processed `so_to_khai_nhap`
            foreach ($xuatHangs as $xuatHang) {
                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                    ->select('so_to_khai_nhap')
                    ->distinct()
                    ->get();

                foreach ($xuatHangConts as $xuatHangCont) {
                    if (!in_array($xuatHangCont->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                        $this->xuatCanhService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp thu hồi yêu cầu hủy tờ khai xuất cảnh số " . $request->ma_xuat_canh, '');
                        $processedSoToKhaiNhap[] = $xuatHangCont->so_to_khai_nhap; // Mark as processed
                    }
                }
            }

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
    public function getPhieuXuats(Request $request)
    {
        $xuatHangs = $this->xuatCanhService->getXuatHangDaDuyet($request->so_ptvt_xuat_canh);

        return response()->json(['xuatHangs' => $xuatHangs]);
    }
    public function getDoanhNghiepsTrongCacPhieu(Request $request)
    {
        $doanhNghieps = XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $request->so_ptvt_xuat_canh)
            ->where('xuat_hang.trang_thai', 'Đã duyệt')
            ->whereDate('xuat_hang.ngay_dang_ky', today())
            ->select('doanh_nghiep.*')
            ->distinct()
            ->get();

        return response()->json(['doanhNghieps' => $doanhNghieps]);
    }

    public function getXuatCanhs(Request $request)
    {
        if ($request->ajax()) {

            // Check user type to filter data accordingly
            if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $xuatCanhs = XuatCanh::where('ma_doanh_nghiep', Auth::user()->ma_doanh_nghiep)
                    ->orderBy('ma_xuat_canh', 'desc')
                    ->get();
            } else {
                $xuatCanhs = XuatCanh::orderBy('ma_xuat_canh', 'desc')->get();
            }

            return DataTables::of($xuatCanhs)
                ->addIndexColumn() // Adds auto-incrementing index
                ->editColumn('ngay_dang_ky', function ($xuatCanh) {
                    return Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($xuatCanh) {
                    return $xuatCanh->doanhNghiep->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('ten_phuong_tien_vt', function ($xuatCanh) {
                    return $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt ?? 'N/A';
                })
                ->addColumn('action', function ($xuatCanh) {
                    return '<a href="' . route('xuat-canh.thong-tin-xuat-canh', $xuatCanh->ma_xuat_canh) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('trang_thai', function ($xuatCanh) {
                    $status = trim($xuatCanh->trang_thai);
                    if (in_array($status, ['Doanh nghiệp xin hủy (Chờ duyệt)', 'Doanh nghiệp xin hủy (Đã duyệt)', 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng'])) {
                        return '<span class="text-warning">' . $status . '</span>';
                    } elseif (in_array($status, ['Đã duyệt', 'Đã thực duyệt'])) {
                        return '<span class="text-success">' . $status . '</span>';
                    } elseif (in_array($status, ['Đã hủy', 'Chấp nhận hủy', 'Từ chối hủy'])) {
                        return '<span class="text-danger">' . $status . '</span>';
                    } elseif (in_array($status, ['Đang chờ duyệt'])) {
                        return '<span class="text-primary">' . $status . '</span>';
                    } else {
                        return '<span class="text-dark">' . $status . '</span>';
                    }
                })
                ->rawColumns(['trang_thai', 'action']) // Allows HTML in status & action columns
                ->make(true);
        }
    }
}
