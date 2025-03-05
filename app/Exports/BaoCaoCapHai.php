<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanh;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoCapHai implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $ma_doanh_nghiep;

    public function __construct($ma_doanh_nghiep, $tu_ngay)
    {
        $this->tu_ngay = $tu_ngay;
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
    }
    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $ten_doanh_nghiep = DoanhNghiep::find($this->ma_doanh_nghiep)->ten_doanh_nghiep;
        $result = [
            ['CỤC HẢI QUAN TỈNH QUẢNG NINH', '', '', '', '', ''],
            ['CHI CỤC HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO CẤP 2', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['KHÓI'],
            [$tu_ngay],
            ['Công ty:', $ten_doanh_nghiep, '', 'Số lượng'],
        ];
        // $ptvtXuatCanhs = PTVTXuatCanh::where('ma_doanh_nghiep', $this->ma_doanh_nghiep)
        // ->join('xuat_hang','ptvt_xuat_canh.so_ptvt_xuat_canh','xuat_hang.so_ptvt_xuat_canh')
        // ->join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
        // ->select(
        //     DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as total_so_luong_xuat')
        // )
        // ->get();


        $ptvtXuatCanhs = XuatHang::join('ptvt_xuat_canh_cua_phieu', 'xuat_hang.so_to_khai_xuat', '=', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat')
            ->join('xuat_canh', 'ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', '=', 'xuat_canh.so_ptvt_xuat_canh')
            ->where('xuat_canh.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->where('xuat_hang.ngay_dang_ky', $this->tu_ngay)
            ->distinct()
            ->pluck('xuat_canh.so_ptvt_xuat_canh');

        foreach ($ptvtXuatCanhs as $so_ptvt_xuat_canh) {
            $ptvt_xc = PTVTXuatCanh::find($so_ptvt_xuat_canh);
            $total = 0;
            $subResult = [];
            $xuatHangs = XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                ->join('ptvt_xuat_canh_cua_phieu', 'xuat_hang.so_to_khai_xuat', '=', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat')
                ->where('xuat_hang.trang_thai', '!=', 'Đã hủy')
                ->where('xuat_hang.ngay_dang_ky', $this->tu_ngay)
                ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $so_ptvt_xuat_canh)
                ->select(
                    'xuat_hang_cont.so_to_khai_nhap',
                    'xuat_hang_cont.so_container',
                    DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as total_so_luong_xuat')
                )
                ->groupBy('xuat_hang_cont.so_to_khai_nhap', 'xuat_hang_cont.so_container')
                ->get();
            foreach ($xuatHangs as $xuatHang) {
                $subResult[] = ['Số tờ khai:', $xuatHang->so_to_khai_nhap, '=', $xuatHang->total_so_luong_xuat];
                $subResult[] = ['Container:', $xuatHang->so_container];
                $total += $xuatHang->total_so_luong_xuat;
            }

            foreach ($xuatHangs as $xuatHang) {
                $subResult[] = ['Số tờ khai:', $xuatHang->so_to_khai_nhap, '=', $xuatHang->total_so_luong_xuat];
                $subResult[] = ['Container:', $xuatHang->so_container];
                $total += $xuatHang->total_so_luong_xuat;
            }
            $result[] = ['Xuồng:', $ptvt_xc->ten_phuong_tien_vt, '', $total];
            $result = array_merge($result, $subResult);
        }

        return $result;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Set print settings first
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:E' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);


                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                foreach (['B', 'C', 'D'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 10);
                $sheet->getColumnDimension('B')->setWidth(width: 20);
                $sheet->getColumnDimension('C')->setWidth(width: 3);


                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);
                for ($row = 9; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A7:D7');


                // Your existing styles
                $sheet->getStyle('A1:E8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:E8')->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                $sheet->getStyle('A8:D' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
