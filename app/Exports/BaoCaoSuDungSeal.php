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

class BaoCaoSuDungSeal implements FromArray, WithEvents
{
    public function array(): array
    {
        $data = Seal::join('cong_chuc', 'seal.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->groupBy('seal.loai_seal', 'seal.ma_cong_chuc')
            ->select(
                'seal.loai_seal',
                'seal.ma_cong_chuc',
                'cong_chuc.ten_cong_chuc',
                DB::raw('COUNT(*) as total_seals'),
                DB::raw('SUM(CASE WHEN seal.trang_thai = 0 THEN 1 ELSE 0 END) as trang_thai_0_count'),
                DB::raw('SUM(CASE WHEN seal.trang_thai = 1 THEN 1 ELSE 0 END) as trang_thai_1_count')
            )
            ->get();

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO SỬ DỤNG SEAL NIÊM PHONG HẢI QUAN', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'TÊN CÔNG CHỨC', 'LOẠI SEAL', 'SỐ LƯỢNG (CÁI)', 'SỐ LƯỢNG SỬ DỤNG', 'SỐ LƯỢNG TỒN'],
        ];

        $stt = 1;
        foreach ($data as $item) {
            $loaiSeal = '';
            if ($item->loai_seal == 1) {
                $loaiSeal = "Seal dây cáp đồng";
            } elseif ($item->loai_seal == 2) {
                $loaiSeal = "Seal dây cáp thép";
            } elseif ($item->loai_seal == 3) {
                $loaiSeal = "Seal container";
            } elseif ($item->loai_seal == 4) {
                $loaiSeal = "Seal dây nhựa dẹt";
            } elseif ($item->loai_seal == 5) {
                $loaiSeal = "Seal định vị điện tử";
            }
            $result[] = [
                $stt++,
                $item->ten_cong_chuc,
                $loaiSeal,
                $item->total_seals,
                $item->trang_thai_1_count,
                $item->trang_thai_0_count,
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
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');


                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 25);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('C1:C' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:F1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:F2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:F4'); // BÁO CÁO
                $sheet->mergeCells('A5:F5'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:F7')->applyFromArray([
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
                $sheet->getStyle('A7:F7')->applyFromArray([
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
                $sheet->getStyle('A7:F' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':F' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':F' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':F' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':F' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':F' . ($chuKyStart + 4))->getFont()->setBold(true);

            },
        ];
    }
}
