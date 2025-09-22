<?php

namespace App\Exports;

use App\Models\HangTrongCont;
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
                DB::raw("MIN(nhap_hang.ngay_thong_quan) as ngay_thong_quan"),
                DB::raw("MIN(nhap_hang.created_at) as created_at"),
                DB::raw("MIN(hang_hoa.ma_hang) as ma_hang"),
                DB::raw("MIN(hang_hoa.ten_hang) as ten_hang"),
                'hang_trong_cont.so_container',
                DB::raw("(SELECT SUM(hh.so_luong_khai_bao) 
                FROM hang_hoa hh 
                WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"),
            )
            ->groupBy('nhap_hang.so_to_khai_nhap', 'hang_trong_cont.so_container', 'nhap_hang.ngay_thong_quan', 'nhap_hang.created_at')
            ->get();

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO HÀNG CÒN TỒN TẠI CỬA KHẨU', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['Mã doanh nghiệp: ' . $this->ma_doanh_nghiep, '', '', '', '', ''],
            ['Tên doanh nghiệp: ' . $this->ten_doanh_nghiep, '', '', '', ''],
            ['', '', '', '', '', 'Đơn vị tính: Thùng/Kiện'],
            ['STT', 'SỐ TỜ KHAI', 'NGÀY TỜ KHAI', 'NGÀY NHẬP TK', 'TÊN HÀNG', 'SL THEO KHAI BÁO', 'SL ĐÃ XUẤT', 'SỐ LƯỢNG TỒN', 'SỐ CONTAINER'],
        ];

        $stt = 1;
        foreach ($data as $item) {
            $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->where('hang_hoa.so_to_khai_nhap', $item->so_to_khai_nhap)
                ->where('hang_trong_cont.so_container', $item->so_container)
                ->select(
                    'nhap_hang.ngay_thong_quan',
                    'nhap_hang.created_at',
                    DB::raw("SUM(hang_trong_cont.so_luong) as total_so_luong"),
                    DB::raw("SUM(hang_hoa.so_luong_khai_bao) as total_so_luong_khai_bao"),
                    'hang_hoa.ten_hang',
                    'hang_trong_cont.so_container',
                    'hang_trong_cont.is_da_chuyen_cont',

                )
                ->get();
            $processedSoToKhaiNhap = [];
            foreach ($hangTrongConts as $hangTrongCont) {
                if ($hangTrongCont->total_so_luong != 0) {
                    if (!in_array($item->so_to_khai_nhap, $processedSoToKhaiNhap)) {
                        $result[] = [
                            $stt++,
                            $item->so_to_khai_nhap,
                            $item->ngay_thong_quan ? Carbon::parse($item->ngay_thong_quan)->format('d-m-Y') : '',
                            $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y') : '',
                            $item->ten_hang,
                            $item->total_so_luong_khai_bao,
                            ($item->total_so_luong_khai_bao - $hangTrongCont->total_so_luong) == 0 ? '0' : ($item->total_so_luong_khai_bao - $hangTrongCont->total_so_luong),
                            $hangTrongCont->total_so_luong,
                            $item->so_container,
                        ];
                    } else {
                        $result[] = [
                            $stt++,
                            $item->so_to_khai_nhap,
                            $item->ngay_thong_quan ? Carbon::parse($item->ngay_thong_quan)->format('d-m-Y') : '',
                            $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y') : '',
                            '',
                            0,
                            0,
                            $hangTrongCont->total_so_luong,
                            $item->so_container,
                        ];
                    }

                    $processedSoToKhaiNhap[] = $hangTrongCont->so_to_khai_nhap;
                    $totalHangTon += $hangTrongCont->total_so_luong;
                    $totalKhaiBao += $item->total_so_luong_khai_bao;
                }
            }
        }
        $result[] = [
            '',
            '',
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
                    ->setPrintArea('A1:I' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('E')->setWidth(width: 38);
                $sheet->getColumnDimension('I')->setWidth(width: 20);
                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('G')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode('0');
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('E1:E' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:D1'); // CỤC HẢI QUAN
                $sheet->mergeCells('F1:I1'); // CỘNG HÒA
                $sheet->mergeCells('A2:D2'); // CHI CỤC
                $sheet->mergeCells('F2:I2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:I4'); // BÁO CÁO
                $sheet->mergeCells('A5:I5'); // Tính đến ngày
                $sheet->mergeCells('A7:I7'); // Mã doanh nghiệp
                $sheet->mergeCells('A8:E8'); // Tên doanh nghiệp
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0');

                // Bold and center align for headers
                $sheet->getStyle('A1:I6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:I6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                // Italic for date row
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A10:I10')->applyFromArray([
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
                $sheet->getStyle('A10:I' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('I9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
