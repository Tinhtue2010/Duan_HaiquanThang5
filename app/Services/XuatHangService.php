<?php

namespace App\Services;

use App\Models\TheoDoiTruLui;
use App\Models\XuatHangChiTietSua;
use App\Models\XuatHangChiTietTruocSua;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\TienTrinh;
use App\Models\XuatHang;
use App\Models\HangHoa;
use App\Models\XuatHangCont;
use App\Models\XuatHangSua;
use App\Models\YeuCauChuyenContainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PTVTXuatCanhCuaPhieuSua;
use App\Models\PTVTXuatCanhCuaPhieuTruocSua;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YeuCauTauCont;

class XuatHangService
{
    public function getToKhaiXuat($statuses, $isAdminView = false)
    {
        $user = Auth::user();
        $accountType = $user->loai_tai_khoan;

        if ($accountType == "Cán bộ công chức") {
            return $this->getTatCaToKhaiXuat($statuses);
        }
        if ($accountType == "Doanh nghiệp") {
            return $this->getToKhaiXuatTheoDoanhNghiep($statuses);
        }
        return collect(); // Return empty
    }

    public function getTatCaToKhaiXuat($cac_trang_thai)
    {
        $exports = XuatHang::whereIn('xuat_hang.trang_thai', $cac_trang_thai)
            ->join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->select(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->orderBy('xuat_hang.so_to_khai_xuat', 'desc')
            ->get();

        return $exports;
    }

    public function getToKhaiXuatTheoDoanhNghiep($cac_trang_thai)
    {
        $ma_doanh_nghiep = $this->getDoanhNghiepHienTai()->ma_doanh_nghiep;

        $exports = XuatHang::whereIn('xuat_hang.trang_thai', $cac_trang_thai)
            ->join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->where('xuat_hang.ma_doanh_nghiep', $ma_doanh_nghiep)
            ->select(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->orderBy('xuat_hang.so_to_khai_xuat', 'desc')
            ->get();

        return $exports;
    }

    public function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail();
    }
    public function getThongTinHangHoaHienTai()
    {
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        return NhapHang::with(['hangHoa' => function ($query) {
            $query->select('so_to_khai_nhap', 'ma_hang', 'ten_hang', 'loai_hang', 'xuat_xu', 'don_vi_tinh', 'don_gia');
        }, 'hangHoa.hangTrongCont' => function ($query) {
            $query->select('ma_hang_cont', 'so_luong', 'so_container');
        }, 'hangHoa.hangTrongCont.xuatHangCont' => function ($query) {
            $query->select('so_to_khai_xuat', 'ma_hang_cont', 'so_luong_xuat', 'so_to_khai_nhap');
        }, 'hangHoa.hangTrongCont.xuatHangCont.xuatHang' => function ($query) {
            $query->select('so_to_khai_xuat', 'trang_thai');
        }])
            ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
            ->where('nhap_hang.trang_thai', '2')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->select(
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.so_container',
                'hang_trong_cont.so_luong',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'nhap_hang.so_to_khai_nhap',
            )
            ->groupBy(
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.so_container',
                'hang_trong_cont.so_luong',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'nhap_hang.so_to_khai_nhap',
            )
            ->get();
    }
    public function getThongTinHangHoaHienTaiSua($xuatHang)
    {
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        return NhapHang::with(['hangHoa' => function ($query) {
            $query->select('so_to_khai_nhap', 'ma_hang', 'ten_hang', 'loai_hang', 'xuat_xu', 'don_vi_tinh', 'don_gia');
        }, 'hangHoa.hangTrongCont' => function ($query) {
            $query->select('ma_hang_cont', 'so_luong', 'so_container');
        }, 'hangHoa.hangTrongCont.xuatHangCont' => function ($query) {
            $query->select('so_to_khai_xuat', 'ma_hang_cont', 'so_luong_xuat', 'so_to_khai_nhap');
        }, 'hangHoa.hangTrongCont.xuatHangCont.xuatHang' => function ($query) {
            $query->select('so_to_khai_xuat', 'trang_thai');
        }])
            ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->select(
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.so_container',
                'hang_trong_cont.so_luong',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'nhap_hang.so_to_khai_nhap',
            )
            ->groupBy(
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.so_container',
                'hang_trong_cont.so_luong',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'nhap_hang.so_to_khai_nhap',
            )
            ->get();
    }
    public function themXuatHang($request)
    {
        $xuatHang = new XuatHang();
        $xuatHang->ma_loai_hinh = $request->ma_loai_hinh;
        $xuatHang->ngay_dang_ky = now();
        $xuatHang->ten_doan_tau = $request->ten_doan_tau;
        $xuatHang->trang_thai = "1";
        $xuatHang->ma_doanh_nghiep = $this->getDoanhNghiepHienTai()->ma_doanh_nghiep;
        $xuatHang->save();
        return $xuatHang;
    }

    public function themPTVTCuaPhieu($so_to_khai_xuat, array $ptvtRowsData)
    {
        foreach ($ptvtRowsData as $row) {
            PTVTXuatCanhCuaPhieu::insert([
                'so_to_khai_xuat' => $so_to_khai_xuat,
                'so_ptvt_xuat_canh' => $row['so_ptvt_xuat_canh'],
            ]);
        }
    }
    public function themSuaPTVTCuaPhieu($ma_yeu_cau, array $ptvtRowsData)
    {
        foreach ($ptvtRowsData as $row) {
            PTVTXuatCanhCuaPhieuSua::insert([
                'ma_yeu_cau' => $ma_yeu_cau,
                'so_ptvt_xuat_canh' => $row['so_ptvt_xuat_canh'],
            ]);
        }
    }
    public function themTruocSuaPTVTCuaPhieu($so_to_khai_xuat, $ma_yeu_cau)
    {
        $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)->get();
        foreach ($ptvts as $ptvt) {
            PTVTXuatCanhCuaPhieuTruocSua::insert([
                'ma_yeu_cau' => $ma_yeu_cau,
                'so_ptvt_xuat_canh' => $ptvt->so_ptvt_xuat_canh,
            ]);
        }
    }

