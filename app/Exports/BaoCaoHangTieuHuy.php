<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\TheoDoiHangHoa;
use App\Models\XuatHangCont;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauHangVeKhoChiTiet;
use App\Models\YeuCauTieuHuy;
use App\Models\YeuCauTieuHuyChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoHangTieuHuy implements FromArray, WithEvents
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
            ['BÁO CÁO HÀNG TIÊU HỦY', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký', 'Chi cục HQ đăng ký', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', '', '', '', '', ''],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng hóa', 'Xuất xứ', 'Loại hàng', 'Số lượng tiêu hủy', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)'],
        ];
        $totalKhaiBao = 0;
        $totalSoLuong = 0;
        $stt = 1;
        $yeuCaus = YeuCauTieuHuy::whereBetween('ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->get();

        foreach ($yeuCaus as $yeuCau) {
            $chiTiets = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->groupBy('so_to_khai_nhap')
                ->get();
            foreach ($chiTiets as $chiTiet) {
                $nhapHang = NhapHang::find($chiTiet->so_to_khai_nhap);
                $hangHoa = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_hoa.so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                    ->first();
                $soLuongTieuHuy = TheoDoiHangHoa::where('so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                    ->where('cong_viec', '6')
                    ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                    ->sum('so_luong_ton');
                $soLuong = $soLuongTieuHuy;
                $totalSoLuong += $soLuongTieuHuy;

                if ($nhapHang) {
                    $result[] = [
                        $stt++,
                        $nhapHang->so_to_khai_nhap,
                        Carbon::createFromFormat('Y-m-d', $nhapHang->ngay_dang_ky)->format('d-m-Y'),
                        $nhapHang->haiQuan->ten_hai_quan,
                        $nhapHang->doanhNghiep->ten_doanh_nghiep,
                        $nhapHang->doanhNghiep->ma_doanh_nghiep,
                        $nhapHang->doanhNghiep->dia_chi,
                        $hangHoa->ten_hang,
                        $hangHoa->xuat_xu,
                        $hangHoa->loai_hang,
                        $soLuong == 0 ? '0' : $soLuong,
                        $hangHoa->don_vi_tinh,
                        $nhapHang->trong_luong,
                        $hangHoa->tri_gia,
                    ];
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
            $totalSoLuong,
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
                foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'] as $column) {
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
                $sheet->getColumnDimension('M')->setWidth(width: 10); //Trị giá
                $sheet->getColumnDimension('N')->setWidth(width: 12);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:R4'); // BÁO CÁO
                $sheet->mergeCells('A5:R5'); // Tính đến ngày

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:N7');



                // Bold and center align for headers
                $sheet->getStyle('A1:N6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:N6')->applyFromArray([
                    'font' => ['bold' => true]
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
                $sheet->getStyle('A7:N8')->applyFromArray([
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
                $sheet->getStyle('A7:N' . $lastRow)->applyFromArray([
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
