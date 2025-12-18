<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\Seal;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoDoanTau implements FromArray, WithEvents
{
    public function array(): array
    {
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO TÀU TRÊN ĐOÀN', '', '', '', '', ''],
            [''],
            [''],
            ['STT', 'Tên đoàn', 'Tên tàu'],
        ];

        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->where('niem_phong.ten_doan_tau', '!=', null)
            // ->where('niem_phong.ten_doan_tau', '!=', '')
            ->select('niem_phong.ten_doan_tau', 'niem_phong.phuong_tien_vt_nhap')
            ->groupBy('phuong_tien_vt_nhap')
            ->havingRaw('SUM(hang_trong_cont.so_luong) > 0')
            ->orderBy('niem_phong.ten_doan_tau', 'asc')
            ->get();


        foreach ($containers as $index => $container) {
            $result[] = [
                $index + 1,
                $container->ten_doan_tau,
                $container->phuong_tien_vt_nhap,
            ];
        }

        $result[] = [
            [''],
            [''],
            ['CÔNG CHỨC HẢI QUAN'],
            [''],
            [''],
            [''],
            [Auth::user()->CongChuc->ten_cong_chuc],
        ];
        return $result;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:F' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);
                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                // Auto-width columns
                foreach (['A', 'D', 'E', 'F'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 25);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('C1:C' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('A4:C4'); // BÁO CÁO
                $sheet->mergeCells('A5:C5'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:C7')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:F7')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A7:F' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:C7')->applyFromArray([
                    'font' => ['bold' => true],
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

                // Add borders to the table content
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A7:C' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $chuKyStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === 'CÔNG CHỨC HẢI QUAN') {
                        $chuKyStart = $i;
                        break;
                    }
                }

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':C' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':C' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':C' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':C' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':C' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