    public function themXuatHangConts($soToKhaiXuat, array $rowsData)
    {
        foreach ($rowsData as $row) {
            $so_luong = HangTrongCont::find($row['ma_hang_cont'])->so_luong;
            $phuong_tien_vt_nhap = NhapHang::find($row['so_to_khai_nhap'])->phuong_tien_vt_nhap;
            XuatHangCont::create([
                'so_to_khai_xuat' => $soToKhaiXuat,
                'ma_hang_cont' => $row['ma_hang_cont'],
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_luong_ton' => $so_luong - $row['so_luong_xuat'],
                'so_luong_xuat' => $row['so_luong_xuat'],
                'so_container' => $row['so_container'],
                'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
                'tri_gia' => $row['tri_gia'],
            ]);
        }
    }
    public function themTienTrinh($so_to_khai_nhap, $ten_cong_viec, $ma_cong_chuc)
    {
        TienTrinh::insert([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'ten_cong_viec' => $ten_cong_viec,
            'ngay_thuc_hien' => now(),
            'ma_cong_chuc' => $ma_cong_chuc
        ]);
    }

    //Lấy thông tin hàng hóa đã xuất hàng bao nhiêu với số lượng xuất không tính phiếu xuất này
    public function getThongTinHangHoaHienTaiChoDuyet($xuatHang)
    {
        return NhapHang::with(['hangHoa' => function ($query) {
            $query->select('so_to_khai_nhap', 'ma_hang', 'ten_hang', 'loai_hang', 'xuat_xu', 'don_vi_tinh', 'don_gia');
        }, 'hangHoa.hangTrongCont' => function ($query) {
            $query->select('ma_hang_cont', 'so_luong', 'so_container');
        }, 'hangHoa.hangTrongCont.xuatHangCont' => function ($query) {
            $query->select('so_to_khai_xuat', 'ma_hang_cont', 'so_luong_xuat');
        }, 'hangHoa.hangTrongCont.xuatHangCont.xuatHang' => function ($query) {
            $query->select('so_to_khai_xuat', 'trang_thai');
        }])
            ->where('nhap_hang.so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->select(
                'hang_trong_cont.ma_hang_cont',
                'nhap_hang.so_to_khai_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_trong_cont.so_luong',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'hang_trong_cont.so_container',
                DB::raw('SUM(CASE 
                WHEN xuat_hang.trang_thai IN ("1", "3","6") 
                AND xuat_hang.so_to_khai_xuat != ' . $xuatHang->so_to_khai_xuat . ' 
                THEN xuat_hang_cont.so_luong_xuat 
                ELSE 0 
                END) as so_luong_cho_xuat')
            )
            ->groupBy(
                'hang_trong_cont.ma_hang_cont',
                'nhap_hang.so_to_khai_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_trong_cont.so_luong',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'hang_trong_cont.so_container',
            )
            ->get()
            ->unique('ma_hang_cont');
    }
    //Lấy thông tin hàng hóa đã xuất hàng bao nhiêu với số lượng xuất không tính phiếu xuất này (Phải cộng thêm số lượng đã xuất ở phiếu này)
    public function getThongTinHangHoaHienTaiKhacDaDuyet($xuatHang)
    {
        return NhapHang::with([
            'hangHoa' => function ($query) {
                $query->select('so_to_khai_nhap', 'ma_hang', 'ten_hang', 'loai_hang', 'xuat_xu', 'don_vi_tinh', 'don_gia');
            },
            'hangHoa.hangTrongCont' => function ($query) {
                $query->select('ma_hang_cont', 'so_luong', 'so_container');
            },
            'hangHoa.hangTrongCont.xuatHangCont' => function ($query) {
                $query->select('so_to_khai_xuat', 'ma_hang_cont', 'so_luong_xuat');
            },
            'hangHoa.hangTrongCont.xuatHangCont.xuatHang' => function ($query) {
                $query->select('so_to_khai_xuat', 'trang_thai');
            }
        ])
            ->where('nhap_hang.so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->select(
                'hang_trong_cont.ma_hang_cont',
                'nhap_hang.so_to_khai_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_trong_cont.so_luong',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'hang_trong_cont.so_container',
                DB::raw('SUM(CASE 
                WHEN xuat_hang.trang_thai IN ("1", "3", "6") 
                AND xuat_hang.so_to_khai_xuat != ' . $xuatHang->so_to_khai_xuat . ' 
                THEN xuat_hang_cont.so_luong_xuat 
                ELSE 0 
                END) as so_luong_cho_xuat')
            )
            ->groupBy(
                'hang_trong_cont.ma_hang_cont',
                'nhap_hang.so_to_khai_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_trong_cont.so_luong',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.ngay_thong_quan',
                'hang_trong_cont.so_container',
            )
            ->get()
            ->unique('ma_hang_cont');
    }
    public function getSoLuongXuat($so_to_khai_xuat)
    {
        return XuatHangCont::with(['hangTrongCont.hangHoa'])
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->leftJoin('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->leftJoin('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang.so_to_khai_xuat', $so_to_khai_xuat)
            ->select([
                'hang_trong_cont.ma_hang_cont',
                'xuat_hang_cont.so_luong_xuat',
            ])
            ->get();
    }


    public function themChiTietSuaXuatHang($suaXuatHang, $rowsData)
    {
        $chiTietSuas = array_map(function ($row) use ($suaXuatHang) {
            $so_luong = HangTrongCont::find($row['ma_hang_cont'])->so_luong;

            return [
                'so_to_khai_xuat' => $suaXuatHang->so_to_khai_xuat,
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'ma_hang_cont' => $row['ma_hang_cont'],
                'so_luong_xuat' => $row['so_luong_xuat'],
                'so_luong_ton' => $so_luong - $row['so_luong_xuat'],
                'so_container' => $row['so_container'],
                'tri_gia' => $row['tri_gia'],
                'ma_yeu_cau' => $suaXuatHang->ma_yeu_cau,
            ];
        }, $rowsData);

        XuatHangChiTietSua::insert($chiTietSuas);
    }

    public function themChiTietTruocSua($suaXuatHang, $request)
    {
        $chiTietTruocSuas = XuatHangCont::where('so_to_khai_xuat', $request->so_to_khai_xuat)
            ->get()
            ->map(function ($xuatHangCont) use ($suaXuatHang) {

                return [
                    'so_to_khai_xuat' => $xuatHangCont->so_to_khai_xuat,
                    'ma_hang_cont' => $xuatHangCont->ma_hang_cont,
                    'so_luong_xuat' => $xuatHangCont->so_luong_xuat,
                    'so_luong_ton' => $xuatHangCont->so_luong_ton,
                    'so_container' => $xuatHangCont->so_container,
                    'tri_gia' => $xuatHangCont->tri_gia,
                    'ma_yeu_cau' => $suaXuatHang->ma_yeu_cau,
                ];
            })->toArray();

        XuatHangChiTietTruocSua::insert($chiTietTruocSuas);
    }

    public function capNhatTrangThaiPhieuXuat($xuatHang)
    {
        if ($xuatHang->trang_thai == "1") {
            $newStatus = '3';
        } elseif ($xuatHang->trang_thai == "2") {
            $newStatus = '4';
        } elseif ($xuatHang->trang_thai == "11") {
            $newStatus = '5';
        } elseif ($xuatHang->trang_thai == "12") {
            $newStatus = '6';
        }
        $xuatHang->update(['trang_thai' => $newStatus]);
    }

    public function getThongTinPhieuXuatHang($so_to_khai_xuat)
    {
        return XuatHangCont::with(['hangTrongCont.hangHoa'])
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->leftJoin('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->leftJoin('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang.so_to_khai_xuat', $so_to_khai_xuat)
            ->select([
                'hang_hoa.*',
                'xuat_hang_cont.*',
                'xuat_hang_cont.tri_gia as tri_gia_xuat',
            ])
            ->get();
    }

    public function getThongTinHangTrongPhieuXuatHienTai($so_to_khai_xuat)
    {
        return XuatHangCont::with(['hangTrongCont.hangHoa'])
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->leftJoin('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->leftJoin('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang.so_to_khai_xuat', $so_to_khai_xuat)
            ->select([
                'hang_hoa.*',
                'xuat_hang_cont.*',
                'xuat_hang_cont.tri_gia as tri_gia_xuat',
            ])
            ->get();
    }
    public function getThongTinSuaXuatHang($so_to_khai_xuat)
    {
        return XuatHangSua::where('so_to_khai_xuat', $so_to_khai_xuat)
            ->orderBy('ma_yeu_cau', 'desc')
            ->first();
    }
    public function getChiTietThongTinSauSuaXuatHang($ma_yeu_cau)
    {
        return XuatHangChiTietSua::with(['hangTrongCont.hangHoa'])
            ->join('xuat_hang_sua', 'xuat_hang_chi_tiet_sua.so_to_khai_xuat', '=', 'xuat_hang_sua.so_to_khai_xuat')
            ->join('hang_trong_cont', 'xuat_hang_chi_tiet_sua.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang_chi_tiet_sua.ma_yeu_cau', $ma_yeu_cau)
            ->where('xuat_hang_sua.ma_yeu_cau', $ma_yeu_cau)
            ->select([
                'hang_hoa.*',
                'xuat_hang_chi_tiet_sua.*',
                'xuat_hang_chi_tiet_sua.tri_gia as tri_gia_xuat',
            ])
            ->get();
    }
    public function getPTVTXuatCanhCuaPhieuSauSua($ma_yeu_cau)
    {
        return PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $ma_yeu_cau)->get();
    }
    public function getPTVTXuatCanhCuaPhieuTruocSua($ma_yeu_cau)
    {
        return PTVTXuatCanhCuaPhieuTruocSua::where('ma_yeu_cau', $ma_yeu_cau)->get();
    }


    public function getChiTietThongTinTruocSuaXuatHang($ma_yeu_cau)
    {
        return XuatHangChiTietTruocSua::with(['hangTrongCont.hangHoa'])
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_chi_tiet_truoc_sua.so_to_khai_xuat')
            ->leftJoin('hang_trong_cont', 'xuat_hang_chi_tiet_truoc_sua.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->leftJoin('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('xuat_hang_chi_tiet_truoc_sua.ma_yeu_cau', $ma_yeu_cau)
            ->select([
                'hang_hoa.*',
                'xuat_hang_chi_tiet_truoc_sua.*',
                'xuat_hang_chi_tiet_truoc_sua.tri_gia as tri_gia_xuat',
            ])
            ->get();
    }


    public function kiemTraSoLuongCoTheXuat($suaXuatHang, $xuatHang, $chiTietSuaXuatHangs)
    {
        foreach ($chiTietSuaXuatHangs as $chiTietSuaXuatHang) {
            $xuatHangCont = XuatHangCont::where('so_to_khai_xuat', $chiTietSuaXuatHang->so_to_khai_xuat)
                ->where('ma_hang_cont', $chiTietSuaXuatHang->ma_hang_cont)
                ->first();

            $hangHoa = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
                ->where('ma_hang_cont', $chiTietSuaXuatHang->ma_hang_cont)
                ->first();


            $ten_hang = $hangHoa->ten_hang;
            $so_luong_trong_phieu  = $xuatHangCont->so_luong_xuat ?? 0;
            $so_luong_moi = $chiTietSuaXuatHang->so_luong_xuat ?? 0;
            if ($so_luong_moi - $so_luong_trong_phieu > $hangHoa->so_luong) {
                $thong_bao = "Số lượng hàng trong kho không đủ cho phiếu xuất sau khi sửa, hàng hóa: {$ten_hang} " .
                    "Số lượng trong phiếu: {$so_luong_trong_phieu}, " .
                    "số lượng sau khi sửa: {$so_luong_moi}, " .
                    "số lượng trong kho: {$hangHoa->so_luong},";

                session()->flash('alert-danger', $thong_bao);
                return false;
            }
        }

        return true;
    }

    public function getThongTinHangHoaTrongPhieu($suaXuatHang, $xuatHang, $chiTietSuaXuatHang)
    {
        return HangTrongCont::leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->select(
                'hang_trong_cont.so_luong',
                'hang_trong_cont.ma_hang',
                'hang_trong_cont.ma_hang_cont',
            )
            ->where('xuat_hang_cont.so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)
            ->where('hang_trong_cont.ma_hang_cont', $chiTietSuaXuatHang->ma_hang_cont)
            ->groupBy('hang_trong_cont.so_luong', 'hang_trong_cont.ma_hang', 'hang_trong_cont.ma_hang_cont')
            ->first();
    }

    public function tinhToanSoLuongCoTheXuat($container, $suaXuatHang, $xuatHang)
    {
        $so_luong_co_the_xuat = $container->so_luong;

        $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->get();
        $matching_cont = $xuatHangConts->firstWhere('ma_hang_cont', $container->ma_hang_cont);

        if ($matching_cont) {
            $so_luong_co_the_xuat = $container->so_luong + $matching_cont->so_luong_xuat;
        }

        return $so_luong_co_the_xuat;
    }

    public function taoThongBaoLoi($ten_hang, $so_luong_phieu, $so_luong_them)
    {
        return "Số lượng hàng trong kho không đủ cho phiếu xuất sau khi sửa, hàng hóa: {$ten_hang} " .
            "Số lượng trong phiếu: {$so_luong_phieu}, " .
            "số lượng thêm: {$so_luong_them}";
    }
    public function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }
    public function capNhatThongTinXuatHang($xuatHang, $suaXuatHang, $chiTietSuaXuatHangs, $xuatHangConts)
    {

        $xuatHang->ma_loai_hinh = $suaXuatHang->ma_loai_hinh;
        $trang_thai = "";
        if ($xuatHang->trang_thai == '3') {
            $trang_thai = "1";
        } elseif ($xuatHang->trang_thai == '4') {
            $trang_thai = "2";
        } elseif ($xuatHang->trang_thai == '5') {
            $trang_thai = "11";
        } elseif ($xuatHang->trang_thai == '6') {
            $trang_thai = "12";
        } else {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
        $xuatHang->trang_thai = $trang_thai;
        $xuatHang->save();

        // if ($trang_thai != "1") {
        $this->capNhatSoLuongHangTrongCont($chiTietSuaXuatHangs, $xuatHangConts);
        // }

        $ptvtSuas = PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->get();
        XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->delete();
        $this->themLaiXuatHangCont($xuatHang, $chiTietSuaXuatHangs);
        PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)->delete();
        $this->themLaiPTVTXuatCanhCuaPhieu($xuatHang, $ptvtSuas);

        $congChuc = $this->getCongChucHienTai();

        $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
            ->select('so_to_khai_nhap')
            ->distinct()
            ->get();
        foreach ($xuatHangConts as $xuatHangCont) {
            $so_to_khai_nhap = $xuatHangCont->so_to_khai_nhap;
            $allZero = !HangTrongCont::whereHas('hangHoa', function ($query) use ($so_to_khai_nhap) {
                $query->where('so_to_khai_nhap', $so_to_khai_nhap);
            })->where('so_luong', '!=', 0)->exists();

            if (!$allZero) {
                NhapHang::find($so_to_khai_nhap)
                    ->update([
                        'trang_thai' => '2',
                    ]);
            }
            // $this->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức đã duyệt yêu cầu sửa phiếu xuất số " . $xuatHang->so_to_khai_xuat, $congChuc->ma_cong_chuc);
        }
        $suaXuatHang->update([
            'trang_thai' => "2",
            'ma_cong_chuc' => $congChuc->ma_cong_chuc ?? '',
            'trang_thai_phieu_xuat' =>  $trang_thai
        ]);

        session()->flash('alert-success', 'Duyệt yêu cầu sửa thành công!');
    }
    public function xoaTheoDoi($so_to_khai_nhap, $so_to_khai_xuat)
    {
        TheoDoiHangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('cong_viec', 1)
            ->where('ma_yeu_cau', $so_to_khai_xuat)
            ->delete();
        TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('cong_viec', 1)
            ->where('ma_yeu_cau', $so_to_khai_xuat)
            ->delete();
    }
    public function capNhatSoLuongHangTrongCont($chiTietSuaXuatHangs, $xuatHangConts)
    {
        //XuatHangCont là ChiTietXuatHang
        foreach ($xuatHangConts as $xuatHangCont) {
            $hang_trong_cont = HangTrongCont::find($xuatHangCont->ma_hang_cont);
            $hang_trong_cont->so_luong += $xuatHangCont->so_luong_xuat;
            $hang_trong_cont->save();
        }
        foreach ($chiTietSuaXuatHangs as $chiTietSuaXuatHang) {
            $hang_trong_cont = HangTrongCont::find($chiTietSuaXuatHang->ma_hang_cont);
            $hang_trong_cont->so_luong -= $chiTietSuaXuatHang->so_luong_xuat;
            $hang_trong_cont->save();
            YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', 'yeu_cau_tau_cont.ma_yeu_cau')
                ->join('yeu_cau_tau_cont_hang_hoa', 'yeu_cau_tau_cont_hang_hoa.ma_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_chi_tiet')
                ->where('yeu_cau_tau_cont.trang_thai', '1')
                ->where('yeu_cau_tau_cont_hang_hoa.ma_hang_cont', $hang_trong_cont->ma_hang_cont)
                ->update(['yeu_cau_tau_cont_hang_hoa.so_luong' => $hang_trong_cont->so_luong]);

            YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
                ->join('yeu_cau_container_hang_hoa', 'yeu_cau_container_hang_hoa.ma_chi_tiet', 'yeu_cau_container_chi_tiet.ma_chi_tiet')
                ->where('yeu_cau_chuyen_container.trang_thai', '1')
                ->where('yeu_cau_container_hang_hoa.ma_hang_cont', $hang_trong_cont->ma_hang_cont)
                ->update(['yeu_cau_container_hang_hoa.so_luong' => $hang_trong_cont->so_luong]);
        }
    }

    public function themTheoDoi($xuatHang, $xuatHangCont, $hangHoaXuat, $ma_cong_chuc)
    {
        $ptvtXuatCanh = $this->getPTVTXuatCanhCuaPhieu($xuatHang->so_to_khai_xuat);
        TheoDoiHangHoa::insert(
            [
                'so_to_khai_nhap' => $xuatHangCont->so_to_khai_nhap,
                'ma_hang' => $hangHoaXuat->ma_hang,
                'thoi_gian' => now(),
                'so_luong_xuat' => $hangHoaXuat->so_luong_xuat,
                'so_luong_ton' => $hangHoaXuat->so_luong - $hangHoaXuat->so_luong_xuat,
                'phuong_tien_cho_hang' => '',
                'cong_viec' => 1,
                'phuong_tien_nhan_hang' => $ptvtXuatCanh,
                'so_container' => $hangHoaXuat->so_container,
                'so_seal' => $hangHoaXuat->so_seal ?? '',
                'ma_cong_chuc' => $ma_cong_chuc ?? '',
                'ma_yeu_cau' => $xuatHang->so_to_khai_xuat,

            ]
        );
        TheoDoiTruLui::insert([
            'so_to_khai_nhap' => $xuatHangCont->so_to_khai_nhap,
            'so_ptvt_nuoc_ngoai' => $ptvtXuatCanh,
            'phuong_tien_vt_nhap' => '',
            'ngay_them' => now(),
            'cong_viec' => 1,
            'ma_yeu_cau' => $xuatHang->so_to_khai_xuat,
        ]);
    }


    public function chuanBiDuLieuTheoDoi($xuatHang, $hangHoaXuat, $ma_cong_chuc)
    {


        return compact('hang_hoa');
    }
    public function themLaiPTVTXuatCanhCuaPhieu($xuatHang, $ptvtSuas)
    {
        $ptvts = $ptvtSuas->map(function ($ptvtSua) use ($xuatHang) {
            return [
                'so_to_khai_xuat' => $xuatHang->so_to_khai_xuat,
                'so_ptvt_xuat_canh' => $ptvtSua->so_ptvt_xuat_canh,
            ];
        });

        PTVTXuatCanhCuaPhieu::insert($ptvts->toArray());
    }

    public function getPTVTXuatCanhCuaPhieu($so_to_khai_xuat)
    {
        return PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)
            ->with('PTVTXuatCanh')
            ->get()
            ->pluck('PTVTXuatCanh.ten_phuong_tien_vt')
            ->filter()
            ->implode('; ');
    }
    public function getTongSoLuongHangXuat($so_to_khai_xuat)
    {
        return XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang.so_to_khai_xuat', $so_to_khai_xuat)
            ->sum('xuat_hang_cont.so_luong_xuat');
    }

    public function themLaiXuatHangCont($xuatHang, $chiTietSuaXuatHangs)
    {
        $xuatHangConts = $chiTietSuaXuatHangs->map(function ($chiTietSuaXuatHang) use ($xuatHang) {
            $phuong_tien_vt_nhap = NhapHang::find($chiTietSuaXuatHang->so_to_khai_nhap)->phuong_tien_vt_nhap;
            return [
                'so_to_khai_xuat' => $xuatHang->so_to_khai_xuat,
                'so_to_khai_nhap' => $chiTietSuaXuatHang->so_to_khai_nhap,
                'ma_hang_cont' => $chiTietSuaXuatHang->ma_hang_cont,
                'so_luong_xuat' => $chiTietSuaXuatHang->so_luong_xuat,
                'so_luong_ton' => $chiTietSuaXuatHang->so_luong_ton,
                'so_container' => $chiTietSuaXuatHang->so_container,
                'tri_gia' => $chiTietSuaXuatHang->tri_gia,
                'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
            ];
        });

        XuatHangCont::insert($xuatHangConts->toArray());
    }


    public function getThongTinHangHoaXuat($xuatHangCont)
    {
        return XuatHangCont::where('ma_xuat_hang_cont', $xuatHangCont->ma_xuat_hang_cont)
            ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->leftJoin('niem_phong', 'hang_trong_cont.so_container', '=', 'niem_phong.so_container')
            ->select(
                'niem_phong.so_seal',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.so_container',
                'hang_trong_cont.so_luong',
                'hang_trong_cont.ma_hang_cont',
                'hang_hoa.ten_hang',
                'hang_hoa.ma_hang'
            )
            ->first();
    }

    public function capNhatSoLuongHang($xuatHangCont, $hangHoaXuat)
    {
        $xuatHangCont = XuatHangCont::find($xuatHangCont->ma_xuat_hang_cont);
        $hangTrongCont = HangTrongCont::find($hangHoaXuat->ma_hang_cont);

        $hangTrongCont->so_luong -= $hangHoaXuat->so_luong_xuat;
        $hangTrongCont->save();

        $xuatHangCont->so_luong_ton = $hangTrongCont->so_luong;
        $xuatHangCont->save();

        YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', 'yeu_cau_tau_cont.ma_yeu_cau')
            ->join('yeu_cau_tau_cont_hang_hoa', 'yeu_cau_tau_cont_hang_hoa.ma_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_chi_tiet')
            ->where('yeu_cau_tau_cont.trang_thai', '1')
            ->where('yeu_cau_tau_cont_hang_hoa.ma_hang_cont', $hangHoaXuat->ma_hang_cont)
            ->update(['yeu_cau_tau_cont_hang_hoa.so_luong' => $hangTrongCont->so_luong]);

        YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->join('yeu_cau_container_hang_hoa', 'yeu_cau_container_hang_hoa.ma_chi_tiet', 'yeu_cau_container_chi_tiet.ma_chi_tiet')
            ->where('yeu_cau_chuyen_container.trang_thai', '1')
            ->where('yeu_cau_container_hang_hoa.ma_hang_cont', $hangHoaXuat->ma_hang_cont)
            ->update(['yeu_cau_container_hang_hoa.so_luong' => $hangTrongCont->so_luong]);
    }

    public function capNhatPhieuXuatHang($xuatHang, $maCongChuc)
    {
        $xuatHang->update([
            'ghi_chu' => '',
            'ma_cong_chuc' => $maCongChuc,
            'trang_thai' => '2',
            'ngay_xuat_canh' => now()
        ]);
    }




    public function themSuaXuatHang(Request $request, $xuatHang)
    {
        return XuatHangSua::create([
            'ma_loai_hinh' => $request->ma_loai_hinh,
            'so_to_khai_xuat' => $request->so_to_khai_xuat,
            'ma_cong_chuc' => $xuatHang->ma_cong_chuc ?? '',
            'trang_thai' => '1',
            'ngay_tao' => now(),
            'trang_thai_phieu_xuat' => $xuatHang->trang_thai,
            'ten_doan_tau' => $request->ten_doan_tau,
        ]);
    }
    public function xuLyDuyetPhieuXuat($ma_cong_chuc, $so_to_khai_xuat)
    {
        $maCongChuc = $ma_cong_chuc;
        $xuatHang = XuatHang::find($so_to_khai_xuat);
        $xuatHang->ma_cong_chuc = $ma_cong_chuc;
        if ($xuatHang->trang_thai != "2") {
            $this->capNhatPhieuXuatHang($xuatHang, $maCongChuc);

            $congChuc = $this->getCongChucHienTai();
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức đã duyệt phiếu xuất hàng số " . $xuatHang->so_to_khai_xuat, $congChuc->ma_cong_chuc);
            }
        }
        session()->flash('alert-success', 'Trạng thái đã được cập nhật thành công!');

        return $xuatHang;
    }
    public function xuLyDuyetThucXuat($ma_cong_chuc, $so_to_khai_xuat)
    {
        $xuatHang = XuatHang::find($so_to_khai_xuat);
        if ($xuatHang->trang_thai != "13") {
            $xuatHang->update([
                'ghi_chu' => '',
                'trang_thai' => '13',
                'ngay_xuat_canh' => now()
            ]);
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức đã duyệt thực xuất phiếu xuất hàng số " . $xuatHang->so_to_khai_xuat, $ma_cong_chuc);
            }
        }
        session()->flash('alert-success', 'Trạng thái đã được cập nhật thành công!');

        return $xuatHang;
    }



