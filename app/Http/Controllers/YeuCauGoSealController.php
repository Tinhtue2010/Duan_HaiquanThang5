<?php

namespace App\Http\Controllers;

use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\CongChuc;
use App\Models\Container;
use App\Models\DoanhNghiep;
use App\Models\NiemPhong;
use App\Models\Seal;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\YeuCauGoSeal;
use App\Models\YeuCauGoSealChiTiet;
use App\Models\XuatHang;
use App\Models\NhapHang;
use App\Models\TienTrinh;
use App\Models\YeuCauGoSealChiTietSua;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;


class YeuCauGoSealController extends Controller
{
    public function danhSachYeuCauGoSeal()
    {
        return view('quan-ly-kho.yeu-cau-go-seal.danh-sach-yeu-cau-go-seal');
    }

    public function themYeuCauGoSeal()
    {
        $soContainers = Container::with(['niemPhong', 'hangTrongCont.hangHoa'])
            ->get()
            ->map(function ($container) {
                return [
                    'so_container' => $container->so_container,
                    'phuong_tien_vt_nhap' => optional($container->niemPhong)->phuong_tien_vt_nhap,
                ];
            });


        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            return view('quan-ly-kho.yeu-cau-go-seal.them-yeu-cau-go-seal', data: compact('doanhNghiep', 'soContainers'));
        }
        return redirect()->back();
    }

    public function themYeuCauGoSealSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCau = YeuCauGoSeal::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'trang_thai' => '1',
                'ngay_yeu_cau' => now(),
                'is_niem_phong' => $request->is_niem_phong
            ]);

            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                YeuCauGoSealChiTiet::insert([
                    'so_container' => $row['so_container'],
                    'phuong_tien_vt_nhap' => $row['phuong_tien_vt_nhap'],
                    'so_seal_cu' => $row['so_seal'],
                    'so_seal_moi' => '',
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau
                ]);
                $this->themTienTrinh($row['so_container'], 'đã thêm', $yeuCau->ma_yeu_cau);
            }

            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu niêm phong thành công');
            return redirect('/danh-sach-yeu-cau-go-seal');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra, có thể là số container không tồn tại trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinYeuCauGoSeal($ma_yeu_cau)
    {
        $yeuCau = YeuCauGoSeal::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_go_seal.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        $chiTiets = YeuCauGoSealChiTiet::where('yeu_cau_go_seal_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
            ->get();

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChucHienTai = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $seals = Seal::where('seal.ngay_cap', today())
                ->where('seal.ma_cong_chuc', $congChucHienTai->ma_cong_chuc)
                ->get();
        } else {
            $congChucHienTai = null;
            $seals = [];
        }

        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        return view('quan-ly-kho.yeu-cau-go-seal.thong-tin-yeu-cau-go-seal', compact('yeuCau', 'chiTiets', 'doanhNghiep', 'congChucs', 'congChucHienTai', 'seals')); // Pass data to the view
    }

    public function suaYeuCauGoSeal($ma_yeu_cau)
    {
        $yeuCau = YeuCauGoSeal::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_go_seal.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        $chiTiets = YeuCauGoSealChiTiet::where('yeu_cau_go_seal_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
            ->get();

        $soContainers = Container::with(['niemPhong', 'hangTrongCont.hangHoa'])
            ->get()
            ->map(function ($container) {
                return [
                    'so_container' => $container->so_container,
                    'phuong_tien_vt_nhap' => optional($container->niemPhong)->phuong_tien_vt_nhap,
                ];
            });
        return view('quan-ly-kho.yeu-cau-go-seal.sua-yeu-cau-go-seal', compact('yeuCau', 'chiTiets', 'soContainers')); // Pass data to the view
    }

    public function suaYeuCauGoSealSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
            $rowsData = json_decode($request->rows_data, true);

            if ($yeuCau->trang_thai == 1) {
                YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
                foreach ($rowsData as $row) {
                    YeuCauGoSealChiTiet::insert([
                        'so_container' => $row['so_container'],
                        'phuong_tien_vt_nhap' => $row['phuong_tien_vt_nhap'],
                        'so_seal_cu' => $row['so_seal'],
                        'so_seal_moi' => '',
                        'ma_yeu_cau' => $request->ma_yeu_cau,
                    ]);
                }
            } else {
                $yeuCau->trang_thai = '3';
                $yeuCau->save();
                foreach ($rowsData as $row) {
                    YeuCauGoSealChiTietSua::insert([
                        'so_container' => $row['so_container'],
                        'phuong_tien_vt_nhap' => $row['phuong_tien_vt_nhap'],
                        'so_seal_cu' => $row['so_seal'],
                        'so_seal_moi' => '',
                        'ma_yeu_cau' => $request->ma_yeu_cau,
                    ]);
                }
            }

            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu niêm phong thành công');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-go-seal', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra, có thể là số container không tồn tại trong hệ thống');
            Log::error('Error in suaYeuCauGoSeal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
        $chiTiets = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        $chiTietSuas = YeuCauGoSealChiTietSua::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

        $containersChiTiet = $chiTiets->pluck('so_container');
        $containersChiTietSua = $chiTietSuas->pluck('so_container');

        $containerBiXoa = $containersChiTiet->diff($containersChiTietSua);
        $containerThemVao = $containersChiTietSua->diff($containersChiTiet);

        $chiTietBiXoa = $chiTiets->whereIn('so_container', $containerBiXoa);
        $chiTietThemVao = $chiTietSuas->whereIn('so_container', $containerThemVao);


        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-go-seal.xem-sua-yeu-cau-go-seal', compact('yeuCau', 'chiTiets', 'chiTietSuas', 'doanhNghiep', 'chiTietThemVao'));
    }


    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
            $chiTiets = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            $chiTietSuas = YeuCauGoSealChiTietSua::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

            $containersChiTiet = $chiTiets->pluck('so_container');
            $containersChiTietSua = $chiTietSuas->pluck('so_container');
            $containerBiXoa = $containersChiTiet->diff($containersChiTietSua);
            $containerThemVao = $containersChiTietSua->diff($containersChiTiet);
            $chiTietBiXoa = $chiTiets->whereIn('so_container', $containerBiXoa);
            $chiTietThemVao = $chiTietSuas->whereIn('so_container', $containerThemVao);


            foreach ($chiTiets as $chiTiet) {
                $this->huyTienTrinh($chiTiet->so_container, $yeuCau);
            }


            if (!empty($containerBiXoa)) {
                $this->quayNguocYeuCau($containerBiXoa, $yeuCau);
                YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                    ->whereIn('so_container', $containerBiXoa)
                    ->delete();
            }

            if ($yeuCau->is_niem_phong == 1) {
                foreach ($chiTietBiXoa as $chiTiet) {
                    $sealModel = Seal::find($chiTiet->so_seal_moi);
                    if ($sealModel) {
                        $sealModel->update(['trang_thai' => '0']);
                    }
                }

                $rowsData = json_decode($request->rows_data, true);
                if (!empty($rowsData)) {
                    foreach ($rowsData as $row) {
                        if ($row['loai_seal'] == "5" && $row['so_seal'] == null) {
                            session()->flash('alert-danger', 'Chưa chọn số seal cho seal định vị điện tử');
                            return redirect()->back();
                        }
                    }
                    $counts = collect($rowsData)->countBy('loai_seal');

                    foreach ($counts as $loai_seal => $count) {
                        if ($loai_seal == 1) {
                            $tenSeal = 'Seal dây cáp đồng';
                        } elseif ($loai_seal == 2) {
                            $tenSeal = 'Seal dây cáp thép';
                        } elseif ($loai_seal == 3) {
                            $tenSeal = 'Seal dây cáp container';
                        } elseif ($loai_seal == 4) {
                            $tenSeal = 'Seal dây nhựa dẹt';
                        } elseif ($loai_seal == 5) {
                            $tenSeal = 'Seal định vị điện tử';
                        }
                        $availableSeals = $this->getSealNhoNhat($loai_seal, $request->ma_cong_chuc, $count);
                        if (!$availableSeals) {
                            session()->flash('alert-danger', 'Không đủ số ' . $tenSeal . ' để cấp cho yêu cầu này');
                            return redirect()->back();
                        }
                    }
                }

                if (!empty($chiTietThemVao)) {
                    $rowMap = collect($rowsData)->keyBy('so_container');
                    foreach ($chiTietThemVao as $chiTietThem) {
                        $soContainer = $chiTietThem->so_container;
                        $row = $rowMap->get($soContainer);
                        if ($row) {
                            $chiTiet = YeuCauGoSealChiTiet::create($chiTietThem->toArray());
                            if ($row['so_container'] === $chiTiet->so_container) {
                                if ($row['loai_seal'] == "5") {
                                    $so_seal_moi = $row['so_seal'];
                                } else {
                                    $availableSeals = $this->getSealNhoNhat($row['loai_seal'], $request->ma_cong_chuc);
                                    $so_seal_moi = $availableSeals->shift();
                                }
                            }
                            $chiTiet->so_seal_moi = $so_seal_moi;
                            $chiTiet->save();

                            $this->themContainerMoi($chiTiet->so_container);
                            $suDungSeal = $this->suDungSeal($so_seal_moi, $chiTiet->so_container, $request->ma_cong_chuc);
                            if (!$suDungSeal) {
                                session()->flash('alert-danger', 'Seal này đã được sử dụng');
                                return redirect()->back();
                            }
                            $this->updateNiemPhong($so_seal_moi, $chiTiet->so_container, $request->ma_cong_chuc);
                            $this->capNhatSealXuatHang($chiTiet->so_container, $so_seal_moi);
                            $this->capNhatSealTruLui($chiTiet->so_container, $so_seal_moi);
                            $this->capNhatSealTheoDoi($chiTiet->so_container, $so_seal_moi);
                            $this->themTienTrinh($chiTiet->so_container, 'đã duyệt đề nghị sửa', $request->ma_yeu_cau);
                        } else {
                            session()->flash('alert-danger', 'Có lỗi xảy ra');
                            return redirect()->back();
                        }
                    }
                }
            } else {
                if (!empty($chiTietThemVao)) {
                    foreach ($chiTietThemVao as $chiTietThem) {
                        $chiTiet = YeuCauGoSealChiTiet::create($chiTietThem->toArray());
                    }
                }
            }

            foreach ($chiTietThemVao as $chiTiet) {
                $this->themTheoDoiTruLui($chiTiet->so_container, $yeuCau, $request->ma_cong_chuc, $chiTiet->so_seal_moi ?? '');
            }
            YeuCauGoSealChiTietSua::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            $yeuCau->trang_thai = '2';
            $yeuCau->save();

            DB::commit();
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-go-seal', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauGoSeal: ' . $e->getMessage());
            return redirect()->back();
        }
    }



    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';
        $yeuCau->save();
        YeuCauGoSealChiTietSua::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-go-seal', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function kiemTraSubmit($rowsData, $ma_cong_chuc)
    {
        foreach ($rowsData as $row) {
            if ($row['loai_seal'] == "5" && $row['so_seal'] == null) {
                session()->flash('alert-danger', 'Chưa chọn số seal cho seal định vị điện tử');
                return redirect()->back();
            }
        }
        $counts = collect($rowsData)->countBy('loai_seal');


        foreach ($counts as $loai_seal => $count) {
            if ($loai_seal == 1) {
                $tenSeal = 'Seal dây cáp đồng';
            } elseif ($loai_seal == 2) {
                $tenSeal = 'Seal dây cáp thép';
            } elseif ($loai_seal == 3) {
                $tenSeal = 'Seal dây cáp container';
            } elseif ($loai_seal == 4) {
                $tenSeal = 'Seal dây nhựa dẹt';
            } elseif ($loai_seal == 5) {
                $tenSeal = 'Seal định vị điện tử';
            }
            $availableSeals = $this->getSealNhoNhat($loai_seal, $ma_cong_chuc, $count);
            if (!$availableSeals) {
                session()->flash('alert-danger', 'Không đủ số ' . $tenSeal . ' để cấp cho yêu cầu này');
                return redirect()->back();
            }
        }
    }


    public function duyetYeuCauGoSeal(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);

        $rowsData = json_decode($request->rows_data, true);
        if ($yeuCau->is_niem_phong == 1) {
            // $this->kiemTraSubmit($rowsData, $request->ma_cong_chuc);
            foreach ($rowsData as $row) {
                if ($row['loai_seal'] == "5" && $row['so_seal'] == null) {
                    session()->flash('alert-danger', 'Chưa chọn số seal cho seal định vị điện tử');
                    return redirect()->back();
                }
            }
            $counts = collect($rowsData)->countBy('loai_seal');
            foreach ($counts as $loai_seal => $count) {
                if ($loai_seal == 1) {
                    $tenSeal = 'Seal dây cáp đồng';
                } elseif ($loai_seal == 2) {
                    $tenSeal = 'Seal dây cáp thép';
                } elseif ($loai_seal == 3) {
                    $tenSeal = 'Seal dây cáp container';
                } elseif ($loai_seal == 4) {
                    $tenSeal = 'Seal dây nhựa dẹt';
                } elseif ($loai_seal == 5) {
                    $tenSeal = 'Seal định vị điện tử';
                }
                $availableSeals = $this->getSealNhoNhat($loai_seal, $request->ma_cong_chuc, $count);
                if (!$availableSeals) {
                    session()->flash('alert-danger', 'Không đủ số ' . $tenSeal . ' để cấp cho yêu cầu này');
                    return redirect()->back();
                }
            }
        }
        try {
            $soSealUsed = collect();
            DB::beginTransaction();
            if ($yeuCau && $yeuCau->trang_thai == '1') {
                $chiTietYeuCaus = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
                if ($yeuCau->is_niem_phong == 1) {
                    $rowMap = collect($rowsData)->keyBy('so_container');
                    foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                        $soContainer = $chiTietYeuCau->so_container;
                        $this->themNiemPhong($soContainer);
                        $row = $rowMap->get($soContainer);
                        if ($row) {
                            if ($row['loai_seal'] != "5") {
                                $availableSeals = $this->getSealNhoNhat($row['loai_seal'], $request->ma_cong_chuc);
                                $so_seal_moi = $availableSeals->shift();
                            } else {
                                $so_seal_moi = $row['so_seal'];
                            }

                            while (true) {
                                if ($soSealUsed->contains($so_seal_moi)) {
                                    $this->suDungSeal($so_seal_moi, $chiTietYeuCau->so_container, $yeuCau->ma_cong_chuc);
                                    $availableSeals = $this->getSealNhoNhat($row['loai_seal'], $request->ma_cong_chuc);
                                    $so_seal_moi = $availableSeals->shift();
                                } else {
                                    $soSealUsed->push($so_seal_moi);
                                    $chiTietYeuCau->so_seal_moi = $so_seal_moi;
                                    $chiTietYeuCau->save();
                                    break;
                                }
                            }

                            $this->themContainerMoi($chiTietYeuCau->so_container);
                            $this->suDungSeal($so_seal_moi, $chiTietYeuCau->so_container, $request->ma_cong_chuc);

                            $this->updateNiemPhong($so_seal_moi, $chiTietYeuCau->so_container, $request->ma_cong_chuc);
                            $this->capNhatSealXuatHang($chiTietYeuCau->so_container, $so_seal_moi);
                            $this->capNhatSealTruLui($chiTietYeuCau->so_container, $so_seal_moi);
                            $this->capNhatSealTheoDoi($chiTietYeuCau->so_container, $so_seal_moi);
                        } else {
                            session()->flash('alert-danger', 'Có lỗi xảy ra');
                            return redirect()->back();
                        }
                    }
                    foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                        $this->updateNiemPhong($chiTietYeuCau->so_seal_moi, $chiTietYeuCau->so_container, $request->ma_cong_chuc);
                        $this->capNhatSealTruLui($chiTietYeuCau->so_container, $chiTietYeuCau->so_seal_moi);
                    }
                    $so_seals = $chiTietYeuCaus->pluck('so_seal_moi');
                    $duplicates = $so_seals->duplicates();
                    if ($duplicates->isNotEmpty()) {
                        session()->flash('alert-danger', 'Có lỗi xảy ra, hãy thử lại');
                        return redirect()->back();
                    }
                }
                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    $this->themTheoDoiTruLui($chiTietYeuCau->so_container, $yeuCau, $request->ma_cong_chuc, $chiTietYeuCau->so_seal_moi ?? '');
                }
                $yeuCau->ma_cong_chuc = $request->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = '2';
                $yeuCau->save();
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();


            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-go-seal');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetYeuCauGoSeal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function themNiemPhong($so_container)
    {
        $record = NiemPhong::where('so_container',  $so_container)->first();
        if (!$record) {
            NiemPhong::insert([
                'so_container' => $so_container,
                'so_seal' => '',
                'ngay_niem_phong' => now(),
                'phuong_tien_vt_nhap' => '',
            ]);
        }
    }
    private function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    public function themTienTrinh($so_container, $noi_dung, $ma_yeu_cau)
    {
        $so_container_no_space = str_replace(' ', '', $so_container);
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        $ma_cong_chuc = $this->getCongChucHienTai()->ma_cong_chuc ?? '';
        $so_to_khai_nhaps = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', 2)
            ->whereIn('hang_trong_cont.so_container', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()->pluck('so_to_khai_nhap')->toArray();

        $so_to_khai_nhaps_2 = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.trang_thai', 1)
            ->whereIn('hang_hoa.so_container_khai_bao', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()->pluck('so_to_khai_nhap')->toArray();

        $so_to_khai_nhaps = array_merge($so_to_khai_nhaps, $so_to_khai_nhaps_2);

        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $this->taoTienTrinh($so_to_khai_nhap,  "Công chức " . $noi_dung . " yêu cầu gỡ seal điện tử số " . $ma_yeu_cau, $ma_cong_chuc);
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $this->taoTienTrinh($so_to_khai_nhap, "Doanh nghiệp " . $noi_dung . " yêu cầu gỡ seal điện tử số " . $ma_yeu_cau, '');
            }
        }
    }
    public function taoTienTrinh($so_to_khai_nhap, $ten_cong_viec, $ma_cong_chuc)
    {
        TienTrinh::insert([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'ten_cong_viec' => $ten_cong_viec,
            'ngay_thuc_hien' => now(),
            'ma_cong_chuc' => $ma_cong_chuc
        ]);
    }


    public function capNhatSealXuatHang($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        XuatHang::where(function ($query) {
            if (now()->hour < 9) {
                $query->whereDate('ngay_dang_ky', today())
                    ->orWhereDate('ngay_dang_ky', today()->subDay());
            } else {
                $query->whereDate('ngay_dang_ky', today());
            }
        })
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->whereIn('xuat_hang_cont.so_container',  [$so_container_no_space, $so_container_with_space])
            ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $so_seal]);
    }

    public function capNhatSealTruLui($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', 'theo_doi_tru_lui.ma_theo_doi')
            ->whereIn('theo_doi_tru_lui_chi_tiet.so_container', [$so_container_no_space, $so_container_with_space])
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('ngay_them', today())
                        ->orWhereDate('ngay_them', today()->subDay());
                } else {
                    $query->whereDate('ngay_them', today());
                }
            })
            ->where('theo_doi_tru_lui.cong_viec', '!=', 1)
            ->update(['theo_doi_tru_lui_chi_tiet.so_seal' => $so_seal]);
    }
    public function capNhatSealTheoDoi($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiHangHoa::whereIn('so_container', [$so_container_no_space, $so_container_with_space])
            ->where(function ($query) {
                if (now()->hour < 9) {
                    $query->whereDate('thoi_gian', today())
                        ->orWhereDate('thoi_gian', today()->subDay());
                } else {
                    $query->whereDate('thoi_gian', today());
                }
            })
            ->where('cong_viec', '!=', 1)
            ->update(['so_seal' => $so_seal]);
    }

    public function huyYeuCauGoSeal(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "1") {
                $yeuCau->trang_thai = '0';
                $yeuCau->ghi_chu = $request->ghi_chu;
                $yeuCau->save();
            } elseif ($yeuCau->trang_thai == "2") {
                $this->huyYeuCauDaDuyet($request);
            } else {
                $this->duyetHuyYeuCau($request);
            }
            session()->flash('alert-success', 'Hủy yêu cầu thành công!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauGoSeal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '4';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();
        $so_containers = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_container');
        foreach ($so_containers as $so_container) {
            $this->huyTienTrinh($so_container, $yeuCau);
        }
    }
    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find(id: $request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
        }

        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-go-seal', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function quayNguocYeuCau($soContainerCanQuayNguoc, $yeuCau)
    {
        foreach ($soContainerCanQuayNguoc as $soContainer) {
            if ($yeuCau->is_niem_phong == 1) {
                $chiTiet = YeuCauGoSealChiTiet::where('so_container', $soContainer)
                    ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                    ->first();
                if ($chiTiet->so_seal_cu == null) {
                    $soSealCu = '';
                } else {
                    $soSealCu = $chiTiet->so_seal_cu;
                }
                $this->capNhatSealXuatHang($soContainer, $soSealCu);
                $this->capNhatSealTruLui($soContainer, $soSealCu);
                $this->capNhatSealTheoDoi($soContainer,  $soSealCu);
                $this->updateNiemPhong($soSealCu, $soContainer, $yeuCau->ma_cong_chuc);
            }
            $this->themTienTrinh($soContainer, 'đã duyệt đề nghị hủy', $yeuCau->ma_yeu_cau);
        }
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
        if ($yeuCau->is_niem_phong == 1) {
            $seals = YeuCauGoSealChiTiet::join('yeu_cau_go_seal', 'yeu_cau_go_seal.ma_yeu_cau', '=', 'yeu_cau_go_seal_chi_tiet.ma_yeu_cau')
                ->where('yeu_cau_go_seal.ma_yeu_cau', $request->ma_yeu_cau)
                ->pluck('yeu_cau_go_seal_chi_tiet.so_seal_moi');
            foreach ($seals as $seal) {
                if ($seal) {
                    $sealModel = Seal::find($seal);
                    if ($sealModel) {
                        $sealModel->update(['trang_thai' => '0']);
                    }
                }
            }
        }
        $soContainers = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_container');
        $this->quayNguocYeuCau($soContainers, $yeuCau);

        $yeuCau->trang_thai = '0';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function suaSealGoSeal(Request $request)
    {
        try {
            DB::beginTransaction();

            $yeuCau = YeuCauGoSeal::find($request->ma_yeu_cau);
            $sealMoi = YeuCauGoSealChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->where('so_container', $request->so_container)
                ->first();

            if (!Seal::find($request->so_seal)) {
                Seal::create([
                    'so_seal' => $request->so_seal,
                    'ma_cong_chuc' => $yeuCau->ma_cong_chuc,
                    'loai_seal' => $request->loai_seal ?? '',
                    'ngay_cap' => now(),
                    'trang_thai' => 1,
                ]);
            }

            $sealMoi->so_seal_moi = $request->so_seal;
            $sealMoi->save();

            $so_seal_moi = $request->so_seal;

            //Tim Seal đã dùng và chuyển thành seal hỏng
            $so_seal = NiemPhong::where('so_container', $request->so_container)->first()->so_seal ?? '';
            $seal = Seal::find($so_seal);
            if ($seal && $seal->loai_seal != 5) {
                $seal->update(['trang_thai' => 2]);
            }

            $this->updateNiemPhong($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);
            $this->capNhatSealXuatHang($request->so_container, $so_seal_moi);
            $this->capNhatSealTruLui($request->so_container, $so_seal_moi);
            $this->capNhatSealTheoDoi($request->so_container, $so_seal_moi);

            session()->flash('alert-success', 'Sửa seal niêm phong thành công');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in suaSealGoSeal: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function themContainerMoi($so_container)
    {
        if (!Container::find($so_container)) {
            Container::insert([
                'so_container' => $so_container,
            ]);
        }
    }

    public function getSealNhoNhat($loai_seal, $ma_cong_chuc, $count = 1)
    {
        $soSeals = Seal::where('seal.trang_thai', 0)
            ->where('ma_cong_chuc', $ma_cong_chuc)
            ->where('loai_seal', $loai_seal)
            ->pluck('so_seal');
        $availableSeals = collect($soSeals)->sort()->values();
        if ($availableSeals->count() < $count) {
            return false;
        }
        return $availableSeals;
    }

    public function suDungSeal($so_seal_moi, $so_container, $ma_cong_chuc)
    {
        $seal = Seal::find($so_seal_moi);
        if ($seal) {
            if ($seal->trang_thai == 1) {
                return false;
            }

            if ($seal->loai_seal == 5) {
                $seal->update([
                    'ngay_su_dung' => now(),
                    'so_container' => $so_container,
                ]);
            } else {
                $seal->update([
                    'trang_thai' => 1,
                    'ngay_su_dung' => now(),
                    'so_container' => $so_container,
                ]);
            }
        }
        return true;
    }
    public function updateNiemPhong($so_seal, $so_container, $ma_cong_chuc)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        NiemPhong::whereIn('so_container', [$so_container_no_space, $so_container_with_space])
            ->update([
                'so_seal' => $so_seal,
                'ngay_niem_phong' => now(),
                'ma_cong_chuc' => $ma_cong_chuc,
            ]);
    }
    public function huyTienTrinh($so_container, $yeuCau)
    {
        $so_container_no_space = str_replace(' ', '', $so_container);
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        $so_to_khai_nhaps = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', 2)
            ->whereIn('hang_trong_cont.so_container', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()
            ->pluck('so_to_khai_nhap')
            ->toArray();

        $so_to_khai_nhaps_2 = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.trang_thai', 1)
            ->whereIn('hang_hoa.so_container_khai_bao', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()->pluck('so_to_khai_nhap')->toArray();

        $so_to_khai_nhaps = array_merge($so_to_khai_nhaps, $so_to_khai_nhaps_2);
        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            TheoDoiHangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 9)
                ->delete();
            TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 9)
                ->delete();
        }
    }

    public function getTauCuaContainer($soContainer)
    {
        $containers = NiemPhong::where('so_container', $soContainer)
            ->select('phuong_tien_vt_nhap', 'so_container')
            ->toArray();
        return $containers;
    }
    public function getYeuCauGoSeal(Request $request)
    {
        if ($request->ajax()) {
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $data = YeuCauGoSeal::join('doanh_nghiep', 'yeu_cau_go_seal.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->select(
                        'doanh_nghiep.*',
                        'yeu_cau_go_seal.*',
                    )
                    ->distinct()  // Ensure unique rows
                    ->orderBy('ma_yeu_cau', 'desc')
                    ->get();
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
                $data = YeuCauGoSeal::join('doanh_nghiep', 'yeu_cau_go_seal.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->where('yeu_cau_go_seal.ma_doanh_nghiep', $maDoanhNghiep)
                    ->select(
                        'doanh_nghiep.*',
                        'yeu_cau_go_seal.*',
                    )
                    ->distinct()  // Ensure unique rows
                    ->orderBy('ma_yeu_cau', 'desc')
                    ->get();
            }

            return DataTables::of($data)
                ->addIndexColumn() // Adds auto-incrementing index
                ->editColumn('ngay_yeu_cau', function ($yeuCau) {
                    return Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($yeuCau) {
                    return $yeuCau->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('action', function ($yeuCau) {
                    return '<a href="' . route('quan-ly-kho.thong-tin-yeu-cau-go-seal', $yeuCau->ma_yeu_cau) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('trang_thai', function ($yeuCau) {
                    $status = trim($yeuCau->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đang chờ duyệt', 'class' => 'text-primary'],
                        '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Doanh nghiệp đề nghị sửa yêu cầu', 'class' => 'text-warning'],
                        '4' => ['text' => 'Doanh nghiệp đề nghị hủy yêu cầu', 'class' => 'text-danger'],
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
    
    public function themTheoDoiTruLui($so_container, $yeuCau, $ma_cong_chuc, $so_seal_moi)
    {
        $so_container_no_space = str_replace(' ', '', $so_container);
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        $so_to_khai_nhaps = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', 2)
            ->whereIn('hang_trong_cont.so_container', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()
            ->pluck('so_to_khai_nhap')
            ->toArray();

        foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
            $theoDoiTruLui = TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)->count();
            if ($theoDoiTruLui > 1) {
                continue;
            }
            $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
                ->get();

            $theoDoi = TheoDoiTruLui::create([
                'so_to_khai_nhap' => $so_to_khai_nhap,
                'so_ptvt_nuoc_ngoai' => '',
                'ngay_them' => now(),
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
                        'so_seal' => $so_seal_moi ?? '',
                        'phuong_tien_vt_nhap' => NiemPhong::where('so_container', $hangHoa->so_container)->first()->phuong_tien_vt_nhap ?? ''
                    ]
                );
                $ptvtChoHang = NiemPhong::where('so_container',  $hangHoa->so_container)->first()->phuong_tien_vt_nhap ?? '';
                TheoDoiHangHoa::insert([
                    'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                    'ma_hang'  => $hangHoa->ma_hang,
                    'thoi_gian'  => now(),
                    'so_luong_xuat'  => $hangHoa->so_luong,
                    'so_luong_ton'  => $hangHoa->so_luong,
                    'phuong_tien_cho_hang' => $ptvtChoHang,
                    'cong_viec' => 9,
                    'phuong_tien_nhan_hang' => '',
                    'so_container' => $hangHoa->so_container,
                    'so_seal' => $so_seal_moi ?? '',
                    'ma_cong_chuc' => $ma_cong_chuc ?? '',
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                ]);
            }
        }


        $so_to_khai_nhaps_2 = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.trang_thai', 1)
            ->whereIn('hang_hoa.so_container_khai_bao', [$so_container_no_space, $so_container_with_space])
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get()->pluck('so_to_khai_nhap')->toArray();

        foreach ($so_to_khai_nhaps_2 as $so_to_khai_nhap) {
            $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
                ->get();

            $theoDoi = TheoDoiTruLui::create([
                'so_to_khai_nhap' => $so_to_khai_nhap,
                'so_ptvt_nuoc_ngoai' => '',
                'ngay_them' => now(),
                'cong_viec' => 9,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
            foreach ($hangHoas as $hangHoa) {
                TheoDoiTruLuiChiTiet::insert(
                    [
                        'ten_hang' => $hangHoa->ten_hang,
                        'so_luong_xuat' => 0,
                        'so_luong_chua_xuat' => $hangHoa->so_luong_khai_bao,
                        'ma_theo_doi' => $theoDoi->ma_theo_doi,
                        'so_container' => $hangHoa->so_container_khai_bao,
                        'so_seal' => $so_seal_moi ?? '',
                        'phuong_tien_vt_nhap' => $hangHoa->ptvt_ban_dau ?? ''
                    ]
                );
                TheoDoiHangHoa::insert([
                    'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                    'ma_hang'  => $hangHoa->ma_hang,
                    'thoi_gian'  => now(),
                    'so_luong_xuat'  => $hangHoa->so_luong_khai_bao,
                    'so_luong_ton'  => $hangHoa->so_luong_khai_bao,
                    'phuong_tien_cho_hang' => $hangHoa->ptvt_ban_dau ?? '',
                    'cong_viec' => 9,
                    'phuong_tien_nhan_hang' => '',
                    'so_container' => $hangHoa->so_container_khai_bao,
                    'so_seal' => $so_seal_moi ?? '',
                    'ma_cong_chuc' => $ma_cong_chuc ?? '',
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                ]);
            }
        }
    }
}
