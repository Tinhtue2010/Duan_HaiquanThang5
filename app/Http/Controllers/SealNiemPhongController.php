<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use Illuminate\Http\Request;
use App\Models\Seal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SealNiemPhongController extends Controller
{
    public function danhSachChiNiemPhong()
    {
        $seals = Seal::all();
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();

        return view('quan-ly-khac.danh-sach-chi-niem-phong', data: compact('seals', 'congChucs'));
    }
    public function themChiNiemPhong(Request $request)
    {
        try {
            $newStart = $request->moc_dau;
            $newEnd = $request->moc_cuoi;
            $data = [];
            $padding = strlen((string) $newEnd); // Calculate the number of digits required

            for ($i = $newStart; $i <= $newEnd; $i++) {
                $soSeal =  $request->tiep_ngu . str_pad($i, $padding, '0', STR_PAD_LEFT);
                if (Seal::find($soSeal)) {
                    session()->flash('alert-danger', 'Trùng lặp seal: ' . $soSeal);
                    return redirect()->back();
                }
                $data[] = [
                    'so_seal' => $soSeal,
                    'ma_cong_chuc' => $request->ma_cong_chuc,
                    'loai_seal' => $request->loai_seal,
                    'ngay_cap' => now(),
                ];
            }

            Seal::insert($data);
            session()->flash('alert-success', 'Thêm seal niêm phong thành công');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Error in themChiNiemPhong: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    public function danhSachSealDienTu()
    {
        $seals = Seal::where('loai_seal', 5)->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();

        return view('quan-ly-khac.danh-sach-seal-dien-tu', data: compact('seals', 'congChucs'));
    }

    public function xoaSeal(Request $request)
    {
        Seal::find($request->so_seal)->delete();
        session()->flash('alert-success', 'Xóa seal thành công');
        return redirect()->back();
    }
    public function xoaNhanhSeal(Request $request)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        return view('quan-ly-khac.xoa-nhanh-seal', data: compact('congChucs'));
    }
    public function xoaNhanhSealSubmit(Request $request)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            Seal::find($row["so_seal"])->delete();
        }
        session()->flash('alert-success', 'Xóa nhanh seal thành công');
        return redirect()->back();
    }
    public function suaNhanhSeal(Request $request)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        return view('quan-ly-khac.sua-nhanh-seal', data: compact('congChucs'));
    }
    public function suaNhanhSealSubmit(Request $request)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            Seal::find($row["so_seal"])->update([
                'ma_cong_chuc' => $request->ma_cong_chuc_moi
            ]);
        }
        session()->flash('alert-success', 'Sửa nhanh seal thành công');
        return redirect()->back();
    }




    public function getThongTinXoaNhanhSeal(Request $request)
    {
        $seals = Seal::join('cong_chuc', 'cong_chuc.ma_cong_chuc', 'seal.ma_cong_chuc')
            ->when(!empty($request->ngay_cap), function ($query) use ($request) {
                $ngay_cap = Carbon::createFromFormat('d/m/Y', $request->ngay_cap)->format('Y-m-d');
                return $query->where('seal.ngay_cap', $ngay_cap);
            })
            ->when(!empty($request->ma_cong_chuc), function ($query) use ($request) {
                return $query->where('seal.ma_cong_chuc', $request->ma_cong_chuc);
            })
            ->when(!empty($request->loai_seal), function ($query) use ($request) {
                return $query->where('seal.loai_seal', $request->loai_seal);
            })
            ->when(isset($request->trang_thai) && $request->trang_thai !== null, function ($query) use ($request) {
                return $query->where('seal.trang_thai', $request->trang_thai);
            })
            ->get()
            ->map(function ($seal) {
                switch ($seal->trang_thai) {
                    case 0:
                        $seal->trang_thai = 'Chưa sử dụng';
                        break;
                    case 1:
                        $seal->trang_thai = 'Đã sử dụng';
                        break;
                    case 2:
                        $seal->trang_thai = 'Seal hỏng';
                        break;
                    default:
                        $seal->trang_thai = 'N/A';
                }

                switch ($seal->loai_seal) {
                    case 1:
                        $seal->loai_seal = 'Seal dây cáp đồng';
                        break;
                    case 2:
                        $seal->loai_seal = 'Seal dây cáp thép';
                        break;
                    case 3:
                        $seal->loai_seal = 'Seal container';
                        break;
                    case 4:
                        $seal->loai_seal = 'Seal dây nhựa dẹt';
                        break;
                    case 5:
                        $seal->loai_seal = 'Seal định vị điện tử';
                        break;
                    default:
                        $seal->loai_seal = 'N/A';
                }

                return $seal;
            });

        return response()->json(['seals' => $seals]);
    }


    //Api lấy data qua Ajax
    public function getSealItems(Request $request)
    {
        $maNhom = $request->ma_nhom;

        // Fetch seals matching the `ma_nhom`
        $items = Seal::where('ma_nhom', $maNhom)->get();

        return response()->json([
            'data' => $items
        ]);
    }

    public function getChiNiemPhong(Request $request)
    {
        if ($request->ajax()) {
            $query = Seal::join('cong_chuc', 'cong_chuc.ma_cong_chuc', 'seal.ma_cong_chuc')
                ->select('seal.*', 'cong_chuc.ten_cong_chuc as ten_cong_chuc');
            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('so_seal', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(ngay_cap, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhereRaw("DATE_FORMAT(ngay_su_dung, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('so_container', 'LIKE', "%{$search}%")
                                ->orWhereRaw("
                                CASE 
                                    WHEN loai_seal = 1 THEN 'Seal dây cáp đồng'
                                    WHEN loai_seal = 2 THEN 'Seal dây cáp thép'
                                    WHEN loai_seal = 3 THEN 'Seal container'
                                    WHEN loai_seal = 4 THEN 'Seal dây nhựa dẹt'
                                    WHEN loai_seal = 5 THEN 'Seal container'
                                    ELSE ''
                                END LIKE ?", ["%{$search}%"])
                                ->orWhere('trang_thai', 'LIKE', "%{$search}%")
                                ->orWhere('cong_chuc.ten_cong_chuc', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($seal) {
                    return '';
                })
                ->editColumn('ngay_su_dung', function ($seal) {
                    return Carbon::parse($seal->ngay_su_dung)->format('d-m-Y');
                })
                ->editColumn('ngay_cap', function ($seal) {
                    return Carbon::parse($seal->ngay_cap)->format('d-m-Y');
                })
                ->addColumn('ten_cong_chuc', function ($seal) {
                    return $seal->ten_cong_chuc ?? 'Unknown';
                })
                ->editColumn('trang_thai', function ($seal) {
                    $status = trim($seal->trang_thai);
                    $statusLabels = [
                        '0' => ['text' => 'Chưa sử dụng', 'class' => 'text-dark'],
                        '1' => ['text' => 'Đã sử dụng', 'class' => 'text-success'],
                        '2' => ['text' => 'Seal hỏng', 'class' => 'text-danger'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Khác</span>';
                })
                ->editColumn('loai_seal', function ($seal) {
                    $status = trim($seal->loai_seal);
                    $statusLabels = [
                        '1' => ['text' => 'Seal dây cáp đồng', 'class' => 'text-dark'],
                        '2' => ['text' => 'Seal dây cáp thép', 'class' => 'text-dark'],
                        '3' => ['text' => 'Seal container', 'class' => 'text-dark'],
                        '4' => ['text' => 'Seal dây nhựa dẹt', 'class' => 'text-dark'],
                        '5' => ['text' => 'Seal định vị điện tử', 'class' => 'text-dark'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Khác</span>';
                })
                ->addColumn('thao_tac', function ($seal) {
                    return '<button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal" 
                                data-so-seal="' . $seal->so_seal . '" data-loai-seal="' . $seal->loai_seal . '">
                                Xóa
                            </button>';
                })
                ->rawColumns(['trang_thai', 'thao_tac', 'loai_seal'])
                ->make(true);
        }
    }
}
