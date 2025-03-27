<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\LoaiHang;
use App\Models\NhapHang;
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
            ['STT', 'Số tờ khai', 'Ngày tờ khai', 'Tên hàng', 'SL đăng ký xuất', 'SL tồn', 'Cont', 'Tàu', 'Ghi chú', 'Phương Tiện', ''],
        ];
        $stt = 1;
        $hangHoaArr = [];

        $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->orderBy('xuat_hang_cont.ma_xuat_hang_cont', 'asc')
            ->get();
        foreach ($hangHoas as $hangHoa) {
            $hangHoaArr[$hangHoa->ma_hang_cont] = $hangHoa->so_luong_khai_bao;
        }
        // $filteredHangHoas = $hangHoas
        //     ->groupBy('ma_hang_cont') // Group by ma_hang_cont
        //     ->map(function ($items) {
        //         $lastItem = $items->last();
        //         $lastItem->so_luong_xuat = $items->sum('so_luong_xuat');

        //         return $lastItem;
        //     })
        //     ->values();
        $date = Carbon::createFromFormat('d/m/Y', $this->tu_ngay)->format('Y-m-d');
        $soToKhaiXuats = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->orderBy('xuat_hang.so_to_khai_xuat', 'asc') // Sorting from low to high
            ->pluck('xuat_hang.so_to_khai_xuat')
            ->unique()
            ->values();
        $groupedResults = [];
        foreach ($soToKhaiXuats as $soToKhaiXuat) {
            $lanXuats = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $soToKhaiXuat)
                ->select(
                    'nhap_hang.so_to_khai_nhap',
                    'nhap_hang.ngay_thong_quan',
                    'nhap_hang.phuong_tien_vt_nhap',
                    'hang_hoa.ten_hang',
                    'xuat_hang_cont.so_luong_xuat',
                    'xuat_hang_cont.ma_hang_cont',
                    'xuat_hang_cont.so_container',
                    'xuat_hang.ten_phuong_tien_vt',
                    'xuat_hang.ngay_dang_ky',
                )
                ->get();

            foreach ($lanXuats as $item) {

                
                if (!isset($groupedResults[$item->ma_hang_cont])) {
                    $groupedResults[$item->ma_hang_cont] = [
                        'ma_hang_cont' => $item->ma_hang_cont,
                        'so_to_khai_nhap' => $item->so_to_khai_nhap,
                        'ngay_thong_quan' => Carbon::createFromFormat('Y-m-d', $item->ngay_thong_quan)->format('d-m-Y'),
                        'ten_hang' => $item->ten_hang,
                        'so_luong_xuat' => 0,
                        'so_container' => $item->so_container,
                        'phuong_tien_vt_nhap' => [],
                        'ten_phuong_tien_vt' => $item->ten_phuong_tien_vt,
                        'ngay_dang_ky' => $item->ngay_dang_ky,
                        'hangHoaArr' => isset($hangHoaArr[$item->ma_hang_cont]) ? $hangHoaArr[$item->ma_hang_cont] : 'Hết',
                    ];
                }

                // Accumulate sum of so_luong_xuat
                $groupedResults[$item->ma_hang_cont]['so_luong_xuat'] += $item->so_luong_xuat;

                // Store unique phuong_tien_vt_nhap values
                if (!in_array($item->phuong_tien_vt_nhap, $groupedResults[$item->ma_hang_cont]['phuong_tien_vt_nhap'])) {
                    $groupedResults[$item->ma_hang_cont]['phuong_tien_vt_nhap'][] = $item->phuong_tien_vt_nhap;
                }

                // Update last hangHoaArr value
                if (isset($hangHoaArr[$item->ma_hang_cont])) {
                    $hangHoaArr[$item->ma_hang_cont] -= $item->so_luong_xuat;
                    $groupedResults[$item->ma_hang_cont]['hangHoaArr'] = $hangHoaArr[$item->ma_hang_cont];
                }
            }
        }

        $stt = 1;
        foreach ($groupedResults as $lastEntry) {
            if (Carbon::parse($lastEntry['ngay_dang_ky'])->format('Y-m-d') != $date) {
                continue;
            }
            $result[] = [
                $stt++,
                $lastEntry['so_to_khai_nhap'],
                $lastEntry['ngay_thong_quan'],
                $lastEntry['ten_hang'],
                $lastEntry['so_luong_xuat'], // Sum of all so_luong_xuat
                $lastEntry['hangHoaArr'] == 0 ? '0' : $lastEntry['hangHoaArr'],
                $lastEntry['so_container'],
                implode('; ', $lastEntry['phuong_tien_vt_nhap']), // Convert array to ';'-separated string
                '',
                $lastEntry['ten_phuong_tien_vt'],
                $lastEntry['hangHoaArr'] == 0 ? 'Hết' : '',
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
                    ->setPrintArea('A1:K' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('D')->setWidth(width: 35);
                $sheet->getColumnDimension('E')->setWidth(width: 8);
                $sheet->getColumnDimension('F')->setWidth(width: 8);
                $sheet->getColumnDimension('G')->setWidth(width: 20);
                $sheet->getColumnDimension('H')->setWidth(width: 20);
                $sheet->getColumnDimension('I')->setWidth(width: 25);
                $sheet->getColumnDimension('J')->setWidth(width: 20);
                $sheet->getColumnDimension('K')->setWidth(width: 7);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');
                $sheet->mergeCells('A4:K4');
                $sheet->mergeCells('A5:K5');



                $sheet->getStyle('A1:K6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A6:K6')->applyFromArray([
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
                $sheet->getStyle('A6:K' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                // Bold and center align for headers
                $sheet->getStyle('A1:K' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
            },
        ];
    }
}
