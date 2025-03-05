<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\PTVTXuatCanh;
use App\Models\Seal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuanLyKhoController extends Controller
{
    public function traCuuContainerIndex()
    {
        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['Đã nhập hàng', 'Đã xuất hết', 'Đã bàn giao hồ sơ'])
            ->select('container.*', 'niem_phong.so_seal')
            ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
            ->groupBy('container.so_container', 'niem_phong.so_seal')
            ->orderByRaw('total_so_luong DESC')
            ->get();


        return view('quan-ly-kho.tra-cuu-container', compact('containers'));
    }
    public function danhSachToKhaiTrongContainer($so_container)
    {
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
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
            $hangHoas = HangHoa::where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->get();
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
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
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
                    ->flatMap(fn($hangHoa) => $hangHoa->hangTrongCont->pluck('so_container'))
                    ->unique()
                    ->implode(';');

                $nhapHang->so_container = $containers;
                return response()->json(['data' => $nhapHang]);
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
    //             ->whereNotIn('nhap_hang.trang_thai', ['Quay về kho ban đầu', 'Đã tiêu hủy', 'Đã xuất hết', 'Đã bàn giao hồ sơ'])
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
    public function getToKhaiTrongCont(Request $request)
    {
        $rowsData = json_decode($request->input('rows_data'), true);

        $groupedData = collect($rowsData)
            ->groupBy(function ($item) {
                return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
            })
            ->map(function ($group) {
                $firstItem = $group->first();

                $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['Đã nhập hàng', 'Doanh nghiệp yêu cầu sửa tờ khai'])
                    ->distinct()
                    ->sum('hang_trong_cont.so_luong');

                $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['Đã nhập hàng', 'Doanh nghiệp yêu cầu sửa tờ khai'])
                    ->distinct()
                    ->pluck('nhap_hang.so_to_khai_nhap')
                    ->implode('</br>');
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

        return response()->json($groupedData);
    }

    public function getToKhaiTrongTauCont(Request $request)
    {
        $rowsData = json_decode($request->input('rows_data'), true);

        $groupedData = collect($rowsData)
            ->groupBy(function ($item) {
                return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
            })
            ->map(function ($group) {
                $firstItem = $group->first();

                $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['Đã nhập hàng', 'Doanh nghiệp yêu cầu sửa tờ khai'])
                    ->distinct()
                    ->sum('hang_trong_cont.so_luong');

                $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $firstItem['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['Đã nhập hàng', 'Doanh nghiệp yêu cầu sửa tờ khai'])
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
}
