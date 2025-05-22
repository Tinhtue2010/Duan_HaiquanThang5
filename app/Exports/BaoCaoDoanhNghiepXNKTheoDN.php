<?php

namespace App\Exports;

use App\Models\LoaiHang;
use App\Models\NhapHang;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoDoanhNghiepXNKTheoDN implements FromArray, WithEvents
{
    protected $ma_doanh_nghiep;
    protected $tu_ngay;
    protected $den_ngay;

    public function __construct($tu_ngay, $den_ngay, $ma_doanh_nghiep)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
    }
    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO CHI TIẾT HÀNG HÓA XUẤT NHẬP KHẨU', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Chi cục HQ đăng ký tờ khai', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', '', '', '', '', 'Số lượng tồn', 'Số tàu hiện tại', 'Số cont hiện tại'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Chủng loại tên hàng hóa', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)', 'Đã xuất', '', '', ''],
        ];

        $stt = 1;
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->whereBetween('xuat_hang.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay])
            ->where('xuat_hang.trang_thai', '!=', 0)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
                DB::raw("(SELECT SUM(hh.so_luong_khai_bao) 
                  FROM hang_hoa hh 
                  WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong_khai_bao"),
                DB::raw("MIN(hang_hoa.ma_hang) as ma_hang"),
                DB::raw("MIN(hang_hoa.loai_hang) as loai_hang"),
                DB::raw("MIN(hang_hoa.xuat_xu) as xuat_xu"),
                DB::raw("MIN(hang_hoa.don_vi_tinh) as don_vi_tinh"),
                DB::raw("MIN(hang_hoa.don_gia) as don_gia"),
                DB::raw("MIN(hang_trong_cont.so_container) as so_container"),
                DB::raw("MIN(doanh_nghiep.ma_doanh_nghiep) as ma_doanh_nghiep"),
                DB::raw("MIN(doanh_nghiep.ten_doanh_nghiep) as ten_doanh_nghiep"),
                DB::raw("MIN(doanh_nghiep.dia_chi) as dia_chi"),
                DB::raw("MIN(hai_quan.ten_hai_quan) as ten_hai_quan"),
                DB::raw("MIN(xuat_hang.ngay_xuat_canh) as ngay_xuat_canh"),
                DB::raw("MIN(xuat_hang.ten_phuong_tien_vt) as ten_phuong_tien_vt"),
                DB::raw("(SELECT SUM(htc.so_luong) 
                FROM hang_hoa hh 
                JOIN hang_trong_cont htc ON hh.ma_hang = htc.ma_hang 
                WHERE hh.so_to_khai_nhap = nhap_hang.so_to_khai_nhap) AS total_so_luong"),
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
            if ($item->total_so_luong != 0) {
                $result[] = [
                    $stt++,
                    $item->so_to_khai_nhap,
                    Carbon::createFromFormat('Y-m-d', $item->ngay_dang_ky)->format('d-m-Y'),
                    $item->ten_hai_quan,
                    $item->ten_doanh_nghiep,
                    $item->ma_doanh_nghiep,
                    $item->dia_chi,
                    $item->loai_hang,
                    $item->xuat_xu,
                    $item->total_so_luong_khai_bao,
                    $item->don_vi_tinh,
                    $item->trong_luong,
                    $item->don_gia * $item->total_so_luong,
                    ($item->total_so_luong_khai_bao - $item->total_so_luong) == 0 ? '0' : ($item->total_so_luong_khai_bao - $item->total_so_luong),
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
            $totalKhaiBao,
            '',
            '',
            '',
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
                    ->setPrintArea('A1:Q' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                // Auto-width columns
                // Auto-width columns
                foreach (['I', 'J', 'K', 'L', 'N'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 15); //Số tờ khai
                $sheet->getColumnDimension('C')->setWidth(width: 12); //Ngày đăng ký
                $sheet->getColumnDimension('D')->setWidth(width: 15); //Chi cục
                $sheet->getColumnDimension('E')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('F')->setWidth(width: 15); //Mã DN
                $sheet->getColumnDimension('G')->setWidth(width: 25); //Địa chỉ
                $sheet->getColumnDimension('H')->setWidth(width: 25); //Tên hàng
                $sheet->getColumnDimension('M')->setWidth(width: 15); //Trị giá
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:Q4'); // BÁO CÁO
                $sheet->mergeCells('A5:Q5'); // Tính đến ngày

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:N7');

                $sheet->mergeCells('O7:O8');
                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');


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
            },
        ];
    }
}
