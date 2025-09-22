<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\TheoDoiHangHoa;
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
            ['STT', 'Số tờ khai', 'Ngày đăng ký', 'Chi cục HQ đăng ký', 'Doanh nghiệp XK,NK', '', '', 'Hàng hóa', '', '', '', '', '', '', '', 'Số lượng chuyển đi', 'Hải quan cửa khẩu nơi hàng hóa chuyển đến (quay về kho)', 'Số tờ khai mới', 'Ngày đăng ký', 'Số container'],
            ['', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN', 'Tên hàng hóa', 'Xuất xứ', 'Loại hàng', 'Số lượng', 'ĐVT', 'Trọng lượng', 'Trị giá hàng hóa (USD)', 'Ngày xuất', '', '', ''],
        ];
        $totalKhaiBao = 0;
        $totalSoLuong = 0;
        $stt = 1;
        $yeuCaus = YeuCauHangVeKho::whereBetween('ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->get();

        foreach ($yeuCaus as $yeuCau) {
            $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->leftJoin('hai_quan', 'yeu_cau_hang_ve_kho_chi_tiet.ma_hai_quan', '=', 'hai_quan.ma_hai_quan')
                ->groupBy('so_to_khai_nhap')->get();
            foreach ($chiTiets as $chiTiet) {
                $nhapHang = NhapHang::find($chiTiet->so_to_khai_nhap);
                $hangHoas = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_hoa.so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                    ->get();
                $soLuongTieuHuy = TheoDoiHangHoa::where('so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                    ->where('cong_viec', '5')
                    ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                    ->sum('so_luong_ton');
                $soLuong = $soLuongTieuHuy;
                $totalSoLuong += $soLuongTieuHuy;
                $soLuongKhaiBao = 0;
                foreach ($hangHoas as $hangHoa) {
                    $soLuongKhaiBao += $hangHoa->so_luong_khai_bao;
                    $totalKhaiBao += $hangHoa->so_luong_khai_bao;
                }

                if ($nhapHang && $nhapHang->updated_at && $soLuong > 0) {
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
                        $hangHoa->loai_hang,
                        $soLuongKhaiBao,
                        $hangHoa->don_vi_tinh,
                        $nhapHang->trong_luong,
                        $hangHoa->tri_gia,
                        $formattedDate,
                        $soLuong,
                        $chiTiet->ten_hai_quan,
                        $chiTiet->so_to_khai_moi,
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
                    ->setPrintArea('A1:T' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('O')->setWidth(width: 15);
                $sheet->getColumnDimension('P')->setWidth(width: 15);
                $sheet->getColumnDimension('Q')->setWidth(width: 15);
                $sheet->getColumnDimension('R')->setWidth(width: 15);
                $sheet->getColumnDimension('S')->setWidth(width: 15);
                $sheet->getColumnDimension('T')->setWidth(width: 15);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('F')->getNumberFormat()->setFormatCode('0'); // Apply format
                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('A4:T4'); // BÁO CÁO
                $sheet->mergeCells('A5:T5'); // Tính đến ngày

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:O7');

                $sheet->mergeCells('P7:P8');
                $sheet->mergeCells('Q7:Q8');
                $sheet->mergeCells('R7:R8');
                $sheet->mergeCells('S7:S8');
                $sheet->mergeCells('T7:T8');



                // Bold and center align for headers
                $sheet->getStyle('A1:T6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:T6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A9:T' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:T5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:T8')->applyFromArray([
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
                $sheet->getStyle('A7:T' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':T' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':T' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':T' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':T' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':T' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
