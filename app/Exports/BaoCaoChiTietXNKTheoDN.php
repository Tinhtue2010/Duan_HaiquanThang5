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

class BaoCaoChiTietXNKTheoDN implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;
    protected $ma_doanh_nghiep;

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
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Chi cục HQ đăng ký tờ khai', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', '', '', '', '', '', 'Số lượng tồn', 'Số phương tiện nhận hàng', 'Số tàu hiện tại', 'Số cont hiện tại'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)', 'Ngày xuất', 'Số lượng xuất', '', '', '', ''],
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
                'nhap_hang.so_to_khai_nhap',
                'xuat_hang.so_to_khai_xuat',
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

                // Set print settings first
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:S' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 15); //Số tờ khai
                $sheet->getColumnDimension('C')->setWidth(width: 12); //Ngày đăng ký
                $sheet->getColumnDimension('D')->setWidth(width: 15); //Chi cục
                $sheet->getColumnDimension('E')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('F')->setWidth(width: 15); //Mã DN
                $sheet->getColumnDimension('G')->setWidth(width: 25); //Địa chỉ
                $sheet->getColumnDimension('H')->setWidth(width: 25); //Tên hàng
                $sheet->getColumnDimension('I')->setWidth(width: 12); //Xuất xứ
                $sheet->getColumnDimension('N')->setWidth(width: 12); //Ngày
                $sheet->getColumnDimension('M')->setWidth(width: 15); //Trị giá
                $sheet->getColumnDimension('Q')->setWidth(width: 15);
                $sheet->getColumnDimension('R')->setWidth(width: 15);
                $sheet->getColumnDimension('S')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                $sheet->mergeCells('A4:R4');
                $sheet->mergeCells('A5:R5');

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:O7');
                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');
                $sheet->mergeCells('R7:R8');
                $sheet->mergeCells('S7:S8');

                // Your existing styles
                $sheet->getStyle('A1:S6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:S6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A9:S' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A5:S5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A7:S8')->applyFromArray([
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

                $sheet->getStyle('A7:S' . $lastRow)->applyFromArray([
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
