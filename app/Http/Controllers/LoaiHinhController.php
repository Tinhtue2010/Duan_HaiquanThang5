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
use App\Models\YeuCauHangVeKhoChiTiet;
use App\Models\XuatCanh;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\YeuCauGoSeal;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauGoSealChiTiet;
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
        $this->checkLechTau();
    }

    public function action4(Request $request)
    {
        $this->normalizeContainer();
    }
    public function action5(Request $request)
    {
        $stt = $this->xuatHet();
        $this->kiemTraDungXuatHet2();
        $this->fixNgayXuatHet();
        $this->fixCCXuatHet();
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
        return true;
    }
    public function action10(Request $request)
    {
        $status = $this->niemPhongLai($request);
        if ($status) {
            session()->flash('alert-success', 'Niêm phong lại thành công!');
        } else {
            session()->flash('alert-success', 'Có lỗi xảy ra');
        }
        return redirect()->back();
    }
    public function action11(Request $request)
    {
        $status = $this->suaContainerBanDau($request);
        if ($status) {
            session()->flash('alert-success', 'Niêm phong lại thành công!');
        } else {
            session()->flash('alert-success', 'Có lỗi xảy ra');
        }
        return redirect()->back();
    }
    public function action12(Request $request)
    {
        $status = $this->suaTauBanDau($request);
        if ($status) {
            session()->flash('alert-success', 'Sửa tàu ban đầu thành công!');
        } else {
            session()->flash('alert-success', 'Có lỗi xảy ra');
        }
        return redirect()->back();
    }

    public function niemPhongLai(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $so_to_khai_nhap = $request->so_to_khai_nhap;
                $ngay_niem_phong = $this->formatDateToYMD($request->ngay_niem_phong);
                $so_seal = $request->so_seal;

                XuatHangCont::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
                    ->where('xuat_hang.ngay_dang_ky', $ngay_niem_phong)
                    ->update([
                        'xuat_hang_cont.so_seal_cuoi_ngay' => $so_seal,
                    ]);
                TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui.ma_theo_doi', '=', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi')
                    ->where('theo_doi_tru_lui.so_to_khai_nhap', $so_to_khai_nhap)
                    ->whereDate('theo_doi_tru_lui.ngay_them', $ngay_niem_phong)
                    ->update([
                        'theo_doi_tru_lui_chi_tiet.so_seal' => $so_seal,
                    ]);
                TheoDoiHangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)
                    ->whereDate('thoi_gian', $ngay_niem_phong)
                    ->update([
                        'so_seal' => $so_seal,
                    ]);
                DB::commit();
                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error in niemPhongLai: ' . $e->getMessage());
            return false;
        }
    }

    public function niemPhongLai2()
    {
        try {
            return DB::transaction(function () {
                $so_to_khai_nhaps = ["500523324530"];
                $ma_yeu_caus = [621, 664, 672, 687];

                foreach ($ma_yeu_caus as $ma_yeu_cau) {
                    $yeuCauNiemPhongChiTiets = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                        ->get();
                    $yeuCauNiemPhong = YeuCauNiemPhong::find($ma_yeu_cau);
                    foreach ($yeuCauNiemPhongChiTiets as $chiTiet) {
                        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
                            XuatHangCont::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                                ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
                                ->whereDate('xuat_hang.ngay_dang_ky', $yeuCauNiemPhong->ngay_yeu_cau)
                                ->where('xuat_hang_cont.so_container', $chiTiet->so_container)
                                ->update([
                                    'xuat_hang_cont.so_seal_cuoi_ngay' => $chiTiet->so_seal_moi,
                                ]);
                        }
                    }
                }

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error in niemPhongLai: ' . $e->getMessage());
            return false;
        }
    }
    public function suaContainerBanDau(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $so_to_khai_nhap = $request->so_to_khai_nhap;
                $so_container_moi = $request->so_container_moi;
                $so_container_cu = $request->so_container_cu;

                XuatHangCont::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_container', $so_container_cu)
                    ->update([
                        'xuat_hang_cont.so_container' => $so_container_moi,
                    ]);
                NhapHang::find($so_to_khai_nhap)->update([
                    'container_ban_dau' => $so_container_moi,
                ]);
                HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)
                    ->where('so_container_khai_bao', $so_container_cu)
                    ->update([
                        'so_container_khai_bao' => $so_container_moi,
                    ]);
                HangTrongCont::join('hang_hoa', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $so_container_cu)
                    ->where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                    ->update([
                        'hang_trong_cont.so_container' => $so_container_moi,
                    ]);
                DB::commit();
                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error in suaContainerBanDau: ' . $e->getMessage());
            return false;
        }
    }
    public function suaTauBanDau(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $so_to_khai_nhap = $request->so_to_khai_nhap;
                $so_tau_moi = $request->so_tau_moi;
                $nhapHang = NhapHang::find($so_to_khai_nhap);

                XuatHangCont::where('so_to_khai_nhap', $so_to_khai_nhap)
                    ->where('phuong_tien_vt_nhap', $nhapHang->ptvt_ban_dau)
                    ->update([
                        'phuong_tien_vt_nhap' => $so_tau_moi,
                    ]);

                NhapHang::find($so_to_khai_nhap)->update([
                    'phuong_tien_vt_nhap' => $so_tau_moi,
                    'ptvt_ban_dau' => $so_tau_moi,
                ]);

                NiemPhong::where('so_container', $nhapHang->container_ban_dau)
                    ->update([
                        'phuong_tien_vt_nhap' => $so_tau_moi,
                    ]);

                DB::commit();
                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error in suaTauBanDau: ' . $e->getMessage());
            return false;
        }
    }
    private function formatDateToYMD($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
    }


    //Đổi chỗ 2 phiếu trừ lùi
    public function switchTruLui()
    {
        $ma_theo_doi_1 = 199907;
        $ma_theo_doi_2 = 199908;

        $truLui1 = TheoDoiTruLui::find($ma_theo_doi_1);
        $truLuiChiTiet1 = TheoDoiTruLuiChiTiet::where('ma_theo_doi', $ma_theo_doi_1)->get();

        $truLui2 = TheoDoiTruLui::find($ma_theo_doi_2);
        $truLuiChiTiet2 = TheoDoiTruLuiChiTiet::where('ma_theo_doi', $ma_theo_doi_2)->get();

        // Switch using -1 as intermediate to avoid duplicate key errors
        DB::transaction(function () use ($truLui1, $truLuiChiTiet1, $truLui2, $truLuiChiTiet2, $ma_theo_doi_1, $ma_theo_doi_2) {
            $truLui1->ma_theo_doi = -1;
            $truLui1->save();
            foreach ($truLuiChiTiet1 as $chiTiet) {
                $chiTiet->ma_theo_doi = -1;
                $chiTiet->save();
            }

            $truLui2->ma_theo_doi = $ma_theo_doi_1;
            $truLui2->save();
            foreach ($truLuiChiTiet2 as $chiTiet) {
                $chiTiet->ma_theo_doi = $ma_theo_doi_1;
                $chiTiet->save();
            }

            $truLui1->ma_theo_doi = $ma_theo_doi_2;
            $truLui1->save();
            foreach ($truLuiChiTiet1 as $chiTiet) {
                $chiTiet->ma_theo_doi = $ma_theo_doi_2;
                $chiTiet->save();
            }
        });
    }


    //Nếu YC gỡ seal lỗi không tạo phiếu trừ lùi
    public function themTheoDoiTruLuiGoSeal()
    {
        $ma_yeu_cau = 479;
        $so_to_khai_nhap = '500583316850';

        $yeuCau = YeuCauGoSeal::find($ma_yeu_cau);
        $nhapHang = NhapHang::find($so_to_khai_nhap);

        $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();

        $theoDoi = TheoDoiTruLui::create([
            'ma_theo_doi' => -10,
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'so_ptvt_nuoc_ngoai' => '',
            'ngay_them' => $yeuCau->ngay_yeu_cau,
            'cong_viec' => 9,
            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
        ]);
        foreach ($hangHoas as $hangHoa) {
            $so_seal_moi = YeuCauGoSealChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                ->where('so_container', $hangHoa->so_container_khai_bao)
                ->first()
                ->so_seal_moi ?? '';
            TheoDoiTruLuiChiTiet::insert(
                [
                    'ma_theo_doi' => -10,
                    'ten_hang' => $hangHoa->ten_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_chua_xuat' => $hangHoa->so_luong_khai_bao,
                    'so_container' => $hangHoa->so_container_khai_bao,
                    'so_seal' => $so_seal_moi ?? '',
                    'phuong_tien_vt_nhap' => $nhapHang->ptvt_ban_dau ?? ''
                ]
            );
            TheoDoiHangHoa::insert([
                'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                'ma_hang'  => $hangHoa->ma_hang,
                'thoi_gian'  => $yeuCau->ngay_yeu_cau,
                'so_luong_xuat'  => $hangHoa->so_luong_khai_bao,
                'so_luong_ton'  => $hangHoa->so_luong_khai_bao,
                'phuong_tien_cho_hang' => $nhapHang->ptvt_ban_dau ?? '',
                'cong_viec' => 9,
                'phuong_tien_nhan_hang' => '',
                'so_container' => $hangHoa->so_container_khai_bao,
                'so_seal' => $so_seal_moi ?? '',
                'ma_cong_chuc' => $ma_cong_chuc ?? '',
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
        }
    }

    //Cho tên container về dạng tên chuẩn
    public function normalizeContainer()
    {
        $nhapHangs = NhapHang::where('trang_thai', 2)->get();
        $so_to_khai_nhaps = $nhapHangs->pluck('so_to_khai_nhap');
        $hangHoas = HangHoa::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        $hang_trong_conts = HangTrongCont::whereIn('ma_hang', $hangHoas->pluck('ma_hang'))->get();

        $niem_phongs = NiemPhong::whereIn('so_container', $hang_trong_conts->pluck('so_container'))->get();
        foreach ($niem_phongs as $niem_phong) {
            $normalized_container = preg_replace('/\s+/', '', $niem_phong->so_container);
            $normalized_phuong_tien = preg_replace('/[\s\-]+/', '', $niem_phong->phuong_tien_vt_nhap);
            $niem_phong->update([
                'phuong_tien_vt_nhap' => $normalized_phuong_tien,
            ]);

            NiemPhong::where('so_container', $normalized_container)
                ->where('ma_niem_phong', '!=', $niem_phong->ma_niem_phong)
                ->delete();
        }

        $containers = Container::all();
        foreach ($containers as $container) {
            $newSoContainer = preg_replace('/\s+/', '', $container->so_container);
            if ($newSoContainer === $container->so_container) {
                continue;
            }
            $exists = Container::where('so_container', $newSoContainer)->exists();
            if ($exists) {
                continue;
            }
            Container::where('so_container', $container->so_container)
                ->update(['so_container' => $newSoContainer]);
        }

        $theo_doi_hang_hoas = TheoDoiHangHoa::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        foreach ($theo_doi_hang_hoas as $theo_doi_hang_hoa) {
            $theo_doi_hang_hoa->update([
                'so_container' => preg_replace('/\s+/', '', $theo_doi_hang_hoa->so_container),
            ]);
        }

        $theo_doi_tru_lui_chi_tiets = TheoDoiTruLuiChiTiet::join('theo_doi_tru_lui', 'theo_doi_tru_lui.ma_theo_doi', '=', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi')
            ->whereIn('theo_doi_tru_lui.so_to_khai_nhap', $so_to_khai_nhaps)
            ->get();

        foreach ($theo_doi_tru_lui_chi_tiets as $theo_doi_tru_lui_chi_tiet) {
            $theo_doi_tru_lui_chi_tiet->update([
                'so_container' => preg_replace('/\s+/', '', $theo_doi_tru_lui_chi_tiet->so_container),
                'phuong_tien_vt_nhap' => str_replace('/[\s\-]+/', '', $theo_doi_tru_lui_chi_tiet->phuong_tien_vt_nhap),
            ]);
        }

        $xuat_hang_conts = XuatHangCont::whereIn('so_to_khai_nhap', $so_to_khai_nhaps)->get();
        foreach ($xuat_hang_conts as $xuat_hang_cont) {
            $xuat_hang_cont->update([
                'so_container' => preg_replace('/\s+/', '', $xuat_hang_cont->so_container),
                'phuong_tien_vt_nhap' => str_replace('/[\s\-]+/', '', $xuat_hang_cont->phuong_tien_vt_nhap),
            ]);
        }

        foreach ($nhapHangs as $nhapHang) {
            $nhapHang->update([
                'container_ban_dau' => preg_replace('/\s+/', '', $nhapHang->container_ban_dau),
                'phuong_tien_vt_nhap' => str_replace('/[\s\-]+/', '', $nhapHang->phuong_tien_vt_nhap),
                'ptvt_ban_dau' => str_replace('/[\s\-]+/', '', $nhapHang->ptvt_ban_dau),
            ]);
        }
        foreach ($hangHoas as $hangHoa) {
            $hangHoa->update([
                'so_container_khai_bao' => preg_replace('/\s+/', '', $hangHoa->so_container_khai_bao),
            ]);
        }
        foreach ($hang_trong_conts as $hang_trong_cont) {
            $hang_trong_cont->update([
                'so_container' => preg_replace('/\s+/', '', $hang_trong_cont->so_container),
            ]);
        }
    }

    //Kiểm tra xem số tàu của container có khác số tàu của tờ khai nhập không
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
        //Nếu muốn tự động sửa thì chạy đoạn dưới
        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            $nhapHang = NhapHang::find($so_to_khai_nhap);
            $container = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
                ->pluck('hang_trong_cont.so_container')
                ->toArray();
            if (count($container) > 2 || empty($container)) {
                continue;
            } else {
                $nhapHangs = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->where('hang_trong_cont.so_container', $container[0])
                    ->where('nhap_hang.trang_thai', 2)
                    ->orderBy('nhap_hang.updated_at', 'desc')
                    ->get();
                $newestNhapHang = $nhapHangs->first();
                foreach ($nhapHangs as $nhapHang) {
                    $nhapHang->update([
                        'phuong_tien_vt_nhap' => $newestNhapHang->phuong_tien_vt_nhap,
                    ]);
                    $soContainers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                        ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
                        ->pluck('hang_trong_cont.so_container')
                        ->toArray();
                    foreach ($soContainers as $soContainer) {
                        NiemPhong::where('so_container', $soContainer)
                            ->update(['phuong_tien_vt_nhap' => $newestNhapHang->phuong_tien_vt_nhap]);
                    }
                }
            }
        }
    }

    //Fix xem tờ khai nào đã hết hàng nhưng chưa chuyển thành xuất hết (Thường lẫn với xuất hàng chờ duyệt)
    public function kiemTraDungXuatHet2()
    {
        $list = [];
        $nhapHangs = NhapHang::where('trang_thai', 4)->whereNull('ngay_xuat_het')->get();
        foreach ($nhapHangs as $nhapHang) {
            $xuatHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->whereNotIn('xuat_hang.trang_thai', [0, 1, 7, 8, 9, 10])
                ->orderBy('xuat_hang.created_at', 'desc')
                ->first();
            if ($xuatHang && $xuatHang->ma_cong_chuc != null) {
                if ($xuatHang->ma_cong_chuc != $nhapHang->ma_cong_chuc_ban_giao) {
                    $list[] = $nhapHang->so_to_khai_nhap;
                    $nhapHang->update([
                        // 'ma_cong_chuc_ban_giao' => $xuatHang->ma_cong_chuc,
                        'ngay_xuat_het' => $xuatHang->ngay_dang_ky,
                    ]);
                }
            }
        }
        dd($list);
    }


    //Khôi phục tờ khai nhập hàng đã hủy (Chưa update từ lâu)
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
                array_push($arr, $nhapHang->so_to_khai_nhap);
            }
        }
        $excludeValues = [];

        $arr = array_filter($arr, function ($value) use ($excludeValues) {
            return !in_array($value, $excludeValues);
        });

        $arr = array_values($arr);

        dd($arr);
    }
    //Khi thêm báo cáo mới thì phân quyền nhanh ở đây 
    public function fixPhanQuyenBaoCao()
    {
        $congChucs = CongChuc::all();
        foreach ($congChucs as $congChuc) {
            for ($i = 1; $i <= 35; $i++) {
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
