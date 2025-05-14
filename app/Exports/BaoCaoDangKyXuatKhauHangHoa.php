<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\LoaiHang;
use App\Models\NhapHang;
use App\Models\TheoDoiTruLui;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoDangKyXuatKhauHangHoa implements FromArray, WithEvents
{
    protected $ma_doanh_nghiep;
    protected $tu_ngay;

    public function __construct($ma_doanh_nghiep, $tu_ngay)
    {
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
        $this->tu_ngay = $tu_ngay;
    }
    public function array(): array
    {
        $tenDoanhNghiep = DoanhNghiep::find($this->ma_doanh_nghiep)->ten_doanh_nghiep;
        $tu_ngay = Carbon::createFromFormat('d/m/Y', $this->tu_ngay);

        $currentDate = $tu_ngay->format('d');  // Day of the month
        $currentMonth = $tu_ngay->format('m'); // Month number
        $currentYear = $tu_ngay->format('Y');  // Year

        $result = [
            [''],
            [$tenDoanhNghiep],
            ['BẢNG TỔNG HỢP ĐĂNG KÝ LÀM THỦ TỤC XUẤT KHẨU HÀNG HÓA'],
            ["NGÀY $currentDate THÁNG $currentMonth NĂM $currentYear"], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày tờ khai', 'Loại hàng', 'SL đăng ký xuất', 'SL tồn', 'Cont', 'Tàu', 'Số seal niêm phong', 'Lượng xuất chi tiết', 'Phương tiện nhận hàng', 'Số TK hết'],
        ];
        $stt = 1;
        $nhapHangArr = [];

        $nhapHangs = NhapHang::where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->select('nhap_hang.so_to_khai_nhap', DB::raw('SUM(hang_hoa.so_luong_khai_bao) as tong_so_luong'))
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->get();
        foreach ($nhapHangs as $nhapHang) {
            $nhapHangArr[$nhapHang->so_to_khai_nhap] = $nhapHang->tong_so_luong;
        }

        $date = Carbon::createFromFormat('d/m/Y', $this->tu_ngay)->format('Y-m-d');
        $lanXuats = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->whereDate('xuat_hang.ngay_dang_ky', '<=', $date)
            ->orderBy('nhap_hang.so_to_khai_nhap', 'asc')
            ->orderBy('xuat_hang_cont.so_container', 'asc')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_thong_quan',
                'nhap_hang.phuong_tien_vt_nhap',
                'hang_hoa.loai_hang',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.ma_hang_cont',
                'xuat_hang_cont.so_container',
                'xuat_hang_cont.so_seal_cuoi_ngay',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.ngay_dang_ky',
            )
            ->groupBy('xuat_hang_cont.ma_xuat_hang_cont')
            ->get();

        $groupedResults = [];

        foreach ($lanXuats as $item) {
            if (!isset($groupedResults[$item->so_to_khai_nhap])) {
                $groupedResults[$item->so_to_khai_nhap] = [
                    'ma_hang_cont' => $item->ma_hang_cont,
                    'so_to_khai_nhap' => $item->so_to_khai_nhap,
                    'ngay_thong_quan' => Carbon::createFromFormat('Y-m-d', $item->ngay_thong_quan)->format('d-m-Y'),
                    'loai_hang' => $item->loai_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_tong_xuat' => 0,
                    'so_container' => $item->so_container,
                    'so_seal_cuoi_ngay' => $item->so_seal_cuoi_ngay,
                    'phuong_tien_vt_nhap' => $item->phuong_tien_vt_nhap,
                    'ten_phuong_tien_vt' => $item->ten_phuong_tien_vt,
                    'ngay_dang_ky' => $item->ngay_dang_ky,
                ];
            }

            // Accumulate sum of so_luong_xuat
            if (Carbon::parse($item->ngay_dang_ky)->format('Y-m-d') == $date) {
                $groupedResults[$item->so_to_khai_nhap]['so_luong_xuat'] += $item->so_luong_xuat;
            }
            $groupedResults[$item->so_to_khai_nhap]['so_luong_tong_xuat'] += $item->so_luong_xuat;
        }

        $stt = 1;
        $prevSoToKhaiNhap = null;
        $prevSoContainer = null;
        $totalXuat = 0;


        foreach ($lanXuats as $item) {
            if (Carbon::parse($item->ngay_dang_ky)->format('Y-m-d') != $date) {
                continue;
            }

            $soLuongTon = $nhapHangArr[$item->so_to_khai_nhap] - $groupedResults[$item->so_to_khai_nhap]['so_luong_tong_xuat'];
            if ($item->so_to_khai_nhap !== $prevSoToKhaiNhap && $item->so_container !== $prevSoContainer) {
                $result[] = [
                    $stt++,
                    $item->so_to_khai_nhap,
                    Carbon::parse($item->ngay_thong_quan)->format('d-m-Y'),
                    $item->loai_hang,
                    $groupedResults[$item->so_to_khai_nhap]['so_luong_xuat'],
                    $soLuongTon == 0 ? '0' : $soLuongTon,
                    $item->so_container,
                    $item->phuong_tien_vt_nhap,
                    $item->so_seal_cuoi_ngay,
                    $item->so_luong_xuat,
                    $item->ten_phuong_tien_vt,
                    $soLuongTon == 0 ? 'Hết' : '',
                ];
            } elseif ($item->so_to_khai_nhap === $prevSoToKhaiNhap && $item->so_container !== $prevSoContainer) {
                $result[] = [
                    $stt++,
                    '',
                    '',
                    '',
                    '',
                    '',
                    $item->so_container,
                    $item->phuong_tien_vt_nhap,
                    $item->so_seal_cuoi_ngay,
                    $item->so_luong_xuat,
                    $item->ten_phuong_tien_vt,
                    $soLuongTon == 0 ? 'Hết' : '',
                ];
            } elseif ($item->so_to_khai_nhap !== $prevSoToKhaiNhap && $item->so_container === $prevSoContainer) {
                $result[] = [
                    $stt++,
                    $item->so_to_khai_nhap,
                    Carbon::parse($item->ngay_thong_quan)->format('d-m-Y'),
                    $item->loai_hang,
                    $groupedResults[$item->so_to_khai_nhap]['so_luong_xuat'],
                    $soLuongTon == 0 ? '0' : $soLuongTon,
                    $item->so_container,
                    $item->phuong_tien_vt_nhap,
                    $item->so_seal_cuoi_ngay,
                    $item->so_luong_xuat,
                    $item->ten_phuong_tien_vt,
                    $soLuongTon == 0 ? 'Hết' : '',
                ];
            } 
            else {
                $result[] = [
                    $stt++,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $item->so_luong_xuat,
                    $item->ten_phuong_tien_vt,
                    $soLuongTon == 0 ? 'Hết' : '',
                ];
            } 
            $prevSoToKhaiNhap = $item->so_to_khai_nhap;
            $prevSoContainer = $item->so_container;
            $totalXuat += $item->so_luong_xuat;
        }
        $result[] = [
            '',
            '',
            '',
            'Tổng',
            $totalXuat,
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
                    ->setPrintArea('A1:L' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 12);
                $sheet->getColumnDimension('D')->setWidth(width: 20);
                $sheet->getColumnDimension('E')->setWidth(width: 8);
                $sheet->getColumnDimension('F')->setWidth(width: 8);
                $sheet->getColumnDimension('G')->setWidth(width: 15);
                $sheet->getColumnDimension('H')->setWidth(width: 10);
                $sheet->getColumnDimension('I')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 7);
                $sheet->getColumnDimension('K')->setWidth(width: 20);
                $sheet->getColumnDimension('L')->setWidth(width: 10);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                $sheet->mergeCells('A4:L4');
                $sheet->mergeCells('A5:L5');



                $sheet->getStyle('A1:L6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A' . $lastRow . ':L' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                // Bold and center align for table headers
                $sheet->getStyle('A6:L6')->applyFromArray([
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
                $sheet->getStyle('A6:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                // Bold and center align for headers
                $sheet->getStyle('A1:L' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
            },
        ];
    }
}
