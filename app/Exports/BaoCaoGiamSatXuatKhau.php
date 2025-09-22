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
use DateTime;
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
        if ($this->ma_cong_chuc == "Tất cả") {
            $ten_cong_chuc = "Toàn thể công chức";
        } else {
            $ten_cong_chuc = CongChuc::find($this->ma_cong_chuc)->ten_cong_chuc;
        }
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
            ['STT', 'Tên doanh nghiệp', 'Đại lý', 'Số tờ khai', 'Số lượng kiện xuất', '', '', '', '', 'Số lượng cont hết', 'Số lượng xuồng', 'Tờ khai xuất hết', 'Ngày xuất', 'Ghi chú'],
            ['', '', '', '', 'Hàng đông lạnh', 'Thuốc lá', 'Cigar', 'Rượu', 'Hàng khác', '', '', '', '', '', '', ''],
        ];
        $loaiHangArr = ['Đông lạnh', 'Thuốc lá', 'Cigar', 'Rượu', 'Khác'];
        $loaiHangCounts = array_fill_keys($loaiHangArr, 0);
        $sumSoContainerHet = 0;
        $sumSoXuong = 0;
        $stt = 1;

        // Single optimized query to get all required data
        $nhapHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('chu_hang', 'doanh_nghiep.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            ->when($this->ma_cong_chuc !== "Tất cả", function ($query) {
                return $query->where('xuat_hang.ma_cong_chuc', $this->ma_cong_chuc);
            })
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->whereBetween('xuat_canh.ngay_duyet', [$this->tu_ngay, $this->den_ngay])
            ->select(
                'nhap_hang.trang_thai',
                'nhap_hang.ngay_xuat_het',
                'nhap_hang.so_to_khai_nhap',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'xuat_hang.so_to_khai_xuat',
                'xuat_canh.ngay_duyet',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_canh.ma_xuat_canh',
                'hang_hoa.loai_hang',
                DB::raw('IFNULL(SUM(xuat_hang_cont.so_luong_ton), 0) as total_so_luong_ton'),
                DB::raw('IFNULL(SUM(xuat_hang_cont.so_luong_xuat), 0) as total_so_luong_xuat')
            )
            ->groupBy(
                'nhap_hang.so_to_khai_nhap',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'xuat_hang.so_to_khai_xuat',
                'xuat_canh.ngay_duyet',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_canh.ma_xuat_canh',
                'hang_hoa.loai_hang'
            )
            ->orderBy('xuat_canh.ngay_duyet', 'asc')
            ->get();

        // Group data by combination of so_to_khai_nhap and ngay_xuat_canh
        $groupedData = [];
        $processedCombinations = [];

        foreach ($nhapHangs as $nhapHang) {
            $ngayXuatCanh = Carbon::createFromFormat('Y-m-d', $nhapHang->ngay_duyet)->format('d-m-Y');
            $combinationKey = $nhapHang->so_to_khai_nhap . '|' . $ngayXuatCanh;

            if (!isset($groupedData[$combinationKey])) {
                $groupedData[$combinationKey] = [
                    'base_data' => $nhapHang,
                    'ngay_duyet_formatted' => $ngayXuatCanh,
                    'ngay_duyet' => $nhapHang->ngay_duyet,
                    'ngay_xuat_het' => $nhapHang->ngay_xuat_het,
                    'trang_thai' => $nhapHang->trang_thai,
                    'loai_hang_data' => array_fill_keys($loaiHangArr, 0),
                    'phuong_tien_list' => [],
                    'total_so_luong_ton' => 0,
                    'container_het' => 0,
                    'so_xuong' => 0
                ];
            }

            // Process loai_hang
            $loaiHang = $nhapHang->loai_hang;
            if (empty($loaiHang)) {
                $loaiHang = 'Khác';
            }

            if (in_array($loaiHang, $loaiHangArr)) {
                $groupedData[$combinationKey]['loai_hang_data'][$loaiHang] += $nhapHang->total_so_luong_xuat;
                $loaiHangCounts[$loaiHang] += $nhapHang->total_so_luong_xuat;
            }

            // Process phuong_tien (avoid duplicates)
            $tenPhuongTien = trim($nhapHang->ten_phuong_tien_vt, '; ');
            if (!in_array($tenPhuongTien, $groupedData[$combinationKey]['phuong_tien_list'])) {
                $groupedData[$combinationKey]['phuong_tien_list'][] = $tenPhuongTien;

                // Count xuong
                $numberXuong = substr_count($tenPhuongTien, ';') + 1;
                $groupedData[$combinationKey]['so_xuong'] += $numberXuong;

                // Count container het
                if ($nhapHang->total_so_luong_ton == 0) {
                    $groupedData[$combinationKey]['container_het']++;
                }
            }

            $groupedData[$combinationKey]['total_so_luong_ton'] += $nhapHang->total_so_luong_ton;
        }

        // Build result array
        foreach ($groupedData as $data) {
            $is_xuat_het = '';
            $baseData = $data['base_data'];
            $loaiHangData = $data['loai_hang_data'];
            if ($data['ngay_xuat_het'] != null && $data['ngay_duyet'] != null) {
                $ngayXuatHet = Carbon::parse(time: $data['ngay_xuat_het']);
                $ngayDuyet = Carbon::parse($data['ngay_duyet']);
                if ($ngayDuyet->eq($ngayXuatHet) && ($data['trang_thai'] == 4 || $data['trang_thai'] == 7)) {
                    $is_xuat_het = 'x';
                } else {
                    $is_xuat_het = '';
                }
            } else {
                $is_xuat_het = '';
            }

            if ($is_xuat_het == 'x') {
                $so_luong_container_het = NhapHang::query()
                    ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('nhap_hang.so_to_khai_nhap', $baseData->so_to_khai_nhap)
                    ->whereNotNull('hang_trong_cont.so_container')
                    ->where('hang_trong_cont.is_da_chuyen_cont', 0)
                    ->distinct()
                    ->count('hang_trong_cont.so_container');
            } else {
                $so_luong_container_het = 0;
            }

            $result[] = [
                $stt++,
                $baseData->ten_doanh_nghiep ?? '',
                $baseData->ten_chu_hang ?? '',
                $baseData->so_to_khai_nhap ?? '',
                $loaiHangData['Đông lạnh'],
                $loaiHangData['Thuốc lá'],
                $loaiHangData['Cigar'],
                $loaiHangData['Rượu'],
                $loaiHangData['Khác'],
                $so_luong_container_het,
                $data['so_xuong'],
                $is_xuat_het,
                $data['ngay_duyet_formatted'],
                implode('; ', $data['phuong_tien_list']),
                '',
                '',
                '',
                '',
                ''
            ];

            $sumSoContainerHet += $so_luong_container_het;
            $sumSoXuong += $data['so_xuong'];
        }

        // Add summary rows
        $result[] = [
            '',
            '',
            '',
            '',
            $loaiHangCounts['Đông lạnh'] ?: '0',
            $loaiHangCounts['Thuốc lá'] ?: '0',
            $loaiHangCounts['Cigar'] ?: '0',
            $loaiHangCounts['Rượu'] ?: '0',
            $loaiHangCounts['Khác'] ?: '0',
            // $sumSoContainerHet,
            // $sumSoXuong,
            // $sumSoContainerHet
        ];
        $result[] = [[''], ['']];

        $result[] = [['CÔNG CHỨC HẢI QUAN'], [''], [''], [''], [Auth::user()->CongChuc->ten_cong_chuc ?? '']];


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
                    ->setPrintArea('A1:N' . $sheet->getHighestRow());

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
                $sheet->mergeCells('A4:N4'); // BÁO CÁO
                $sheet->mergeCells('A5:N5'); // Tính đến ngày
                $sheet->mergeCells('A6:N6'); // Tính đến ngày

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


                // Bold and center align for headers
                $sheet->getStyle('A1:N6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:N6')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('A9:N' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:N5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A8:N8')->applyFromArray([
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
                $sheet->getStyle('A8:N' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':N' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':N' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':N' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':N' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':N' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
