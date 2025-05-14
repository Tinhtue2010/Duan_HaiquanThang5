<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\LoaiHang;
use App\Models\NhapHang;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\YeuCauTauContChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoDangKyXuatKhauHangHoa2 implements FromArray, WithEvents
{
    protected $ma_doanh_nghiep;
    protected $tu_ngay;

    public function __construct($ma_doanh_nghiep, $tu_ngay)
    {
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
        $this->tu_ngay = $tu_ngay;
    }
    public function array(): array
    {
        $tenDoanhNghiep = DoanhNghiep::find($this->ma_doanh_nghiep)->ten_doanh_nghiep;
        $tu_ngay = Carbon::createFromFormat('d/m/Y', $this->tu_ngay);

        $currentDate = $tu_ngay->format('d');  // Day of the month
        $currentMonth = $tu_ngay->format('m'); // Month number
        $currentYear = $tu_ngay->format('Y');  // Year

        $result = [
            [''],
            [$tenDoanhNghiep],
            ['BẢNG TỔNG HỢP ĐĂNG KÝ LÀM THỦ TỤC XUẤT KHẨU HÀNG HÓA'],
            ["NGÀY $currentDate THÁNG $currentMonth NĂM $currentYear"], // Updated line
            ['', '', '', '', '', ''],
            ['STT', 'Số tờ khai', 'Ngày tờ khai', 'Loại hàng', 'SL tồn', 'SL chuyển', 'Cont, tàu cũ', 'Cont, tàu mới', 'Số seal niêm phong', 'Công việc'],
        ];
        $stt = 1;

        $date = Carbon::createFromFormat('d/m/Y', $this->tu_ngay)->format('Y-m-d');
        $theoDois = TheoDoiHangHoa::whereIn('theo_doi_hang_hoa.cong_viec', [2, 3, 4, 7])
            ->join('nhap_hang', 'theo_doi_hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->whereDate('theo_doi_hang_hoa.thoi_gian', $date)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'theo_doi_hang_hoa.ma_yeu_cau',
                'theo_doi_hang_hoa.cong_viec',
                'theo_doi_hang_hoa.so_seal',
                'theo_doi_hang_hoa.so_container',
                'hang_hoa.loai_hang',
            )
            ->groupBy('nhap_hang.so_to_khai_nhap', 'theo_doi_hang_hoa.ma_yeu_cau', 'theo_doi_hang_hoa.cong_viec')
            ->get();


        foreach ($theoDois as $item) {
            $so_luong = TheoDoiHangHoa::where('ma_yeu_cau', $item->ma_yeu_cau)
                ->where('so_to_khai_nhap', $item->so_to_khai_nhap)
                ->where('cong_viec', $item->cong_viec)
                ->where('so_container', $item->so_container)
                ->sum('so_luong_ton');

            $containerCu = '';
            $tauCu = '';
            $containerMoi = '';
            $tauDich = '';
            $congViec = '';
            if ($item->cong_viec == 2) {
                $congViec = "Chuyển container và tàu";
                $yeuCau = YeuCauTauContChiTiet::where('ma_yeu_cau', $item->ma_yeu_cau)
                    ->where('so_to_khai_nhap', $item->so_to_khai_nhap)
                    ->first();
                $containerCu = $yeuCau->so_container_goc;
                $tauCu = $yeuCau->tau_goc;
                $containerMoi = $yeuCau->so_container_dich;
                $tauDich = $yeuCau->tau_dich;
            } elseif ($item->cong_viec == 3) {
                $congViec = "Chuyển container";
                $yeuCau = YeuCauContainerChiTiet::where('ma_yeu_cau', $item->ma_yeu_cau)
                    ->where('so_to_khai_nhap', $item->so_to_khai_nhap)
                    ->first();
                $containerCu = $yeuCau->so_container_goc;
                $tauCu = $yeuCau->tau_goc;
                $containerMoi = $yeuCau->so_container_dich;
                $tauDich = $yeuCau->tau_goc;
            } elseif ($item->cong_viec == 4) {
                $congViec = "Chuyển tàu";
                $yeuCau = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $item->ma_yeu_cau)
                    ->where('so_to_khai_nhap', $item->so_to_khai_nhap)
                    ->first();
                $containerCu = $yeuCau->so_container;
                $tauCu = $yeuCau->tau_goc;
                $containerMoi = $yeuCau->so_container;
                $tauDich = $yeuCau->tau_dich;
            } elseif ($item->cong_viec == 7) {
                $congViec = "Kiểm tra hàng";
                $yeuCau = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $item->ma_yeu_cau)
                    ->where('so_to_khai_nhap', $item->so_to_khai_nhap)
                    ->first();
                $containerCu = $yeuCau->so_container;
                $tauCu = $yeuCau->so_tau;
                $containerMoi = $yeuCau->so_container;
                $tauDich = $yeuCau->so_tau;
            }

            $containerTauGoc = $containerCu . ' (' . $tauCu . ')';
            $containerTauMoi = $containerMoi . ' (' . $tauDich . ')';
            $result[] = [
                $stt++,
                $item->so_to_khai_nhap,
                Carbon::parse($item->ngay_thong_quan)->format('d-m-Y'),
                $item->loai_hang,
                $so_luong,
                $so_luong,
                $containerTauGoc,
                $containerTauMoi,
                $item->so_seal,
                $congViec,
            ];
        }

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
                    ->setPrintArea('A1:J' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                // Auto-width columns
                // Auto-width columns
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 12);
                $sheet->getColumnDimension('D')->setWidth(width: 20);
                $sheet->getColumnDimension('E')->setWidth(width: 8);
                $sheet->getColumnDimension('F')->setWidth(width: 8);
                $sheet->getColumnDimension('G')->setWidth(width: 15);
                $sheet->getColumnDimension('H')->setWidth(width: 15);
                $sheet->getColumnDimension('I')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 20);

                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                // Merge cells for headers
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');
                $sheet->mergeCells('A4:J4');
                $sheet->mergeCells('A5:J5');



                $sheet->getStyle('A1:J6')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                // Bold and center align for table headers
                $sheet->getStyle('A6:J6')->applyFromArray([
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
                $sheet->getStyle('A6:J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                // Bold and center align for headers
                $sheet->getStyle('A1:J' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
            },
        ];
    }
}
