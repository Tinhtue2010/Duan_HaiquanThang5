<?php

namespace App\Exports;

use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\CongChuc;
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

class BaoCaoSangContChuyenTau implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;
    protected $ma_cong_chuc;

    public function __construct($tu_ngay, $den_ngay,$ma_cong_chuc)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
        $this->ma_cong_chuc = $ma_cong_chuc;
    }
    public function array(): array
    {
        $congChuc = CongChuc::find($this->ma_cong_chuc);
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');
        $result = [
            ['CỤC HẢI QUAN TỈNH QUẢNG NINH', '', '', '', '', ''],
            ['CHI CỤC HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO THỐNG KÊ HÀNG HÓA SANG CONT, CHUYỂN TÀU, KIỂM TRA HÀNG', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''], // Updated line
            ['Công chức: '. $congChuc->ten_cong_chuc],
            [''],
            ['STT', 'Thời gian', 'Công ty', 'Số tờ khai', 'Ngày TK', 'Loại hàng', 'Số kiện sang cont', 'Loại đơn', 'Số cont gốc', 'Số cont mới', 'Tàu cũ', 'Tàu mới', 'Đại lý'],
        ];
        $stt = 1;
        $yeuCauContainers = NhapHang::join('yeu_cau_container_chi_tiet', 'nhap_hang.so_to_khai_nhap', 'yeu_cau_container_chi_tiet.so_to_khai_nhap')
            ->join('yeu_cau_chuyen_container', 'yeu_cau_chuyen_container.ma_yeu_cau', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'yeu_cau_chuyen_container.ma_doanh_nghiep')
            ->join('chu_hang', 'chu_hang.ma_chu_hang', 'doanh_nghiep.ma_chu_hang')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->whereBetween('yeu_cau_chuyen_container.ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->where('yeu_cau_chuyen_container.trang_thai','Đã duyệt')
            ->where('yeu_cau_chuyen_container.ma_cong_chuc',$this->ma_cong_chuc)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_thong_quan',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'yeu_cau_chuyen_container.ngay_yeu_cau',
                'yeu_cau_container_chi_tiet.tau_goc',
                'yeu_cau_container_chi_tiet.so_container_goc',
                'yeu_cau_container_chi_tiet.so_container_dich',
                'yeu_cau_container_chi_tiet.so_luong_chuyen',
                'hang_hoa.loai_hang',
                DB::raw('"SC" as loai')
            )
            ->distinct()     
            ->get();
        $yeuCauTauConts = NhapHang::join('yeu_cau_tau_cont_chi_tiet', 'nhap_hang.so_to_khai_nhap', 'yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap')
            ->join('yeu_cau_tau_cont', 'yeu_cau_tau_cont.ma_yeu_cau', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->join('chu_hang', 'chu_hang.ma_chu_hang', 'doanh_nghiep.ma_chu_hang')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->whereBetween('yeu_cau_tau_cont.ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->where('yeu_cau_tau_cont.trang_thai','Đã duyệt')
            ->where('yeu_cau_tau_cont.ma_cong_chuc',$this->ma_cong_chuc)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_thong_quan',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'yeu_cau_tau_cont.ngay_yeu_cau',
                'yeu_cau_tau_cont_chi_tiet.so_container_goc',
                'yeu_cau_tau_cont_chi_tiet.so_container_dich',
                'yeu_cau_tau_cont_chi_tiet.tau_goc',
                'yeu_cau_tau_cont_chi_tiet.tau_dich',
                'yeu_cau_tau_cont_chi_tiet.so_luong_chuyen',
                'hang_hoa.loai_hang',
                DB::raw('"SC-CT" as loai')
            )
            ->distinct()                 
            ->get();

        $yeuCauChuyenTaus = NhapHang::join('yeu_cau_chuyen_tau_chi_tiet', 'nhap_hang.so_to_khai_nhap', 'yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap')
            ->join('yeu_cau_chuyen_tau', 'yeu_cau_chuyen_tau.ma_yeu_cau', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->join('chu_hang', 'chu_hang.ma_chu_hang', 'doanh_nghiep.ma_chu_hang')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->where('yeu_cau_chuyen_tau.trang_thai','Đã duyệt')
            ->whereBetween('yeu_cau_chuyen_tau.ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->where('yeu_cau_chuyen_tau.ma_cong_chuc',$this->ma_cong_chuc)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_thong_quan',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'yeu_cau_chuyen_tau.ngay_yeu_cau',
                'yeu_cau_chuyen_tau_chi_tiet.tau_goc',
                'yeu_cau_chuyen_tau_chi_tiet.so_container as so_container_goc',
                'yeu_cau_chuyen_tau_chi_tiet.tau_dich',
                'yeu_cau_chuyen_tau_chi_tiet.so_luong as so_luong_chuyen',
                'hang_hoa.loai_hang',
                DB::raw('"CT" as loai')
            )
            ->distinct()            
            ->get();

        $yeuCauKiemTras = NhapHang::join('yeu_cau_kiem_tra_chi_tiet', 'nhap_hang.so_to_khai_nhap', 'yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap')
            ->join('yeu_cau_kiem_tra', 'yeu_cau_kiem_tra.ma_yeu_cau', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->join('chu_hang', 'chu_hang.ma_chu_hang', 'doanh_nghiep.ma_chu_hang')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->where('yeu_cau_kiem_tra.trang_thai','Đã duyệt')
            ->whereBetween('yeu_cau_kiem_tra.ngay_yeu_cau', [$this->tu_ngay, $this->den_ngay])
            ->where('yeu_cau_kiem_tra.ma_cong_chuc',$this->ma_cong_chuc)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'nhap_hang.ngay_thong_quan',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'yeu_cau_kiem_tra.ngay_yeu_cau',
                'yeu_cau_kiem_tra_chi_tiet.so_tau as tau_goc',
                'yeu_cau_kiem_tra_chi_tiet.so_container as so_container_goc',
                'yeu_cau_kiem_tra_chi_tiet.so_luong as so_luong_chuyen',
                'hang_hoa.loai_hang',
                DB::raw('"K" as loai')
            )
            ->distinct()     
            ->get();

        $yeuCauContainers = collect($yeuCauContainers);
        $yeuCauTauConts = collect($yeuCauTauConts);
        $yeuCauKiemTras = collect($yeuCauKiemTras);
        $yeuCauChuyenTaus = collect($yeuCauChuyenTaus);

        $mergedCollection = $yeuCauContainers
            ->merge($yeuCauTauConts)
            ->merge($yeuCauKiemTras)
            ->merge($yeuCauChuyenTaus);

        $sortedCollection = $mergedCollection->sortBy('ngay_yeu_cau')->values();

        foreach ($sortedCollection as $collection) {
            $result[] = [
                $stt++,
                Carbon::parse($collection->ngay_yeu_cau)->format('d-m-Y'),
                $collection->ten_doanh_nghiep,
                $collection->so_to_khai_nhap,
                Carbon::parse($collection->ngay_thong_quan)->format('d-m-Y'),
                $collection->loai_hang,
                $collection->so_luong_chuyen,
                $collection->loai,
                $collection->so_container_goc ?? '',
                $collection->so_container_dich ?? '',
                $collection->tau_goc ?? '',
                $collection->tau_dich ?? '',
                $collection->ten_chu_hang,
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

                // Auto-width columns
                foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 25);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 15);
                $sheet->getColumnDimension('G')->setWidth(width: 10);
                $sheet->getColumnDimension('H')->setWidth(width: 12);
                $sheet->getColumnDimension('I')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 15);
                $sheet->getColumnDimension('K')->setWidth(width: 12);
                $sheet->getColumnDimension('L')->setWidth(width: 12);
                $sheet->getColumnDimension('M')->setWidth(width: 12);

                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0'); // Apply format
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
                $sheet->mergeCells('A4:M4'); // BÁO CÁO
                $sheet->mergeCells('A5:M5'); // Tính đến ngày
                $sheet->mergeCells('A6:M6'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:M6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:M6')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('A9:M' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('A8:M8')->applyFromArray([
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
                $sheet->getStyle('A8:M' . $lastRow)->applyFromArray([
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
