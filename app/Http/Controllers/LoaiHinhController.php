<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Seal;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangHoaDaHuy;
use App\Models\HangTrongCont;
use Illuminate\Http\Request;
use App\Models\LoaiHinh;
use App\Models\NhapHang;
use App\Models\NhapHangDaHuy;
use App\Models\PTVTXuatCanh;
use App\Models\NhapHangSua;
use App\Models\NiemPhong;
use App\Models\Container;
use App\Models\PhanQuyenBaoCao;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\TaiKhoan;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\XuatCanh;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\YeuCauGoSeal;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauHangVeKhoChiTiet;
use App\Models\YeuCauContainerHangHoa;
use App\Models\YeuCauGiaHan;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTauContChiTiet;
use App\Models\TienTrinh;
use App\Models\XuatNhapCanh;
use App\Models\YeuCauTieuHuy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yaza\LaravelGoogleDriveStorage\Gdrive;

class LoaiHinhController extends Controller
{
    public function danhSachLoaiHinh()
    {
        $data = LoaiHinh::all();
        return view('quan-ly-khac.danh-sach-loai-hinh', data: compact(var_name: 'data'));
    }
    public function ttest()
    {
        return view('quan-ly-khac.test');
    }

    public function themLoaiHinh(Request $request)
    {
        if (LoaiHinh::find($request->ma_loai_hinh)) {
            session()->flash('alert-danger', 'Mã loại hình này đã tồn tại.');
            return redirect()->back();
        }
        LoaiHinh::create([
            'ma_loai_hinh' => $request->ma_loai_hinh,
            'ten_loai_hinh' => $request->ten_loai_hinh,
            'loai' => $request->loai,
        ]);
        session()->flash('alert-success', 'Thêm loại hình mới thành công');
        return redirect()->back();
    }


    public function xoaLoaiHinh(Request $request)
    {
        LoaiHinh::find($request->ma_loai_hinh)->delete();
        session()->flash('alert-success', 'Xóa loại hình thành công');
        return redirect()->back();
    }
    public function action1(Request $request)
    {
        $this->checkLechSoLuong($request);
    }

    public function action2(Request $request)
    {
        $this->khoiPhucXuatHang2($request->so_to_khai_xuat, $request->trang_thai);
    }
    public function action3(Request $request)
    {
        // $this->checkLechTau();
        // $yeuCau = YeuCauGoSeal::find(378);
        // $this->themGoSeal('GLDU0889990', $yeuCau, '2025-09-29');
        $this->checkLechTau();
    }

    public function action4(Request $request)
    {
        $this->normalizeContainer();
    }

