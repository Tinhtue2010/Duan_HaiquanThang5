<?php

namespace App\Exports;

use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class YeuCauNiemPhongExport implements FromArray, WithEvents
{
    protected $ma_yeu_cau;

    public function __construct($ma_yeu_cau)
    {
        $this->ma_yeu_cau = $ma_yeu_cau;
    }
    public function array(): array
    {
        $yeuCau = YeuCauNiemPhong::find($this->ma_yeu_cau);
        $chiTiets = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $this->ma_yeu_cau)->get();
        $date = Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_yeu_cau)->format('d-m-Y');
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['YÊU CẦU NIÊM PHONG', '', '', '', '', '', ''],
            ["Số {$yeuCau->ma_yeu_cau}, ngày: {$date}", '', '', '', '', ''],
            ["Công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số container','Tàu', 'Số seal niêm phong cũ', 'Số seal niêm phong mới'],
        ];
        $stt = 1;
        foreach ($chiTiets as $chiTiet) {
            $result[] = [
                $stt++,
                $chiTiet->so_container,
                $chiTiet->phuong_tien_vt_nhap,
                $chiTiet->so_seal_cu,
                $chiTiet->so_seal_moi
            ];
        }


        $result[] = [
            '',
            '',
            '',
            '',
            '',
        ];

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
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(14);

                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 15);
                $sheet->getColumnDimension('D')->setWidth(width: 25);
                $sheet->getColumnDimension('E')->setWidth(width: 25);

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');

                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A6:E6');

                // Your existing styles
                $sheet->getStyle('A1:E6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:E6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A9:E' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A8:E8')->applyFromArray([
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

                $sheet->getStyle('A8:E' . $lastRow)->applyFromArray([
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
