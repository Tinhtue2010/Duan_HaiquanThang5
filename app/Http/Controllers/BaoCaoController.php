<?php

namespace App\Http\Controllers;

use App\Exports\BangKeCongViec;
use App\Exports\BaoCaoCapHai;
use App\Exports\BaoCaoChiTietXNKTheoDN;
use App\Exports\BaoCaoDoanhNghiepXNKTheoDN;
use App\Exports\BaoCaoTheoDoiTruLuiExport;
use App\Exports\BaoCaoTonChuHangExport;
use App\Exports\BaoCaoTauLuuTaiCang;
use App\Exports\BaoCaoTonDoanhNghiepExport;
use App\Exports\BaoCaoHangTonTheoToKhaiExport;
use App\Exports\BaoCaoTiepNhanHangNgayExport;
use App\Exports\BaoCaoChiTietXNKTrongNgay;
use App\Exports\BaoCaoDoanhNghiepXNK;
use App\Exports\BaoCaoChuyenCuaKhauXuat;
use App\Exports\BaoCaoHangTieuHuy;
use App\Exports\BaoCaoHangTonTaiCang;
use App\Exports\BaoCaoContainerLuuTaiCang;
use App\Exports\BaoCaoHangHoaChuaThucXuat;
use App\Exports\BaoCaoSuDungSealChiTiet;
use App\Exports\BaoCaoPhieuXuatDoanhNghiep;
use App\Exports\BaoCaoSoLuongToKhaiXuat;
use App\Exports\BaoCaoTheoDoiHangHoa;
use App\Exports\BaoCaoPhieuXuatTheoXuong;
use App\Exports\BaoCaoTheoDoiTruLuiCuoiNgayExport;
use App\Exports\BaoCaoTheoDoiHangHoaTong;
use App\Exports\BaoCaoTheoDoiTruLuiTatCaExport;
use App\Exports\BaoCaoTheoDoiTruLuiTheoNgayExport;
use App\Exports\BaoCaoDangKyXuatKhauHangHoa;
use App\Exports\BaoCaoSangContChuyenTau;
use App\Exports\BaoCaoGiamSatXuatKhau;
use App\Exports\BaoCaoSuDungSeal;
use App\Exports\BaoCaoContainerLuuTaiCangTheoCont;
use App\Exports\BaoCaoPhuongTienXuatCanhSuaHuy;
use App\Exports\BaoCaoDangKyXuatKhauHangHoa2;
use App\Exports\BaoCaoPhuongTienNhapCanh;
use App\Exports\BaoCaoPhuongTienXuatCanh;
use App\Exports\BaoCaoTheoDoiTruLuiAllSheetExport;
use App\Exports\BaoCaoToKhaiXuatHetDoanhNghiep;
use App\Exports\BaoCaoTraCuuContainer;
use App\Exports\PhanCongNhiemVuGiamSat;
use App\Exports\TheoDoiXuatNhapCanh;
use App\Exports\BaoCaoThoiGianToKhai;
use App\Models\ChuHang;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\DoanhNghiepQL;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanh;
use App\Models\TheoDoiTruLui;
use App\Models\XuatHang;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;



