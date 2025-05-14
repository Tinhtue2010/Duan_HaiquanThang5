<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\NhapHang;
use App\Models\HangHoa;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoTraCuuContainer implements FromArray, WithEvents
{
    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO TRA CỨU CONTAINER ', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số container', 'Tàu','Số seal niêm phong' ,'Số lượng'],

        ];

        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->select('container.*', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
            ->groupBy('container.so_container', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap')
            ->orderByRaw('total_so_luong DESC')
            ->get();

        $stt = 1;
        foreach ($containers as $container) {
            if ($container->total_so_luong == 0) {
                continue;
            }
            $result[] = [
                $stt++,
                $container->so_container,
                $container->phuong_tien_vt_nhap,
                $container->so_seal,
                $container->total_so_luong,
            ];
        }

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
                    ->setPrintArea('A1:D' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 20);
                $sheet->getColumnDimension('C')->setWidth(width: 20);
                $sheet->getColumnDimension('D')->setWidth(width: 20);
                $sheet->getColumnDimension('E')->setWidth(width: 20);

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');

                // Bold and center align for headers
                $sheet->getStyle('A1:E6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:D6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:E' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);
                // Bold and center align for table headers
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

                // Add borders to the table content
                $lastRow = $sheet->getHighestRow();
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
