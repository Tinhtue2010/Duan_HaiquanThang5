<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\XuatHangCont;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauHangVeKhoChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoChuyenCuaKhauXuat implements FromArray, WithEvents
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
            ['BÁO CÁO HÀNG CHUYỂN CỬA KHẨU XUẤT (QUAY VỀ KHO)', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký', 'Chi cục HQ đăng ký', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', '', '', '', '', 'Số lượng chuyển đi', 'Ngày đăng ký', 'Số container'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng hóa', 'Xuất xứ', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)', 'Ngày xuất', '', ''],
        ];
        $totalKhaiBao = 0;
        $totalSoLuong = 0;
        $stt = 1;
        $yeuCaus = YeuCauHangVeKho::whereBetween('ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->get();

        foreach ($yeuCaus as $yeuCau) {
            $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->get();
            foreach ($chiTiets as $chiTiet) {
                $nhapHang = NhapHang::find($chiTiet->so_to_khai_nhap);
                $hangHoas = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_hoa.so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                    ->get();
                $soLuong = 0;
                $soLuongKhaiBao = 0;
                foreach ($hangHoas as $hangHoa) {
                    $soLuongKhaiBao += $hangHoa->so_luong_khai_bao;
                    $soLuong += $hangHoa->so_luong;
                    $totalKhaiBao += $hangHoa->so_luong_khai_bao;
                    $totalSoLuong += $hangHoa->so_luong;
                }
                if ($nhapHang && $nhapHang->updated_at) {
                    $formattedDate = Carbon::parse($nhapHang->updated_at)->format('d-m-Y');
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
                        $soLuongKhaiBao,
                        $hangHoa->don_vi_tinh,
                        $nhapHang->trong_luong,
                        $hangHoa->tri_gia,
                        $formattedDate,
                        $soLuong,
                        Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_yeu_cau)->format('d-m-Y'),
                        $hangHoa->so_container
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
            $totalKhaiBao,
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
                $sheet->getColumnDimension('G')->setWidth(width: 25); //Địa chỉ
                $sheet->getColumnDimension('H')->setWidth(width: 25); //Tên hàng
                $sheet->getColumnDimension('I')->setWidth(width: 12); //Xuất xứ
                $sheet->getColumnDimension('M')->setWidth(width: 15); //Trị giá
                $sheet->getColumnDimension('N')->setWidth(width: 12);
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0');

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

                $sheet->mergeCells('O7:O8');
                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');



                // Bold and center align for headers
                $sheet->getStyle('A1:Q6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:Q6')->applyFromArray([
                    'font' => ['bold' => true]
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
                $sheet->getStyle('A7:Q8')->applyFromArray([
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
                $sheet->getStyle('A7:Q' . $lastRow)->applyFromArray([
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
