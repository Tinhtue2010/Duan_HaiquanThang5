<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use Illuminate\Http\Request;
use App\Models\NhomNiemPhong;
use App\Models\Seal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SealNiemPhongController extends Controller
{
    public function danhSachChiNiemPhong()
    {
        $seals = Seal::all();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();

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
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();

        return view('quan-ly-khac.danh-sach-seal-dien-tu', data: compact('seals', 'congChucs'));
    }

    public function xoaSeal(Request $request)
    {
        Seal::find($request->so_seal)->delete();
        session()->flash('alert-success', 'Xóa seal thành công');
        return redirect()->back();
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
}