class BaoCaoController extends Controller
{
    public function index()
    {
        $ptvtXuatCanhs = PTVTXuatCanh::all();
        $doanhNghieps = DoanhNghiep::with('chuHang')->get();
        $chuHangs = ChuHang::select('ma_chu_hang', 'ten_chu_hang')->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        $phuongTienVTNhaps = NhapHang::all()->pluck('phuong_tien_vt_nhap')->unique()->toArray();
        return view('bao-cao/bao-cao-hang-ton', compact('doanhNghieps', 'chuHangs', 'ptvtXuatCanhs', 'congChucs', 'phuongTienVTNhaps')); // Pass the data to the view
    }
    public function baoCaoTheoDoanhNghiep()
    {
        $ma_doanh_nghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
        $maDoanhNghiepKhacs = DoanhNghiepQL::where('ma_doanh_nghiep_ql', $ma_doanh_nghiep)->pluck('ma_doanh_nghiep_khac');
        $maDoanhNghiepKhacs->push($ma_doanh_nghiep);
        $doanhNghieps = DoanhNghiep::whereIn('ma_doanh_nghiep', $maDoanhNghiepKhacs)->get();
        $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail()->ma_doanh_nghiep;
        $phuongTienVTNhaps = NhapHang::where('ma_doanh_nghiep', $maDoanhNghiep)
            ->pluck('phuong_tien_vt_nhap')
            ->unique()
            ->toArray();
        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->select('container.*', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->groupBy('container.so_container', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->where('nhap_hang.ma_doanh_nghiep', $maDoanhNghiep)
            ->get();
        return view('bao-cao/bao-cao-hang-ton-doanh-nghiep', compact('doanhNghieps', 'phuongTienVTNhaps', 'containers')); // Pass the data to the view
    }
    public function theoDoiTruLui(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi hàng hóa xuất khẩu.xlsx';
        return Excel::download(new BaoCaoTheoDoiTruLuiExport($request->cong_viec, $request->ma_yeu_cau, $request->so_to_khai_nhap), $fileName);
    }
    public function theoDoiTruLuiCuoiNgay(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi cuối ngày ' . $request->so_to_khai_nhap . '.xlsx';
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);

        if (!NhapHang::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
            return redirect()->back();
        };
        return Excel::download(new BaoCaoTheoDoiTruLuiCuoiNgayExport($request->so_to_khai_nhap, $tu_ngay), $fileName);
    }
    public function theoDoiTruLuiTatCa(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . '.xlsx';
        if (!NhapHang::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
            return redirect()->back();
        };
        return Excel::download(new BaoCaoTheoDoiTruLuiTatCaExport($request->so_to_khai_nhap), $fileName);
    }
    public function theoDoiTruLuiTheoNgayZip($rowsData, $ma_doanh_nghiep, $doanhNghiep, $date)
    {
        $allTheoDoiTruLuis = collect();

        foreach ($rowsData as $row) {
            $theoDoiTruLuis = $this->getTruLuis($row['cong_viec'], $ma_doanh_nghiep, $date, $row['so_to_khai_nhap']);
            $allTheoDoiTruLuis = $allTheoDoiTruLuis->merge($theoDoiTruLuis);
        }
        $theoDoiTruLuis = $allTheoDoiTruLuis->unique(function ($item) {
            return $item->so_to_khai_nhap . '-' . $item->cong_viec;
        })->values();

        // Create a unique temporary folder for storing Excel files (supports concurrency)
        $uniqueFolder = Str::uuid()->toString();
        $storagePath = storage_path("app/public/exports/{$uniqueFolder}");
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        }

        $files = [];
        $ngay_name = Carbon::parse($date)->format('d-m-Y');

        foreach ($theoDoiTruLuis as $theoDoiTruLui) {
            $so_to_khai = $theoDoiTruLui->so_to_khai_nhap;
            if ($theoDoiTruLui->cong_viec == 10) {
                $fileName = 'Phiếu theo dõi trừ lùi của tờ khai: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                $export = new BaoCaoTheoDoiTruLuiCuoiNgayExport($so_to_khai, $theoDoiTruLui->ngay_them);
            } elseif ($theoDoiTruLui->cong_viec == 1) {
                $fileName = 'Phiếu theo dõi trừ lùi xuất hàng của tờ khai: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                $export = new BaoCaoTheoDoiTruLuiTheoNgayExport($so_to_khai, $theoDoiTruLui->ngay_them);
            } else {
                if ($theoDoiTruLui->cong_viec == 2) {
                    $fileName = 'Phiếu theo dõi trừ lùi chuyển tàu container của tờ khai: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 3) {
                    $fileName = 'Phiếu theo dõi trừ lùi chuyển container của tờ khai: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 4) {
                    $fileName = 'Phiếu theo dõi trừ lùi chuyển tàu của tờ khai: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 5) {
                    $fileName = 'Đưa hàng trở lại kho ban đầu: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 6) {
                    $fileName = 'Tiêu hủy hàng: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 7) {
                    $fileName = 'Kiểm tra hàng: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                } elseif ($theoDoiTruLui->cong_viec == 9) {
                    $fileName = 'Gỡ seal điện tử: ' . $so_to_khai . ' ngày ' . $ngay_name . ' ' . uniqid() . '.xlsx';
                }
                $export = new BaoCaoTheoDoiTruLuiExport($theoDoiTruLui->cong_viec, $theoDoiTruLui->ma_yeu_cau, $so_to_khai);
            }

            // Build relative path and store on the "public" disk
            $relativePath = "exports/{$uniqueFolder}/{$fileName}";
            Excel::store($export, $relativePath, 'public');

            if (!Storage::disk('public')->exists($relativePath)) {
                Log::error("File not found after storing: " . Storage::disk('public')->path($relativePath));
                continue;
            }

            $files[] = Storage::disk('public')->path($relativePath);
        }
        if (empty($files)) {
            return response()->json(['error' => 'No Excel files were generated.'], 500);
        }

        $zipFileName = "Theo dõi trừ lùi ngày {$ngay_name}, {$doanhNghiep->ten_doanh_nghiep}, {$doanhNghiep->ten_chu_hang}.zip";
        $zipFilePath = storage_path("app/public/exports/{$zipFileName}");

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        } else {
            Log::error("Cannot open ZIP file at path: {$zipFilePath}");
            return response()->json(['error' => 'Could not create ZIP file'], 500);
        }

