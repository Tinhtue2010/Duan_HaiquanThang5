<?php

namespace App\Exports;

use App\Models\HangHoa;
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

class BaoCaoSoLuongToKhaiXuat implements FromArray, WithEvents
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
            ['BÁO CÁO SỐ LƯỢNG TỜ KHAI XUẤT HẾT', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['', '', '', '', '', ''],
            [''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký', 'Chi cục HQ đăng ký tờ khai', 'Tên DN', 'Mã số DN', 'Tên hàng', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá (USD)', 'Ngày xuất hết', 'Số lượng xuất', 'Số container', 'Cán bộ công chức giám sát','PT đã xuất cảnh'],
        ];
        $stt = 1;
        $today = Carbon::now()->format('Y-m-d'); // Format now() as yyyy-mm-dd

        $xuatHangs = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->join('hai_quan', 'hai_quan.ma_hai_quan', 'nhap_hang.ma_hai_quan')
            ->whereIn('nhap_hang.trang_thai', ['7', '4'])
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->whereBetween('xuat_hang.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay]) // This filter is added for the xuat_hang
            ->select(
                'hang_hoa.ten_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.ten_hang',
                'hang_hoa.so_luong_khai_bao',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.tri_gia',
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.ma_cong_chuc_ban_giao',
                'nhap_hang.ngay_xuat_het',
                'nhap_hang.ma_doanh_nghiep',
                'nhap_hang.trong_luong',
                'xuat_hang_cont.so_container',
                'doanh_nghiep.ten_doanh_nghiep',
                'hai_quan.ten_hai_quan',
                'xuat_hang.ngay_dang_ky as ngay_dang_ky_xuat',
                'xuat_hang.trang_thai',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as total_so_luong_xuat')
            )
            ->groupBy(
                'hang_hoa.ten_hang',
                'hang_hoa.xuat_xu',
                'hang_hoa.ten_hang',
                'hang_hoa.so_luong_khai_bao',
                'hang_hoa.don_vi_tinh',
                'hang_hoa.tri_gia',
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky',
                'nhap_hang.trong_luong',
                'nhap_hang.ma_cong_chuc_ban_giao',
                'nhap_hang.ngay_xuat_het',
                'nhap_hang.ma_doanh_nghiep',
                'nhap_hang.trong_luong',
                'xuat_hang_cont.so_container',
                'doanh_nghiep.ten_doanh_nghiep',
                'hai_quan.ten_hai_quan',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.trang_thai'
            )
            ->orderBy('xuat_hang.so_to_khai_xuat', 'desc') // Ordering to get the highest so_to_khai_xuat
            ->get();

        $processedSoToKhai = [];
        foreach ($xuatHangs as $xuatHang) {
            if (in_array($xuatHang->so_to_khai_nhap, $processedSoToKhai)) {
                continue;
            }
            $processedSoToKhai[] = $xuatHang->so_to_khai_nhap;
            $result[] = [
                $stt++,
                $xuatHang->so_to_khai_nhap,
                Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y'),
                $xuatHang->ten_hai_quan ?? '',
                $xuatHang->ten_doanh_nghiep ?? '',
                $xuatHang->ma_doanh_nghiep ?? '',
                $xuatHang->ten_hang ?? '',
                $xuatHang->xuat_xu ?? '',
                $xuatHang->so_luong_khai_bao ?? '',
                $xuatHang->don_vi_tinh ?? '',
                $xuatHang->trong_luong ?? '',
                $xuatHang->tri_gia ?? '',
                $xuatHang->ngay_dang_ky_xuat ? Carbon::parse($xuatHang->ngay_dang_ky_xuat)->format('d-m-Y') : '',
                $xuatHang->total_so_luong_xuat ?? '',
                $xuatHang->so_container ?? '',
                $xuatHang->congChucBanGiao->ten_cong_chuc ?? '',
                in_array($xuatHang->trang_thai, [13, 12]) ? 'X' : '',
            ];
        }



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
                    ->setPrintArea('A1:Q' . $sheet->getHighestRow());

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
                foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7); //STT
                $sheet->getColumnDimension('B')->setWidth(width: 15); //Số tờ khai
                $sheet->getColumnDimension('C')->setWidth(width: 12); //Ngày đăng ký
                $sheet->getColumnDimension('D')->setWidth(width: 15); //Chi cục
                $sheet->getColumnDimension('E')->setWidth(width: 15); //Tên DN
                $sheet->getColumnDimension('F')->setWidth(width: 15); //Mã DN
                $sheet->getColumnDimension('G')->setWidth(width: 25); //Tên hàng
                $sheet->getColumnDimension('H')->setWidth(width: 12); //Xuất xứ
                $sheet->getColumnDimension('L')->setWidth(width: 15);
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('M')->setWidth(width: 12);
                $sheet->getColumnDimension('Q')->setWidth(width: 10);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:Q4'); // BÁO CÁO
                $sheet->mergeCells('A5:Q5'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:Q6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:Q6')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('A9:Q' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:Q5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A8:Q8')->applyFromArray([
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
                $sheet->getStyle('A8:Q' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':Q' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':Q' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':Q' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':Q' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':Q' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
