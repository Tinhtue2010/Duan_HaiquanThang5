<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\XuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\XuatHangCont;
use App\Models\XuatNhapCanh;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoPhuongTienChuaXuatCanh implements FromArray, WithEvents
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
            ['BÁO CÁO PHƯƠNG TIỆN VẬN TẢI CHƯA XUẤT CẢNH', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Chi cục HQ đăng ký tờ khai', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', 'SỐ PTVT XNC', '', 'Giờ nhập cảnh', 'Đại lý', 'Công chức giám sát', 'Ghi chú'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Loại hàng', 'Số lượng xuất', 'ĐVT', 'HÀNG LẠNH', 'HÀNG NÓNG', '', ''],
        ];
        $stt = 1;
        // $this->tu_ngay = Carbon::yesterday()->toDateString();
        // $this->den_ngay = Carbon::today()->toDateString();
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('ptvt_xuat_canh_cua_phieu', 'xuat_hang.so_to_khai_xuat', '=', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->join('cong_chuc', 'xuat_hang.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->join('chu_hang', 'nhap_hang.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            ->join('ptvt_xuat_canh', 'ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->whereBetween('xuat_hang.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay])
            ->whereNotIn('xuat_hang.trang_thai', [0])
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.phuong_tien_vt_nhap',
                'hang_hoa.ma_hang',
                'hang_hoa.ten_hang',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.loai_hang',
                'hang_trong_cont.so_container',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.dia_chi',
                'hai_quan.ten_hai_quan',
                'chu_hang.ten_chu_hang',
                'cong_chuc.ten_cong_chuc',
                'xuat_hang.ngay_dang_ky as ngay_xuat',
                'xuat_hang.ten_phuong_tien_vt',
                'ptvt_xuat_canh.ten_phuong_tien_vt',
                'ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
            )
            ->groupBy('nhap_hang.so_to_khai_nhap', 'xuat_hang.so_to_khai_xuat')
            ->get();
        $stt = 1;
        foreach ($nhapHangs as $item) {
            // $xuatCanh = XuatCanh::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.ma_xuat_canh', '=', 'xuat_canh.ma_xuat_canh')
            //     ->where('xuat_canh_chi_tiet.so_to_khai_xuat', $item->so_to_khai_xuat)
            //     ->whereNotIn('xuat_canh.trang_thai', [0, 1])
            //     ->first();
            // if ($xuatCanh) {
            //     continue;
            // }
            $nhapCanh = XuatNhapCanh::where('so_ptvt_xuat_canh', $item->so_ptvt_xuat_canh)
                ->whereBetween('ngay_them', [$this->tu_ngay, $this->den_ngay])
                ->orderByRaw('ABS(DATEDIFF(ngay_them, ?))', [$item->ngay_xuat])
                ->first();

            // $nhapCanh = $nhapCanh ?? (object)[
            //     'is_hang_lanh' => null,
            //     'is_hang_nong' => null,
            //     'thoi_gian_nhap_canh' => null,
            //     'thoi_gian_xuat_canh' => null,
            // ];

            if ($nhapCanh === null) {
                continue;
            }

            if ($nhapCanh->thoi_gian_xuat_canh != null) {
                continue;
            }

            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::createFromFormat('Y-m-d', $item->ngay_dang_ky)->format('d-m-Y'),
                $item->ten_hai_quan,
                $item->ten_doanh_nghiep,
                $item->ma_doanh_nghiep,
                $item->dia_chi,
                $item->loai_hang,
                $item->tong_so_luong_xuat,
                $item->don_vi_tinh,
                $nhapCanh->is_hang_lanh == 1 ? $item->ten_phuong_tien_vt : '',
                $nhapCanh->is_hang_nong == 1 ? $item->ten_phuong_tien_vt : '',
                $nhapCanh->thoi_gian_nhap_canh ?? '',
                $item->ten_chu_hang,
                $item->ten_cong_chuc,
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
                    ->setPrintArea('A1:P' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('H')->setWidth(width: 15); //Tên hàng
                $sheet->getColumnDimension('I')->setWidth(width: 12); //Xuất xứ
                $sheet->getColumnDimension('N')->setWidth(width: 12); //Ngày
                $sheet->getColumnDimension('M')->setWidth(width: 15); //Trị giá


                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                $sheet->mergeCells('A4:P4');
                $sheet->mergeCells('A5:P5');

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:J7');
                $sheet->mergeCells('K7:L7');
                $sheet->mergeCells('M7:M8');
                $sheet->mergeCells('N7:N8');
                $sheet->mergeCells('O7:O8');
                $sheet->mergeCells('P7:P8');

                // Your existing styles
                $sheet->getStyle('A1:P6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:P6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A9:P' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A5:P5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A7:P8')->applyFromArray([
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

                $sheet->getStyle('A7:P' . $lastRow)->applyFromArray([
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
