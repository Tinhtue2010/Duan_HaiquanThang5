<?php

namespace App\Exports;

use App\Models\XuatHang;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaoCaoTheoDoiTruLuiAllSheetExport implements WithMultipleSheets
{
    protected $theoDoiTruLuis;
    protected $is_cuoi_ngay;

    public function __construct($theoDoiTruLuis, $is_cuoi_ngay = false)
    {
        $this->theoDoiTruLuis = $theoDoiTruLuis;
        $this->is_cuoi_ngay = $is_cuoi_ngay;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->theoDoiTruLuis as $item) {
            if ($item->cong_viec == 1) {
                $sheets[] = new BaoCaoTheoDoiTruLuiTheoNgayExport($item->so_to_khai_nhap, $item->ngay_them);
            } elseif ($item->cong_viec == 10) {
                $sheets[] = new BaoCaoTheoDoiTruLuiCuoiNgayExport($item->so_to_khai_nhap, $item->ngay_them);
            } else {
                $sheets[] = new BaoCaoTheoDoiTruLuiExport($item->cong_viec, $item->ma_yeu_cau, $item->so_to_khai_nhap);
            }
        }

        return $sheets;
    }
}
