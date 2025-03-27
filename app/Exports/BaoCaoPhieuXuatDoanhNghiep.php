<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanh;
use App\Models\PTVTXuatCanhCuaPhieu;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoPhieuXuatDoanhNghiep implements FromArray, WithEvents
{
    protected $ma_doanh_nghiep;
    protected $tu_ngay;
    protected $den_ngay;

    public function __construct($ma_doanh_nghiep, $tu_ngay, $den_ngay)
    {
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
    }

    public function array(): array
    {
        $data = NhapHang::where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'hang_hoa.ten_hang',
                'hang_hoa.so_luong_khai_bao',
                'hang_hoa.don_vi_tinh',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.lan_xuat_canh',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.so_container',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_phuong_tien_vt',
            )
            ->distinct()
            ->get();

        $doanhNghiep = DoanhNghiep::find($this->ma_doanh_nghiep);
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII'],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA'],
            ['', '', '', '', '', ''],
            ['BÁO CÁO PHIẾU XUẤT CỦA DOANH NGHIỆP', '', '', '', '', ''],
            ["Từ $this->tu_ngay đến $this->den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['Mã doanh nghiệp: ' . $this->ma_doanh_nghiep, '', '', '', '', ''],
            ['Tên doanh nghiệp: ' . $doanhNghiep->ten_doanh_nghiep, '', '', '', ''],
            ['', '', '', '', '', ''],
            ['Stt', 'Số tờ khai', 'Tên hàng', 'Đơn vị tính', 'Số lượng', 'Lần xuất cảnh', 'Loại hình', 'Số lượng xuất', 'Phương tiện nhận hàng', 'Trạng thái', 'Ngày xuất hàng','Số container'],
        ];

        $stt = 1;
        $counts = []; // To keep track of occurrences of each so_to_khai_nhap

        foreach ($data as $item) {
            // Increment counter for so_to_khai_nhap or initialize it to 1
            $counts[$item->so_to_khai_nhap] = ($counts[$item->so_to_khai_nhap] ?? 0) + 1;

            $trangThai = '';
            if($item->trang_thai == "1"){
                $trangThai = "Đang chờ duyệt";
            } elseif($item->trang_thai == "2"){
                $trangThai = "Đã duyệt";
            } elseif($item->trang_thai == "3"){
                $trangThai = "Doanh nghiệp yêu cầu sửa phiếu chờ duyệt";
            } elseif($item->trang_thai == "4"){
                $trangThai = "Doanh nghiệp yêu cầu sửa phiếu đã duyệt";
            } elseif($item->trang_thai == "5"){
                $trangThai = "Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC";
            } elseif($item->trang_thai == "6"){
                $trangThai = "Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng";
            } elseif($item->trang_thai == "7"){
                $trangThai = "Doanh nghiệp yêu cầu hủy phiếu chờ duyệt";
            } elseif($item->trang_thai == "8"){
                $trangThai = "Doanh nghiệp yêu cầu hủy phiếu đã duyệt";
            } elseif($item->trang_thai == "9"){
                $trangThai = "Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC";
            } elseif($item->trang_thai == "10"){
                $trangThai = "Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng";
            } elseif($item->trang_thai == "11"){
                $trangThai = "Đã chọn phương tiện xuất cảnh";
            } elseif($item->trang_thai == "12"){
                $trangThai = "Đã duyệt xuất hàng";
            } elseif($item->trang_thai == "13"){
                $trangThai = "Đã thực xuất hàng";
            }

            $result[] = [
                $stt++, // Auto-incremented row number
                $item->so_to_khai_nhap,
                $item->ten_hang,
                $item->don_vi_tinh,
                $item->so_luong_khai_bao,
                $counts[$item->so_to_khai_nhap], // Count of occurrences (starting from 1)
                $item->ma_loai_hinh,
                $item->so_luong_xuat,
                $item->ten_phuong_tien_vt,
                $trangThai,
                isset($item->ngay_xuat_canh) ? Carbon::parse($item->ngay_xuat_canh)->format('d-m-Y') : '',
                $item->so_container,
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
                    ->setPrintArea('A1:L' . $sheet->getHighestRow());

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
                foreach (['B', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 5);
                $sheet->getColumnDimension('C')->setWidth(width: 38);
                $sheet->getColumnDimension('L')->setWidth(width: 20);
                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0');


                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('C1:C' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:F1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('A4:L4'); // BÁO CÁO
                $sheet->mergeCells('A5:L5'); // Tính đến ngày
                $sheet->mergeCells('A7:F7'); // Mã doanh nghiệp
                $sheet->mergeCells('A8:E8'); // Tên doanh nghiệp

                // Bold and center align for headers
                $sheet->getStyle('A1:L6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:L6')->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                // Italic for date row
                $sheet->getStyle('A5:L5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A10:L10')->applyFromArray([
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
                $sheet->getStyle('A10:L' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
