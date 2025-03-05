<?php

namespace App\Exports;

use App\Models\BanGiaoHoSo;
use App\Models\BanGiaoHoSoChiTiet;
use App\Models\NhapHang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BienBanBanGiaoHoSo implements FromArray, WithEvents
{
    protected $ma_ban_giao;
    public function __construct($ma_ban_giao)
    {
        $this->ma_ban_giao = $ma_ban_giao;
    }

    public function array(): array
    {


        $bienBan = BanGiaoHoSo::find($this->ma_ban_giao);
        $chiTiets =  BanGiaoHoSoChiTiet::where('ma_ban_giao', $this->ma_ban_giao)->get();

        $currentDate = Carbon::parse($bienBan->ngay_tao)->format('d');        
        $currentMonth = Carbon::parse($bienBan->ngay_tao)->format('m');
        $currentYear = Carbon::parse($bienBan->ngay_tao)->format('Y');

        $result = [
            ['BIÊN BẢN BÀN GIAO HỒ SƠ'],
            ["Ngày $currentDate tháng $currentMonth Năm $currentYear"],
            ['STT', 'SỐ TỜ KHAI', 'DOANH NGHIỆP', 'LOẠI HÀNG', 'GHI CHÚ (Số xuồng)'],
        ];

        $stt = 1;
        foreach ($chiTiets as $chiTiet) {
            $nhapHang = NhapHang::join('hang_hoa', function ($join) {
                $join->on('nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->whereRaw('hang_hoa.so_luong_khai_bao = (SELECT MAX(so_luong_khai_bao) FROM hang_hoa WHERE hang_hoa.so_to_khai_nhap = nhap_hang.so_to_khai_nhap)');
            })->where('nhap_hang.so_to_khai_nhap', $chiTiet->so_to_khai_nhap)
                ->first();
            $result[] = [
                $stt++,
                $nhapHang->so_to_khai_nhap,
                $nhapHang->doanhNghiep->ten_doanh_nghiep,
                $nhapHang->loai_hang,
                ''
            ];
        }

        $result[] = [
            [''],
            [''],
            [''],
            ['', 'Người bàn giao', '', 'Người nhận bàn giao'],
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
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:E' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);

                $sheet->getColumnDimension('A')->setWidth(width: 8);
                $sheet->getColumnDimension('B')->setWidth(width: 20);
                $sheet->getColumnDimension('C')->setWidth(width: 30);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 20);

                $lastRow = $sheet->getHighestRow();
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1:E1')->getFont()->setSize(14);
                $sheet->mergeCells('A2:E2');
                $this->centerCell($sheet, 'A1:E3');
                $sheet->getStyle('A1:E3')->getFont()->setBold(true);

                $sheet->getRowDimension(3)->setRowHeight(45);
                $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format

                $secondStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('B' . $i)->getValue() === 'Người bàn giao') {
                        $secondStart = $i;
                        break;
                    }
                }

                $this->applyBorder($sheet, 'A3' . ':E'. $secondStart - 4);
                $this->centerCell($sheet, 'A1'. ':E'. $secondStart);
                $sheet->mergeCells('D'. $secondStart.':E'. $secondStart);

                $sheet->getStyle('A1:' . 'E' . $lastRow)->getAlignment()->setWrapText(true);
            },
        ];
    }

    function centerCell($sheet, string $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
    function applyBorder($sheet, string $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }
    function applyOuterBorder($sheet, string $range)
    {
        // Apply outer border only
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
                'left'   => ['borderStyle' => Border::BORDER_THIN],
                'right'  => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }
}
