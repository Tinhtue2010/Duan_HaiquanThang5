<?php

namespace App\Exports;


use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\PTVTXuatCanhCuaPhieu;
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

class BaoCaoTheoDoiHangHoaTong implements FromArray, WithEvents, WithDrawings
{

    protected $so_to_khai_nhap;
    protected $nhapHang;

    public function __construct($so_to_khai_nhap)
    {
        $this->so_to_khai_nhap = $so_to_khai_nhap;
    }
    public function array(): array
    {
        $this->nhapHang = NhapHang::find($this->so_to_khai_nhap);
        $theoDoiHangHoas = TheoDoiHangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)
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
            ['STT', 'Thời gian hoàn thành giám sát', 'Tên hàng', 'Số lượng tái xuất (Kiện)', 'Số lượng tồn (Kiện)', 'Phương tiện chở hàng', 'Mô tả công việc', 'Phương tiện nhận hàng XK', 'Số container', 'Số seal/chì hải quan', 'Công chức giám sát(Ký tên, đóng dấu công chức)', 'Ghi chú'],
        ];


        $hangHoas = HangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)->get();
        $hangHoaArr = [];
        foreach ($hangHoas as $hangHoa) {
            $hangHoaArr[$hangHoa->ma_hang] = $hangHoa->so_luong_khai_bao;
        }

        $seen = [];
        $stt = 1;
        foreach ($theoDoiHangHoas as $theoDoiHangHoa) {
            $datetime = Carbon::parse($theoDoiHangHoa->thoi_gian); // Example datetime value
            $hour = $datetime->format('H'); // 24-hour format
            $minute = $datetime->format('i'); // Minute with leading zero
            $date = $datetime->format('d/m/Y'); // Day/Month/Year format
            $time = 'Hồi ' . $hour . ' giờ ' . $minute . ' Ngày ' . $date;

            $tenCongViec = "";
            if ($theoDoiHangHoa->cong_viec == 1) {
                $tenCongViec = "Xuất hàng";
                $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', 'xuat_hang_cont.ma_hang_cont')
                    ->join('hang_hoa', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.ma_hang', $theoDoiHangHoa->ma_hang)
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
                        'hang_hoa.ten_hang',
                    )
                    ->get();

                foreach ($xuatHangs as $xuatHang) {
                    if (isset($seen[$xuatHang->ma_xuat_hang_cont])) {
                        continue;
                    }
                    $seen[$xuatHang->ma_xuat_hang_cont] = true;

                    if (isset($hangHoaArr[$theoDoiHangHoa->ma_hang])) {
                        $hangHoaArr[$theoDoiHangHoa->ma_hang] -= $xuatHang->so_luong_xuat;
                    }

                    $result[] = [
                        $stt++,
                        $time,
                        $xuatHang->ten_hang ?? '',
                        $xuatHang->so_luong_xuat == 0 ? '0' : $xuatHang->so_luong_xuat,
                        $hangHoaArr[$theoDoiHangHoa->ma_hang] == 0 ? '0' : $hangHoaArr[$theoDoiHangHoa->ma_hang],
                        $theoDoiHangHoa->phuong_tien_cho_hang ?? '',
                        $tenCongViec,
                        $xuatHang->ten_phuong_tien_vt,
                        $xuatHang->so_container,
                        $xuatHang->so_seal_cuoi_ngay,
                        $xuatHang->congChuc->ten_cong_chuc ?? '',
                        $xuatHang->ghi_chu,
                    ];
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
            }
            $hangHoa = HangHoa::find($theoDoiHangHoa->ma_hang);
            $result[] = [
                $stt++,
                $time,
                $hangHoa->ten_hang ?? '',
                $theoDoiHangHoa->so_luong_xuat == 0 ? '0' : $theoDoiHangHoa->so_luong_xuat,
                $theoDoiHangHoa->so_luong_ton == 0 ? '0' : $theoDoiHangHoa->so_luong_ton,
                $theoDoiHangHoa->phuong_tien_cho_hang ?? '',
                $tenCongViec,
                $theoDoiHangHoa->phuong_tien_nhan_hang,
                $theoDoiHangHoa->so_container,
                $theoDoiHangHoa->so_seal ?? '',
                $theoDoiHangHoa->congChuc->ten_cong_chuc ?? '',
                $theoDoiHangHoa->ghi_chu,
            ];
        }

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
        $drawing->setCoordinates('K1'); // Adjust as needed
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
                    ->setPrintArea('A1:L' . $sheet->getHighestRow());

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
                $sheet->getColumnDimension('D')->setWidth(width: 10);
                $sheet->getColumnDimension('C')->setWidth(width: 25);
                $sheet->getColumnDimension('E')->setWidth(width: 7);
                $sheet->getColumnDimension('J')->setWidth(width: 15);
                $sheet->getColumnDimension('K')->setWidth(width: 20);
                $sheet->getColumnDimension('L')->setWidth(width: 13);
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('E')->getNumberFormat()->setFormatCode('0');
                $sheet->getStyle('A11:L' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0');

                $lastRow = $sheet->getHighestRow();

                // Your existing cell merges
                $sheet->mergeCells('K1:L1');
                $sheet->mergeCells('K2:L2');
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A4:L4');
                $sheet->setCellValue('K2',  '                            ' . $this->nhapHang->so_to_khai_nhap);
                // $sheet->setCellValue('K3', now()->format('d/m/Y'));
                $this->centerCell($sheet, "L2:L3");




                // Apply RichText formatting for specific rows


                $hangHoaLonNhat = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('nhap_hang.so_to_khai_nhap', $this->nhapHang->so_to_khai_nhap)
                    ->orderByDesc('hang_hoa.so_luong_khai_bao')
                    ->first();
                $tongSoLuongs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
                    ->where('nhap_hang.so_to_khai_nhap', $this->nhapHang->so_to_khai_nhap)
                    ->sum('hang_hoa.so_luong_khai_bao');
                $this->applyRichText($sheet, 'A6', 'Tên doanh nghiệp: ', $this->nhapHang->doanhNghiep->ten_doanh_nghiep);
                $this->applyRichText($sheet, 'A7', 'Số tờ khai: ', $this->nhapHang->so_to_khai_nhap, '; ngày đăng ký: ', date('d-m-Y', strtotime($this->nhapHang->ngay_dang_ky)),  ' tại ', $this->nhapHang->haiQuan->ten_hai_quan);
                $this->applyRichText($sheet, 'A8', 'Tên hàng hóa: ', $hangHoaLonNhat->ten_hang ?? '');
                $this->applyRichText($sheet, 'A9', 'Số lượng: ', (string)$tongSoLuongs, '; Đơn vị tính: ', $hangHoaLonNhat->don_vi_tinh ?? '', '; Xuất xứ: ', $hangHoaLonNhat->xuat_xu ?? '');
                $this->applyRichText($sheet, 'A10', 'Số container: ', $this->nhapHang->container_ban_dau, '; Số tàu: ', $this->nhapHang->phuong_tien_vt_nhap, '; Số seal: ', $hangHoaLonNhat->so_seal ?? '');

                $sheet->getStyle('A1:L4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:L4')->applyFromArray([
                    'font' => ['bold' => true]
                ]);

                $sheet->getStyle('A11:L11')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);



                $sheet->getStyle('A11:L' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('L2:L3')->getFont()->setBold(false);
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
        $sheet->mergeCells("$cell:L$cell");
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