    public function huyPhieuXuatFunc($so_to_khai_xuat, $ghi_chu, $user, $ly_do)
    {
        $xuatHang = XuatHang::find($so_to_khai_xuat);
        if ($xuatHang) {
            $xuatHang->trang_thai = '0';
            $xuatHang->ghi_chu = $ghi_chu;
            $xuatHang->save();

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            if ($user == "Cán bộ công chức") {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                foreach ($xuatHangConts as $xuatHangCont) {
                    $this->themTienTrinh($xuatHang->so_to_khai_nhap, "Cán bộ công chức đã hủy phiếu xuất số " . $so_to_khai_xuat, $congChuc->ma_cong_chuc);
                }
            } elseif ($user == "Doanh nghiệp") {
                foreach ($xuatHangConts as $xuatHangCont) {
                    $this->themTienTrinh($xuatHang->so_to_khai_nhap, "Doanh nghiệp đã hủy phiếu xuất số " . $so_to_khai_xuat, '');
                }
            } elseif ($user == "Hệ thống") {
                foreach ($xuatHangConts as $xuatHangCont) {
                    $this->themTienTrinh($xuatHang->so_to_khai_nhap, "Hệ thống đã hủy phiếu xuất số " . $so_to_khai_xuat . $ly_do, '');
                }
            }
        }
    }
    public function huyPhieuXuats($so_to_khai_nhap, $ly_do)
    {
        $ghi_chu = "Hệ thống đã hủy phiếu xuất " . $ly_do;
        $xuatHangs = XuatHang::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', '1')
            ->get();
        foreach ($xuatHangs as $xuatHang) {
            $this->huyPhieuXuatFunc($xuatHang->so_to_khai_xuat, $ghi_chu, 'Hệ thống', $ly_do);
        }
    }
}
