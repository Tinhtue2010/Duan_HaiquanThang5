<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\NhapCanh;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\XuatCanh;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoPhuongTienNhapCanh implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;

    public function __construct($tu_ngay, $den_ngay)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
    }
    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', '', '', '', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', '', '', '', '', '', '', '', '', 'Độc lập - Tự do - Hạnh phúc'],
            ['', '', '', '', '', ''],
            ['BÁO CÁO PHƯƠNG TIỆN NHẬP CẢNH', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Tên tàu', 'Quốc tịch tàu', 'Họ tên thuyền trưởng', 'Nhập cảnh', '', '', '', '', '', 'Xuất cảnh', '', '', 'Công chức làm thủ tục', 'Tên công ty nhập hàng', 'Tên đại lý làm thủ tục', 'Khác', 'Ghi chú'],
            ['', '', '', '', 'Cảng đi', 'Ngày nhập cảnh', 'Giờ nhập cảnh', 'Loại hàng', 'Số lượng hàng hóa', 'Đơn vị tính', 'Cảng đến', 'Ngày xuất cảnh', 'Giờ xuất cảnh'],
        ];
        $nhapCanhs = NhapCanh::join('ptvt_xuat_canh', 'nhap_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->join('cong_chuc', 'nhap_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->join('doanh_nghiep', 'nhap_canh.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('chu_hang', 'doanh_nghiep.ma_chu_hang', '=', 'doanh_nghiep.ma_chu_hang')
            ->where('nhap_canh.trang_thai', 2)
            ->whereBetween('nhap_canh.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay])
            ->groupBy('nhap_canh.ma_nhap_canh')
            ->get();


        foreach ($nhapCanhs as $key => $nhapCanh) {
            $ngayDangKys = XuatCanh::where('so_ptvt_xuat_canh', $nhapCanh->so_ptvt_xuat_canh)
                ->pluck('ngay_dang_ky')
                ->toArray();

            $targetDate = $nhapCanh->ngay_dang_ky;

            if (!empty($ngayDangKys)) {
                $nearestDate = collect($ngayDangKys)
                    ->filter(function ($date) use ($targetDate) {
                        return $date >= $targetDate;
                    })
                    ->sortDesc()
                    ->first();

                $nearestDate = $nearestDate ?? null;
            } else {
                $nearestDate = null;
            }


            $result[] = [
                $key + 1,
                $nhapCanh->ten_phuong_tien_vt ?? '',
                $nhapCanh->quoc_tich_tau,
                $nhapCanh->ten_thuyen_truong,
                'Vạn Gia',
                Carbon::parse($nhapCanh->ngay_duyet)->format('d-m-Y'),
                '',
                $nhapCanh->loai_hang ?? '',
                $nhapCanh->so_luong ?? '',
                $nhapCanh->don_vi_tinh ?? '',
                $nhapCanh->cang_den ?? '',
                !empty($nearestDate) ? Carbon::createFromFormat('Y-m-d', $nearestDate)->format('d-m-Y') : null,
                '',
                $nhapCanh->ten_cong_chuc,
                $nhapCanh->ten_doanh_nghiep,
                $nhapCanh->ten_chu_hang,
            ];
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
            '',
            '',
        ];

        return $result;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Set print settings first
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:R' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);


                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                foreach (['B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 10);
                $sheet->getColumnDimension('D')->setWidth(width: 10);
                $sheet->getColumnDimension('E')->setWidth(width: 12);
                $sheet->getColumnDimension('N')->setWidth(width: 15);
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 10);
                $sheet->getColumnDimension('R')->setWidth(width: 10);



                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('M1:R1');
                $sheet->mergeCells('M2:R2');

                $sheet->mergeCells('A4:R4');
                $sheet->mergeCells('A5:R5');

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:J7');
                $sheet->mergeCells('K7:M7');
                $sheet->mergeCells('N7:N8');
                $sheet->mergeCells('O7:O8');
                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');
                $sheet->mergeCells('R7:R8');


                // Your existing styles
                $sheet->getStyle('A1:R6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:R6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A9:R' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A5:R5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A7:R8')->applyFromArray([
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

                $sheet->getStyle('A7:R' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $event->sheet->getDelegate()->getStyle('N1')->getFont()->setBold(true);
            },
        ];
    }
}
