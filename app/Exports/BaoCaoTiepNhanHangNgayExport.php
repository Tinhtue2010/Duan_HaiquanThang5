<?php

namespace App\Exports;

use App\Models\LoaiHang;
use App\Models\HaiQuan;
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

class BaoCaoTiepNhanHangNgayExport implements FromArray, WithEvents
{
    protected $tu_ngay;

    public function __construct($tu_ngay)
    {
        $this->tu_ngay = $tu_ngay;
    }
    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay);

        $day = $tu_ngay->format('d');  // Day of the month
        $month = $tu_ngay->format('m'); // Month number
        $year = $tu_ngay->format('Y');  // Year

        $loaiHangs = LoaiHang::all();
        $haiQuans = HaiQuan::all();

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO TIẾP NHẬN HẰNG NGÀY', '', '', '', '', ''],
            ["Ngày $day tháng $month năm $year", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Tên HQ', 'Tên hàng', 'Số lượng tờ khai', 'Số lượng cont', 'Số lượng', 'Trị giá (USD)'],
        ];

        $today = Carbon::now()->format('Y-m-d'); // Format now() as yyyy-mm-dd
        $stt = 1;
        $thongTinData = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->whereIn('nhap_hang.ma_hai_quan', $haiQuans->pluck('ma_hai_quan'))
            ->whereIn('hang_hoa.loai_hang', $loaiHangs->pluck('ten_loai_hang'))
            ->whereDate('nhap_hang.created_at', $this->tu_ngay)
            ->select(
                'nhap_hang.ma_hai_quan',
                'hang_hoa.loai_hang',
                DB::raw('COUNT(DISTINCT nhap_hang.so_to_khai_nhap) as count_so_to_khai'),
                DB::raw('SUM(hang_hoa.so_luong_khai_bao) as total_so_luong'),
                DB::raw('SUM(hang_hoa.tri_gia) as total_tri_gia')
            )
            ->groupBy('nhap_hang.ma_hai_quan', 'hang_hoa.loai_hang')
            ->get();

        foreach ($thongTinData as $data) {
            $haiQuanName = $haiQuans->where('ma_hai_quan', $data->ma_hai_quan)->first()->ten_hai_quan ?? 'Unknown';
            $loaiHangName = $loaiHangs->where('ten_loai_hang', $data->loai_hang)->first()->ten_loai_hang ?? 'Unknown';

            $result[] = [
                $stt++,
                $haiQuanName,
                $loaiHangName,
                $data->count_so_to_khai, // Unique count of so_to_khai_nhap
                $data->count_so_to_khai, // Same as totalSoContainer
                $data->total_so_luong,
                $data->total_tri_gia,
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
                foreach (['D', 'E', 'F', 'G'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 30);
                $sheet->getColumnDimension('C')->setWidth(width: 30);
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:G1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:G2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:G4'); // BÁO CÁO
                $sheet->mergeCells('A5:G5'); // Tính đến ngày


                // Bold and center align for headers
                $sheet->getStyle('A1:F6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:F6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:G7')->applyFromArray([
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
                $sheet->getStyle('A7:G' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':G' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':G' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':G' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':G' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':G' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
