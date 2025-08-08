<?php

namespace App\Exports;


use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\NiemPhong;
use App\Models\TheoDoiHangHoa;
use App\Models\XuatHang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class BaoCaoTheoDoiHangHoa implements FromArray, WithEvents, WithDrawings
{

    protected $ma_hang;
    protected $nhapHang;
    protected $hangHoa;

    public function __construct($ma_hang)
    {
        $this->ma_hang = $ma_hang;
    }
    public function array(): array
    {
        $hangHoa = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('hang_trong_cont.ma_hang', $this->ma_hang)
            ->first();
        $nhapHang = NhapHang::find($hangHoa->so_to_khai_nhap);
        $this->nhapHang = $nhapHang;
        $this->hangHoa = $hangHoa;
        $theoDoiHangHoas = TheoDoiHangHoa::where('ma_hang', $this->ma_hang)
            ->orderBy('thoi_gian', 'asc') // or 'asc' for ascending
            ->get();
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['PHIẾU THEO DÕI HÀNG HÓA XUẤT NHẬP KHẨU', '', '', '', '', ''],
            [''],
            [''],
            [''],
            [''],
            [''],
            [''],
            ['STT', 'Thời gian hoàn thành giám sát', 'Số lượng tái xuất (Kiện)', 'Số lượng tồn (Kiện)', 'Phương tiện chở hàng', 'Mô tả công việc', 'Phương tiện nhận hàng XK', 'Số container', 'Số seal/chì hải quan', 'Công chức giám sát(Ký tên, đóng dấu công chức)', 'Ghi chú'],
        ];
        $hangHoa = HangHoa::find($this->ma_hang);
        $soLuongTon = $hangHoa->so_luong_khai_bao;
        $soLuongDaXuat = 0;
        $theoDoiCuoiCung = TheoDoiHangHoa::where('so_to_khai_nhap', $hangHoa->so_to_khai_nhap)
            ->orderBy('ma_theo_doi', 'desc')
            ->where('cong_viec', '!=', '4')
            ->get()
            ->first();
        $ngayCuoiCung = $theoDoiCuoiCung->thoi_gian ?? '2000-01-01';

        $seen = [];
        $stt = 1;
        foreach ($theoDoiHangHoas as $theoDoiHangHoa) {
            $datetime = Carbon::parse($theoDoiHangHoa->thoi_gian); // Example datetime value
            $hour = $datetime->format('H'); // 24-hour format
            $minute = $datetime->format('i'); // Minute with leading zero
            $date = $datetime->format('d/m/Y'); // Day/Month/Year format
            $time = 'Hồi ' . $hour . ' giờ ' . $minute . ' Ngày ' . $date;

            $is_xuat_het = false;
            if ($nhapHang->trang_thai == 4 || $nhapHang->trang_thai == 7) {
                if (Carbon::parse($nhapHang->ngay_xuat_het)->isSameDay(Carbon::parse($theoDoiHangHoa->thoi_gian))) {
                    $is_xuat_het = true;
                }
            }

            $tenCongViec = "";
            if ($theoDoiHangHoa->cong_viec == 1) {
                $tenCongViec = "Xuất hàng";
                $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', 'xuat_hang_cont.ma_hang_cont')
                    ->where('hang_trong_cont.ma_hang', $this->ma_hang)
                    ->where('xuat_hang.so_to_khai_xuat', $theoDoiHangHoa->ma_yeu_cau)
                    ->where('xuat_hang.trang_thai', '!=', '0')
                    ->select(
                        'xuat_hang_cont.ma_xuat_hang_cont',
                        'xuat_hang_cont.so_luong_xuat',
                        'xuat_hang_cont.so_container',
                        'xuat_hang.ten_phuong_tien_vt',
                        'xuat_hang_cont.so_seal_cuoi_ngay',
                        'xuat_hang.ma_cong_chuc',
                        'xuat_hang.ghi_chu',
                    )
                    ->get();
                foreach ($xuatHangs as $xuatHang) {
                    if (isset($seen[$xuatHang->ma_xuat_hang_cont])) {
                        continue;
                    }
                    $seen[$xuatHang->ma_xuat_hang_cont] = true;
                    $soLuongTon -= $xuatHang->so_luong_xuat;

                    if ($is_xuat_het == true) {
                        $seal = '';
                    } elseif (\Carbon\Carbon::parse($theoDoiHangHoa->thoi_gian)->greaterThanOrEqualTo(Carbon::parse($ngayCuoiCung))) {
                        $sealCuoiCung = NiemPhong::where('so_container', $xuatHang->so_container)->first()->so_seal ?? '';
                        $seal = $sealCuoiCung;
                    } else {
                        $seal = $xuatHang->so_seal_cuoi_ngay;
                    }
                    $result[] = [
                        $stt++,
                        $time,
                        $xuatHang->so_luong_xuat == 0 ? '0' : $xuatHang->so_luong_xuat,
                        $soLuongTon == 0 ? '0' : $soLuongTon,
                        '',
                        $tenCongViec,
                        $xuatHang->ten_phuong_tien_vt,
                        $xuatHang->so_container,
                        $seal,
                        $xuatHang->congChuc->ten_cong_chuc ?? '',
                        $xuatHang->ghi_chu,
                    ];
                    $soLuongDaXuat += $xuatHang->so_luong_xuat;
                }
                continue;
            } else if ($theoDoiHangHoa->cong_viec == 2) {
                $tenCongViec = "Chuyển container và tàu";
            } else if ($theoDoiHangHoa->cong_viec == 3) {
                $tenCongViec = "Chuyển container";
            } else if ($theoDoiHangHoa->cong_viec == 4) {
                $tenCongViec = "Chuyển tàu";
            } else if ($theoDoiHangHoa->cong_viec == 5) {
                $tenCongViec = "Đưa hàng trở lại kho ban đầu";
            } else if ($theoDoiHangHoa->cong_viec == 6) {
                $tenCongViec = "Tiêu hủy hàng";
            } else if ($theoDoiHangHoa->cong_viec == 7) {
                $tenCongViec = "Kiểm tra hàng";
            } else if ($theoDoiHangHoa->cong_viec == 8) {
                $tenCongViec = "Niêm phong";
            } else if ($theoDoiHangHoa->cong_viec == 9) {
                $tenCongViec = "Gỡ seal điện tử";
            }

            if ($is_xuat_het == true) {
                $seal = '';
            } elseif (\Carbon\Carbon::parse($theoDoiHangHoa->thoi_gian)->greaterThanOrEqualTo(Carbon::parse($ngayCuoiCung))) {
                $sealCuoiCung = NiemPhong::where('so_container', $theoDoiHangHoa->so_container)->first()->so_seal ?? '';
                $seal = $sealCuoiCung;
            } else {
                $seal = $theoDoiHangHoa->so_seal;
            }
            if ($theoDoiHangHoa->so_luong_xuat == 0) {
                continue;
            }
            $result[] = [
                $stt++,
                $time,
                $theoDoiHangHoa->so_luong_xuat == 0 ? '0' : $theoDoiHangHoa->so_luong_xuat,
                $theoDoiHangHoa->so_luong_ton == 0 ? '0' : $theoDoiHangHoa->so_luong_ton,
                $theoDoiHangHoa->phuong_tien_cho_hang,
                $tenCongViec,
                $theoDoiHangHoa->phuong_tien_nhan_hang,
                $theoDoiHangHoa->so_container,
                $seal,
                $theoDoiHangHoa->congChuc->ten_cong_chuc ?? '',
                $theoDoiHangHoa->ghi_chu,
            ];
        }
        $tongLuongTon = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('hang_hoa.ma_hang', $this->ma_hang)
            ->sum('hang_trong_cont.so_luong');
        $result[] = [
            '',
            '',
            'SL Tồn',
            $tongLuongTon == 0 ? '0' : $tongLuongTon,
        ];

        return $result;
    }
    public function drawings()
    {
        // Generate barcode in memory
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($this->nhapHang->so_to_khai_nhap, $generator::TYPE_CODE_128);

        // Create image from binary PNG data
        $image = imagecreatefromstring($barcodeData);

        // Create in-memory drawing
        $drawing = new MemoryDrawing();
        $drawing->setName('Barcode');
        $drawing->setDescription('Barcode');
        $drawing->setImageResource($image);
        $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
        $drawing->setCoordinates('J1'); // Adjust as needed
        $drawing->setOffsetX(210);
        $drawing->setOffsetY(0);
        $drawing->setHeight(30);
        $drawing->setWidth(250);

        return $drawing;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Set print settings first
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:K' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(22);


                foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 15);
                }

                $sheet->getColumnDimension('A')->setWidth(width: 5);
                $sheet->getColumnDimension('C')->setWidth(width: 9);
                $sheet->getColumnDimension('D')->setWidth(width: 7);
                $sheet->getColumnDimension('E')->setWidth(width: 12);
                $sheet->getColumnDimension('J')->setWidth(width: 20);
                $sheet->getColumnDimension('K')->setWidth(width: 13);
                $sheet->getStyle('C')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('A11:K' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getStyle('J')->getNumberFormat()->setFormatCode('0');

                $lastRow = $sheet->getHighestRow();

                // Your existing cell merges
                $sheet->mergeCells('J1:K1');
                $sheet->mergeCells('J2:K2');
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A4:K4');
                $sheet->setCellValue('J2',  '                            ' . $this->nhapHang->so_to_khai_nhap);
                // $sheet->setCellValue('K3', now()->format('d/m/Y'));
                $this->centerCell($sheet, "K2:K3");




                // Apply RichText formatting for specific rows
                $this->applyRichText($sheet, 'A6', 'Tên doanh nghiệp: ', $this->nhapHang->doanhNghiep->ten_doanh_nghiep);
                $this->applyRichText($sheet, 'A7', 'Số tờ khai: ', $this->nhapHang->so_to_khai_nhap, '; ngày đăng ký: ', date('d-m-Y', strtotime($this->nhapHang->ngay_dang_ky)),  ' tại ', $this->nhapHang->haiQuan->ten_hai_quan);
                $this->applyRichText($sheet, 'A8', 'Tên hàng hóa: ', $this->hangHoa->ten_hang);
                $this->applyRichText($sheet, 'A9', 'Số lượng: ', $this->hangHoa->so_luong_khai_bao, '; Đơn vị tính: ', $this->hangHoa->don_vi_tinh ?? '', '; Xuất xứ: ', $this->hangHoa->xuat_xu ?? '');
                $this->applyRichText($sheet, 'A10', 'Số container: ', $this->hangHoa->so_container, '; Số tàu: ', $this->nhapHang->ptvt_ban_dau, '; Số seal: ', $this->hangHoa->so_seal);

                $sheet->getStyle('A1:K4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:K4')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                $sheet->getStyle('A11:K11')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A11:K' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,  // Horizontal center
                        'vertical' => Alignment::VERTICAL_CENTER,      // Vertical center
                    ],
                ]);
                $sheet->getStyle('K2:K3')->getFont()->setBold(false);
                $sheet->getStyle('J1')->getFont()->setBold(false);
            },
        ];
    }
    private function applyRichText($sheet, $cell, ...$parts)
    {
        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();

        foreach ($parts as $index => $part) {
            if (is_string($part) && trim($part) !== '') { // Validate non-empty strings
                if ($index % 2 == 0) {
                    // Static text
                    $text = $richText->createText($part);
                } else {
                    // Dynamic text (bold)
                    $bold = $richText->createTextRun($part);
                    $bold->getFont()->setName('Times New Roman'); // Set font to Times New Roman
                    $bold->getFont()->setBold(true); // Set text to bold
                    $bold->getFont()->setSize(22);
                }
            }
        }

        $sheet->getCell($cell)->setValue($richText);
        $sheet->mergeCells("$cell:K$cell");
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
}
