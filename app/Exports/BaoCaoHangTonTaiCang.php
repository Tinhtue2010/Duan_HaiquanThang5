<?php

namespace App\Exports;


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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BaoCaoHangTonTaiCang implements FromArray, WithEvents
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
            ['BÁO CÁO HÀNG TỒN TẠI CẢNG', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký', 'Chi cục HQ đăng ký', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng', 'Loại hàng', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá (USD)', 'Số lượng tồn', 'Số tàu', 'Số cont hiện tại'],
            [''],
        ];
        $stt = 1;

        $nhapHangs = DB::table('nhap_hang')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->where('nhap_hang.trang_thai', '2')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.loai_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.so_container',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.dia_chi',
                'hai_quan.ten_hai_quan',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY nhap_hang.so_to_khai_nhap ORDER BY hang_hoa.ma_hang) as rn'),
                DB::raw("SUM(hang_hoa.so_luong_khai_bao) OVER (PARTITION BY nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"),
                DB::raw("SUM(hang_trong_cont.so_luong) OVER (PARTITION BY nhap_hang.so_to_khai_nhap) AS total_so_luong"),
                DB::raw("SUM(hang_hoa.tri_gia) OVER (PARTITION BY nhap_hang.so_to_khai_nhap) AS total_tri_gia")
            )
            ->get()
            ->where('rn', 1)
            ->map(function ($item) {
                unset($item->rn);
                return $item;
            });
        $totalHangTon = 0;
        $totalKhaiBao = 0;

        $stt = 1;
        foreach ($nhapHangs as $item) {
            if ($item->total_so_luong != 0) {
                $result[] = [
                    $stt++,
                    $item->so_to_khai_nhap,
                    Carbon::parse($item->ngay_dang_ky)->format('d-m-Y'),
                    $item->ten_hai_quan,
                    $item->ten_doanh_nghiep,
                    $item->ma_doanh_nghiep,
                    $item->dia_chi,
                    $item->ten_hang,
                    $item->loai_hang,
                    $item->xuat_xu,
                    $item->total_so_luong_khai_bao,
                    $item->don_vi_tinh,
                    $item->trong_luong,
                    $item->total_tri_gia,
                    $item->total_so_luong,
                    $item->phuong_tien_vt_nhap,
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
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalKhaiBao,
            '',
            '',
            '',
            $totalHangTon,
        ];

        $result[] = [
            [''],
            [''],
            ['CÔNG CHỨC HẢI QUAN'],
            [''],
            [''],
            [''],
            [Auth::user()->congChuc->ten_cong_chuc],
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
                    ->setPrintArea('A1:Q' . $sheet->getHighestRow());

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
                foreach (['I', 'J', 'K', 'L', 'M', 'N'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 12);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 15);
                $sheet->getColumnDimension('G')->setWidth(width: 25);
                $sheet->getColumnDimension('H')->setWidth(width: 25);
                $sheet->getColumnDimension('M')->setWidth(width: 15);
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);


                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:Q4'); // BÁO CÁO
                $sheet->mergeCells('A5:Q5'); // Tính đến ngày

                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $column) {
                    $sheet->mergeCells($column . '7:' . $column . '8');
                }

                // Bold and center align for headers
                $sheet->getStyle('A1:Q6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:Q6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:Q' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:Q5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:Q8')->applyFromArray([
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
                $sheet->getStyle('A7:Q' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $chuKyStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === 'CÔNG CHỨC HẢI QUAN') {
                        $chuKyStart = $i;
                        break;
                    }
                }

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':Q' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':Q' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':Q' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':Q' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':Q' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
