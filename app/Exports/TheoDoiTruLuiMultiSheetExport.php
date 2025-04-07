<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TheoDoiTruLuiMultiSheetExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $index => $theoDoiTruLui) {
            if ($theoDoiTruLui->cong_viec == 1) {
                $sheets[] = new BaoCaoTheoDoiTruLuiTheoNgayExport(
                    $theoDoiTruLui->so_to_khai_nhap,
                    $theoDoiTruLui->ngay_them,
                    "Sheet " . ($index + 1)
                );
            } else {
                $sheets[] = new BaoCaoTheoDoiTruLuiExport(
                    $theoDoiTruLui->cong_viec,
                    $theoDoiTruLui->ma_yeu_cau,
                    $theoDoiTruLui->so_to_khai_nhap,
                    "Sheet " . ($index + 1)
                );
            }
        }

        return $sheets;
    }
}