    public function action5(Request $request)
    {
        $stt = $this->xuatHet();
        $this->fixNgayXuatHet();
        $this->fixCCXuatHet();
        dd($stt);
    }
    public function action6(Request $request)
    {
        $hangTrongConts = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $request->so_to_khai_nhap)
            ->get();
        foreach ($hangTrongConts as $hangTrongCont) {
            $so_luong_xuat = XuatHang::join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                ->where('xuat_hang_cont.ma_hang_cont', $hangTrongCont->ma_hang_cont)
                ->where('xuat_hang.trang_thai', '!=', 0)
                ->sum('xuat_hang_cont.so_luong_xuat');
            $so_luong_hien_tai = $hangTrongCont->so_luong_khai_bao - $so_luong_xuat;
            HangTrongCont::find($hangTrongCont->ma_hang_cont)->update([
                'so_luong' => $so_luong_hien_tai,
                'is_da_chuyen_cont' => 0
            ]);
        }
    }
    public function action7(Request $request)
    {
        $this->uploadExcel($request);
    }
    public function uploadExcel(Request $request)
    {
        try {
            $file = $request->file('excel_file');
            $extension = $file->getClientOriginalExtension();
            $list = [];
            if ($extension === 'csv') {
                $csvData = array_map('str_getcsv', file($file->getRealPath()));
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $csvData = [];

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowData = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $rowData[] = $cell->getFormattedValue();
                    }
                    $csvData[] = $rowData;
                }
            } else {
                return response("Không hỗ trợ định dạng file này, hệ thống chỉ hỗ trợ định dạng .xls, .xlsx và .csv");
            }

            // Find header row by looking for "Tên tàu" and "Ngày nhập cảnh"
            $headerRowIndex = -1;

            foreach ($csvData as $index => $row) {
                if (empty($row) || count($row) < 2) {
                    continue;
                }

                $normalizedRow = array_map(function ($val) {
                    return mb_strtolower(trim($val ?? ''));
                }, $row);

                $hasTenTau = false;
                $hasNgayNhap = false;

                foreach ($normalizedRow as $col) {
                    if (!is_string($col) || empty($col)) continue;

                    // Match "tên tàu"
                    if (str_contains($col, 'tên') && str_contains($col, 'tàu')) {
                        $hasTenTau = true;
                    }
                    // Match "ngày nhập cảnh"
                    if (str_contains($col, 'ngày') && str_contains($col, 'nhập') && str_contains($col, 'cảnh')) {
                        $hasNgayNhap = true;
                    }
                }

                if ($hasTenTau && $hasNgayNhap) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy dòng tiêu đề với các cột: Tên tàu, Ngày nhập cảnh'
                ], 400);
            }

            $header = array_map(function ($val) {
                return mb_strtolower(trim($val ?? ''));
            }, $csvData[$headerRowIndex]);

            // Map column indices
            $columnIndices = [
                'ten_tau' => -1,
                'ngay_nhap' => -1
            ];

            foreach ($header as $colIndex => $colName) {
                // Match "tên tàu"
                if (str_contains($colName, 'tên') && str_contains($colName, 'tàu')) {
                    $columnIndices['ten_tau'] = $colIndex;
                }
                // Match "ngày nhập cảnh"
                if (str_contains($colName, 'ngày') && str_contains($colName, 'nhập') && str_contains($colName, 'cảnh')) {
                    $columnIndices['ngay_nhap'] = $colIndex;
                }
            }

            if ($columnIndices['ten_tau'] === -1 || $columnIndices['ngay_nhap'] === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cột "Tên tàu" hoặc "Ngày nhập cảnh"'
                ], 400);
            }

            // Initialize counters
            $insertedCount = 0;
            $skippedCount = 0;
            $errors = [];
            $processedRows = []; // Track rows to insert

            // First pass: collect all valid rows from the file
            foreach (array_slice($csvData, $headerRowIndex + 1) as $rowIndex => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $tenTau = trim($row[$columnIndices['ten_tau']] ?? '');
                $ngayNhap = trim($row[$columnIndices['ngay_nhap']] ?? '');

                // Skip if both are empty
                if (empty($tenTau) || empty($ngayNhap)) {
                    continue;
                }

                // Parse date
                try {
                    if (is_numeric($ngayNhap)) {
                        // Excel numeric date
                        $ngayNhapObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($ngayNhap);
                    } else {
                        // Handle dates in format "01-05-2025" or "01/05/2025"
                        if (strpos($ngayNhap, '-') !== false) {
                            $ngayNhapObj = Carbon::createFromFormat('d-m-Y', $ngayNhap);
                        } elseif (strpos($ngayNhap, '/') !== false) {
                            $ngayNhapObj = Carbon::createFromFormat('d/m/Y', $ngayNhap);
                        } else {
                            $ngayNhapObj = Carbon::parse($ngayNhap);
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Dòng " . ($headerRowIndex + $rowIndex + 2) . ": Định dạng ngày không hợp lệ '{$ngayNhap}'";
                    $skippedCount++;
                    continue;
                }

                // Find the vehicle record
                $ptvt = PTVTXuatCanh::where('ten_phuong_tien_vt', $tenTau)->first();

                if (!$ptvt) {
                    $errors[] = "Dòng " . ($headerRowIndex + $rowIndex + 2) . ": Không tìm thấy tàu '{$tenTau}' trong cơ sở dữ liệu";
                    $skippedCount++;
                    continue;
                }

                // Add to processed rows
                $processedRows[] = [
                    'so_ptvt_xuat_canh' => $ptvt->so_ptvt_xuat_canh,
                    'ngay_them' => $ngayNhapObj->format('Y-m-d'),
                    'ten_tau' => $tenTau
                ];
            }

            // Second pass: insert only rows that don't exist in database
            foreach ($processedRows as $rowData) {
                // Check if this exact record already exists in database
                $exists = XuatNhapCanh::where('so_ptvt_xuat_canh', $rowData['so_ptvt_xuat_canh'])
                    ->whereDate('ngay_them', $rowData['ngay_them'])
                    ->exists();

                if (!$exists) {
                    XuatNhapCanh::create([
                        'so_ptvt_xuat_canh' => $rowData['so_ptvt_xuat_canh'],
                        'ngay_them' => $rowData['ngay_them'],
                    ]);
                    $list[] = $rowData;
                    $insertedCount++;
                } else {
                    $skippedCount++;
                }
            }
            dd($list);
            return response()->json([
                'success' => true,
                'message' => "Import hoàn tất thành công",
                'inserted' => $insertedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý file: ' . $e->getMessage()
            ], 500);
        }
    }
    public function normalizeContainer()
    {
        $nhapHangs = NhapHang::where('trang_thai', 2)->get();
        $so_to_khai_nhaps = $nhapHangs->pluck('so_to_khai_nhap');
        $hangHoas = HangHoa::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        $hang_trong_conts = HangTrongCont::whereIn('ma_hang', $hangHoas->pluck('ma_hang'))->get();

        $niem_phongs = NiemPhong::whereIn('so_container', $hang_trong_conts->pluck('so_container'))->get();
        foreach ($niem_phongs as $niem_phong) {
            // $normalized_container = preg_replace('/\s+/', '', $niem_phong->so_container);
            // $normalized_phuong_tien = str_replace('-', '', $niem_phong->phuong_tien_vt_nhap);
            $normalized_phuong_tien = preg_replace('/\s+/', '', $niem_phong->phuong_tien_vt_nhap);
            $niem_phong->update([
                'phuong_tien_vt_nhap' => $normalized_phuong_tien,
            ]);

            // NiemPhong::where('so_container', $normalized_container)
            //     ->where('ma_niem_phong', '!=', $niem_phong->ma_niem_phong)
            //     ->delete();
        }

        // $containers = Container::all();
        // foreach ($containers as $container) {
        //     $newSoContainer = preg_replace('/\s+/', '', $container->so_container);
        //     if ($newSoContainer === $container->so_container) {
        //         continue;
        //     }
        //     $exists = Container::where('so_container', $newSoContainer)->exists();
        //     if ($exists) {
        //         continue;
        //     }
        //     Container::where('so_container', $container->so_container)
        //         ->update(['so_container' => $newSoContainer]);
        // }

        // $theo_doi_hang_hoas = TheoDoiHangHoa::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        // foreach ($theo_doi_hang_hoas as $theo_doi_hang_hoa) {
        //     $theo_doi_hang_hoa->update([
        //         'so_container' => preg_replace('/\s+/', '', $theo_doi_hang_hoa->so_container),
        //     ]);
        // }

        $theo_doi_tru_lui_chi_tiets = TheoDoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui.ma_theo_doi', '=', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi')
            ->whereIn('theo_doi_tru_lui.so_to_khai_nhap', $so_to_khai_nhaps)
            ->get();

        foreach ($theo_doi_tru_lui_chi_tiets as $theo_doi_tru_lui_chi_tiet) {
            $theo_doi_tru_lui_chi_tiet->update([
                // 'so_container' => preg_replace('/\s+/', '', $theo_doi_tru_lui_chi_tiet->so_container),
                // 'phuong_tien_vt_nhap' => str_replace('-', '', $theo_doi_tru_lui_chi_tiet->phuong_tien_vt_nhap),
                'phuong_tien_vt_nhap' => preg_replace('/\s+/', '', $theo_doi_tru_lui_chi_tiet->phuong_tien_vt_nhap),
            ]);
        }

        $xuat_hang_conts = XuatHangCont::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        foreach ($xuat_hang_conts as $xuat_hang_cont) {
            $xuat_hang_cont->update([
                // 'so_container' => preg_replace('/\s+/', '', $xuat_hang_cont->so_container),
                // 'phuong_tien_vt_nhap' => str_replace('-', '', $xuat_hang_cont->phuong_tien_vt_nhap),
                'phuong_tien_vt_nhap' => preg_replace('/\s+/', '', $xuat_hang_cont->phuong_tien_vt_nhap),
            ]);
        }



        foreach ($nhapHangs as $nhapHang) {
            $nhapHang->update([
                // 'container_ban_dau' => preg_replace('/\s+/', '', $nhapHang->container_ban_dau),
                // 'phuong_tien_vt_nhap' => str_replace('-', '', $nhapHang->phuong_tien_vt_nhap),
                // 'ptvt_ban_dau' => str_replace('-', '', $nhapHang->ptvt_ban_dau),
                'phuong_tien_vt_nhap' => preg_replace('/\s+/', '', $nhapHang->phuong_tien_vt_nhap),
                'ptvt_ban_dau' => preg_replace('/\s+/', '', $nhapHang->ptvt_ban_dau),
            ]);
        }
        // foreach ($hangHoas as $hangHoa) {
        //     $hangHoa->update([
        //         'so_container_khai_bao' => preg_replace('/\s+/', '', $hangHoa->so_container_khai_bao),
        //     ]);
        // }
        // foreach ($hang_trong_conts as $hang_trong_cont) {
        //     $hang_trong_cont->update([
        //         'so_container' => preg_replace('/\s+/', '', $hang_trong_cont->so_container),
        //     ]);
        // }

    }
    public function checkLechTau()
    {
        $so_to_khai_nhaps = [];
        $nhapHangs = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->join('niem_phong', 'niem_phong.so_container', '=', 'hang_trong_cont.so_container')
            ->where('hang_trong_cont.so_luong', '!=', 0)
            ->where('trang_thai', 2)
            ->where('nhap_hang.ngay_thong_quan', '>', '2025-07-01')
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->select('nhap_hang.phuong_tien_vt_nhap as phuong_tien_vt_nhap_1', 'niem_phong.phuong_tien_vt_nhap as phuong_tien_vt_nhap_2', 'nhap_hang.so_to_khai_nhap')
            ->get();
        foreach ($nhapHangs as $nhapHang) {
            if ($nhapHang->phuong_tien_vt_nhap_1 != $nhapHang->phuong_tien_vt_nhap_2) {
                $so_to_khai_nhaps[] = $nhapHang->so_to_khai_nhap;
            }
        }
        dd($so_to_khai_nhaps);
    }
    public function fillTiepNhan()
    {
        $nhapHangs = NhapHang::where('nhap_hang.ngay_tiep_nhan', null)
            ->get();

        foreach ($nhapHangs as $nhapHang) {
            $nhapHang->update(['ngay_tiep_nhan' => $nhapHang->ngay_thong_quan]);
        }
    }
    public function xoaTheoDoiHang(Request $request)
    {
        // $this->fixPhanQuyenBaoCao();
        // $this->kiemTraDungXuatHet();
        // $this->fixKiemTra();
        // $this->fixSoContKhaiBao();
        // $this->sealXuyenNgay();


        // $nhapHangs = NhapHang::where('ngay_xuat_het', '>', '2025-07-01')->get();
        // foreach ($nhapHangs as $nhapHang) {
        //     $so_to_khai_nhap = $nhapHang->so_to_khai_nhap;
        //     $xuatHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
        //         ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
        //         ->whereIn('xuat_hang.trang_thai', [2, 12, 13])
        //         ->orderBy('xuat_hang.updated_at', 'desc')
        //         ->first();

        //     NhapHang::find($so_to_khai_nhap)
        //         ->update([
        //             'ma_cong_chuc_ban_giao' => $xuatHang->ma_cong_chuc ?? '',
        //         ]);
        // }


        // $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->where('ngay_yeu_cau', today())
        //     ->get();

        // foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        //     $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_container); // Remove spaces
        //     $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        //     XuatHang::where(function ($query) {
        //         if (now()->hour < 9) {
        //             $query->whereDate('ngay_dang_ky', today())
        //                 ->orWhereDate('ngay_dang_ky', today()->subDay());
        //         } else {
        //             $query->whereDate('ngay_dang_ky', today());
        //         }
        //     })
        //         ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
        //         ->whereIn('xuat_hang_cont.so_container',  [$so_container_no_space, $so_container_with_space])
        //         ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $chiTietYeuCau->so_seal_moi]);
        // }

        // $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->where('yeu_cau_niem_phong.ma_yeu_cau', 4117)
        //     ->get();

        // foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        //     $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_container); // Remove spaces
        //     $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        //     XuatHang::where(function ($query) {
        //         $query->whereDate('ngay_dang_ky', today())
        //             ->orWhereDate('ngay_dang_ky', today()->subDay());
        //     })
        //         ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
        //         ->whereIn('xuat_hang_cont.so_container',  [$so_container_no_space, $so_container_with_space])
        //         ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $chiTietYeuCau->so_seal_moi]);
        // }




        // $yeuCaus = YeuCauGoSeal::join('yeu_cau_go_seal_chi_tiet', 'yeu_cau_go_seal.ma_yeu_cau', '=', 'yeu_cau_go_seal_chi_tiet.ma_yeu_cau')
        //     ->where('yeu_cau_go_seal.is_niem_phong', 1)
        //     ->where('yeu_cau_go_seal.trang_thai', 2)
        //     ->where('yeu_cau_go_seal.ma_yeu_cau', '>', 3)
        //     ->where('yeu_cau_go_seal.ma_yeu_cau', '<', 29)
        //     ->get();

        // foreach ($yeuCaus as $yeuCau) {
        //     TheoDoiHangHoa::where('so_container', $yeuCau->so_container)
        //         ->where('cong_viec', 9)
        //         ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
        //         ->update([
        //             'so_seal' => $yeuCau->so_seal_moi,
        //         ]);
        //     TheodoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
        //         ->where('theo_doi_tru_lui_chi_tiet.so_container', $yeuCau->so_container)
        //         ->where('theo_doi_tru_lui.cong_viec', 9)
        //         ->where('theo_doi_tru_lui.ma_yeu_cau', $yeuCau->ma_yeu_cau)
        //         ->update([
        //             'so_seal' => $yeuCau->so_seal_moi,
        //         ]);
        // }

        // $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->whereDate('ngay_yeu_cau', today()->subDay())
        //     ->get();

        // foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        //     $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_container); // Remove spaces
        //     $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        //     TheoDoiHangHoa::whereIn('so_container', [$so_container_no_space, $so_container_with_space])
        //         ->where('cong_viec', 9)
        //         ->update([
        //             'so_seal' => $chiTietYeuCau->so_seal_moi,
        //         ]);
        //     TheodoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
        //         ->whereIn('so_container', [$so_container_no_space, $so_container_with_space])
        //         ->where('theo_doi_tru_lui.cong_viec', 9)
        //         ->update([
        //             'so_seal' => $chiTietYeuCau->so_seal_moi,
        //         ]);
        // }

        // $yeuCaus = YeuCauGoSeal::join('yeu_cau_go_seal_chi_tiet', 'yeu_cau_go_seal.ma_yeu_cau', '=', 'yeu_cau_go_seal_chi_tiet.ma_yeu_cau')
        //     ->where('yeu_cau_go_seal.trang_thai', 2)
        //     ->where('yeu_cau_go_seal.ma_yeu_cau', '>', 3)
        //     ->where('yeu_cau_go_seal.ma_yeu_cau', '<', 28)
        //     ->get();

        // foreach ($yeuCaus as $yeuCau) {
        //     $this->themGoSeal($yeuCau->so_container, $yeuCau,$yeuCau->ngay_yeu_cau);
        // }

        // $yeuCau = YeuCauGoSeal::find(161);
        // $thoi_gian = Carbon::parse($yeuCau->ngay_yeu_cau);
        // $now = Carbon::now();
        // $thoi_gian->setTime($now->hour, $now->minute, $now->second);
        // $this->themGoSeal('WHLU5667358', $yeuCau, $thoi_gian);


        // $this->fixYeuCauTau();
        // $this->capNhatSealTruLui();


        // $nps = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->where('yeu_cau_niem_phong.ma_yeu_cau', 547)
        //     ->get();
        // foreach($nps as $np){
        //     $this->capNhatSealXuatHang($np->so_container, $np->so_seal_moi);
        // }


        // $this->fixTauTrenCont();
        // $this->fixNiemPhong();

        // $this->khoiPhucNhapHang();

        // $this->fixContainer();
        // $this->checkDupsNiemPhong();
        // $this->fixTauTheoDoiTruLui();
        // $this->fixTheoDoi();
        return redirect()->back();
    }
    public function kiemTraDungXuatHet()
    {
        $list = [];
        $nhapHangs = NhapHang::where('trang_thai', 4)->where('ngay_xuat_het', '>', '2025-07-01')->get();
        foreach ($nhapHangs as $nhapHang) {
            $xuatHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->whereNotIn('xuat_hang.trang_thai', [0, 1, 7, 8, 9, 10])
                ->orderBy('xuat_hang.created_at', 'desc')
                ->first();
            if ($xuatHang && $xuatHang->ma_cong_chuc != null) {
                if ($xuatHang->ma_cong_chuc != $nhapHang->ma_cong_chuc_ban_giao) {
                    $list[] = $nhapHang->so_to_khai_nhap;
                    // $nhapHang->update([
                    //     'ma_cong_chuc_ban_giao' => $xuatHang->ma_cong_chuc,
                    //     'ngay_xuat_het' => $xuatHang->ngay_dang_ky,
                    // ]);
                }
            }
        }
        dd($list);
    }
    public function kiemTraDungXuatHet2()
    {
        $list = [];
        $nhapHangs = NhapHang::where('trang_thai', 4)->where('ngay_xuat_het', '>', '2025-07-01')->get();
        foreach ($nhapHangs as $nhapHang) {
            $xuatHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->whereNotIn('xuat_hang.trang_thai', [0, 1, 7, 8, 9, 10])
                ->orderBy('xuat_hang.created_at', 'desc')
                ->first();
            if ($xuatHang && $xuatHang->ma_cong_chuc != null) {
                if ($xuatHang->ma_cong_chuc != $nhapHang->ma_cong_chuc_ban_giao) {
                    $list[] = $nhapHang->so_to_khai_nhap;
                    // $nhapHang->update([
                    //     'ma_cong_chuc_ban_giao' => $xuatHang->ma_cong_chuc,
                    //     'ngay_xuat_het' => $xuatHang->ngay_dang_ky,
                    // ]);
                }
            }
        }
        dd($list);
    }

    public function sealXuyenNgay()
    {
        $so_to_khai_nhaps = [];
        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            $so_seal = XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
                ->where('xuat_hang.ngay_dang_ky', '2025-07-16')
                ->where('xuat_hang_cont.so_seal_cuoi_ngay', '!=', '')
                ->select('xuat_hang_cont.so_seal_cuoi_ngay')
                ->first()
                ?->so_seal_cuoi_ngay ?? '';
            XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
                ->where('xuat_hang.ngay_dang_ky', '2025-07-17')
                ->where('xuat_hang_cont.so_seal_cuoi_ngay', '')
                ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $so_seal]);
        }
    }

    public function themGoSeal($so_container, $yeuCau, $ngay_yeu_cau)
    {
        $so_container_no_space = str_replace(' ', '', $so_container);
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        $so_to_khai_nhaps = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->whereIn('hang_trong_cont.so_container', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap', DB::raw('SUM(hang_trong_cont.so_luong) as total_so_luong'))
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()->pluck('so_to_khai_nhap')->toArray();

        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            $theoDoiTruLui = TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)->count();
            // if ($theoDoiTruLui > 1) {
            //     continue;
            // }
            $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
                ->get();

            $theoDoi = TheoDoiTruLui::create([
                'so_to_khai_nhap' => $so_to_khai_nhap,
                'so_ptvt_nuoc_ngoai' => '',
                'ngay_them' => $ngay_yeu_cau,
                'cong_viec' => 9,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
            foreach ($hangHoas as $hangHoa) {
                TheoDoiTruLuiChiTiet::insert(
                    [
                        'ten_hang' => $hangHoa->ten_hang,
                        'so_luong_xuat' => 0,
                        'so_luong_chua_xuat' => $hangHoa->so_luong,
                        'ma_theo_doi' => $theoDoi->ma_theo_doi,
                        'so_container' => $hangHoa->so_container,
                        'so_seal' => NiemPhong::where('so_container', $hangHoa->so_container)->first()->so_seal ?? '',
                        'phuong_tien_vt_nhap' => NiemPhong::where('so_container', $hangHoa->so_container)->first()->phuong_tien_vt_nhap ?? ''
                    ]
                );
                $ptvtChoHang = NiemPhong::where('so_container',  $hangHoa->so_container)->first()->phuong_tien_vt_nhap ?? '';
                TheoDoiHangHoa::insert([
                    'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                    'ma_hang'  => $hangHoa->ma_hang,
                    'thoi_gian'  => $ngay_yeu_cau,
                    'so_luong_xuat'  => $hangHoa->so_luong,
                    'so_luong_ton'  => $hangHoa->so_luong,
                    'phuong_tien_cho_hang' => $ptvtChoHang,
                    'cong_viec' => 9,
                    'phuong_tien_nhan_hang' => '',
                    'so_container' => $hangHoa->so_container,
                    'so_seal' => NiemPhong::where('so_container', $hangHoa->so_container)->first()->so_seal ?? '',
                    'ma_cong_chuc' => $yeuCau->ma_cong_chuc,
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                ]);
            }
        }
    }


    public function fixKiemTra()
    {
        $ma_yeu_cau = 511;
        $so_container = "CCLU 6643705";
        $so_tau = "ND 2338";
        YeuCauKiemTraChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->update([
            'so_container' => $so_container,
            'so_tau' => $so_tau,
        ]);
        TheoDoiHangHoa::where('ma_yeu_cau', $ma_yeu_cau)
            ->where('cong_viec', 7)
            ->update([
                'so_container' => $so_container,
                'phuong_tien_cho_hang' => $so_tau,
            ]);
        TheoDoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
            ->where('theo_doi_tru_lui.ma_yeu_cau', $ma_yeu_cau)
            ->where('theo_doi_tru_lui.cong_viec', 7)
            ->update([
                'theo_doi_tru_lui_chi_tiet.so_container' => $so_container,
                'theo_doi_tru_lui_chi_tiet.phuong_tien_vt_nhap' => $so_tau,
            ]);
    }
    public function fixYeuCauTau()
    {
        $ma_yeu_cau = 1664;
        $so_container = "PRGU 9509577";
        $so_tau = "ND 3076";
        YeuCauTauContChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->update([
            'so_container_dich' => $so_container,
            'tau_dich' => $so_tau,
        ]);
        TheoDoiHangHoa::where('ma_yeu_cau', $ma_yeu_cau)
            ->where('cong_viec', 4)
            ->update([
                'so_container' => $so_container,
                'phuong_tien_cho_hang' => $so_tau,
            ]);
        TheoDoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
            ->where('theo_doi_tru_lui.ma_yeu_cau', $ma_yeu_cau)
            ->where('theo_doi_tru_lui.cong_viec', 2)
            ->update([
                'theo_doi_tru_lui_chi_tiet.so_container' => $so_container,
                'theo_doi_tru_lui_chi_tiet.phuong_tien_vt_nhap' => $so_tau,
            ]);
    }
    public function fixTruLuiDaHuy()
    {
        $ycs = YeuCauTauCont::where('trang_thai', 0)
            ->get();
        foreach ($ycs as $yc) {
            TheoDoiTruLui::where('ma_yeu_cau', $yc->ma_yeu_cau)
                ->where('cong_viec', 2)
                ->delete();
        }
    }
    public function checkDupsNiemPhong()
    {
        $allNiemPhong = NiemPhong::all();
        $grouped = $allNiemPhong->groupBy('so_container');

        foreach ($grouped as $items) {
            // Keep the first, delete the rest
            $items->skip(1)->each(function ($item) {
                $item->delete();
            });
        }
    }
    public function kiemTraSealDung()
    {
        $ycs = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_niem_phong.ma_yeu_cau', '>=', 2400)
            ->where('yeu_cau_niem_phong.trang_thai', operator: '2')
            ->orderBy('yeu_cau_niem_phong.ma_yeu_cau', 'desc')
            ->get();
        $containerDaCheck = [];
        $container = [];
        foreach ($ycs as $yc) {
            if (in_array($yc->so_container, $containerDaCheck)) {
                continue;
            }
            $seal = NiemPhong::where('so_container', $yc->so_container)->first()->so_seal ?? '';
            if ($yc->so_seal_moi != $seal && Seal::find($seal)) {
                $container[] = $yc->so_container;
            }
            $containerDaCheck[] = $yc->so_container;
        }
        dd($container);
    }

    public function fixNiemPhong()
    {
        $ycs = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
            ->where('ngay_yeu_cau', today())
            ->get();
        $arr = [];
        foreach ($ycs as $yc) {
            if ($yc->phuong_tien_vt_nhap == '') {
                $arr[] = $yc->ma_yeu_cau;
            }
        }
        $arr = array_unique($arr);
        foreach ($arr as $ar) {
            $ycs = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $ar)->get();
            foreach ($ycs as $yc) {
                $ptvt = NiemPhong::where('so_container', $yc->so_container)->first()->phuong_tien_vt_nhap;
                $yc->phuong_tien_vt_nhap = $ptvt;
                $yc->save();
            }
        }
    }
    public function fixContainer()
    {
        $niemPhongs = NiemPhong::all();

        foreach ($niemPhongs as $niemPhong) {
            $so_container_no_space = str_replace(' ', '', $niemPhong->so_container);
            $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);
            if (!Container::whereIn('so_container', [$so_container_no_space, $so_container_with_space])->exists()) {
                Container::insert([
                    'so_container' => $so_container_with_space,
                ]);
            }
        }
    }


    public function fixTheoDoi()
    {
        $theoDoi = TheoDoiTruLui::where('cong_viec', 2)
            ->where('ma_theo_doi', '>', 49000)
            ->get();
        foreach ($theoDoi as $td) {
            $chiTiets = YeuCauTauContChiTiet::where('ma_yeu_cau', $td->ma_yeu_cau)
                ->groupBy('so_to_khai_nhap')
                ->get();
            foreach ($chiTiets as $chiTiet) {
                if ($chiTiet->so_to_khai_nhap == $td->so_to_khai_nhap) {
                    TheoDoiTruLuiChiTiet::where('ma_theo_doi', $td->ma_theo_doi)
                        ->update(['phuong_tien_vt_nhap' => $chiTiet->tau_dich]);
                }
            }
        }
    }

    public function fixPTVTXC()
    {
        PTVTXuatCanh::all()->each(function ($ptvt) {
            $ptvt->update([
                'draft_den' => $ptvt->draft_roi,
                'dwt_den' => $ptvt->dwt_roi,
                'loa_den' => $ptvt->loa_roi,
                'breadth_den' => $ptvt->breadth_roi,
                'trang_thai' => 2,
            ]);
        });
    }

    public function fixTauTrenCont()
    {
        $nhapHangs = NhapHang::where('trang_thai', 2)->get();
        foreach ($nhapHangs as $nhapHang) {
            $soContainers = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_hoa.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->pluck('so_container')
                ->unique();
            foreach ($soContainers as $soContainer) {
                NiemPhong::where('so_container', $soContainer)
                    ->where('phuong_tien_vt_nhap', operator: null)
                    ->update([
                        'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
                    ]);
            }
        }
    }
    public function fixTauTheoDoiTruLui()
    {
        $theoDoiTruLuis = TheoDoiTruLui::where('cong_viec', '!=', 1)
            ->get();
        foreach ($theoDoiTruLuis as $theoDoiTruLui) {
            TheoDoiTruLuiChiTiet::where('ma_theo_doi', $theoDoiTruLui->ma_theo_doi)->update([
                'phuong_tien_vt_nhap' => $theoDoiTruLui->phuong_tien_vt_nhap,
            ]);
        }
    }
    public function fixSuaXuatHang()
    {
        $count = 0;
        $xuatHangs = XuatHang::whereNot('trang_thai', 0)->get();
        foreach ($xuatHangs as $xuatHang) {
            $ngay_them = TheoDoiTruLui::where('cong_viec', 1)->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)->first()->ngay_them ?? 0;
            if ($ngay_them != $xuatHang->ngay_dang_ky) {
                $count++;
            }
        }
        dd($count);
    }

    public function suaSoContainer()
    {
        $so_to_khai_nhap = '500489964960';
        $so_container = 'TCLU 9408800';

        $tau = 'ND 3721';
        NhapHang::find($so_to_khai_nhap)->update([
            'container_ban_dau' => $so_container,
        ]);
        HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->update([
            'so_container_khai_bao' => $so_container
        ]);
        HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
            ->update([
                'hang_trong_cont.so_container' => $so_container
            ]);
        XuatHangCont::where('so_to_khai_nhap', $so_to_khai_nhap)->update([
            'so_container' => $so_container,
        ]);
    }

    public function khoiPhucNhapHang()
    {
        $nhapHang = NhapHangDaHuy::find(479);
        $nhapHangKP = NhapHang::create(
            [
                'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
                'ma_chu_hang' => $nhapHang->ma_chu_hang,
                'ma_hai_quan' => $nhapHang->ma_hai_quan,
                'ma_doanh_nghiep' => $nhapHang->ma_doanh_nghiep,
                'ma_loai_hinh' => $nhapHang->ma_loai_hinh,
                'ngay_thong_quan' => $nhapHang->ngay_thong_quan,
                'ngay_dang_ky'  => $nhapHang->ngay_dang_ky,
                'trang_thai' => 1,
                'container_ban_dau' => $nhapHang->container_ban_dau,
                'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
                'trong_luong' => $nhapHang->trong_luong,
                'ptvt_ban_dau' => $nhapHang->ptvt_ban_dau,
            ]
        );
        $hangHoas = HangHoaDaHuy::where('id_huy', $nhapHang->id_huy)->get();
        foreach ($hangHoas as $hangHoa) {
            HangHoa::create(
                [
                    'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
                    'ten_hang' => $hangHoa->ten_hang,
                    'xuat_xu' => $hangHoa->xuat_xu,
                    'loai_hang' => $hangHoa->loai_hang,
                    'so_luong_khai_bao' => $hangHoa->so_luong_khai_bao,
                    'don_gia' => $hangHoa->don_gia,
                    'tri_gia' => $hangHoa->tri_gia,
                    'don_vi_tinh' => $hangHoa->don_vi_tinh,
                    'so_container_khai_bao' => $hangHoa->so_container_khai_bao,
                ]
            );
        }
    }


    public function fixSoContKhaiBao()
    {
        $nhapHangs = NhapHang::all();
        foreach ($nhapHangs as $nhapHang) {
            HangHoa::where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)->update(['so_container_khai_bao' => $nhapHang->container_ban_dau]);
        }
        $nhapHangsHuy = NhapHangDaHuy::where('trang_thai', '0')->get();
        foreach ($nhapHangsHuy as $nhapHangHuy) {
            HangHoaDaHuy::where('id_huy', $nhapHangHuy->id_huy)->update(['so_container_khai_bao' => $nhapHang->container_ban_dau]);
        }
    }

    public function khoiPhucXuatHang2($so_to_khai_xuat, $trang_thai)
    {
        try {
            DB::beginTransaction();
            $xuatHang = XuatHang::find($so_to_khai_xuat);
            XuatHang::find($so_to_khai_xuat)->update([
                'trang_thai' => $trang_thai,
            ]);
            $xuatHangConts = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->get();

            foreach ($xuatHangConts as $xuatHangCont) {
                $hangTrongCont = HangTrongCont::find($xuatHangCont->ma_hang_cont);
                $hangTrongCont->so_luong -= $xuatHangCont->so_luong_xuat;
                $hangTrongCont->save();
                $this->kiemTraXuatHetHang($xuatHangCont->so_to_khai_nhap);
            }
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in fix: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function kiemTraXuatHetHang($so_to_khai_nhap)
    {
        $allZero = !HangTrongCont::whereHas('hangHoa', function ($query) use ($so_to_khai_nhap) {
            $query->where('so_to_khai_nhap', $so_to_khai_nhap);
        })->where('so_luong', '!=', 0)->exists();
        if ($allZero) {
            $this->capNhatXuatHetHang($so_to_khai_nhap);
        }
    }
    public function capNhatXuatHetHang($so_to_khai_nhap)
    {
        $maCongChuc = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
            ->whereIn('xuat_hang.trang_thai', [12, 13])
            ->orderBy('xuat_hang.updated_at', 'desc')
            ->select('xuat_hang.ma_cong_chuc')
            ->first()?->ma_cong_chuc;

        NhapHang::find($so_to_khai_nhap)
            ->update([
                'ngay_xuat_het' => now(),
                'trang_thai' => '4',
                'ma_cong_chuc_ban_giao' => $maCongChuc
            ]);
    }
    public function quayNguocYeuCau($yeuCau)
    {
        $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->get();
        foreach ($chiTietYeuCaus as $chiTietYeuCau) {
            $nhapHangs = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                ->get();

            foreach ($nhapHangs as $nhapHang) {
                $nhapHang->so_container = $chiTietYeuCau->so_container_goc;
                $nhapHang->save();
            }
        }
    }


    public function capNhatSealTheoDoi($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiHangHoa::whereIn('so_container', [$so_container_no_space, $so_container_with_space])
            ->whereDate('thoi_gian', today()->subDay())
            ->update(['so_seal' => $so_seal]);
    }
    public function capNhatSealXuatHang($so_container, $so_seal, $ngay_dang_ky): void
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        XuatHang::whereDate('xuat_hang.ngay_dang_ky', $ngay_dang_ky)
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->whereIn('xuat_hang_cont.so_container',  [$so_container_no_space, $so_container_with_space])
            ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $so_seal]);
    }
    public function capNhatSealTruLui($so_container, $so_seal): void
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', 'theo_doi_tru_lui.ma_theo_doi')
            ->whereIn('theo_doi_tru_lui_chi_tiet.so_container', [$so_container_no_space, $so_container_with_space])
            ->whereDate('ngay_them', today()->subDay())
            ->update(['theo_doi_tru_lui_chi_tiet.so_seal' => $so_seal]);
    }

    public function xoaTheoDoiTruLui2($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 2)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 2)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
    }


    public function themTheoDoiTruLui($so_to_khai_nhap, $yeuCau)
    {
        $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        $theoDoi = TheoDoiTruLui::create([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'so_ptvt_nuoc_ngoai' => '',
            'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap ?? '',
            'ngay_them' => now(),
            'cong_viec' => 2,
            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
        ]);
        foreach ($hangHoas as $hangHoa) {
            TheoDoiTruLuiChiTiet::insert(
                [
                    'ten_hang' => $hangHoa->ten_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_chua_xuat' => $hangHoa->so_luong,
                    'ma_theo_doi' => $theoDoi->ma_theo_doi,
                    'so_container' => $hangHoa->so_container,
                    'so_seal' => '',
                ]
            );
        }
    }





    public function fixNgayXuatHet()
    {
        try {
            DB::beginTransaction();

            $xuatHet = NhapHang::where('trang_thai', '4')
                ->whereNull('ma_cong_chuc_ban_giao')
                ->get();

            foreach ($xuatHet as $nhapHang) {
                $ngay_xuat_canh = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                    ->where('xuat_hang.trang_thai', '2')
                    ->orderBy('xuat_hang.updated_at', 'desc')
                    ->select('xuat_hang.ngay_xuat_canh')
                    ->first()?->ngay_xuat_canh;
                $nhapHang->ngay_xuat_het = $ngay_xuat_canh;
                $nhapHang->save();
            }
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in fix: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function thayDoiMaDoanhNghiep(Request $request)
    {
        $maDNCu = "4900216802";
        $maDNMoi = "5901982917MT";

        NhapHang::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        NhapHangDaHuy::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        NhapHangSua::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        XuatCanh::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        XuatCanh::where('ma_doanh_nghiep_chon', $maDNCu)->update([
            'ma_doanh_nghiep_chon' => $maDNMoi,
        ]);
        YeuCauGiaHan::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauTauCont::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauKiemTra::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauChuyenContainer::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauChuyenTau::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauHangVeKho::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauTieuHuy::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauNiemPhong::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        DoanhNghiep::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        TaiKhoan::where('ten_dang_nhap', $maDNCu)->update([
            'ten_dang_nhap' => $maDNMoi,
        ]);
    }

    public function thayPTVT(Request $request)
    {
        $xuatHangs = XuatHang::all();
        foreach ($xuatHangs as $xuatHang) {
            $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->with('PTVTXuatCanh')
                ->get()
                ->pluck('PTVTXuatCanh.ten_phuong_tien_vt')
                ->filter()
                ->implode('; ');
            $xuatHang->ten_phuong_tien_vt = $ptvts ?? '';
            $xuatHang->save();
        }
    }
    public function xuatHet()
    {
        $allNhapHangs = NhapHang::where('trang_thai', '2')->get();
        $arr = [];
        $stt = 0;
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon == 0) {
                $stt++;
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
                $nhapHang->trang_thai = "4";
                $nhapHang->save();
            }
        }
        return $stt;
    }

    public function fixCCXuatHet()
    {
        $allNhapHangs = NhapHang::where('trang_thai', '4')
            ->whereNull('ma_cong_chuc_ban_giao')
            ->get();
        foreach ($allNhapHangs as $nhapHang) {
            $maCongChuc = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->where('xuat_hang.trang_thai', '12')
                ->orderBy('xuat_hang.updated_at', 'desc')
                ->select('xuat_hang.ma_cong_chuc')
                ->first()?->ma_cong_chuc;
            $nhapHang->ma_cong_chuc_ban_giao = $maCongChuc;
            $nhapHang->save();
        }
    }

    public function containerTauGoc()
    {
        $yeuCaus = YeuCauChuyenContainer::where('trang_thai', '2')->get();
        foreach ($yeuCaus as $yeuCau) {
            $phuong_tien_cho_hang = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 3)
                ->first()->phuong_tien_cho_hang;
            YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'tau_goc' => $phuong_tien_cho_hang
            ]);
        }
    }
    public function ycKiemTraSoLuong()
    {
        $yeuCaus = YeuCauKiemTra::where('trang_thai', '2')->get();
        foreach ($yeuCaus as $yeuCau) {
            $so_luong_ton = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 7)
                ->sum('so_luong_ton');
            YeuCauKiemTraChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'so_luong' => $so_luong_ton
            ]);
        }
    }

    public function ycChuyenTauSoLuong()
    {
        $yeuCaus = YeuCauChuyenTau::all();
        foreach ($yeuCaus as $yeuCau) {
            $so_luong_ton = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 4)
                ->sum('so_luong_ton');
            YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'so_luong' => $so_luong_ton
            ]);
        }
    }

    public function checkLechSoLuong(Request $request)
    {
        $allNhapHangs = NhapHang::where('trang_thai', '2')->get();

        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $slKhaiBao = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_hoa.so_luong_khai_bao');

            $slDaXuat = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.trang_thai', '!=', '0')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('xuat_hang_cont.so_luong_xuat');

            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');

            if ($slKhaiBao - $slDaXuat != $soLuongTon) {
                array_push($arr, $slKhaiBao, $slDaXuat, $soLuongTon, $nhapHang->so_to_khai_nhap);
            }
        }
        $excludeValues = []; // Add more if needed

        $arr = array_filter($arr, function ($value) use ($excludeValues) {
            return !in_array($value, $excludeValues);
        });

        $arr = array_values($arr);

        dd($arr);
    }
    public function checkXuatHetHang()
    {
        $allNhapHangs = NhapHang::where('trang_thai', '4')->get();
        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon != 0) {
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
            }
        }
        dd($arr);
    }
    public function fixPhanQuyenBaoCao()
    {
        $congChucs = CongChuc::all();
        foreach ($congChucs as $congChuc) {
            for ($i = 1; $i <= 30; $i++) {
                $check = PhanQuyenBaoCao::where('ma_cong_chuc', $congChuc->ma_cong_chuc)
                    ->where('ma_bao_cao', $i)
                    ->exists();
                if (!$check) {
                    PhanQuyenBaoCao::insert(values: [
                        'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                        'ma_bao_cao' => $i,
                        'phan_quyen' => 0,
                    ]);
                }
            }
        }
    }
}
