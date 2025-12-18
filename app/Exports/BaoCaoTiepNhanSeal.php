<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HaiQuan;
use App\Models\TheoDoiHangHoa;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauHangVeKhoChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoTiepNhanSeal implements FromArray, WithEvents
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
        $data = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hai_quan', 'hai_quan.ma_hai_quan', '=', 'nhap_hang.ma_hai_quan')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', '=', 'nhap_hang.ma_doanh_nghiep')
            ->leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'hang_hoa.cong_chuc_go_seal')
            ->where('hang_hoa.so_seal_dinh_vi', '!=', '')
            ->whereBetween('nhap_hang.ngay_tiep_nhan', [$this->tu_ngay, $this->den_ngay])
            ->groupBy('hang_hoa.so_seal_dinh_vi')
            ->orderBy('nhap_hang.ngay_dang_ky')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky as ngay_dang_ky_to_khai',
                'nhap_hang.ngay_tiep_nhan',
                'hai_quan.ten_hai_quan',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.dia_chi',
                'hang_hoa.loai_hang',
                'hang_hoa.so_container_khai_bao',
                'hang_hoa.so_seal_dinh_vi',
                'cong_chuc.ten_cong_chuc',
            )
            ->get();


        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO CHI TIẾT GÁN, GỠ SEAL ĐỊNH VỊ ĐIỆN TỬ', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày đăng ký tờ khai', 'Hải quan nơi đi', 'Hải quan nơi đến', 'Doanh nghiệp XK,NK', '', '', 'Loại hàng', 'Số container', 'Số seal định vị', 'Ngày gỡ/gán seal định vị', 'Công chức thực hiện gỡ/ gán seal'],
            ['', '', '', '', '', 'Tên DN', 'Mã số DN', 'Địa chỉ DN'],
        ];

        $stt = 1;
        foreach ($data as $item) {

            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::parse($item->ngay_dang_ky_to_khai)->format('d/m/Y'),
                $item->ten_hai_quan,
                '',
                $item->ten_doanh_nghiep,
                $item->ma_doanh_nghiep,
                $item->dia_chi,
                $item->loai_hang,
                $item->so_container_khai_bao,
                $item->so_seal_dinh_vi,
                Carbon::parse($item->ngay_tiep_nhan)->format('d/m/Y'),
                $item->ten_cong_chuc,
            ];
        }

        $quayVeKhos = YeuCauHangVeKho::join('yeu_cau_hang_ve_kho_chi_tiet', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_hang_ve_kho.ma_yeu_cau')
            ->join('nhap_hang', 'nhap_hang.so_to_khai_nhap', '=', 'yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap')
            ->join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hai_quan', 'hai_quan.ma_hai_quan', '=', 'nhap_hang.ma_hai_quan')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', '=', 'nhap_hang.ma_doanh_nghiep')
            ->leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'yeu_cau_hang_ve_kho.ma_cong_chuc')
            ->whereBetween('yeu_cau_hang_ve_kho.ngay_hoan_thanh', [$this->tu_ngay, $this->den_ngay])
            ->groupBy('yeu_cau_hang_ve_kho_chi_tiet.so_seal_dinh_vi')
            ->orderBy('yeu_cau_hang_ve_kho.ngay_hoan_thanh')
            ->where('yeu_cau_hang_ve_kho_chi_tiet.so_seal_dinh_vi', '!=', '')
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_dang_ky as ngay_dang_ky_to_khai',
                'yeu_cau_hang_ve_kho.ngay_hoan_thanh',
                'yeu_cau_hang_ve_kho_chi_tiet.ma_hai_quan',
                'hai_quan.ten_hai_quan',
                'doanh_nghiep.ma_doanh_nghiep',
                'doanh_nghiep.ten_doanh_nghiep',
                'doanh_nghiep.dia_chi',
                'hang_hoa.loai_hang',
                'hang_hoa.so_container_khai_bao',
                'yeu_cau_hang_ve_kho_chi_tiet.so_seal_dinh_vi',
                'cong_chuc.ten_cong_chuc',
            )
            ->get();
        foreach ($quayVeKhos as $item) {
            $ten_hai_quan_den = HaiQuan::find($item->ma_hai_quan)->ten_hai_quan;
            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::parse($item->ngay_dang_ky_to_khai)->format('d/m/Y'),
                $item->ten_hai_quan,
                $ten_hai_quan_den ?? '',
                $item->ten_doanh_nghiep,
                $item->ma_doanh_nghiep,
                $item->dia_chi,
                $item->loai_hang,
                $item->so_container_khai_bao,
                $item->so_seal_dinh_vi,
                Carbon::parse($item->ngay_hoan_thanh)->format('d/m/Y'),
                $item->ten_cong_chuc,
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
                    ->setPrintArea('A1:M' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);
                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format

                $sheet->getColumnDimension('B')->setWidth(width: 20);
                $sheet->getColumnDimension('C')->setWidth(width: 15);
                $sheet->getColumnDimension('D')->setWidth(width: 20);
                $sheet->getColumnDimension('E')->setWidth(width: 20);
                $sheet->getColumnDimension('F')->setWidth(width: 15);
                $sheet->getColumnDimension('G')->setWidth(width: 15);
                $sheet->getColumnDimension('H')->setWidth(width: 20);
                $sheet->getColumnDimension('I')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 15);
                $sheet->getColumnDimension('K')->setWidth(width: 15);
                $sheet->getColumnDimension('L')->setWidth(width: 15);
                $sheet->getColumnDimension('M')->setWidth(width: 20);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:M' . $lastRow)->getAlignment()->setWrapText(true);
                // Merge cells for headers
                $sheet->mergeCells('A1:E1'); // CỤC HẢI QUAN
                $sheet->mergeCells('F1:M1'); // CỘNG HÒA
                $sheet->mergeCells('A2:E2'); // CHI CỤC
                $sheet->mergeCells('F2:M2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:M4'); // BÁO CÁO
                $sheet->mergeCells('A5:M5'); // Tính đến ngày

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');
                $sheet->mergeCells('E7:E8');

                $sheet->mergeCells('F7:H7');
                $sheet->mergeCells('I7:I8');
                $sheet->mergeCells('J7:J8');
                $sheet->mergeCells('K7:K8');
                $sheet->mergeCells('L7:L8');
                $sheet->mergeCells('M7:M8');
                // Bold and center align for headers
                $sheet->getStyle('A1:M7')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:M7')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A7:M' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:M5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A7:M8')->applyFromArray([
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
                $sheet->getStyle('A7:M' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':M' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':M' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':M' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':M' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':M' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
