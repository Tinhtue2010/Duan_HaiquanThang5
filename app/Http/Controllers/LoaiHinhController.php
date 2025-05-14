<?php

namespace App\Http\Controllers;

use App\Models\BaoCao;
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
use App\Models\YCContainerMaHangContMoi;
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
use App\Models\YeuCauTieuHuy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    public function xoaTheoDoiHang(Request $request)
    {
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

        // $this->xuatHet();
        // $this->fixNgayXuatHet();
        // $this->fixCCXuatHet();
        // $this->fixSoContKhaiBao();3086
        // $this->gap();
        // $this->fixSuaXuatHang();



        // $this->khoiPhucXuatHang(5846, 6317);

        // $this->capNhatSealTruLui();
        // $nps = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->where('yeu_cau_niem_phong.ma_yeu_cau', 547)
        //     ->get();

        // foreach($nps as $np){
        //     $this->capNhatSealXuatHang($np->so_container, $np->so_seal_moi);
        // }

        // $this->khoiPhucXuatHang2('9713');
        // $this->kiemTraXuatHetHang('500522731850');


        // $this->fixTauTrenCont();
        $this->fixPhanQuyenBaoCao();
        // $this->fixContainer();
        // $this->fixPTVTXC();
        // $this->fixTauTrenCont();
        // $this->fixTauTheoDoiTruLui();
        // $this->fixPhanQuyenBaoCao();
        // $this->fixTienTrinh();
        // $this->fixTheoDoi();
        return redirect()->back();
    }

    public function kiemTraSealDung()
    {
        $ycs = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_niem_phong.ma_yeu_cau', '>=', 2000)
            ->where('yeu_cau_niem_phong.trang_thai', '2')
            ->orderBy('yeu_cau_niem_phong.ma_yeu_cau', 'desc')
            ->get();
        $containerDaCheck = [];
        $container = [];
        foreach ($ycs as $yc) {
            if (in_array($yc->so_container, $containerDaCheck)) {
                continue;
            }
            $seal = NiemPhong::where('so_container', $yc->so_container)->first()->so_seal ?? '';
            if ($yc->so_seal_moi != $seal) {
                $container[] = $yc->so_container;
            }
            $containerDaCheck[] = $yc->so_container;
        }
        dd($container);
    }

    public function fixNiemPhong()
    {
        $ycs = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', 2251)->get();
        foreach ($ycs as $yc) {
            $so_container_no_space = str_replace(' ', '', $yc->so_container); // Remove spaces
            $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);
            $nhapHang = $nhapHang = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.trang_thai', '2')
                ->whereIn('hang_trong_cont.so_container', [$so_container_no_space, $so_container_with_space])
                ->groupBy('nhap_hang.so_to_khai_nhap') // or any unique NhapHang column
                ->havingRaw('SUM(hang_trong_cont.so_luong) != 0')
                ->select('nhap_hang.*')
                ->first();

            $yc->phuong_tien_vt_nhap = $nhapHang->phuong_tien_vt_nhap;
            $yc->save();
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
    public function fixTienTrinh()
    {
        $previousTenCongViec = '';
        $previousSoToKhai = '';
        $tienTrinhs = TienTrinh::orderByDesc('ma_tien_trinh')->get();
        $count = 0;
        foreach ($tienTrinhs as $tienTrinh) {
            if ($tienTrinh->ten_cong_viec === $previousTenCongViec && $tienTrinh->so_to_khai_nhap === $previousSoToKhai) {
                $tienTrinh->delete();
            } else {
                $previousTenCongViec = $tienTrinh->ten_cong_viec;
                $previousSoToKhai = $tienTrinh->so_to_khai_nhap;
            }
            $count++;
        }
        dd($count);
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
        $nhapHang = NhapHangDaHuy::find(278);
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


    public function checkCont()
    {
        $ma_doanh_nghiep = '0202222686';
        $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('ma_doanh_nghiep', $ma_doanh_nghiep)
            ->where('trang_thai', '!=', 0)
            ->where('ngay_dang_ky', today())
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('ngay_dang_ky', today())
                        ->orWhereDate('ngay_dang_ky', today()->subDay());
                } else {
                    $query->whereDate('ngay_dang_ky', today());
                }
            })
            ->pluck('xuat_hang_cont.so_container')
            ->unique();

        $tauConts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', 'yeu_cau_tau_cont.ma_yeu_cau')
            ->where('ma_doanh_nghiep', $ma_doanh_nghiep)
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('ngay_yeu_cau', today())
                        ->orWhereDate('ngay_yeu_cau', today()->subDay());
                } else {
                    $query->whereDate('ngay_yeu_cau', today());
                }
            })->pluck('yeu_cau_tau_cont_chi_tiet.so_container_dich')
            ->unique();

        $containers = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->where('ma_doanh_nghiep', $ma_doanh_nghiep)
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('ngay_yeu_cau', today())
                        ->orWhereDate('ngay_yeu_cau', today()->subDay());
                } else {
                    $query->whereDate('ngay_yeu_cau', today());
                }
            })->pluck('yeu_cau_container_chi_tiet.so_container_dich')
            ->unique();

        $kiemTra = YeuCauKiemTra::join('yeu_cau_kiem_tra_chi_tiet', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau', 'yeu_cau_kiem_tra.ma_yeu_cau')
            ->where('ma_doanh_nghiep', $ma_doanh_nghiep)
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('ngay_yeu_cau', today())
                        ->orWhereDate('ngay_yeu_cau', today()->subDay());
                } else {
                    $query->whereDate('ngay_yeu_cau', today());
                }
            })->pluck('yeu_cau_kiem_tra_chi_tiet.so_container')
            ->unique();

        $containers = $xuatHangs
            ->merge($tauConts)
            ->merge($containers)
            ->merge($kiemTra)
            ->unique();
        dd($containers);
    }



    public function gap()
    {
        $ngay_dang_ky = '2025-02-23';
        $maTheoDoiList = TheoDoiHangHoa::whereDate('thoi_gian', $ngay_dang_ky)
            ->pluck('ma_theo_doi')
            ->sort()
            ->toArray();

        // If no records exist, return empty array
        if (empty($maTheoDoiList)) {
            return [];
        }

        // Find the missing numbers
        $min = min($maTheoDoiList);
        $max = max($maTheoDoiList);
        $fullRange = range($min, $max);
        $missingNumbers = array_diff($fullRange, $maTheoDoiList);

        // Convert missing numbers to an indexed array
        $missingNumbers = array_values($missingNumbers);

        dd($missingNumbers);
        return $missingNumbers; // This contains the list of missing numbers
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
    public function khoiPhucXuatHang($phieu_goc, $phieu_moi)
    {
        try {
            DB::beginTransaction();
            $xuatHangGoc = XuatHang::find($phieu_goc);
            $maCongChuc = $xuatHangGoc->ma_cong_chuc;
            $ngay_dang_ky = $xuatHangGoc->ngay_dang_ky;
            $ngay_xuat_canh = $xuatHangGoc->ngay_xuat_canh;
            XuatHang::find($phieu_goc)->delete();

            XuatHang::find($phieu_moi)->update([
                'so_to_khai_xuat' => $phieu_goc,
                'ma_cong_chuc' => $maCongChuc,
                'trang_thai' => 12,
                'ngay_dang_ky' => $ngay_dang_ky,
                'ngay_xuat_canh' => $ngay_xuat_canh
            ]);

            XuatHangCont::where('so_to_khai_xuat', $phieu_moi)->delete();

            // $thoi_gian = TheoDoiHangHoa::where('ma_yeu_cau', $phieu_moi)
            //     ->where('cong_viec', 1)
            //     ->first()->thoi_gian;

            // $isNgayDangKy = false;
            // if (date('Y-m-d', strtotime($thoi_gian)) == $ngay_dang_ky) {
            //     $isNgayDangKy = true;
            // }

            // //Cần phải check trước
            // if (!$isNgayDangKy) {
            //     // Get the list of existing ma_theo_doi values
            //     $maTheoDoiList = TheoDoiHangHoa::whereDate('thoi_gian', $ngay_dang_ky)
            //         ->pluck('ma_theo_doi')
            //         ->sort()
            //         ->toArray();

            //     // Find the missing numbers in the sequence
            //     $min = min($maTheoDoiList);
            //     $max = max($maTheoDoiList);
            //     $fullRange = range($min, $max);
            //     $missingNumbers = array_diff($fullRange, $maTheoDoiList);

            //     // Get the records that need updating
            //     $theoDoiHangHoas = TheoDoiHangHoa::where('ma_yeu_cau', $phieu_goc)
            //         ->where('cong_viec', 1)
            //         ->orderBy('ma_theo_doi')
            //         ->get();

            //     $missingNumbers = array_values($missingNumbers); // Reset array keys
            //     $index = 0;

            //     // Step 1: Temporarily set ma_theo_doi to a negative placeholder (ensuring it's never 0)
            //     foreach ($theoDoiHangHoas as $theoDoiHangHoa) {
            //         $theoDoiHangHoa->ma_theo_doi = -1 * ($theoDoiHangHoa->id + 1); // Adjusted placeholder
            //         $theoDoiHangHoa->save();
            //     }

            //     // Step 2: Assign missing numbers to these records
            //     foreach ($theoDoiHangHoas as $theoDoiHangHoa) {
            //         if (isset($missingNumbers[$index])) {
            //             $theoDoiHangHoa->ma_theo_doi = $missingNumbers[$index];
            //             $theoDoiHangHoa->save();
            //             $index++;
            //         } else {
            //             break; // No more missing numbers available
            //         }
            //     }
            // }

            TheoDoiTruLui::where('ma_yeu_cau', $phieu_moi)->where('cong_viec', 1)->delete();

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in fix: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function khoiPhucXuatHang2($so_to_khai_xuat)
    {
        try {
            DB::beginTransaction();
            $xuatHang = XuatHang::find($so_to_khai_xuat);
            XuatHang::find($so_to_khai_xuat)->update([
                'trang_thai' => 12,
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
    public function xoaTheoDoiTruLui(Request $request)
    {
        $this->checkLechSoLuong($request);
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
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon == 0) {
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
                $nhapHang->trang_thai = "4";
                $nhapHang->save();
            }
        }
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

    public function doiMatKhau(Request $request)
    {
        $taiKhoan = TaiKhoan::where('ten_dang_nhap', $request->ten_dang_nhap)->first();

        if (!$taiKhoan) {
            session()->flash('alert-danger', 'Không tìm thấy');
            return redirect()->back();
        }

        if ($request->mat_khau != '') {
            $taiKhoan->update([
                'mat_khau' => Hash::make($request->mat_khau)
            ]);
        }

        session()->flash('alert-success', 'OK');
        return redirect()->back();
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
    public function checkXuatHetHang(Request $request)
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
            for ($i = 1; $i <= 26; $i++) {
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