        // Double-check that the ZIP file exists before attempting to download
        if (!file_exists($zipFilePath)) {
            Log::error("ZIP file does not exist after creation: {$zipFilePath}");
            return response()->json(['error' => 'ZIP file not found.'], 500);
        }

        File::deleteDirectory($storagePath);
        return $zipFilePath;
    }

    public function theoDoiTruLuiTheoNgaySheet($rowsData, $ma_doanh_nghiep, $doanhNghiep, $date)
    {
        $allTheoDoiTruLuis = collect();
        foreach ($rowsData as $row) {
            $theoDoiTruLuis = $this->getTruLuis($row['cong_viec'], $ma_doanh_nghiep, $date, $row['so_to_khai_nhap']);
            $allTheoDoiTruLuis = $allTheoDoiTruLuis->merge($theoDoiTruLuis);
        }
        $theoDoiTruLuis = $allTheoDoiTruLuis->unique(function ($item) {
            return $item->so_to_khai_nhap . '-' . $item->cong_viec . '-' . $item->ma_yeu_cau;
        })->values();
        return $theoDoiTruLuis;
    }
    public function theoDoiTruLuiTheoNgayZipSheet(Request $request)
    {

        $ma_doanh_nghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail()->ma_doanh_nghiep;
        $rowsData = json_decode($request->rows_data, true);
        $doanhNghiep = DoanhNghiep::leftJoin('chu_hang', 'chu_hang.ma_chu_hang', '=', 'doanh_nghiep.ma_chu_hang')
            ->where('doanh_nghiep.ma_doanh_nghiep', $ma_doanh_nghiep)
            ->first();
        $date = $this->formatDateToYMD($request->ngay_tru_lui);

        if ($request->loai_in == "zip") {
            $zipFilePath = $this->theoDoiTruLuiTheoNgayZip($rowsData, $ma_doanh_nghiep, $doanhNghiep, $date);
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } else {
            $theoDoiTruLuis = $this->theoDoiTruLuiTheoNgaySheet($rowsData, $ma_doanh_nghiep, $doanhNghiep, $date);
            $ngay_name = Carbon::parse($date)->format('d-m-Y');
            $fileName = "Theo dõi trừ lùi ngày {$ngay_name} - {$doanhNghiep->ten_doanh_nghiep} - {$doanhNghiep->ten_chu_hang}.xlsx";

            try {
                return Excel::download(new BaoCaoTheoDoiTruLuiAllSheetExport($theoDoiTruLuis), $fileName);
            } catch (\Exception $e) {
                Log::error('Error exporting Excel: ' . $e->getMessage());
                return redirect()->back()->with('alert-danger', 'Đã xảy ra lỗi khi tạo file Excel.');
            }
        }
    }


    public function theoDoiTruLuiTheoNgay(Request $request)
    {
        if ($request->xuat_hang) {
            $ngay_name = Carbon::parse($request->ngay_dang_ky)->format('d-m-Y');
            $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $ngay_name . '.xlsx';
            return Excel::download(new BaoCaoTheoDoiTruLuiTheoNgayExport($request->so_to_khai_nhap, $request->ngay_dang_ky), $fileName);
        } else {
            $theoDoiTruLui = TheoDoiTruLui::find($request->ma_theo_doi);
            $ngay_name = Carbon::parse($theoDoiTruLui->ngay_them)->format('d-m-Y');
            $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $ngay_name . '.xlsx';
            if (!NhapHang::find($request->so_to_khai_nhap)) {
                session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
                return redirect()->back();
            };

            if ($theoDoiTruLui->cong_viec == 1) {
                return Excel::download(new BaoCaoTheoDoiTruLuiTheoNgayExport($theoDoiTruLui->so_to_khai_nhap, $theoDoiTruLui->ngay_them), $fileName);
            } else {
                return Excel::download(new BaoCaoTheoDoiTruLuiExport($theoDoiTruLui->cong_viec, $theoDoiTruLui->ma_yeu_cau, $request->so_to_khai_nhap), $fileName);
            }
        }
    }
    // public function theoDoiTruLuiTungLan(Request $request)
    // {
    //     $theoDoiTruLui = TheoDoiTruLui::find($request->ma_theo_doi);
    //     $ngay_name = Carbon::parse($theoDoiTruLui->ngay_them)->format('d-m-Y');
    //     $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $ngay_name . '.xlsx';
    //     if (!NhapHang::find($request->so_to_khai_nhap)) {
    //         session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
    //         return redirect()->back();
    //     };

    //     if ($theoDoiTruLui->cong_viec == 1) {
    //         return Excel::download(new BaoCaoTheoDoiTruLuiTheoNgayExport($theoDoiTruLui->so_to_khai_nhap, $theoDoiTruLui->ngay_them), $fileName);
    //     } else {
    //         return Excel::download(new BaoCaoTheoDoiTruLuiExport($theoDoiTruLui->cong_viec, $theoDoiTruLui->ma_yeu_cau, $request->so_to_khai_nhap), $fileName);
    //     }
    // }

    public function phieuXuatTheoXuong(Request $request)
    {
        $ten_doanh_nghiep = DoanhNghiep::find($request->ma_doanh_nghiep)->ten_doanh_nghiep;
        $systemDate = $this->formatDateToYMD($request->tu_ngay);
        $fileName = 'Phiếu đăng ký kế hoạch xuất nhập khẩu theo xuồng ' . $ten_doanh_nghiep . '.xlsx';
        return Excel::download(new BaoCaoPhieuXuatTheoXuong($request->ma_doanh_nghiep, $request->so_ptvt_xuat_canh, $systemDate), $fileName);
    }
    public function hangTonDoanhNghiep(Request $request)
    {
        $date = $this->formatDateNow();
        $ma_doanh_nghiep = $request->get('ma_doanh_nghiep');
        $ten_doanh_nghiep = DoanhNghiep::find($ma_doanh_nghiep)->ten_doanh_nghiep;
        $fileName = 'Báo cáo hàng tồn của doanh nghiệp ' . $ten_doanh_nghiep . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTonDoanhNghiepExport($ma_doanh_nghiep, $ten_doanh_nghiep), $fileName);
    }
    public function baoCaoDangKyXuatKhauHangHoa(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $ma_doanh_nghiep = $request->get('ma_doanh_nghiep');
        $ten_doanh_nghiep = DoanhNghiep::find($ma_doanh_nghiep)->ten_doanh_nghiep;
        $fileName = 'Báo cáo tổng hợp đăng ký làm thủ tục xuất khẩu hàng hóa doanh nghiệp ' . $ten_doanh_nghiep . ' ngày ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoDangKyXuatKhauHangHoa($ma_doanh_nghiep, $request->tu_ngay), $fileName);
    }
    public function baoCaoDangKyXuatKhauHangHoa2(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $ma_doanh_nghiep = $request->get('ma_doanh_nghiep');
        $ten_doanh_nghiep = DoanhNghiep::find($ma_doanh_nghiep)->ten_doanh_nghiep;
        $fileName = 'Báo cáo tổng hợp đăng ký làm thủ tục (Sang cont, cẩu cont, kiểm tra hàng) hàng hóa doanh nghiệp ' . $ten_doanh_nghiep . ' ngày ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoDangKyXuatKhauHangHoa2($ma_doanh_nghiep, $request->tu_ngay), $fileName);
    }
    public function hangTonChuHang(Request $request)
    {
        $date = $this->formatDateNow();
        $ma_chu_hang = $request->get('ma_chu_hang');
        $ten_chu_hang = $request->get('ten_chu_hang');
        $fileName = 'Báo cáo hàng tồn của đại lý ' . $ten_chu_hang . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTonChuHangExport($ma_chu_hang, $ten_chu_hang), $fileName);
    }
    public function hangTonTheoToKhai(Request $request)
    {
        $date = $this->formatDateNow();
        $so_to_khai_nhap = $request->get('so_to_khai_nhap');
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        if (!$nhapHang) {
            session()->flash('alert-danger', 'Số tờ khai nhập không tồn tại!');
            return redirect()->back();
        }
        $fileName = 'Báo cáo hàng tồn theo tờ khai ' . $so_to_khai_nhap . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoHangTonTheoToKhaiExport($so_to_khai_nhap), $fileName);
    }

    public function theoDoiHangHoa(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo theo dõi hàng hóa xuất nhập khẩu ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTheoDoiHangHoa($request->ma_hang), $fileName);
    }
    public function theoDoiHangHoaTong(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo theo dõi hàng hóa tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTheoDoiHangHoaTong($request->so_to_khai_nhap), $fileName);
    }

    public function tiepNhanHangNgay(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);

        $fileName = 'Báo cáo tiếp nhận hằng ngày từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoTiepNhanHangNgayExport($tu_ngay, $den_ngay), $fileName);
    }
    public function chiTietXNKTrongNgay(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo chi tiết xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoChiTietXNKTrongNgay($tu_ngay, $den_ngay), $fileName);
    }
    public function sangContChuyenTau(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo thống kê hàng hóa sang cont, chuyển tàu, kiểm tra hàng từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSangContChuyenTau($tu_ngay, $den_ngay, $request->ma_cong_chuc), $fileName);
    }
    public function giamSatXuatKhau(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo giám sát hàng hóa xuất khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoGiamSatXuatKhau($tu_ngay, $den_ngay, $request->ma_cong_chuc), $fileName);
    }
    public function suDungSeal(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo sử dụng seal niêm phong từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSuDungSeal($tu_ngay, $den_ngay), $fileName);
    }
    public function suDungSealChiTiet(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo sử dụng seal niêm phong chi tiết từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSuDungSealChiTiet($tu_ngay, $den_ngay, $request->ma_cong_chuc), $fileName);
    }
    public function baoCaoTheoDoiXNC(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Theo dõi phương tiện xuất nhập cảnh tại khu vực đầu tán từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new TheoDoiXuatNhapCanh($tu_ngay, $den_ngay), $fileName);
    }

    public function hangHoaChuaThucXuat(Request $request)
    {
        $fileName = 'Theo dõi hàng hóa quá 15 ngày chưa thực xuất ' . $this->formatDateNow() . '.xlsx';
        return Excel::download(new BaoCaoHangHoaChuaThucXuat(), $fileName);
    }
    public function soLuongToKhaiXuatHet(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo số lượng tờ khai xuất hết từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSoLuongToKhaiXuat($tu_ngay, $den_ngay), $fileName);
    }
    public function toKhaiXuatHetDoanhNghiep(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo số lượng tờ khai xuất hết từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoToKhaiXuatHetDoanhNghiep($tu_ngay, $den_ngay, $request->ma_doanh_nghiep), $fileName);
    }
    public function containerLuuTaiCang(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo số lượng container lưu tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoContainerLuuTaiCang(), $fileName);
    }
    public function tauLuuTaiCang(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo số lượng hàng tàu ' . $request->phuong_tien_vt_nhap . ' tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTauLuuTaiCang($request->phuong_tien_vt_nhap), $fileName);
    }
    public function containerLuuTaiCangTheoCont(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo số lượng container lưu tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoContainerLuuTaiCangTheoCont($request->so_container), $fileName);
    }
    public function doanhNghiepXNK(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo doanh nghiệp xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoDoanhNghiepXNK($tu_ngay, $den_ngay), $fileName);
    }

    public function chuyenCuaKhauXuat(Request $request)
    {
        $date = $this->formatDateNow();
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo hàng chuyển cửa khẩu xuất (Quay về kho) từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoChuyenCuaKhauXuat($tu_ngay, $den_ngay), $fileName);
    }
    public function baoCaoTieuHuy(Request $request)
    {
        $date = $this->formatDateNow();
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo hàng tiêu hủy từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoHangTieuHuy($tu_ngay, $den_ngay), $fileName);
    }
    public function hangTonTaiCang()
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo hàng tồn tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoHangTonTaiCang(), $fileName);
    }
    public function thoiGianToKhaiLuuTaiCang()
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo thời gian tờ khai tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoThoiGianToKhai(), $fileName);
    }



    public function chiTietXNKTheoDN(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo chi tiết xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoChiTietXNKTheoDN($tu_ngay, $den_ngay, $request->ma_doanh_nghiep), $fileName);
    }

    public function doanhNghiepXNKTheoDN(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo doanh nghiệp xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';

        return Excel::download(new BaoCaoDoanhNghiepXNKTheoDN($tu_ngay, $den_ngay, $request->ma_doanh_nghiep), $fileName);
    }

    public function phieuXuatTheoDoanhNghiep(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo phiếu xuất của doanh nghiệp từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoPhieuXuatDoanhNghiep($request->ma_doanh_nghiep, $tu_ngay, $den_ngay), $fileName);
    }
    public function baoCaoCapHai(Request $request)
    {
        $ngay_name = Carbon::createFromFormat('d/m/Y', $request->ngay)->format('d-m-Y');
        $tu_ngay = Carbon::createFromFormat('d/m/Y', $request->ngay)->format('Y-m-d');
        $fileName = 'Báo cáo cấp 2 ngày ' . $ngay_name . '.xlsx';
        return Excel::download(new BaoCaoCapHai($request->ma_doanh_nghiep, $tu_ngay), $fileName);
    }
    public function baoCaoPhuongTienNhapCanh(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo phương tiện nhập cảnh từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoPhuongTienNhapCanh($tu_ngay, $den_ngay), $fileName);
    }
    public function baoCaoPhuongTienXuatCanh(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo phương tiện xuất cảnh từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoPhuongTienXuatCanh($tu_ngay, $den_ngay), $fileName);
    }
    public function baoCaoPhuongTienXuatCanhSuaHuy(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo phương tiện xuất cảnh đã sửa, hủy từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoPhuongTienXuatCanhSuaHuy($tu_ngay, $den_ngay), $fileName);
    }
    public function bangKeCongViec(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $fileName = 'Bảng kê công việc ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new BangKeCongViec($tu_ngay, $request->ma_cong_chuc), $fileName);
    }

    public function phanCongNhiemVuGiamSat(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $fileName = 'Báo cáo phân công nhiệm vụ giám sát ngày ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new PhanCongNhiemVuGiamSat($tu_ngay), $fileName);
    }
    public function baoCaoTraCuuContainer(Request $request)
    {
        $fileName = 'Báo cáo tra cứu container.xlsx';
        return Excel::download(new BaoCaoTraCuuContainer(), $fileName);
    }








    private function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail();
    }

    private function formatDateToYMD($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
    }
    private function formatDateToDMY($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('d-m-Y');
    }
    private function formatDateNow()
    {
        return Carbon::now()->format('d-m-Y');
    }

    public function getHangHoa($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::with('hangHoa')->find($so_to_khai_nhap);
        if (!$nhapHang) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($nhapHang->hangHoa);
    }



    public function getTruLuis($cong_viec, $ma_doanh_nghiep, $date, $so_to_khai_nhap = null)
    {
        if ($cong_viec == 10) {
            return TheoDoiTruLui::join('nhap_hang', 'nhap_hang.so_to_khai_nhap', '=', 'theo_doi_tru_lui.so_to_khai_nhap')
                ->where('nhap_hang.ma_doanh_nghiep', $ma_doanh_nghiep)
                ->whereDate('theo_doi_tru_lui.ngay_them', $date)
                ->when(!is_null($so_to_khai_nhap), function ($query) use ($so_to_khai_nhap) {
                    return $query->where('theo_doi_tru_lui.so_to_khai_nhap', $so_to_khai_nhap);
                })
                ->get()
                ->unique(function ($item) {
                    return $item->so_to_khai_nhap;
                })
                ->map(function ($item) {
                    $item->cong_viec = 10; // Set temporarily
                    return $item;
                })
                ->values();
        } else {
            $records = TheoDoiTruLui::leftJoin('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
                ->leftJoin('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'theo_doi_tru_lui.ma_yeu_cau')
                ->join('nhap_hang', 'nhap_hang.so_to_khai_nhap', '=', 'theo_doi_tru_lui.so_to_khai_nhap')
                ->where('nhap_hang.ma_doanh_nghiep', $ma_doanh_nghiep)
                ->whereDate('theo_doi_tru_lui.ngay_them', $date)
                ->when(!is_null($so_to_khai_nhap), function ($query) use ($so_to_khai_nhap) {
                    return $query->where('theo_doi_tru_lui.so_to_khai_nhap', operator: $so_to_khai_nhap);
                })
                ->when($cong_viec != 0, function ($query) use ($cong_viec) {
                    return $query->where('theo_doi_tru_lui.cong_viec', $cong_viec);
                })
                ->select(
                    'theo_doi_tru_lui.*',
                    'theo_doi_tru_lui_chi_tiet.ma_chi_tiet',
                    'xuat_hang.trang_thai',
                    'nhap_hang.so_to_khai_nhap as so_to_khai_nhap'
                )
                ->get()
                ->filter(function ($item) {
                    if ($item->cong_viec != 1) {
                        return !is_null($item->ma_chi_tiet); // has chi tiet
                    } else {
                        return $item->trang_thai != 0; // xuat_hang.trang_thai != 0
                    }
                })
                ->unique(function ($item) {
                    return $item->so_to_khai_nhap . '-' . $item->cong_viec;
                })
                ->values();
            return $records;
        }
    }
    public function getSoToKhaiTruLui(Request $request)
    {
        $ngay_tru_lui = $this->formatDateToYMD($request->ngay_tru_lui);
        $theoDois = $this->getTruLuis($request->cong_viec, $this->getDoanhNghiepHienTai()->ma_doanh_nghiep, $ngay_tru_lui);
        $theoDois = $theoDois->map(function ($item) {
            if ($item->cong_viec == "1") {
                $item->ten_cong_viec = "Xuất hàng";
            } elseif ($item->cong_viec == "2") {
                $item->ten_cong_viec = "Chuyển container và tàu";
            } elseif ($item->cong_viec == "3") {
                $item->ten_cong_viec = "Chuyển container";
            } elseif ($item->cong_viec == "4") {
                $item->ten_cong_viec = "Chuyển tàu";
            } elseif ($item->cong_viec == "5") {
                $item->ten_cong_viec = "Đưa hàng trở lại kho ban đầu";
            } elseif ($item->cong_viec == "6") {
                $item->ten_cong_viec = "Tiêu hủy hàng";
            } elseif ($item->cong_viec == "7") {
                $item->ten_cong_viec = "Kiểm tra hàng";
            } elseif ($item->cong_viec == "9") {
                $item->ten_cong_viec = "Gỡ seal điện tử";
            } elseif ($item->cong_viec == "10") {
                $item->ten_cong_viec = "Tất cả";
            }
            return $item;
        });
        return response()->json($theoDois);
    }


    public function getLanTruLui($so_to_khai_nhap)
    {
        $theoDoiTruLuis = TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->leftJoin('xuat_hang', function ($join) {
                $join->on('xuat_hang.so_to_khai_xuat', '=', 'theo_doi_tru_lui.ma_yeu_cau')
                    ->where('theo_doi_tru_lui.cong_viec', 1);
            })
            ->where(function ($query) {
                $query->where('theo_doi_tru_lui.cong_viec', '!=', 1)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('theo_doi_tru_lui.cong_viec', 1)
                            ->where('xuat_hang.trang_thai', '!=', 0);
                    });
            })
            ->when(request('cong_viec') == 1, function ($query) {
                return $query->where('theo_doi_tru_lui.cong_viec', 1);
            })
            ->select('theo_doi_tru_lui.*')
            ->get()
            ->groupBy(function ($item) {
                return $item->cong_viec == 1 ? $item->ngay_them : $item->ma_yeu_cau;
            })
            ->map(function ($group) {
                return $group->first();
            })
            ->values()
            ->sortByDesc('ma_theo_doi')
            ->values();

        if (!$theoDoiTruLuis) {
            return response()->json(['error' => 'Not found'], 404);
        }
        foreach ($theoDoiTruLuis as $theoDoiTruLui) {
            if ($theoDoiTruLui->cong_viec == 1) {
                $theoDoiTruLui->cong_viec = "Xuất hàng";
            } else if ($theoDoiTruLui->cong_viec == 2) {
                $theoDoiTruLui->cong_viec = "Chuyển container và tàu";
            } else if ($theoDoiTruLui->cong_viec == 3) {
                $theoDoiTruLui->cong_viec = "Chuyển container";
            } else if ($theoDoiTruLui->cong_viec == 4) {
                $theoDoiTruLui->cong_viec = "Chuyển tàu";
            } else if ($theoDoiTruLui->cong_viec == 5) {
                $theoDoiTruLui->cong_viec = "Đưa hàng trở lại kho ban đầu";
            } else if ($theoDoiTruLui->cong_viec == 6) {
                $theoDoiTruLui->cong_viec = "Tiêu hủy hàng";
            } else if ($theoDoiTruLui->cong_viec == 7) {
                $theoDoiTruLui->cong_viec = "Kiểm tra hàng";
            } else if ($theoDoiTruLui->cong_viec == 9) {
                $theoDoiTruLui->cong_viec = "Gỡ seal điện tử";
            }

            $theoDoiTruLui->ngay_them = Carbon::parse($theoDoiTruLui->ngay_them)->format('d-m-Y');
        }
        return response()->json($theoDoiTruLuis);
    }
}
