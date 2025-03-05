<?php

namespace App\Exports;

use App\Models\NhapHang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoTonChuHangExport implements FromArray, WithEvents
{
    protected $ma_chu_hang;
    protected $ten_chu_hang;

    public function __construct($ma_chu_hang, $ten_chu_hang)
    {
        $this->ma_chu_hang = $ma_chu_hang;
        $this->ten_chu_hang = $ten_chu_hang;
    }

    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year
        $data = NhapHang::where('nhap_hang.ma_chu_hang', $this->ma_chu_hang)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('chu_hang', 'chu_hang.ma_chu_hang', '=', 'nhap_hang.ma_chu_hang')
            ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                DB::raw("(SELECT SUM(hh.so_luong_khai_bao) 
                      FROM hang_hoa hh 
                      WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"), // Ensure correct summation
                DB::raw("MIN(hang_hoa.ma_hang) as ma_hang"), // Pick any hang_hoa as representative
                DB::raw("MIN(hang_hoa.ten_hang) as ten_hang"), // Pick any hang_hoa as representative
                DB::raw("(SELECT SUM(htc.so_luong) 
                    FROM hang_hoa hh 
                    JOIN hang_trong_cont htc ON hh.ma_hang = htc.ma_hang 
                    WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong"),
            )
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->get();

        $result = [
            ['CỤC HẢI QUAN TỈNH QUẢNG NINH', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['CHI CỤC HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO HÀNG CÒN TỒN TẠI CỬA KHẨU', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['Tên đại lý: ' . $this->ten_chu_hang, '', '', '', ''],
            ['', '', '', '', '', 'Đơn vị tính: Thùng/Kiện'],
            ['STT', 'SỐ TỜ KHAI', 'TÊN HÀNG', 'SL THEO KHAI BÁO', 'SL ĐÃ XUẤT', 'SỐ LƯỢNG TỒN'],
        ];
        $totalHangTon = 0;
        $totalKhaiBao = 0;

        $stt = 1;
        foreach ($data as $item) {
            if ($item->total_so_luong != 0) {
                $result[] = [
                    $stt++,
                    $item->so_to_khai_nhap,
                    $item->ten_hang,
                    $item->total_so_luong_khai_bao,
                    ($item->total_so_luong_khai_bao - $item->total_so_luong) == 0 ? '0' : ($item->total_so_luong_khai_bao - $item->total_so_luong),
                    $item->total_so_luong,
                ];
                $totalHangTon += $item->total_so_luong;
                $totalKhaiBao += $item->total_so_luong_khai_bao;
            }
        }
        $result[] = [
            '',
            '',
            '',
            $totalKhaiBao,
            $totalKhaiBao - $totalHangTon,
            $totalHangTon,
        ];

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


                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 38);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('C1:C' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:F1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:F2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:F4'); // BÁO CÁO
                $sheet->mergeCells('A5:F5'); // Tính đến ngày
                $sheet->mergeCells('A7:F7'); // Mã
                $sheet->mergeCells('A8:E8'); // Tên

                // Bold and center align for headers
                $sheet->getStyle('A1:F6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:F6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('A10:F10')->applyFromArray([
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
                $sheet->getStyle('A10:F' . $lastRow)->applyFromArray([
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

                // Left align for specific cells
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle(cellCoordinate: 'F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
