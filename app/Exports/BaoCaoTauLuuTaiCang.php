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

class BaoCaoTauLuuTaiCang implements FromArray, WithEvents
{

    protected $phuong_tien_vt_nhap;
    public function __construct($phuong_tien_vt_nhap)
    {
        $this->phuong_tien_vt_nhap = $phuong_tien_vt_nhap;
    }
    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO SỐ LƯỢNG CONTAINER LƯU TRÊN TÀU', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Tên tàu', 'Số container', 'Số lượng hàng'],

        ];
        $tau_no_space = str_replace(' ', '', $this->phuong_tien_vt_nhap); // Remove spaces
        $tau_with_space = substr($tau_no_space, 0, 2) . ' ' . substr($tau_no_space, 2);
        $tau_normalized = substr($tau_with_space, 0, 2) . '-' . substr($tau_with_space, 2);

        $soContainers = Container::join('hang_trong_cont', 'container.so_container', '=', 'hang_trong_cont.so_container')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->join('nhap_hang', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->where('nhap_hang.trang_thai', '2')
            ->whereIn('niem_phong.phuong_tien_vt_nhap', [$tau_no_space, $tau_with_space,$tau_normalized])
            ->groupBy('container.so_container')
            ->select('container.so_container', DB::raw('SUM(hang_trong_cont.so_luong) as total_so_luong'))
            ->get();
        $stt = 1;
        foreach ($soContainers as $container) {
            if ($container->total_so_luong == 0) {
                continue;
            }
            $result[] = [
                $stt++,
                $this->phuong_tien_vt_nhap,
                $container->so_container,
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
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 20);
                $sheet->getColumnDimension('D')->setWidth(width: 20);

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A4:D4');
                $sheet->mergeCells('A5:D5');

                // Bold and center align for headers
                $sheet->getStyle('A1:D6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:D6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:D' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:D5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);
                // Bold and center align for table headers
                $sheet->getStyle('A8:D8')->applyFromArray([
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
                $sheet->getStyle('A8:D' . $lastRow)->applyFromArray([
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
