<?php

namespace App\Exports;

use App\Models\LoaiHang;
use App\Models\HaiQuan;
use App\Models\NhapHang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoTiepNhanHangNgayExport implements FromArray, WithEvents
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
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay);
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay);

        $loaiHangs = LoaiHang::all();
        $haiQuans = HaiQuan::all();

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', 'Độc lập - Tự do - Hạnh phúc', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO TIẾP NHẬN HẰNG NGÀY', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Tên HQ', 'Tên hàng', 'Số lượng tờ khai', 'Số lượng cont', 'Số lượng', 'Đơn vị tính', 'Trị giá (USD)'],
        ];
        $from = Carbon::parse($this->tu_ngay)->startOfDay();
        $to = Carbon::parse($this->den_ngay)->endOfDay();
        $today = Carbon::now()->format('Y-m-d');
        $stt = 1;
        $totalSoLuong = 0;
        $totalContainers = 0;

        // Get all nhap_hang entries within date range
        $nhapHangEntries = NhapHang::whereBetween('created_at', [$from, $to])
            ->select('ma_hai_quan', 'so_to_khai_nhap')
            ->get()
            ->groupBy('ma_hai_quan');

        $thongTinData = [];

        foreach ($nhapHangEntries as $maHaiQuan => $entries) {
            $soToKhaiList = $entries->pluck('so_to_khai_nhap')->toArray();
            
            // Get hang_hoa for each group
            $hangHoaData = DB::table('hang_hoa')
            ->whereIn('so_to_khai_nhap', $soToKhaiList)
            ->select(
                'loai_hang',
                'don_vi_tinh',
                'so_to_khai_nhap',
                'so_container_khai_bao',
                'so_luong_khai_bao',
                'tri_gia'
            )
            ->get()
            ->groupBy('loai_hang');

            foreach ($hangHoaData as $loaiHang => $items) {
            $countSoToKhai = count(array_unique($items->pluck('so_to_khai_nhap')->toArray()));
            $uniqueContainers = [];
            
            // Group containers by so_to_khai_nhap and count unique containers per group
            $containersByToKhai = $items->groupBy('so_to_khai_nhap');
            foreach ($containersByToKhai as $soToKhai => $itemsInGroup) {
                $containersInGroup = [];
                foreach ($itemsInGroup as $item) {
                    if (!empty($item->so_container_khai_bao) && !in_array($item->so_container_khai_bao, $containersInGroup)) {
                        $containersInGroup[] = $item->so_container_khai_bao;
                    }
                }
                $uniqueContainers = array_merge($uniqueContainers, $containersInGroup);
            }
            
            $countContainers = count($uniqueContainers);
            $totalSoLuongGroup = $items->sum('so_luong_khai_bao');
            $totalTriGiaGroup = $items->sum('tri_gia');
            $donViTinh = $items->first()->don_vi_tinh;

            $thongTinData[] = (object)[
                'ma_hai_quan' => $maHaiQuan,
                'loai_hang' => $loaiHang,
                'don_vi_tinh' => $donViTinh,
                'count_so_to_khai' => $countSoToKhai,
                'count_containers' => $countContainers,
                'total_so_luong' => $totalSoLuongGroup,
                'total_tri_gia' => $totalTriGiaGroup
            ];
            
            $totalContainers += $countContainers;
            }
        }

        foreach ($thongTinData as $data) {
            $haiQuanName = $haiQuans->where('ma_hai_quan', $data->ma_hai_quan)->first()->ten_hai_quan ?? 'Unknown';
            $loaiHangName = $loaiHangs->where('ten_loai_hang', $data->loai_hang)->first()->ten_loai_hang ?? 'Unknown';

            $result[] = [
            $stt++,
            $haiQuanName,
            $loaiHangName,
            $data->count_so_to_khai,
            $data->count_containers,
            $data->total_so_luong,
            $data->don_vi_tinh,
            $data->total_tri_gia,
            ];
            $totalSoLuong += $data->count_so_to_khai;
        }
        $result[] = [
            '',
            '',
            '',
            $totalSoLuong,
            $totalContainers,
            '',
            '',
            '',
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
                    ->setPrintArea('A1:H' . $sheet->getHighestRow());

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
                foreach (['D', 'E', 'F', 'G'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 30);
                $sheet->getColumnDimension('C')->setWidth(width: 30);
                $sheet->getColumnDimension('H')->setWidth(width: 15);
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:G1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:H2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:H4'); // BÁO CÁO
                $sheet->mergeCells('A5:H5'); // Tính đến ngày


                // Bold and center align for headers
                $sheet->getStyle('A1:F6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:F6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:H7')->applyFromArray([
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
                $sheet->getStyle('A7:H' . $lastRow)->applyFromArray([
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
                $chuKyStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === 'CÔNG CHỨC HẢI QUAN') {
                        $chuKyStart = $i;
                        break;
                    }
                }

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':H' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':H' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':H' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':H' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':H' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
