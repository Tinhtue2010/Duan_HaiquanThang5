<?php

namespace App\Exports;

use App\Models\CongChuc;
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
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoGiamSatXuatKhau implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;
    protected $ma_cong_chuc;

    public function __construct($tu_ngay, $den_ngay, $ma_cong_chuc)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
        $this->ma_cong_chuc = $ma_cong_chuc;
    }
    public function array(): array
    {
        $ten_cong_chuc = CongChuc::find($this->ma_cong_chuc)->ten_cong_chuc;
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['BÁO CÁO GIÁM SÁT HÀNG HÓA XUẤT KHẨU', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], // Updated line
            ['Công chức phụ trách: ' . $ten_cong_chuc, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['STT', 'Tên doanh nghiệp', 'Đại lý', 'Số tờ khai', 'Số lượng kiện xuất', '', '', '', '', 'Số lượng cont hết', 'Số lượng xuồng', 'Tờ khai xuất hết', 'Ngày xuất', 'Ghi chú', 'Stt', 'Tờ khai xuất hết', 'Doanh nghiệp', 'Loại hàng', 'Ghi chú'],
            ['', '', '', '', 'Hàng đông lạnh', 'Thuốc lá', 'Cigar', 'Rượu', 'Hàng khác', '', '', '', '', '', '', ''],
        ];
        $loaiHangArr = ['Đông lạnh', 'Thuốc lá', 'Cigar', 'Rượu', 'Khác'];
        $loaiHangCounts = array_fill_keys($loaiHangArr, 0); // Initialize counts to 0
        $sumSoContainerHet = 0;
        $sumSoXuong = 0;
        $maXuatCanhList = [];

        $stt = 1;
        $nhapHangs  = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('chu_hang', 'doanh_nghiep.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            ->where('xuat_hang.ma_cong_chuc', $this->ma_cong_chuc)
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->whereBetween('xuat_hang.ngay_xuat_canh', [$this->tu_ngay, $this->den_ngay])
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_canh.ma_xuat_canh',
                DB::raw('IFNULL(SUM(xuat_hang_cont.so_luong_ton), 0) as total_so_luong_ton')
            )
            ->groupBy(
                'nhap_hang.so_to_khai_nhap',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_canh.ma_xuat_canh',
            )
            ->orderBy('xuat_hang.ngay_xuat_canh', 'asc')
            ->get();



        foreach ($nhapHangs as $nhapHang) {
            $ngayXuatCanh = Carbon::createFromFormat('Y-m-d', $nhapHang->ngay_xuat_canh)->format('d-m-Y');
            $exists = false; // Flag to check if the entry already exists

            foreach ($result as $row) {
                if (isset($row[3], $row[12]) && $row[3] == $nhapHang->so_to_khai_nhap && $row[12] == $ngayXuatCanh) {
                    $exists = true;
                    break;
                }
            }

            // If the combination does NOT exist, add a new entry
            if (!$exists) {
                $result[] = [
                    $stt++,
                    $nhapHang->ten_doanh_nghiep ?? '',
                    $nhapHang->ten_chu_hang ?? '',
                    $nhapHang->so_to_khai_nhap ?? '',
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    $ngayXuatCanh,
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
            }


            foreach ($loaiHangArr as $loaiHang) {
                if ($loaiHang == 'Khác') {
                    $data = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                        ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                        ->where('xuat_hang.trang_thai', '!=', '0')
                        ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                        ->where('xuat_hang.so_to_khai_xuat', $nhapHang->so_to_khai_xuat)
                        ->where('xuat_hang.ma_cong_chuc', $this->ma_cong_chuc)
                        ->whereIn('hang_hoa.loai_hang', ['Khác', ''])
                        ->select(
                            DB::raw('IFNULL(SUM(xuat_hang_cont.so_luong_xuat), 0) as total_so_luong_xuat'),
                        )
                        ->first()->total_so_luong_xuat;
                } else {
                    $data = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                        ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                        ->where('xuat_hang.trang_thai', '!=', '0')
                        ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                        ->where('xuat_hang.so_to_khai_xuat', $nhapHang->so_to_khai_xuat)
                        ->where('xuat_hang.ma_cong_chuc', $this->ma_cong_chuc)
                        ->where('hang_hoa.loai_hang', $loaiHang)
                        ->select(
                            DB::raw('IFNULL(SUM(xuat_hang_cont.so_luong_xuat), 0) as total_so_luong_xuat'),
                        )
                        ->first()->total_so_luong_xuat;
                }
                $loaiHangCounts[$loaiHang] += $data;

                foreach ($result as &$row) {
                    if ($row[3] == $nhapHang->so_to_khai_nhap && $row[12] == $ngayXuatCanh) {
                        if ($loaiHang == "Đông lạnh") {
                            $row[4] += $data;
                        } elseif ($loaiHang == "Thuốc lá") {
                            $row[5] += $data;
                        } elseif ($loaiHang == "Cigar") {
                            $row[6] += $data;
                        } elseif ($loaiHang == "Rượu") {
                            $row[7] += $data;
                        } else {
                            $row[8] += $data;
                        }

                        // Trim semicolons and count actual items
                        $tenPhuongTien = trim($nhapHang->ten_phuong_tien_vt, '; ');
                        $numberXuong = substr_count($tenPhuongTien, ';') + 1;

                        // Update values safely
                        $row[11] = ($nhapHang->total_so_luong_ton == 0) ? 'x' : $row[11];

                        if (strpos($row[13], $nhapHang->ten_phuong_tien_vt) === false) {
                            $row[9] += ($nhapHang->total_so_luong_ton == 0) ? 1 : 0;
                            $row[13] .= ($row[13] !== '' ? '; ' : '') . $nhapHang->ten_phuong_tien_vt;
                            $row[10] += $numberXuong;
                        }
                    }
                }
                unset($row); // Prevent reference issues
            }
        }

        $nhapHangXuatHets = NhapHang::whereIn('nhap_hang.trang_thai', ['7', '4'])
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->where('nhap_hang.ma_cong_chuc_ban_giao', $this->ma_cong_chuc)
            ->whereBetween('nhap_hang.ngay_xuat_het', [$this->tu_ngay, $this->den_ngay])

            ->select(
                'nhap_hang.so_to_khai_nhap',
                'doanh_nghiep.ten_doanh_nghiep',
                DB::raw('(SELECT loai_hang FROM hang_hoa WHERE hang_hoa.so_to_khai_nhap = nhap_hang.so_to_khai_nhap LIMIT 1) as loai_hang')
            )
            ->groupBy('nhap_hang.so_to_khai_nhap', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $stt2 = 0;
        foreach ($nhapHangXuatHets as $nhapHangXuatHet) {
            $result[$stt2 + 9][14] = $stt2 + 1;
            $result[$stt2 + 9][15] = $nhapHangXuatHet->so_to_khai_nhap;
            $result[$stt2 + 9][16] = $nhapHangXuatHet->ten_doanh_nghiep;
            $result[$stt2 + 9][17] = $nhapHangXuatHet->loai_hang;
            $stt2++;
        }

        $result[] = [
            ['', '', '', '', $loaiHangCounts['Đông lạnh'] ?: '0', $loaiHangCounts['Thuốc lá'] ?: '0', $loaiHangCounts['Cigar'] ?: '0', $loaiHangCounts['Rượu'] ?: '0', $loaiHangCounts['Khác'] ?: '0', $sumSoContainerHet, $sumSoXuong, $sumSoContainerHet],
        ];
        $result[] = [
            [''],
            [''],
            ['CÔNG CHỨC HẢI QUAN'],
            [''],
            [''],
            [''],
            [Auth::user()->CongChuc->ten_cong_chuc],
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
                    ->setPrintArea('A1:S' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 15);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 15);
                $sheet->getColumnDimension('G')->setWidth(width: 10);
                $sheet->getColumnDimension('H')->setWidth(width: 12);
                $sheet->getColumnDimension('I')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 10);
                $sheet->getColumnDimension('K')->setWidth(width: 10);
                $sheet->getColumnDimension('L')->setWidth(width: 10);
                $sheet->getColumnDimension('M')->setWidth(width: 12);
                $sheet->getColumnDimension('N')->setWidth(width: 18);
                $sheet->getColumnDimension('O')->setWidth(width: 7);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);
                $sheet->getColumnDimension('R')->setWidth(width: 15);
                $sheet->getColumnDimension('S')->setWidth(width: 15);

                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->getRowDimension(9)->setRowHeight(50);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:S4'); // BÁO CÁO
                $sheet->mergeCells('A5:S5'); // Tính đến ngày
                $sheet->mergeCells('A6:S6'); // Tính đến ngày

                $sheet->mergeCells('E8:I8');
                $sheet->mergeCells('A8:A9');
                $sheet->mergeCells('B8:B9');
                $sheet->mergeCells('C8:C9');
                $sheet->mergeCells('D8:D9');
                $sheet->mergeCells('J8:J9');
                $sheet->mergeCells('K8:K9');
                $sheet->mergeCells('L8:L9');
                $sheet->mergeCells('M8:M9');
                $sheet->mergeCells('N8:N9');
                $sheet->mergeCells('O8:O9');
                $sheet->mergeCells('P8:P9');
                $sheet->mergeCells('Q8:Q9');
                $sheet->mergeCells('R8:R9');
                $sheet->mergeCells('S8:S9');


                // Bold and center align for headers
                $sheet->getStyle('A1:S6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:S6')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('A9:S' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:S5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A8:S8')->applyFromArray([
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
                $sheet->getStyle('A8:S' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':S' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':S' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':S' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':S' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':S' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
