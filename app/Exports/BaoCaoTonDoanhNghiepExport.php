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
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoTonDoanhNghiepExport implements FromArray, WithEvents
{
    protected $ma_doanh_nghiep;
    protected $ten_doanh_nghiep;

    public function __construct($ma_doanh_nghiep, $ten_doanh_nghiep)
    {
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
        $this->ten_doanh_nghiep = $ten_doanh_nghiep;
    }

    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $totalHangTon = 0;
        $totalKhaiBao = 0;
        $totalXuat = 0;

        $data = NhapHang::where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                DB::raw("(SELECT SUM(hh.so_luong_khai_bao) 
                      FROM hang_hoa hh 
                      WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"),
                DB::raw("MIN(hang_hoa.ma_hang) as ma_hang"),
                DB::raw("MIN(hang_hoa.ten_hang) as ten_hang"), 
                DB::raw("MIN(hang_trong_cont.so_container) as so_container"), 
                DB::raw("(SELECT SUM(htc.so_luong) 
                    FROM hang_hoa hh 
                    JOIN hang_trong_cont htc ON hh.ma_hang = htc.ma_hang 
                    WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong"),
            )
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->get();

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO HÀNG CÒN TỒN TẠI CỬA KHẨU', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['Mã doanh nghiệp: ' . $this->ma_doanh_nghiep, '', '', '', '', ''],
            ['Tên doanh nghiệp: ' . $this->ten_doanh_nghiep, '', '', '', ''],
            ['', '', '', '', '', 'Đơn vị tính: Thùng/Kiện'],
            ['STT', 'SỐ TỜ KHAI', 'TÊN HÀNG', 'SL THEO KHAI BÁO', 'SL ĐÃ XUẤT', 'SỐ LƯỢNG TỒN','SỐ CONTAINER'],
        ];

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
                    $item->so_container,
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
                    ->setPrintArea('A1:G' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'D', 'E', 'F'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('C')->setWidth(width: 38);
                $sheet->getColumnDimension('G')->setWidth(width: 20);
                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0');
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('C1:C' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:G1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:G2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:G4'); // BÁO CÁO
                $sheet->mergeCells('A5:G5'); // Tính đến ngày
                $sheet->mergeCells('A7:G7'); // Mã doanh nghiệp
                $sheet->mergeCells('A8:E8'); // Tên doanh nghiệp
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');

                // Bold and center align for headers
                $sheet->getStyle('A1:G6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:G6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                // Italic for date row
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A10:G10')->applyFromArray([
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
                $sheet->getStyle('A10:G' . $lastRow)->applyFromArray([
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

                // Left align for specific cells
                $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('G9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
