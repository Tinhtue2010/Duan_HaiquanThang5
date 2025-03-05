<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use Illuminate\Http\Request;
use App\Models\NhomNiemPhong;
use App\Models\Seal;

class SealNiemPhongController extends Controller
{
    public function danhSachChiNiemPhong()
    {
        $seals = Seal::all();
        $congChucs = CongChuc::where('is_chi_xem',0)->get();

        return view('quan-ly-khac.danh-sach-chi-niem-phong', data: compact('seals', 'congChucs'));
    }
    public function themChiNiemPhong(Request $request)
    {
        $newStart = $request->moc_dau;
        $newEnd = $request->moc_cuoi;
        $overlapExists = NhomNiemPhong::where(function ($query) use ($newStart, $newEnd, $request) {
            $query->where('moc_dau', '<', $newEnd)
                ->where('moc_cuoi', '>', $newStart)
                ->where('tiep_ngu', '=', $request->tiep_ngu);
        })
            ->exists();
        if ($overlapExists) {
            session()->flash('alert-danger', 'Dải số chì bị trùng lặp');
            return redirect()->back();
        }

        NhomNiemPhong::insert([
            'moc_dau' => $newStart,
            'moc_cuoi' => $newEnd,
            'tiep_ngu' => $request->tiep_ngu
        ]);

        $data = [];
        $padding = strlen((string) $newEnd); // Calculate the number of digits required

        for ($i = $newStart; $i <= $newEnd; $i++) {
            $data[] = [
                'so_seal' => $request->tiep_ngu . str_pad($i, $padding, '0', STR_PAD_LEFT),
                'ma_cong_chuc' => $request->ma_cong_chuc,
                'loai_seal' => $request->loai_seal,
                'ngay_cap' => now(),
            ];
        }

        // Batch insert into the database
        Seal::insert($data);
        session()->flash('alert-success', 'Thêm seal niêm phong thành công');
        return redirect()->back();
    }
    public function danhSachSealDienTu()
    {
        $seals = Seal::where('loai_seal', 5)->get();
        $congChucs = CongChuc::where('is_chi_xem',0)->get();

        return view('quan-ly-khac.danh-sach-seal-dien-tu', data: compact('seals', 'congChucs'));
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
    }}
