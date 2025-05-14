<?php

namespace App\Exports;

use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\XuatHang;
use App\Models\NiemPhong;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTieuHuy;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use DateTime;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class BaoCaoTheoDoiTruLuiExport implements FromArray, WithEvents, WithDrawings, WithTitle
{
    protected $tenCongViec;
    protected $cong_viec;
    protected $ma_yeu_cau;
    protected $so_to_khai_nhap;
    protected $theoDoi;
    protected $nhapHang;
    protected $ten_hai_quan;

    public function title(): string
    {
        return $this->so_to_khai_nhap . ' - ' . $this->tenCongViec;
    }

    public function __construct($cong_viec, $ma_yeu_cau, $so_to_khai_nhap)
    {
        $this->cong_viec = $cong_viec;
        $this->ma_yeu_cau = $ma_yeu_cau;
        $this->so_to_khai_nhap = $so_to_khai_nhap;
        $this->tenCongViec = "";
        if ($cong_viec == 1) {
            $this->tenCongViec = "Xuất hàng";
        } else if ($cong_viec == 2) {
            $this->tenCongViec = "Chuyển tàu cont ";
        } else if ($cong_viec == 3) {
            $this->tenCongViec = "Chuyển container";
        } else if ($cong_viec == 4) {
            $this->tenCongViec = "Chuyển tàu";
        } else if ($cong_viec == 5) {
            $this->tenCongViec = "Hàng về kho ban đầu";
        } else if ($cong_viec == 6) {
            $this->tenCongViec = "Tiêu hủy hàng";
        } else if ($cong_viec == 7) {
            $this->tenCongViec = "Kiểm tra hàng";
        }
    }

    public function array(): array
    {
        if ($this->cong_viec == 1) {
            $xuatHang = XuatHang::find($this->ma_yeu_cau);
            $nhapHang = NhapHang::find($xuatHang->so_to_khai_nhap);
            $theoDoi = TheoDoiTruLui::where('cong_viec', $this->cong_viec)
                ->where("ma_yeu_cau", $this->ma_yeu_cau)
                ->first();
        } else {
            $theoDoi = TheoDoiTruLui::where('cong_viec', $this->cong_viec)
                ->where("ma_yeu_cau", $this->ma_yeu_cau)
                ->where('so_to_khai_nhap', $this->so_to_khai_nhap)
                ->first();

            $nhapHang = NhapHang::find($this->so_to_khai_nhap);
        }

        $theoDoiCuoiCung = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->orderBy('ma_theo_doi', 'desc')
            ->where('cong_viec', '!=', 4)
            ->get()
            ->first();
        $ngayCuoiCung = $theoDoiCuoiCung->ngay_them ?? '2000-01-01';

        $theoDoiChiTiet = TheoDoiTruLuiChiTiet::where('ma_theo_doi', $theoDoi->ma_theo_doi)->get();

        $tu_ngay = Carbon::createFromFormat('Y-m-d', $theoDoi->ngay_them);

        $day = $tu_ngay->format('d');  // Day of the month
        $month = $tu_ngay->format('m'); // Month number
        $year = $tu_ngay->format('Y');  // Year

        $ten_doanh_nghiep = $nhapHang->doanhNghiep->ten_doanh_nghiep;
        $this->so_to_khai_nhap = $nhapHang->so_to_khai_nhap;

        $hangHoaLonNhat = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
            ->orderByDesc('hang_hoa.so_luong_khai_bao')
            ->select('hang_hoa.*')
            ->first();

        $this->nhapHang = $nhapHang;
        $this->theoDoi = $theoDoi;

        $ngay_dang_ky = $nhapHang->ngay_dang_ky;
        $date = DateTime::createFromFormat('Y-m-d', $ngay_dang_ky);

        $tongSoLuongs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
            ->sum('hang_hoa.so_luong_khai_bao');

        $this->ten_hai_quan = $nhapHang->haiQuan->ten_hai_quan;

        $result = [
            [''],
            [''],
            ['PHIẾU THEO DÕI, TRỪ LÙI HÀNG HÓA XUẤT KHẨU TỪNG LẦN'],
            [''],
            ['', '', '', '', '', '', '', 'Ngày ' . $day . ' Tháng ' . $month . ' Năm ' . $year],
            ['Tên Doanh Nghiệp: ' . $ten_doanh_nghiep],
            ['Số tờ khai: ' . $nhapHang->so_to_khai_nhap, '', '', 'Ngày đăng ký: Ngày ' . $date->format('d') . ' Tháng ' . $date->format('m') . ' Năm 20' . $date->format('y'), '', '', '', '', 'Chi cục hải quan đăng ký: ' . $this->ten_hai_quan],
            ['Tên hàng hóa: ' . $hangHoaLonNhat->ten_hang],
            ['Số lượng: ' . $tongSoLuongs . '; Đơn vị tính: ' . $hangHoaLonNhat->don_vi_tinh . '; Xuất xứ: ' . $hangHoaLonNhat->xuat_xu],
            [],
            // ['STT', 'TÊN HÀNG', 'SỐ LƯỢNG', 'ĐƠN VỊ TÍNH', 'XUẤT XỨ']
        ];


        $result[] = ['Số Tàu(Xà Lan):' . $nhapHang->ptvt_ban_dau, '', '', '', '', '', '', 'Số Container: ' . $nhapHang->container_ban_dau];
        $result[] = [
            'STT',
            'Nội dung công việc',
            'Số, hiệu PTVT nước ngoài Nhận hàng',
            $this->createRichTextBoldItalic('Tên hàng ', '(ghi rõ quy cách hàng hóa)'),
            '',
            '',
            $this->createRichTextBoldItalic('Số lượng hàng hóa xuất khẩu ', '(Kiện)'),
            $this->createRichTextBoldItalic('Số Lượng hàng hóa chưa xuất khẩu ', '(Kiện)'),
            'Số seal hải quan niêm phong',
            $this->createRichTextBoldItalic('Số hiệu PTVT ', '(tàu Việt Nam nếu có thay đổi)'),
            $this->createRichTextBoldItalic('Số hiệu container ', '(nếu có thay đổi)'),
            'Ghi chú'
        ];
        $result[] = [
            '',
            '1',
            '2',
            '3',
            '',
            '',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ];
        $sum = 0;
        $stt = 1;
        $is_xuat_het = false;
        if ($nhapHang->trang_thai == 4) {
            if (\Carbon\Carbon::parse($nhapHang->ngay_xuat_het)->isSameDay($tu_ngay)) {
                $is_xuat_het = true;
            }
        }

        foreach ($theoDoiChiTiet as $item) {
            if ($item->so_luong_chua_xuat != 0) {
                if ($is_xuat_het == true) {
                    $result[] = [
                        $stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        '',
                        $item->phuong_tien_vt_nhap == $nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } elseif (\Carbon\Carbon::parse($item->ngay_dang_ky)->greaterThanOrEqualTo($ngayCuoiCung)) {
                    $sealCuoiCung = NiemPhong::where('so_container', $item->so_container)->first()->so_seal;
                    $result[] = [
                        $stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        $item->so_seal ?? $sealCuoiCung,
                        $item->phuong_tien_vt_nhap == $nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } else {
                    $result[] = [
                        $stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        $item->so_seal ?? '',
                        $item->phuong_tien_vt_nhap == $nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                }

                $sum += $item->so_luong_chua_xuat;
            }
        }
        $result[] = ['', '', '', 'Tổng cộng', '', '', '', $sum, '', '', ''];
        $result[] = [
            [''],
            [''],
            ['', 'CÔNG CHỨC HẢI QUAN GIÁM SÁT', '', '', '', '', '', '', 'ĐẠI DIỆN DOANH NGHIỆP'],
            ['', '(Ký, đóng dấu công chức)', '', '', '', '', '', '', '(Ký, ghi rõ họ tên)']
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
                    ->setPrintArea('A1:L' . $sheet->getHighestRow());

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);
                $sheet->getDelegate()->getSheetView()->setZoomScale(85);

                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(20);

                // Auto-width columns
                $sheet->getColumnDimension('A')->setWidth(width: 5);
                $sheet->getColumnDimension('B')->setWidth(width: 18);
                $sheet->getColumnDimension('C')->setWidth(width: 18);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 5);
                $sheet->getColumnDimension('G')->setWidth(width: 10);
                $sheet->getColumnDimension('H')->setWidth(width: 10);
                $sheet->getColumnDimension('I')->setWidth(width: 13);
                $sheet->getColumnDimension('J')->setWidth(width: 10);
                $sheet->getColumnDimension('K')->setWidth(width: 15);
                $sheet->getColumnDimension('L')->setWidth(width: 20);
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(28);
                $sheet->getRowDimension(6)->setRowHeight(28);
                $sheet->getRowDimension(7)->setRowHeight(28);
                $sheet->getRowDimension(8)->setRowHeight(28);
                $sheet->getRowDimension(9)->setRowHeight(28);
                $sheet->getRowDimension(10)->setRowHeight(28);
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('0'); // Apply format

                $sheet->setCellValue('L2', $this->so_to_khai_nhap);
                $this->centerCell($sheet, "L2");




                $lastRow = $sheet->getHighestRow();
                $sheet->mergeCells('A3:L4'); //Phiếu
                $sheet->mergeCells('H5:L5'); //Ngày tháng năm
                $sheet->mergeCells('A6:I6'); //Tên DN
                $sheet->mergeCells('A7:C7');
                $sheet->mergeCells('D7:H7');
                $sheet->mergeCells('I7:L7');
                $sheet->mergeCells('A8:L8');
                $sheet->mergeCells('A9:L9');
                $sheet->mergeCells('A10:G10');
                $sheet->mergeCells('H10:L10');

                $sheet->getStyle('A3:L4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('H5:L5')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('A12:L12')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'italic' => true,
                    ],
                ]);

                // Find the row where "II-PHẦN XUẤT KHẨU" starts
                $secondTableStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('B' . $i)->getValue() === 'Nội dung công việc') {
                        $secondTableStart = $i;
                        break;
                    }
                }
                $lastStart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('B' . $i)->getValue() === 'CÔNG CHỨC HẢI QUAN GIÁM SÁT') {
                        $lastStart = $i;
                        break;
                    }
                }
                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);


                if ($secondTableStart) {
                    // Bold and center



                    $sheet->getStyle('A' . $secondTableStart . ':L' . $secondTableStart)->applyFromArray([
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
                }

                for ($row = $secondTableStart; $row <= $lastRow; $row++) {
                    $sheet->mergeCells('D' . $row . ':F' . $row);
                }
                $sheet->getStyle('A' . $secondTableStart . ':L' . $lastStart - 3)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $tenCongViec = $this->tenCongViec;

                $sheet->mergeCells('B' . $secondTableStart + 2 . ':B' . $lastStart - 4);
                $sheet->setCellValue('B' . $secondTableStart + 2, $tenCongViec);
                $sheet->mergeCells('C' . $secondTableStart + 2 . ':C' . $lastStart - 4);
                $sheet->setCellValue('C' . $secondTableStart + 2, $this->theoDoi->so_ptvt_nuoc_ngoai);

                $tau = $this->theoDoi->theoDoiChiTiet->first()?->phuong_tien_vt_nhap;
                $sheet->mergeCells('J' . $secondTableStart + 2 . ':J' . $lastStart - 4);
                $sheet->setCellValue('J' . $secondTableStart + 2, $tau == $this->nhapHang->ptvt_ban_dau ? '' : $tau);


                $sheet->getStyle('A' . $secondTableStart . ':L' . $lastStart - 3)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A' . ($lastStart) . ':L' . ($lastStart + 1))->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A' . ($lastStart + 1) . ':L' . ($lastStart + 1))->getFont()->setItalic(true)->setBold(false);
                $sheet->mergeCells('B' . $lastStart . ':C' . ($lastStart));
                $sheet->mergeCells('B' . $lastStart + 1 . ':C' . ($lastStart + 1));
                $sheet->mergeCells('I' . $lastStart . ':L' . ($lastStart));
                $sheet->mergeCells('I' . $lastStart + 1 . ':L' . ($lastStart + 1));
                $sheet->getStyle('A' . ($lastStart - 3) . ':L' . ($lastStart - 3))->getFont()->setBold(true);

                $first = 0;
                for ($row = $secondTableStart; $row <= $lastStart - 3; $row++) {
                    if ($first == 1) {
                        $sheet->getRowDimension($row)->setRowHeight(height: 80);
                    }
                    $first = 1;
                }

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(12)->setRowHeight(30);
                // Set left alignment for number columns
                // $sheet->getStyle('A13:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                if (mb_strlen($this->ten_hai_quan, 'UTF-8') > 40) {
                    $sheet->getRowDimension(7)->setRowHeight(50);
                    $sheet->getStyle('A7:L7')->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);
                }
            },
        ];
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
        $drawing->setCoordinates('L1'); // Adjust as needed
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(0);
        $drawing->setHeight(30);
        $drawing->setWidth(250);

        return $drawing;
    }
    function createRichText($text, $bold)
    {
        $richText = new RichText();
        $plainText = $richText->createText($text);
        $boldText = $richText->createTextRun($bold);
        $boldText->getFont()->setBold(true)->setName('Times New Roman'); // Bold + Times New Roman
        $boldText->getFont()->setSize(10);

        return $richText;
    }
    function createRichTextBoldItalic($text, $italic)
    {
        $richText = new RichText();

        // Bold text
        $boldText = $richText->createTextRun($text);
        $boldText->getFont()->setBold(true)->setName('Times New Roman')->setSize(20);

        // Italic text
        $italicText = $richText->createTextRun($italic);
        $italicText->getFont()->setName('Times New Roman')->setSize(20);

        return $richText;
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
    public function __destruct()
    {
        if (file_exists(storage_path('app/temp-barcode-tru-lui.png'))) {
            unlink(storage_path('app/temp-barcode-tru-lui.png'));
        }
    }
}
