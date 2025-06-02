<?php

namespace App\Exports;

use App\Models\CongChuc;
use App\Models\Seal;
use App\Models\YeuCauNiemPhongChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoSuDungSealChiTiet implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;
    protected $ma_cong_chuc;

    public function __construct($tu_ngay, $den_ngay, $ma_cong_chuc)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
        $this->ma_cong_chuc = $ma_cong_chuc;
    }
    public function array(): array
    {
        if ($this->ma_cong_chuc == "Tất cả") {
            $tenCongChuc = "Toàn thể công chức";
        } else {
            $tenCongChuc = CongChuc::find($this->ma_cong_chuc)->ten_cong_chuc;
        }
        $data = Seal::join('cong_chuc', 'seal.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->leftJoin('yeu_cau_niem_phong_chi_tiet', 'seal.so_seal', '=', 'yeu_cau_niem_phong_chi_tiet.so_seal_moi')
            ->leftJoin('yeu_cau_go_seal_chi_tiet', 'seal.so_seal', '=', 'yeu_cau_go_seal_chi_tiet.so_seal_moi')
            ->whereBetween('ngay_su_dung', [$this->tu_ngay, $this->den_ngay])
            ->where('trang_thai', 1)
            ->when($this->ma_cong_chuc !== "Tất cả", function ($query) {
                return $query->where('seal.ma_cong_chuc', $this->ma_cong_chuc);
            })
            ->groupBy('seal.so_seal')
            ->select('seal.so_seal', 'seal.loai_seal', 'seal.ngay_cap', 'seal.ngay_su_dung', 'seal.so_container', 'yeu_cau_niem_phong_chi_tiet.phuong_tien_vt_nhap', 'yeu_cau_go_seal_chi_tiet.phuong_tien_vt_nhap as ptvt_go_seal', 'cong_chuc.ten_cong_chuc')
            ->get();

        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BÁO CÁO SỬ DỤNG SEAL NIÊM PHONG HẢI QUAN', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['Công chức: ' . $tenCongChuc, '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'LOẠI SEAL', 'SỐ SEAL', 'SỐ CONTAINER', 'SỐ TÀU', 'NGÀY CẤP', 'NGÀY SỬ DỤNG', 'CÔNG CHỨC'],
        ];

        $stt = 1;
        foreach ($data as $item) {
            // $ptvt = YeuCauNiemPhongChiTiet::where('so_seal_moi', $item->so_seal)->first()->phuong_tien_vt_nhap ?? "";
            $loaiSeal = '';
            if ($item->loai_seal == 1) {
                $loaiSeal = "Seal dây cáp đồng";
            } elseif ($item->loai_seal == 2) {
                $loaiSeal = "Seal dây cáp thép";
            } elseif ($item->loai_seal == 3) {
                $loaiSeal = "Seal container";
            } elseif ($item->loai_seal == 4) {
                $loaiSeal = "Seal dây nhựa dẹt";
            } elseif ($item->loai_seal == 5) {
                $loaiSeal = "Seal định vị điện tử";
            }
            $result[] = [
                $stt++,
                $loaiSeal,
                $item->so_seal,
                $item->so_container,
                $item->phuong_tien_vt_nhap ?? $item->ptvt_go_seal,
                Carbon::parse($item->ngay_cap)->format('d-m-Y'),
                Carbon::parse($item->ngay_su_dung)->format('d-m-Y'),
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
                    ->setPrintArea('A1:G' . $sheet->getHighestRow());

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
                foreach (['A', 'D', 'E', 'F'] as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 25);
                $sheet->getColumnDimension('G')->setWidth(width: 15);
                $sheet->getColumnDimension('H')->setWidth(width: 25);

                $lastRow = $sheet->getHighestRow();
                $sheet->mergeCells('A1:C1'); // CỤC HẢI QUAN
                $sheet->mergeCells('D1:F1'); // CỘNG HÒA
                $sheet->mergeCells('A2:C2'); // CHI CỤC
                $sheet->mergeCells('D2:H2'); // ĐỘC LẬP
                $sheet->mergeCells('A4:H4'); // BÁO CÁO
                $sheet->mergeCells('A5:H5'); // Tính đến ngày
                $sheet->mergeCells('A6:H6'); // Tính đến ngày

                // Bold and center align for headers
                $sheet->getStyle('A1:H8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:H8')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                $sheet->getStyle('A8:H' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Italic for date row
                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                // Bold and center align for table headers
                $sheet->getStyle('A8:H8')->applyFromArray([
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
                $sheet->getStyle('A8:H' . $lastRow)->applyFromArray([
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

                $sheet->getStyle('A' . ($chuKyStart - 2) . ':H' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $sheet->mergeCells('A' . $chuKyStart . ':H' . $chuKyStart);
                $sheet->getStyle('A' . $chuKyStart . ':H' . $chuKyStart)->getFont()->setBold(true);
                $sheet->mergeCells('A' . ($chuKyStart + 4) . ':H' . ($chuKyStart + 4));
                $sheet->getStyle('A' . ($chuKyStart + 4) . ':H' . ($chuKyStart + 4))->getFont()->setBold(true);
            },
        ];
    }
}
