<?php

namespace App\Exports;

use App\Models\LoaiHang;
use App\Models\NhapHang;
use App\Models\HangHoa;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoHangHoaChuaThucXuat implements FromArray, WithEvents
{
    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year
        $result = [
            ['CỤC HẢI QUAN TỈNH QUẢNG NINH', '', '', '', '', ''],
            ['CHI CỤC HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['THEO DÕI HÀNG HÓA QUÁ 15 NGÀY CHƯA THỰC XUẤT', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ["STT", 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Chi cục HQ đăng ký tờ khai', 'Doanh nghiệp XK,NK', '', '', 'Phương tiện vận tải', 'Hàng hóa', '', '', '', '', '', 'Địa điểm xuất hàng', 'SL tồn', 'Ngày quá hạn', 'Số tàu hiện tại', 'Số cont hiện tại', 'Ghi chú'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Ký hiệu,Số Container,BKS PTVT', 'Chủng loại tên hàng hóa', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)'],
        ];
        $today = Carbon::now()->format('Y-m-d'); // Format now() as yyyy-mm-dd
        $cutoffDate = Carbon::now()->subDays(15);
        $stt = 1;

        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->where('nhap_hang.ngay_dang_ky', '<', $cutoffDate)
            ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
                DB::raw("(SELECT SUM(hh.so_luong_khai_bao) 
                    FROM hang_hoa hh 
                    WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"),
                DB::raw("(SELECT SUM(htc.so_luong) 
                    FROM hang_hoa hh 
                    JOIN hang_trong_cont htc ON hh.ma_hang = htc.ma_hang 
                    WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong"),
                DB::raw("MIN(hang_hoa.ma_hang) as ma_hang"),
                DB::raw("MIN(hang_hoa.ten_hang) as ten_hang"),
                DB::raw("MIN(hang_hoa.loai_hang) as loai_hang"),
                DB::raw("MIN(hang_hoa.xuat_xu) as xuat_xu"),
                DB::raw("MIN(hang_hoa.don_vi_tinh) as don_vi_tinh"),
                DB::raw("MIN(hang_hoa.don_gia) as don_gia"),
                DB::raw("MIN(hang_trong_cont.so_container) as so_container"),
                DB::raw("MIN(doanh_nghiep.ma_doanh_nghiep) as ma_doanh_nghiep"),
                DB::raw("MIN(doanh_nghiep.ten_doanh_nghiep) as ten_doanh_nghiep"),
                DB::raw("MIN(doanh_nghiep.dia_chi) as dia_chi"),
                DB::raw("MIN(hai_quan.ten_hai_quan) as ten_hai_quan"),
            )
            ->groupBy(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
            )
            ->get();
        $totalHangTon = 0;
        $totalKhaiBao = 0;

        $stt = 1;
        foreach ($nhapHangs as $item) {
            $ngayThongQuan = Carbon::parse($item->ngay_thong_quan);
            $ngayQuaHan = $ngayThongQuan->addDays(15)->format('d-m-Y');
            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::createFromFormat('Y-m-d', $item->ngay_dang_ky)->format('d-m-Y'),
                $item->ten_hai_quan ?? '',
                $item->ten_doanh_nghiep ?? '',
                $item->ma_doanh_nghiep,
                $item->dia_chi ?? '',
                $item->so_container . ', ' . $item->ptvt_ban_dau,
                $item->loai_hang,
                $item->xuat_xu,
                $item->total_so_luong_khai_bao,
                $item->don_vi_tinh,
                $item->trong_luong,
                $item->don_gia * $item->total_so_luong,
                '',
                $item->total_so_luong,
                $ngayQuaHan,
                $item->phuong_tien_vt_nhap,
                $item->so_container,
                ''
            ];
            $totalHangTon += $item->total_so_luong;
            $totalKhaiBao += $item->total_so_luong_khai_bao;
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
                    ->setPrintArea('A1:T' . $sheet->getHighestRow());

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
                foreach (['B', 'C', 'D', 'E', 'G', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 15); //Số tờ khai
                $sheet->getColumnDimension('C')->setWidth(width: 12); //Ngày đăng ký
                $sheet->getColumnDimension('D')->setWidth(width: 15); //Chi cục
                $sheet->getColumnDimension('E')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('F')->setWidth(width: 15); //Mã DN
                $sheet->getColumnDimension('G')->setWidth(width: 25); //Địa chỉ
                $sheet->getColumnDimension('H')->setWidth(width: 20);
                $sheet->getColumnDimension('I')->setWidth(width: 25); //Tên hàng
                $sheet->getColumnDimension('J')->setWidth(width: 12); //Xuất xứ
                $sheet->getColumnDimension('N')->setWidth(width: 15); //Trị giá
                $sheet->getColumnDimension('Q')->setWidth(width: 12);
                $sheet->getColumnDimension('R')->setWidth(width: 15);
                $sheet->getColumnDimension('S')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);


                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:T4'); // BÁO CÁO
                $sheet->mergeCells('A5:T5'); // Tính đến ngày

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('I7:N7');

                $sheet->mergeCells('O7:O8');
                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');
                $sheet->mergeCells('R7:R8');
                $sheet->mergeCells('S7:S8');
                $sheet->mergeCells('T7:T8');

                // Bold and center align for headers
                $sheet->getStyle('A1:T6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:T6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:T' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:T5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:T8')->applyFromArray([
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
                $sheet->getStyle('A7:T' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':T' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':T' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':T' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':T' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':T' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
