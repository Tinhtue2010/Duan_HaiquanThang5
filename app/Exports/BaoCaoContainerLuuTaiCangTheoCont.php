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

class BaoCaoContainerLuuTaiCangTheoCont implements FromArray, WithEvents
{
    protected $so_container;

    public function __construct($so_container)
    {
        $this->so_container = $so_container;
    }
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
            ['STT', 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Chi cục HQ đăng ký tờ khai', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)', 'Số lượng tồn', 'Số container'],

        ];


        $nhapHangs = NhapHang::with(['hangHoa.hangTrongCont'])
            ->where('trang_thai', 'Đã nhập hàng')
            ->select([
                'nhap_hang.*',
                'hai_quan.ten_hai_quan',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.dia_chi'
            ])
            ->join('hai_quan', 'nhap_hang.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
            ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->get();

        $stt = 1;
        $totalHangTon = 0;
        foreach ($nhapHangs as $nhapHang) {
            foreach ($nhapHang->hangHoa as $hangHoa) {
                foreach ($hangHoa->hangTrongCont as $hangTrongCont) {
                    if($hangTrongCont->so_luong != 0){
                        if($hangTrongCont->so_container == $this->so_container &&  $hangTrongCont->is_da_chuyen_cont == 0){
                            $result[] = [
                                'stt' => $stt++,
                                'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
                                'ngay_dang_ky' => Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y'),
                                'ten_hai_quan' => $nhapHang->ten_hai_quan,
                                'ten_doanh_nghiep' => $nhapHang->ten_doanh_nghiep,
                                'ma_doanh_nghiep' => $nhapHang->ma_doanh_nghiep,
                                'dia_chi' => $nhapHang->dia_chi,
                                'ten_hang' => $hangHoa->ten_hang,
                                'xuat_xu' => $hangHoa->xuat_xu,
                                'so_luong_khai_bao' => $hangHoa->so_luong_khai_bao,
                                'don_vi_tinh' => $hangHoa->don_vi_tinh,
                                'trong_luong' => $nhapHang->trong_luong,
                                'tri_gia' => $hangHoa->tri_gia,
                                'so_luong' => $hangTrongCont->so_luong,
                                'so_container' => $hangTrongCont->so_container ?? null,
                            ];
                            $totalHangTon +=  $hangTrongCont->so_luong;
                        }
                    }
                }
            }
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
            '',
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
                    ->setPrintArea('A1:O' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'C', 'D', 'E', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'] as $column) {
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
                $sheet->getColumnDimension('I')->setWidth(width: 12);
                $sheet->getColumnDimension('M')->setWidth(width: 15);
                $sheet->getColumnDimension('N')->setWidth(width: 12);
                $sheet->getColumnDimension('O')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:O4'); // BÁO CÁO
                $sheet->mergeCells('A5:O5'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:O6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:O6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:O' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:O5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);
                // Bold and center align for table headers
                $sheet->getStyle('A8:O8')->applyFromArray([
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
                $sheet->getStyle('A8:O' . $lastRow)->applyFromArray([
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
