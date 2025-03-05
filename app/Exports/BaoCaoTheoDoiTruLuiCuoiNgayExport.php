<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\XuatHang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class BaoCaoTheoDoiTruLuiCuoiNgayExport implements FromArray, WithEvents, WithDrawings
{

    protected $so_to_khai_nhap;
    protected $theoDoi;
    protected $nhapHang;
    protected $sum;
    protected $array;

    public function __construct($so_to_khai_nhap)
    {
        $this->so_to_khai_nhap = $so_to_khai_nhap;
    }

    public function array(): array
    {
        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year

        $nhapHang = NhapHang::find($this->so_to_khai_nhap);
        $this->nhapHang = $nhapHang;
        $doanhNghiep = DoanhNghiep::where('ma_doanh_nghiep', $nhapHang->ma_doanh_nghiep)->first();

        $ten_doanh_nghiep = $doanhNghiep->ten_doanh_nghiep;
        $hangHoaLonNhat = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->orderByDesc('hang_hoa.so_luong_khai_bao')
            ->select('hang_hoa.*')
            ->first();

        $tongSoLuongs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
            ->sum('hang_hoa.so_luong_khai_bao');

        $ngay_dang_ky = $nhapHang->ngay_dang_ky;
        $date = DateTime::createFromFormat('Y-m-d', $ngay_dang_ky);

        $ngay_thong_quan = NhapHang::where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)->value('ngay_thong_quan');
        $ten_hai_quan = $nhapHang->haiQuan->ten_hai_quan;

        $result = [
            [''],
            [''],
            ['PHIẾU THEO DÕI, TRỪ LÙI HÀNG HÓA XUẤT KHẨU TỪNG LẦN'],
            [''],
            ['', '', '', '', '', '', '', 'Ngày ' . $currentDate . ' Tháng ' . $currentMonth . ' Năm ' . $currentYear],
            ['Tên Doanh Nghiệp: ' . $ten_doanh_nghiep],
            ['Số tờ khai: ' . $nhapHang->so_to_khai_nhap, '', '', 'Ngày đăng ký: Ngày ' . $date->format('d') . ' Tháng ' . $date->format('m') . ' Năm 20' . $date->format('y'), '', '', '', '', 'Chi cục hải quan đăng ký: ' . $ten_hai_quan],
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
            $this->createRichTextBoldItalic('Số hiệu Container', '(nếu có thay đổi)'),
            'Ghi chú'
        ];

        $stt = 1;
        $sum = 0;
        $array = [];
        $hangHoas = HangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)->get();
        $hangHoaArr = [];

        $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->sum('hang_trong_cont.so_luong');

        foreach ($hangHoas as $hangHoa) {
            $hangHoaArr[$hangHoa->ma_hang] = $hangHoa->so_luong_khai_bao;
        }

        $soToKhaiXuats = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->where('xuat_hang.trang_thai', '!=', 'Đã hủy')
            ->orderBy('xuat_hang.so_to_khai_xuat', 'asc') // Sorting from low to high
            ->pluck('xuat_hang.so_to_khai_xuat')
            ->unique() // Ensures unique values
            ->values(); // Reset index


        foreach ($soToKhaiXuats as $soToKhaiXuat) {
            $start = $stt + 11;

            $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $soToKhaiXuat)
                ->with('PTVTXuatCanh')
                ->get()
                ->pluck('PTVTXuatCanh.ten_phuong_tien_vt')
                ->filter()
                ->implode('; ');


            $lanXuats = NhapHang::where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
                ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $soToKhaiXuat)
                ->get();

            foreach ($lanXuats as $item) {
                if (isset($hangHoaArr[$item->ma_hang])) {
                    $hangHoaArr[$item->ma_hang] -= $item->so_luong_xuat;
                }
                if (\Carbon\Carbon::parse($item->ngay_dang_ky)->isSameDay(today())) {
                    $result[] = [
                        $stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $hangHoaArr[$item->ma_hang] == 0 ? '0' : $hangHoaArr[$item->ma_hang],
                        $item->so_seal_cuoi_ngay ?? '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                    $sum += $item->so_luong_xuat;
                }
            }
            // Print or return $hangHoaArr if you want to see the updated values
            // dd($hangHoaArr);


            if ($start == $stt + 11) {
                $end = $stt + 11;
            } else {
                $end = $stt + 10;
            }
            array_push($array, [$start, $end, $ptvts]);
        }
        $result[] = ['', '', '', 'Tổng cộng', '', '', $sum, $soLuongTon, '', '', ''];
        $this->sum = $sum;
        $this->array =  $array;
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
                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(14);

                // Auto-width columns
                $sheet->getColumnDimension('A')->setWidth(width: 5);
                $sheet->getColumnDimension('B')->setWidth(width: 20);
                $sheet->getColumnDimension('C')->setWidth(width: 18);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('E')->setWidth(width: 15);
                $sheet->getColumnDimension('F')->setWidth(width: 10);
                $sheet->getColumnDimension('G')->setWidth(width: 10);
                $sheet->getColumnDimension('H')->setWidth(width: 10);
                $sheet->getColumnDimension('I')->setWidth(width: 18);
                $sheet->getColumnDimension('J')->setWidth(width: 15);
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

                $tenCongViec = "Xuất hàng";


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
                $sheet->getStyle('A' . ($lastStart - 3) . ':L' . ($lastStart - 3))->getFont()->setBold(true);
                if ($this->sum != 0) {
                    foreach ($this->array as $item) {
                        $sheet->mergeCells('B' . $item[0] . ':B' . $item[1]);
                        $sheet->setCellValue('B' . $item[0], $tenCongViec);
                        $sheet->mergeCells('C' . $item[0] . ':C' . $item[1]);
                        $sheet->setCellValue('C' . $item[0], $item[2]);
                    }
                }

                $sheet->getStyle('A' . $secondTableStart . ':L' . $lastStart - 2)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);


                $sheet->getStyle('A' . $lastStart . ':L' . ($lastStart + 1))->applyFromArray([
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

                $first = 0;
                for ($row = $secondTableStart; $row <= $lastStart - 3; $row++) {
                    if ($first == 1) {
                        $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
                        $sheet->getRowDimension($row)->setRowHeight(height: 55);
                    }
                    $first = 1;
                }

                // Set left alignment for number columns
                // $sheet->getStyle('A13:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
    public function drawings()
    {
        // Generate Barcode
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($this->so_to_khai_nhap, $generator::TYPE_CODE_128);

        // Save barcode temporarily
        $barcodePath = storage_path('app/temp-barcode-tru-lui.png');
        file_put_contents($barcodePath, $barcodeData);

        // Create Barcode Drawing
        $barcodeDrawing = new Drawing();
        $barcodeDrawing->setName('Barcode');
        $barcodeDrawing->setDescription('Barcode');
        $barcodeDrawing->setPath($barcodePath);
        $barcodeDrawing->setCoordinates('L1'); // Position barcode at the top right
        $barcodeDrawing->setOffsetX(0);
        $barcodeDrawing->setOffsetY(0);
        $barcodeDrawing->setHeight(20);
        $barcodeDrawing->setWidth(180);

        $drawings[] = $barcodeDrawing; // Add barcode drawing to array

        return $drawings; // Return both drawings
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
        $boldText->getFont()->setBold(true)->setName('Times New Roman')->setSize(14);

        // Italic text
        $italicText = $richText->createTextRun($italic);
        $italicText->getFont()->setItalic(true)->setName('Times New Roman')->setSize(14);

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
