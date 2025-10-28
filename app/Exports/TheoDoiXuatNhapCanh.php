<?php

namespace App\Exports;

use App\Models\CongChuc;
use App\Models\XuatNhapCanh;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class TheoDoiXuatNhapCanh implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;
    protected $tongNhapCanh = 0;
    protected $tongDaXuatCanh = 0;
    protected $tongChuaXuatCanh = 0;
    protected $hangLanhNhap = 0;
    protected $hangNongNhap = 0;
    protected $hangNongChuaXuat = 0;
    protected $hangLanhChuaXuat = 0;
    protected $luyKeHangLanh = 0;
    protected $luyKeHangNong = 0;

    public function __construct($tu_ngay, $den_ngay)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
    }
    public function array(): array
    {
        $query = XuatNhapCanh::leftJoin('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->leftJoin('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->leftJoin('chu_hang', 'xuat_nhap_canh.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            // ->whereBetween('ngay_them', [$this->tu_ngay, $this->den_ngay]);
            ->whereRaw("STR_TO_DATE(CONCAT(ngay_them, ' ', REPLACE(REPLACE(thoi_gian_nhap_canh, 'H', ':'), 'h', ':')), '%Y-%m-%d %H:%i') >= ?", [$this->tu_ngay . ' 06:00'])
            ->whereRaw("STR_TO_DATE(CONCAT(ngay_them, ' ', REPLACE(REPLACE(thoi_gian_nhap_canh, 'H', ':'), 'h', ':')), '%Y-%m-%d %H:%i') <= DATE_ADD(?, INTERVAL 1 DAY)", [$this->den_ngay . ' 06:00']);
        // Get all data once and calculate statistics
        $data = $query->get();

        $this->tongNhapCanh = $data->whereNotNull('thoi_gian_nhap_canh')->count();
        $this->tongDaXuatCanh = $data->whereNotNull('thoi_gian_xuat_canh')->count();
        $this->tongChuaXuatCanh = $data->whereNull('thoi_gian_xuat_canh')->count();
        $this->hangLanhNhap = $data->where('is_hang_lanh', 1)->count();
        $this->hangNongNhap = $data->where('is_hang_nong', 1)->count();
        $this->hangNongChuaXuat = $data->where('is_hang_nong', 1)->whereNull('thoi_gian_xuat_canh')->count();
        $this->hangLanhChuaXuat = $data->where('is_hang_lanh', 1)->whereNull('thoi_gian_xuat_canh')->count();

        $month = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('m');
        $year = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('Y');
        $startOfMonth = Carbon::create($year, $month, 1)->format('Y-m-d');

        $query2 = XuatNhapCanh::leftJoin('cong_chuc', 'xuat_nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->leftJoin('ptvt_xuat_canh', 'xuat_nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->leftJoin('chu_hang', 'xuat_nhap_canh.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            ->whereRaw("STR_TO_DATE(CONCAT(ngay_them, ' ', REPLACE(REPLACE(thoi_gian_nhap_canh, 'H', ':'), 'h', ':')), '%Y-%m-%d %H:%i') >= ?", [$startOfMonth . ' 06:00:00'])
            ->whereRaw("STR_TO_DATE(CONCAT(ngay_them, ' ', REPLACE(REPLACE(thoi_gian_nhap_canh, 'H', ':'), 'h', ':')), '%Y-%m-%d %H:%i') <= DATE_ADD(?, INTERVAL 1 DAY)", [$this->den_ngay . ' 06:00'])
            ->get();

        $this->luyKeHangLanh = $query2->where('is_hang_lanh', 1)->count();
        $this->luyKeHangNong = $query2->where('is_hang_nong', 1)->count();

        $data = $query->get();

        $congChucs = $data->pluck('ten_cong_chuc')->unique()->implode(' - ');

        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['THEO DÕI PHƯƠNG TIỆN VẬN TẢI XUẤT NHẬP CẢNH TẠI KHU VỰC ĐẦU TÁN', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['', 'Người trực: ' . $congChucs, '', '', '', ''],
            ['STT', 'SỐ THẺ', 'SỐ PTVT XNC', '', 'ĐẠI LÝ', 'TỔNG TRỌNG TẢI (Tấn)', 'SỐ LƯỢNG MÁY', 'THỜI GIAN NHẬP CẢNH', 'THỜI GIAN XUẤT CẢNH', 'GHI CHÚ'],
            ['', '', 'HÀNG LẠNH', 'HÀNG NÓNG'],
        ];


        $stt = 1;
        foreach ($data as $item) {
            $result[] = [
                $stt++,
                $item->so_the,
                $item->is_hang_lanh == 1 ? $item->ten_phuong_tien_vt : '',
                $item->is_hang_nong == 1 ? $item->ten_phuong_tien_vt : '',
                $item->ten_chu_hang,
                $item->tong_trong_tai,
                $item->so_luong_may,
                $item->thoi_gian_nhap_canh,
                $item->thoi_gian_xuat_canh,
                $item->ghi_chu,
            ];
        }
        $result[] = [
            [''],
            [''],
            ['NGƯỜI TRỰC THỨ NHẤT', '', '', '', '', '', 'NGƯỜI TRỰC THỨ II'],
            ['(Ký ghi rõ họ tên)', '', '', '', '', '', '(Ký ghi rõ họ tên)']
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
                    ->setPrintArea('A1:M' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                $sheet->getDelegate()->getSheetView()->setZoomScale(145);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 7);
                $sheet->getColumnDimension('C')->setWidth(width: 15);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 20);
                $sheet->getColumnDimension('F')->setWidth(width: 10);
                $sheet->getColumnDimension('G')->setWidth(width: 10);
                $sheet->getColumnDimension('H')->setWidth(width: 10);
                $sheet->getColumnDimension('I')->setWidth(width: 10);
                $sheet->getColumnDimension('J')->setWidth(width: 15);
                $sheet->getColumnDimension('K')->setWidth(width: 20);
                $sheet->getColumnDimension('L')->setWidth(width: 15);
                $sheet->getColumnDimension('M')->setWidth(width: 15);
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle('E1:C' . $lastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A2:M' . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A4:M4');
                $sheet->mergeCells('A5:M5');
                $sheet->mergeCells('A6:M6');

                $sheet->mergeCells('C8:D8');
                $sheet->mergeCells('B7:I7');

                $sheet->mergeCells('A8:A9');
                $sheet->mergeCells('B8:B9');
                $sheet->mergeCells('E8:E9');
                $sheet->mergeCells('F8:F9');
                $sheet->mergeCells('G8:G9');
                $sheet->mergeCells('H8:H9');
                $sheet->mergeCells('I8:I9');
                $sheet->mergeCells('J8:J9');

                // Changed K->J, L->K, M->L
                $sheet->setCellValue('K8', "TỔNG SỐ PHƯƠNG TIỆN NHẬP CẢNH");
                $sheet->setCellValue('L8', $this->tongNhapCanh);
                $sheet->setCellValue('L9', "HÀNG LẠNH");
                $sheet->setCellValue('M9', "HÀNG NÓNG");
                $sheet->setCellValue('L10', $this->hangLanhNhap);
                $sheet->setCellValue('M10', $this->hangNongNhap);
                $sheet->setCellValue('K11', "PHƯƠNG TIỆN ĐÃ XUẤT CẢNH");
                $sheet->setCellValue('L11', $this->tongDaXuatCanh);
                $sheet->setCellValue('K12', "PHƯƠNG TIỆN CHƯA XUẤT CẢNH");
                $sheet->setCellValue('L13', "HÀNG LẠNH");
                $sheet->setCellValue('M13', "HÀNG NÓNG");
                $sheet->setCellValue('L12', $this->tongChuaXuatCanh);
                $sheet->setCellValue('L14', $this->hangLanhChuaXuat);
                $sheet->setCellValue('M14', $this->hangNongChuaXuat);
                $sheet->setCellValue('K15', "LŨY KẾ TỪ ĐẦU THÁNG");
                $sheet->setCellValue('L15', $this->luyKeHangLanh + $this->luyKeHangNong);
                $sheet->setCellValue('L16', "HÀNG LẠNH");
                $sheet->setCellValue('M16', "HÀNG NÓNG");
                $sheet->setCellValue('L17', $this->luyKeHangLanh);
                $sheet->setCellValue('M17', $this->luyKeHangNong);

                $sheet->mergeCells('K8:K10');
                $sheet->mergeCells('L8:M8');
                $sheet->mergeCells('L11:M11');

                $sheet->mergeCells('K12:K14');
                $sheet->mergeCells('L12:M12');

                $sheet->mergeCells('K15:K17');
                $sheet->mergeCells('L15:M15');



                $sheet->getStyle('L9')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '0096FF']
                    ]
                ]);
                $sheet->getStyle('M9')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000']
                    ]
                ]);
                $sheet->getStyle('L13')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '0096FF']
                    ]
                ]);
                $sheet->getStyle('M13')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000']
                    ]
                ]);
                $sheet->getStyle('L16')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '0096FF']
                    ]
                ]);
                $sheet->getStyle('M16')->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000']
                    ]
                ]);
                $sheet->getStyle('L8')->applyFromArray([
                    'font' => [
                        'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
                    ]
                ]);
                $sheet->getStyle('L11')->applyFromArray([
                    'font' => [
                        'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
                    ]
                ]);
                $sheet->getStyle('L12')->applyFromArray([
                    'font' => [
                        'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
                    ]
                ]);
                $sheet->getStyle('L15')->applyFromArray([
                    'font' => [
                        'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE
                    ]
                ]);


                $event->sheet->getDelegate()->getRowDimension(8)->setRowHeight(30);
                $event->sheet->getDelegate()->getRowDimension(9)->setRowHeight(30);

                $sheet->getStyle('A1:K9')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:K9')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                $sheet->getStyle('B2:B' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A8:M' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('J2:M' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                $sheet->getStyle('A8:J8')->applyFromArray([
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
                $sheet->getStyle('J8:M17')->applyFromArray([
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

                $sheet->getStyle('B7')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                // Add borders to the table content
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A8:J' . $lastRow)->applyFromArray([
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
                    if ($sheet->getCell('A' . $i)->getValue() === "NGƯỜI TRỰC THỨ NHẤT") {
                        $chuKyStart = $i;
                        break;
                    }
                }
                $sheet->getStyle('A' . ($chuKyStart - 2) . ':J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':E' . $chuKyStart);
                $sheet->mergeCells('A' . ($chuKyStart + 1) . ':E' . ($chuKyStart + 1));
                $sheet->mergeCells('F' . $chuKyStart . ':J' . $chuKyStart);
                $sheet->mergeCells('F' . ($chuKyStart + 1) . ':J' . ($chuKyStart + 1));
                $sheet->getStyle('A' . ($chuKyStart) . ':J' . ($chuKyStart + 1))->getFont()->setBold(true);
            },
        ];
    }
}
