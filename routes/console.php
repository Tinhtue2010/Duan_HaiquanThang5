<?php

use App\Models\XuatHang;
use App\Models\YeuCauNiemPhong;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        ->where('ngay_yeu_cau', today())
        ->get();

    foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_seal_moi); // Remove spaces
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
            ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $chiTietYeuCau->so_seal_moi]);
    }
})->everyTenMinutes();
