<?php

namespace App\Exports;

use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\SecondDB\NhapHangSecond;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoHangTonTheoToKhaiExport implements FromArray, WithEvents
{
    protected $so_to_khai_nhap;

    public function __construct($so_to_khai_nhap)
    {
        $this->so_to_khai_nhap = $so_to_khai_nhap;
    }

    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $phanMot = NhapHang::select(
            'hang_hoa.ten_hang',
            'hang_hoa.so_to_khai_nhap',
            'hang_hoa.so_luong_khai_bao',
        )
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->distinct()
            ->get();
        if ($phanMot->isEmpty()) {
            $phanMot = NhapHang::select(
                'hang_hoa.ten_hang',
                'hang_hoa.so_to_khai_nhap',
                'hang_hoa.so_luong_khai_bao',
            )
                ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
                ->distinct()
                ->get();
        }

        $hangHoas = HangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)->get();
        $hangHoaArr = [];
        foreach ($hangHoas as $hangHoa) {
            $hangHoaArr[$hangHoa->ma_hang] = $hangHoa->so_luong_khai_bao;
        }

        $soToKhaiXuats = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->orderBy('xuat_hang.so_to_khai_xuat', 'asc')
            ->pluck('xuat_hang.so_to_khai_xuat')
            ->unique()
            ->values();





        $nhapHang = NhapHang::find($this->so_to_khai_nhap);
        $ngay_dang_ky = Carbon::createFromFormat('Y-m-d', $nhapHang->ngay_dang_ky)->format('d-m-Y');
        $ngay_thong_quan = Carbon::createFromFormat('Y-m-d', $nhapHang->ngay_thong_quan)->format('d-m-Y');
        $ten_hai_quan = $nhapHang->haiQuan->ten_hai_quan;

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO THEO DÕI HÀNG TỒN THEO TỜ KHAI', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', 'Đơn vị tính: Thùng/Kiện'],
            ['Số tờ khai: ' . $this->so_to_khai_nhap, '', '', '', ''],
            ['Ngày đăng ký: ' . $ngay_dang_ky, '', '', '', ''],
            ['Ngày hàng đến: ' . $ngay_thong_quan, '', '', '', ''],
            ['Cơ quan hải quan đăng ký tờ khai: ' . $ten_hai_quan, '', '', '', ''],
            ['', '', '', '', '', ''],
            ['I-PHẦN ĐẾN CỬA KHẨU', '', '', '', '', ''],
            ['STT', 'TÊN HÀNG', 'SỐ LƯỢNG', '', '', ''],

        ];

        $stt = 1;
        foreach ($phanMot as $item) {
            $result[] = [
                $stt++,
                $item->ten_hang,
                $item->so_luong_khai_bao,
                '',
                '',
                ''
            ];
        }


        $result[] = ['II-PHẦN XUẤT KHẨU', '', '', '', '', ''];
        $result[] = ['STT', 'TÊN HÀNG', 'NGÀY XUẤT', 'SL XUẤT', 'SL TỒN', 'SỐ CONTAINER'];
        $stt = 1;

        foreach ($soToKhaiXuats as $soToKhaiXuat) {
            $lanXuats = NhapHang::where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
                ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $soToKhaiXuat)
                ->select(
                    'xuat_hang.ngay_dang_ky',
                    'xuat_hang_cont.phuong_tien_vt_nhap',
                    'xuat_hang_cont.*',
                    'hang_hoa.*',
                    'hang_trong_cont.ma_hang',
                    'hang_trong_cont.so_luong',
                    'hang_trong_cont.is_da_chuyen_cont',
                )
                ->get();

            foreach ($lanXuats as $item) {
                if (isset($seen[$item->ma_xuat_hang_cont])) {
                    continue;
                }
                $seen[$item->ma_xuat_hang_cont] = true;
                if (isset($hangHoaArr[$item->ma_hang])) {
                    $hangHoaArr[$item->ma_hang] -= $item->so_luong_xuat;
                }
                $result[] = [
                    $stt++,
                    $item->ten_hang,
                    Carbon::createFromFormat('Y-m-d', $item->ngay_dang_ky)->format('d-m-Y'),
                    $item->so_luong_xuat,
                    $hangHoaArr[$item->ma_hang] == 0 ? '0' : $hangHoaArr[$item->ma_hang],
                    $item->so_container,
                ];
            }
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
                foreach (['C', 'D', 'E', 'F'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');

                $sheet->getColumnDimension('B')->setWidth(width: 38);
                $sheet->getColumnDimension('A')->setWidth(width: 9);


                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('B1:B' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:F1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:F2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:F4'); // BÁO CÁO
                $sheet->mergeCells('A5:F5');
                $sheet->mergeCells('A7:F7');
                $sheet->mergeCells('A8:F8');
                $sheet->mergeCells('A9:F9');
                $sheet->mergeCells('A10:F10');
                $sheet->mergeCells('A12:F12');


                // Bold and center align for headers
                $sheet->getStyle('A1:F5')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:F5')->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                // Italic for date row
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A12:F13')->applyFromArray([
                    'font' => ['italic' => false, 'bold' => true],
                ]);

                // Bold and center align for "I-PHẦN ĐẾN CỬA KHẨU" headers
                $sheet->getStyle('A13:C13')->applyFromArray([
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

                // Find the row where "II-PHẦN XUẤT KHẨU" starts
                $secondTableStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === 'II-PHẦN XUẤT KHẨU') {
                        $secondTableStart = $i;
                        break;
                    }
                }

                if ($secondTableStart) {
                    // Bold and center align for "II-PHẦN XUẤT KHẨU" headers
                    $sheet->mergeCells('A' . $secondTableStart . ':F' . ($secondTableStart));

                    $sheet->getStyle('A' . $secondTableStart . ':F' . ($secondTableStart))->applyFromArray([
                        'font' => ['bold' => true]
                    ]);
                    $sheet->getStyle('A' . ($secondTableStart + 1) . ':F' . ($secondTableStart + 1))->applyFromArray([
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
                }

                // Add borders and alignment to all content
                $sheet->getStyle('A13:C' . ($secondTableStart-1))->applyFromArray([
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
                $sheet->getStyle('A' . ($secondTableStart + 1) . ':F' . $lastRow)->applyFromArray([
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


                $sheet->getStyle('B' . ($secondTableStart + 2) . ':B' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('B14:B' . ($secondTableStart - 1 ))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
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
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
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

                // Set left alignment for number columns
                // $sheet->getStyle('A13:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
