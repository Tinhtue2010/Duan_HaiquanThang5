<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\NhapHang;
use App\Models\HangHoa;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoContainerLuuTaiCang implements FromArray, WithEvents
{
    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO SỐ LƯỢNG CONTAINER LƯU TẠI CẢNG', '', '', '', '', ''],
            ["(Tính đến ngày $currentDate tháng $currentMonth năm $currentYear)", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT','Tên DN','Loại hàng','Số lượng tồn','Đơn vị tính','Số container', 'Số tàu', 'Số seal','Số đoàn tàu'],

        ];

        $containers = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2','3'])
            ->select('container.*', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap','doanh_nghiep.ten_doanh_nghiep','hang_hoa.loai_hang','hang_hoa.don_vi_tinh','nhap_hang.ten_doan_tau')
            ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
            ->groupBy('container.so_container', 'niem_phong.so_seal', 'niem_phong.phuong_tien_vt_nhap','hang_hoa.don_vi_tinh')
            ->orderByRaw('total_so_luong DESC')
            ->get();

        $totalHangTon = 0;
        $stt = 1;

        foreach ($containers as $container) {
            if ($container->total_so_luong != 0 && $container->so_container != '') {
                $result[] = [
                    $stt++,
                    $container->ten_doanh_nghiep,
                    $container->loai_hang,
                    $container->total_so_luong,
                    $container->don_vi_tinh,
                    $container->so_container,
                    $container->phuong_tien_vt_nhap,
                    $container->so_seal,
                    $container->ten_doan_tau,
                ];
                $totalHangTon += $container->total_so_luong;
            }
        }

        $result[] = [
            '',
            '',
            '',
            $totalHangTon,
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
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:I' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 25); //Số tờ khai
                $sheet->getColumnDimension('C')->setWidth(width: 20); //Số tờ khai
                $sheet->getColumnDimension('D')->setWidth(width: 15); //Ngày đăng ký
                $sheet->getColumnDimension('E')->setWidth(width: 15); //Chi cục
                $sheet->getColumnDimension('F')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('G')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('H')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('I')->setWidth(width: 15); //Tên DN

                $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:I4'); // BÁO CÁO
                $sheet->mergeCells('A5:I5'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:I6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:I6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:I' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);
                // Bold and center align for table headers
                $sheet->getStyle('A8:I8')->applyFromArray([
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
                $sheet->getStyle('A8:I' . $lastRow)->applyFromArray([
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
