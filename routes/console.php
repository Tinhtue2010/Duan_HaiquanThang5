<?php

use App\Models\NiemPhong;
use App\Models\HangHoa;
use App\Models\XuatHang;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauGoSeal;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        ->where(function ($query) {
            if (now()->hour < 1) {
                $query->whereDate('ngay_yeu_cau', today())
                    ->orWhereDate('ngay_yeu_cau', today()->subDay());
            } else {
                $query->whereDate('ngay_yeu_cau', today());
            }
        })
        ->where('ngay_yeu_cau', today())
        ->get();

    foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_container); // Remove spaces
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


    $chiTietYeuCaus = YeuCauGoSeal::join('yeu_cau_go_seal_chi_tiet', 'yeu_cau_go_seal.ma_yeu_cau', '=', 'yeu_cau_go_seal_chi_tiet.ma_yeu_cau')
        ->where(function ($query) {
            if (now()->hour < 1) {
                $query->whereDate('ngay_yeu_cau', today())
                    ->orWhereDate('ngay_yeu_cau', today()->subDay());
            } else {
                $query->whereDate('ngay_yeu_cau', today());
            }
        })
        ->where('ngay_yeu_cau', today())
        ->get();

    foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        $so_container_no_space = str_replace(' ', '', $chiTietYeuCau->so_container); // Remove spaces
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
})
    ->when(function () {
        $hour = (int)now()->format('G'); // Get current hour (0-23)

        if ($hour >= 21 || $hour <= 1) {
            return true;
        }

        if ($hour >= 6 && $hour <= 21) {
            return in_array((int)now()->format('i') % 30, [0]);
        }

        return false;
    })
    ->everyFiveMinutes();

// Schedule::call(function () {
//     $nhapHangs = NhapHang::where('trang_thai', 2)->get();
//     foreach ($nhapHangs as $nhapHang) {
//         $soContainers = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
//             ->where('hang_hoa.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
//             ->pluck('so_container')
//             ->unique();
//         foreach ($soContainers as $soContainer) {
//             NiemPhong::where('so_container', $soContainer)
//                 ->where('phuong_tien_vt_nhap', operator: null)
//                 ->update([
//                     'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
//                 ]);
//         }
//     }
// })
//     ->everyThirtyMinutes();
