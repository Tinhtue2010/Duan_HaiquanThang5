<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoSoSanhCacThoiDiem implements FromArray, WithEvents
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
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BẢNG TỔNG HỢP SỐ LIỆU HÀNG HÓA XUẤT NHẬP KHẨU', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['Tháng/năm', 'TIẾP NHẬN', '', '', '', '', '', '', '', '', 'GIÁM SÁT'],
            ['', 'Số tờ khai tiếp nhận', 'Đông lạnh', '', 'Thuốc lá', '', 'Rượu', '', 'Hàng khác', '', 'Đông lạnh', '', 'Thuốc lá', '', 'Rượu', '', 'Hàng khác'],
            ['', '', 'Số lượng (cont)', 'Trị giá (USD)', 'Số lượng (Kiện)', 'Trị giá (USD)', 'Số lượng (cont)', 'Trị giá (USD)', 'Số lượng (cont)', 'Trị giá (USD)', 'Số lượng (Kiện)', 'Trị giá (USD)', 'Số lượng (cont)', 'Trị giá (USD)'],
        ];
        $stt = 1;

        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->whereBetween('xuat_hang.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay])
            ->where('xuat_hang.trang_thai', '!=', 0)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
                'hang_hoa.so_luong_khai_bao',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.don_gia',
                'hang_trong_cont.so_container',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.dia_chi',
                'hai_quan.ten_hai_quan',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.so_luong_ton',
            )
            ->groupBy(
                'xuat_hang_cont.ma_xuat_hang_cont',
            )
            ->get();

        $totalXuat = 0;
        $totalKhaiBao = 0;
        $totalHangTon = 0;
        $stt = 1;
        foreach ($nhapHangs as $item) {
            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::createFromFormat('Y-m-d', $item->ngay_dang_ky)->format('d-m-Y'),
                $item->ten_hai_quan,
                $item->ten_doanh_nghiep,
                $item->ma_doanh_nghiep,
                $item->dia_chi,
                $item->ten_hang,
                $item->xuat_xu,
                $item->so_luong_khai_bao,
                $item->don_vi_tinh,
                $item->trong_luong,
                $item->don_gia * $item->so_luong_xuat,
                isset($item->ngay_dang_ky) ? Carbon::parse($item->ngay_dang_ky)->format('d-m-Y') : '',
                $item->so_luong_xuat,
                $item->so_luong_ton > 0 ? $item->so_luong_ton : '0',
                $item->ten_phuong_tien_vt ?? '',
                $item->phuong_tien_vt_nhap,
                $item->so_container,
            ];
            $totalXuat += $item->so_luong_xuat;
            $totalKhaiBao += $item->so_luong_khai_bao;
            $totalHangTon += $item->so_luong_ton > 0 ? $item->so_luong_ton : 0;
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
            '',
            $totalXuat,
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
                    ->setPrintArea('A1:R' . $sheet->getHighestRow());

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
                    $sheet->getStyle($column)->getNumberFormat()->setFormatCode('#,##0');
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 12);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 15);
                $sheet->getColumnDimension('G')->setWidth(width: 25);
                $sheet->getColumnDimension('H')->setWidth(width: 25);
                $sheet->getColumnDimension('I')->setWidth(width: 12);
                $sheet->getColumnDimension('N')->setWidth(width: 12);
                $sheet->getColumnDimension('M')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);
                $sheet->getColumnDimension('R')->setWidth(width: 15);
                $sheet->getColumnDimension('S')->setWidth(width: 15);


                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                $sheet->mergeCells('A4:R4');
                $sheet->mergeCells('A5:R5');

                $sheet->mergeCells('B7:J7');
                $sheet->mergeCells('B8:B9');
                $sheet->mergeCells('C8:D8');
                $sheet->mergeCells('E8:F8');
                $sheet->mergeCells('G8:H8');
                $sheet->mergeCells('I8:J8');
                $sheet->mergeCells('K8:L8');
                $sheet->mergeCells('M8:N8');
                $sheet->mergeCells('O8:P8');
                $sheet->mergeCells('Q8:R8');

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
            },
        ];
    }
    public function getDataNhap($loaiHang, $tu_ngay, $den_ngay)
    {
        return HangHoa::where('loai_hang', $loaiHang->ten_loai_hang)
            ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->whereBetween('nhap_hang.created_at', [
                Carbon::parse($tu_ngay)->startOfDay(),
                Carbon::parse($den_ngay)->endOfDay()
            ])
            ->selectRaw('
            SUM(hang_hoa.tri_gia) as total_tri_gia,
            SUM(hang_hoa.so_luong_khai_bao) as total_so_luong,
            COUNT(DISTINCT hang_hoa.so_container_khai_bao) as total_so_container
        ')
            ->first();
    }
}
