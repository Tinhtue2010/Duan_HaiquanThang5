<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\PTVTXuatCanh;
use App\Models\Seal;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauContainerHangHoa;
use App\Models\YeuCauTauContChiTiet;
use App\Models\YeuCauTauContHangHoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class QuanLyKhoController extends Controller
{
    public function traCuuContainerIndex()
    {
        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->select('container.*', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
            ->groupBy('container.so_container', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->orderByRaw('total_so_luong DESC')
            ->get();

        return view('quan-ly-kho.tra-cuu-container', compact('containers'));
    }
    public function danhSachToKhaiTrongContainer($so_container)
    {
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->where('hang_trong_cont.so_container', $so_container)
            ->select('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap', DB::raw('SUM(hang_trong_cont.so_luong) as total_so_luong'))
            ->groupBy('nhap_hang.ma_doanh_nghiep', 'nhap_hang.so_to_khai_nhap')
            ->get();

        $container = Container::find($so_container);
        return view('quan-ly-kho.thong-tin-container', compact(['nhapHangs', 'container']));
    }

    public function themContainer(Request $request)
    {
        if (Container::where('so_container', $request->so_container)->exists()) {
            session()->flash('alert-danger', 'Số container đã tồn tại.');
            return redirect('/tra-cuu-container');
        }

        Container::create([
            'so_container' => $request->so_container,
        ]);

        $this->xuLySeal($request->so_container);
        session()->flash('alert-success', 'Thêm container mới thành công');
        return redirect('/tra-cuu-container');
    }

    private function xuLySeal($so_container)
    {
        $record = NiemPhong::where('so_container', $so_container)->first();

        if (!$record) {
            NiemPhong::insert([
                'so_container' => $so_container,
                'so_seal' => '',
                'ngay_niem_phong' => now(),
            ]);
        }
    }

    public function getToKhaiItems(Request $request)
    {
        try {
            $so_to_khai_nhap = $request->so_to_khai_nhap;
            $phuong_tien_vt_nhap = NhapHang::find($so_to_khai_nhap)->phuong_tien_vt_nhap;

            if ($request->ma_yeu_cau && $request->loai == 'tau_cont' && YeuCauTauCont::find($request->ma_yeu_cau)->trang_thai == 2) {
                $yeuCauChiTiets = YeuCauTauContHangHoa::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_chi_tiet', '=', 'yeu_cau_tau_cont_hang_hoa.ma_chi_tiet')
                    ->where('yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', $request->ma_yeu_cau)
                    ->pluck('yeu_cau_tau_cont_hang_hoa.so_container_cu', 'yeu_cau_tau_cont_hang_hoa.ma_hang_cont');

                $phuong_tien_vt_nhap = YeuCauTauContChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->first()->tau_goc;
                $hangHoas = HangHoa::where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->get();

                $hangHoas = $hangHoas->map(function ($item) use ($yeuCauChiTiets) {
                    if (isset($yeuCauChiTiets[$item->ma_hang_cont])) {
                        $item->so_container = $yeuCauChiTiets[$item->ma_hang_cont];
                    }
                    return $item;
                });

                $containers = Container::all()->pluck('so_container')->toArray();
            } elseif ($request->ma_yeu_cau && $request->loai == 'container' && YeuCauChuyenContainer::find($request->ma_yeu_cau)->trang_thai == 2) {
                $yeuCauChiTiets = YeuCauContainerHangHoa::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_chi_tiet', '=', 'yeu_cau_container_hang_hoa.ma_chi_tiet')
                    ->where('yeu_cau_container_chi_tiet.ma_yeu_cau', $request->ma_yeu_cau)
                    ->pluck('yeu_cau_container_hang_hoa.so_container_cu', 'yeu_cau_container_hang_hoa.ma_hang_cont');

                $hangHoas = HangHoa::where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->get();
                $hangHoas = $hangHoas->map(function ($item) use ($yeuCauChiTiets) {
                    if (isset($yeuCauChiTiets[$item->ma_hang_cont])) {
                        $item->so_container = $yeuCauChiTiets[$item->ma_hang_cont];
                    }
                    return $item;
                });
            } else {
                $hangHoas = HangHoa::where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->get();
            }
            $containers = Container::all()->pluck('so_container')->toArray();



            if ($hangHoas) {
                return response()->json(['hangHoas' => $hangHoas, 'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap, 'containers' => $containers, 'so_to_khai_nhap' => $so_to_khai_nhap]);
            }
            Log::warning('No data found for so_to_khai_nhap: ' . $so_to_khai_nhap);
            return response()->json(['message' => 'No data found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in getToKhaiItems: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    ///
    public function getSoLuongTrongContainer($soContainerMoi)
    {
        try {
            $total_so_luong = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.trang_thai', '2')
                ->where('so_container', $soContainerMoi)
                ->distinct()
                ->sum('so_luong');

            if ($total_so_luong) {
                return response()->json(['total_so_luong' => $total_so_luong]);
            }
            Log::warning('No data found for getSoLuongTrongContainer');
            return response()->json(['message' => 'No data found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in getToKhaiItems: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'An error occurred'], 500);
        }
    }


    public function getToKhaiItems2(Request $request)
    {
        try {
            $so_to_khai_nhap = $request->so_to_khai_nhap;
            $nhapHang = NhapHang::find($so_to_khai_nhap);
            if ($nhapHang) {
                $containers = $nhapHang->hangHoa
                    ->flatMap(fn($hangHoa) => $hangHoa->hangTrongCont->where('so_luong', '>', 0)->pluck('so_container'))
                    ->unique()
                    ->implode(';');

                $nhapHang->so_container = $containers;
                $containers = $nhapHang->hangHoa
                    ->flatMap(fn($hangHoa) => $hangHoa->hangTrongCont->where('so_luong', '>', 0)
                    ->pluck('so_container'))
                    ->unique()
                    ->toArray();
                return response()->json(['data' => $nhapHang,'containers'=> $containers]);
            }

            Log::warning('No data found for so_to_khai_nhap: ' . $so_to_khai_nhap);
            return response()->json(['message' => 'No data found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in getToKhaiItems: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    public function getToKhaiKiemTra(Request $request)
    {
        try {
            $so_container = $request->so_container;

            $toKhai = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_trong_cont.so_container', $so_container)
                ->get();

            if ($toKhai) {
                $containers = $toKhai->hangHoa->flatMap(
                    fn($hangHoa) =>
                    optional($hangHoa->hangTrongCont)->so_container ? [$hangHoa->hangTrongCont->so_container] : []
                )->unique()->implode(';');
                $toKhai->so_container = $containers;
                return response()->json(['data' => $toKhai]);
            }

            Log::warning('No data found for so_container: ' . $so_container);
            return response()->json(['message' => 'No data found'], 404);
        } catch (\Exception $e) {
            Log::error('Error in getToKhaiItems: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    // public function getToKhaiTrongCont(Request $request)
    // {
    //     try {
    //         $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
    //             ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
    //             ->where('hang_trong_cont.so_container', $request->soContainerMoi)
    //             ->whereNotIn('nhap_hang.trang_thai', ['6', '5', '4', '7'])
    //             ->distinct()
    //             ->pluck('nhap_hang.so_to_khai_nhap')
    //             ->implode('</br>');


    //         $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $request->soToKhai;

    //         return response($so_to_khai_cont_moi, 200)->header('Content-Type', 'text/plain');
    //     } catch (\Exception $e) {
    //         Log::error('Error in getToKhaiTrongCont: ' . $e->getMessage(), [
    //             'stack' => $e->getTraceAsString(),
    //             'request' => $request->all()
    //         ]);
    //         return response()->json(['message' => 'An error occurred'], 500);
    //     }
    // }
    // public function getSoToKhaiDangChuyen($so_container)
    // {
    //     $so_to_khai_tau_conts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont.ma_yeu_cau', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
    //         ->where('yeu_cau_tau_cont.trang_thai', 1)
    //         ->where('yeu_cau_tau_cont_chi_tiet.so_container_dich', $so_container)
    //         ->pluck('yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap');
    //     $so_to_khai_containers = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
    //         ->where('yeu_cau_chuyen_container.trang_thai', 1)
    //         ->where('yeu_cau_container_chi_tiet.so_container_dich', $so_container)
    //         ->pluck('yeu_cau_container_chi_tiet.so_to_khai_nhap');
    //     return $so_to_khai_tau_conts->merge($so_to_khai_containers)->unique()->values();

    // }

    // public function getSoLuongDangChuyen($so_container)
    // {
    //     $so_luong_dang_chuyen_tau_cont = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont.ma_yeu_cau', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
    //         ->where('yeu_cau_tau_cont.trang_thai', 1)
    //         ->where('yeu_cau_tau_cont_chi_tiet.so_container_dich', $so_container)
    //         ->sum('yeu_cau_tau_cont_chi_tiet.so_luong_chuyen');

    //     $so_luong_dang_chuyen_container = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
    //         ->where('yeu_cau_chuyen_container.trang_thai', 1)
    //         ->where('yeu_cau_container_chi_tiet.so_container_dich', $so_container)
    //         ->sum('yeu_cau_container_chi_tiet.so_luong_chuyen');

    //     return $so_luong_dang_chuyen_tau_cont + $so_luong_dang_chuyen_container;
    // }


    public function getToKhaiTrongCont(Request $request)
    {
        $rowsData = json_decode($request->input('rows_data'), true);

        if ($request->ma_yeu_cau && $request->loai == 'container' && YeuCauChuyenContainer::find($request->ma_yeu_cau)->trang_thai == 2) {
            $ma_yeu_cau = $request->ma_yeu_cau;
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) use ($ma_yeu_cau) {
                    $firstItem = $group->first();

                    $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->sum('hang_trong_cont.so_luong');

                    $soLuongTrongDon = YeuCauContainerChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                        ->where('so_container_dich', $firstItem['so_container_dich'])
                        ->where('so_container_goc', $firstItem['so_container_goc'])
                        ->sum('so_luong_chuyen');

                    $soToKhaiList = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->distinct()
                        ->pluck('nhap_hang.so_to_khai_nhap')
                        ->toArray();

                    if (!in_array($firstItem['so_to_khai_nhap'], $soToKhaiList)) {
                        $soToKhaiList[] = $firstItem['so_to_khai_nhap'];
                    }

                    $so_to_khai_cont_moi = implode('</br>', $soToKhaiList);


                    $so_luong_ton_cont_moi -= $soLuongTrongDon;
                    return [
                        'so_to_khai_nhap' => $firstItem['so_to_khai_nhap'],
                        'so_container_goc' => $firstItem['so_container_goc'],
                        'so_container_dich' => $firstItem['so_container_dich'],
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),
                        'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                        'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                        'so_luong_sau_chuyen' => $group->sum('so_luong_chuyen')  + $so_luong_ton_cont_moi
                    ];
                })
                ->values();
        } elseif ($request->ma_yeu_cau && $request->loai == 'tau_cont' && YeuCauTauCont::find($request->ma_yeu_cau)->trang_thai == 2) {
            $ma_yeu_cau = $request->ma_yeu_cau;
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) use ($ma_yeu_cau) {
                    $firstItem = $group->first();

                    $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->sum('hang_trong_cont.so_luong');

                    $soLuongTrongDon = YeuCauTauContChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                        ->where('so_container_dich', $firstItem['so_container_dich'])
                        ->where('so_container_goc', $firstItem['so_container_goc'])
                        ->sum('so_luong_chuyen');

                    $soToKhaiList = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->distinct()
                        ->pluck('nhap_hang.so_to_khai_nhap')
                        ->toArray();

                    if (!in_array($firstItem['so_to_khai_nhap'], $soToKhaiList)) {
                        $soToKhaiList[] = $firstItem['so_to_khai_nhap'];
                    }

                    $so_to_khai_cont_moi = implode('</br>', $soToKhaiList);
                    $so_luong_ton_cont_moi -= $soLuongTrongDon;
                    return [
                        'so_to_khai_nhap' => $firstItem['so_to_khai_nhap'],
                        'so_container_goc' => $firstItem['so_container_goc'],
                        'so_container_dich' => $firstItem['so_container_dich'],
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),
                        'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                        'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                        'so_luong_sau_chuyen' => $group->sum('so_luong_chuyen')  + $so_luong_ton_cont_moi
                    ];
                })
                ->values();
        } else {
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) {
                    $firstItem = $group->first();

                    $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->sum('hang_trong_cont.so_luong');

                    // $so_luong_ton_cont_moi += $this->getSoLuongDangChuyen($firstItem['so_container_dich']);


                    $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->distinct()
                        ->pluck('nhap_hang.so_to_khai_nhap')
                        ->implode('</br>');

                    // $so_to_khai_dang_chuyen = $this->getSoToKhaiDangChuyen($firstItem['so_container_dich']);
                    // $so_to_khai_cont_moi = $so_to_khai_cont_moi->merge($so_to_khai_dang_chuyen)->unique()->implode('</br>');

                    $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $firstItem['so_to_khai_nhap'];

                    return [
                        'so_to_khai_nhap' => $firstItem['so_to_khai_nhap'],
                        'so_container_goc' => $firstItem['so_container_goc'],
                        'so_container_dich' => $firstItem['so_container_dich'],
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),
                        'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                        'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                        'so_luong_sau_chuyen' => $group->sum('so_luong_chuyen')  + $so_luong_ton_cont_moi
                    ];
                })
                ->values();
        }

        return response()->json($groupedData);
    }

    public function getToKhaiTrongTauCont(Request $request)
    {
        $rowsData = json_decode($request->input('rows_data'), true);
        if ($request->ma_yeu_cau) {
            $ma_yeu_cau = $request->ma_yeu_cau;
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) use ($ma_yeu_cau) {
                    $firstItem = $group->first();

                    $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->sum('hang_trong_cont.so_luong');

                    $soLuongTrongDon = YeuCauTauContChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                        ->where('so_container_dich', $firstItem['so_container_dich'])
                        ->where('so_container_goc', $firstItem['so_container_goc'])
                        ->where('tau_goc', $firstItem['tau_goc'])
                        ->where('tau_dich', $firstItem['tau_dich'])
                        ->sum('so_luong_chuyen');

                    $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->distinct()
                        ->pluck('nhap_hang.so_to_khai_nhap')
                        ->slice(0, -1) // Exclude the last item
                        ->implode('</br>');

                    $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $firstItem['so_to_khai_nhap'];


                    $so_luong_ton_cont_moi -= $soLuongTrongDon;
                    return [
                        'so_to_khai_nhap' => $firstItem['so_to_khai_nhap'],
                        'so_container_goc' => $firstItem['so_container_goc'] . ' (' . $firstItem['tau_goc'] . ')',
                        'so_container_dich' => $firstItem['so_container_dich'] . ' (' . $firstItem['tau_dich'] . ')',
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),
                        'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                        'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                        'so_luong_sau_chuyen' => $group->sum('so_luong_chuyen')  + $so_luong_ton_cont_moi
                    ];
                })
                ->values();
        } else {
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) {
                    $firstItem = $group->first();

                    $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->sum('hang_trong_cont.so_luong');

                    $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                        ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                        ->distinct()
                        ->pluck('nhap_hang.so_to_khai_nhap')
                        ->implode('</br>');

                    $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $firstItem['so_to_khai_nhap'];

                    return [
                        'so_to_khai_nhap' => $firstItem['so_to_khai_nhap'],
                        'so_container_goc' => $firstItem['so_container_goc'] . ' (' . $firstItem['tau_goc'] . ')',
                        'so_container_dich' => $firstItem['so_container_dich'] . ' (' . $firstItem['tau_dich'] . ')',
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),
                        'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                        'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                        'so_luong_sau_chuyen' => $group->sum('so_luong_chuyen')  + $so_luong_ton_cont_moi
                    ];
                })
                ->values();
        }


        return response()->json($groupedData);
    }

    public function getTenPTVT(Request $request)
    {
        try {
            $ten_ptvt = PTVTXuatCanh::find($request->so_ptvt_xuat_canh)->ten_phuong_tien_vt;
            $ten_ptvt = $ten_ptvt . ' (Số:' . $request->so_ptvt_xuat_canh . ')';
            return response($ten_ptvt, 200)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::error('Error in getTenPTVT: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    public function getSeals(Request $request)
    {
        $seals = Seal::where('ma_cong_chuc', $request->ma_cong_chuc)
            ->where('loai_seal', $request->loai_seal)
            ->where('trang_thai', '0')
            ->get();

        return response()->json(['seals' => $seals]);
    }
    public function kiemTraContainerDangChuyen(Request $request)
    {
        $existConts = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', '=', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_chuyen_container.trang_thai', '1')
            ->where(function ($query) use ($request) {
                $query->where('yeu_cau_container_chi_tiet.so_container_goc', $request->so_container)
                    ->orWhere('yeu_cau_container_chi_tiet.so_container_dich', $request->so_container);
            })
            ->exists();

        $existTauConts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont.ma_yeu_cau', '=', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_tau_cont.trang_thai', '1')
            ->where(function ($query) use ($request) {
                $query->where('yeu_cau_tau_cont_chi_tiet.so_container_goc', $request->so_container)
                    ->orWhere('yeu_cau_tau_cont_chi_tiet.so_container_dich', $request->so_container);
            })->exists();
        if ($existConts || $existTauConts) {
            return true;
        }
        return false;
    }
    public function kiemTraContainerDangChuyenSua(Request $request)
    {
        if ($request->loai == 'container') {
            $exists = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->where(function ($query) use ($request) {
                    $query->where('so_container_goc', $request->so_container)
                        ->orWhere('so_container_dich', $request->so_container);
                })
                ->exists();
        } else if ($request->loai == 'tau_cont') {
            $exists = YeuCauTauContChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->where(function ($query) use ($request) {
                    $query->where('so_container_goc', $request->so_container)
                        ->orWhere('so_container_dich', $request->so_container);
                })
                ->exists();
        }
        if ($exists) {
            return false;
        }

        $existConts = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', '=', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_chuyen_container.trang_thai', '1')
            ->where(function ($query) use ($request) {
                $query->where('yeu_cau_container_chi_tiet.so_container_goc', $request->so_container)
                    ->orWhere('yeu_cau_container_chi_tiet.so_container_dich', $request->so_container);
            })
            ->exists();

        $existTauConts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont.ma_yeu_cau', '=', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_tau_cont.trang_thai', '1')
            ->where(function ($query) use ($request) {
                $query->where('yeu_cau_tau_cont_chi_tiet.so_container_goc', $request->so_container)
                    ->orWhere('yeu_cau_tau_cont_chi_tiet.so_container_dich', $request->so_container);
            })->exists();
        if ($existConts || $existTauConts) {
            return true;
        }
        return false;
    }
    public function getHangTrongToKhai(Request $request)
    {
        $so_to_khai_nhap = $request->so_to_khai_nhap;
        $data = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $request->so_to_khai_nhap)
            ->select(
                'hang_trong_cont.so_container',
                'hang_hoa.ten_hang',
                'hang_trong_cont.so_luong'
            )
            ->get()
            ->groupBy('so_container');

        $result = $data->map(function ($items, $so_container) {
            $hang_hoa_info = $items->map(function ($item) {
                return "{$item->ten_hang} - Số lượng: {$item->so_luong}";
            })->implode('<br>');

            return [
                'so_container' => $so_container,
                'hang_hoa' => $hang_hoa_info,
            ];
        });

        // Convert to an array if needed
        $formattedResult = $result->values()->toArray();

        return response()->json($formattedResult);
    }
    public function getTraCuuContainer(Request $request)
    {
        if ($request->ajax()) {
            $containers = Container::with('niemPhong')
                ->whereHas('hangTrongCont.hangHoa.nhapHang', function ($query) {
                    $query->where('trang_thai', 2);
                })
                ->withSum(['hangTrongCont as total_so_luong'], 'so_luong')
                ->orderByDesc('total_so_luong')
                ->get(['so_container']);

            return DataTables::of($containers)
                ->addIndexColumn()
                ->addColumn('so_container', function ($container) {
                    return $container->so_container;
                })
                ->addColumn('so_seal', function ($container) {
                    return optional($container->niemPhong)->so_seal ?? '';
                })
                ->addColumn('phuong_tien_vt_nhap', function ($container) {
                    return optional($container->niemPhong)->phuong_tien_vt_nhap ?? '';
                })
                ->addColumn('total_so_luong', function ($container) {
                    return $container->total_so_luong ?? 0;
                })
                ->rawColumns(['so_container', 'so_seal', 'phuong_tien_vt_nhap', 'total_so_luong']) // Optional: mark raw HTML columns
                ->make(true);
        }
    }
    public function getToKhaiTrongCont2(Request $request)
    {
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('hang_trong_cont.so_container', $request->so_container)
            ->whereIn('nhap_hang.trang_thai', ['2'])
            ->where('nhap_hang.ma_doanh_nghiep', DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep)
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->select('nhap_hang.so_to_khai_nhap', DB::raw('SUM(hang_trong_cont.so_luong) as tong_so_luong'))
            ->get();

        $tauCu = NiemPhong::where('so_container', $request->so_container)->first()->phuong_tien_vt_nhap ?? '';
        return response()->json(['nhapHangs' => $nhapHangs, 'tauCu' => $tauCu]);
    }

    public function getThongTinChuyenChonNhanh(Request $request)
    {
        $so_container_goc = $request->so_container_goc;
        $hangHoas = [];
        foreach ($request->rows as $row) {
            $soToKhai = $row['so_to_khai_nhap'];
            $hangHoas[] = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $soToKhai)
                ->where('hang_trong_cont.so_container', $so_container_goc)
                ->select('nhap_hang.so_to_khai_nhap', 'hang_hoa.ten_hang', 'hang_trong_cont.so_luong', 'hang_trong_cont.ma_hang_cont')
                ->get();
        }

        return response()->json(['hangHoas' => $hangHoas]);
    }
}
