<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\TheoDoiTruLui;
use App\Models\NiemPhong;
use App\Models\XuatHangCont;
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
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;


class BaoCaoTheoDoiTruLuiTheoNgayExport implements FromArray, WithEvents, WithDrawings, WithTitle
{
    protected $tu_ngay;
    protected $so_to_khai_nhap;
    protected $theoDoi;
    protected $nhapHang;
    protected $sum;
    protected $array;
    protected $ten_hai_quan;
    protected $is_nhieu_tau;
    protected $lan_phieu = 0;
    protected $lanArray = [];
    protected $seenMaTheoDois = [];



    public function title(): string
    {
        return $this->so_to_khai_nhap . ' - Xuất hàng';
    }
    public function __construct($so_to_khai_nhap, $tu_ngay)
    {
        $this->so_to_khai_nhap = $so_to_khai_nhap;
        $this->tu_ngay = $tu_ngay;
    }

    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay);
        $day = $tu_ngay->format('d');  // Day of the month
        $month = $tu_ngay->format('m'); // Month number
        $year = $tu_ngay->format('Y');  // Year


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
            'Số, hiệu PTVT nước ngoài nhận hàng',
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
        $stt = 1;
        $sum = 0;
        $seen = [];
        $array = [];
        $hangHoas = HangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)->get();
        $hangHoaArr = [];
        $soTaus = [];

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
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->orderBy('xuat_hang.so_to_khai_xuat', 'asc') // Sorting from low to high
            ->pluck('xuat_hang.so_to_khai_xuat')
            ->unique() // Ensures unique values
            ->values(); // Reset index


        $theoDoiCuoiCung = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->orderBy('ma_theo_doi', 'desc')
            ->where('cong_viec', '!=', '4')
            ->get()
            ->first();
        $ngayCuoiCung = $theoDoiCuoiCung->ngay_them;

        $soToKhaiXuatTrongPhieus = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang_cont.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->whereDate('xuat_hang.ngay_dang_ky',  Carbon::parse($tu_ngay)->toDateString())
            ->pluck('xuat_hang.so_to_khai_xuat')
            ->unique()
            ->toArray();
        $theoDoiTruLuis = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->when(request('cong_viec') == 1, function ($query) {
                return $query->join('xuat_hang', 'xuat_hang.ma_xuat_hang', '=', 'theo_doi_tru_lui.ma_yeu_cau')
                    ->where('xuat_hang.trang_thai', '!=', 0);
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->cong_viec . '-' . $item->ma_yeu_cau; // Group by both fields combined
            })
            ->map(function ($group) {
                return $group->first();
            })
            ->values()
            ->sortBy('ma_theo_doi')
            ->values();

        foreach ($soToKhaiXuats as $soToKhaiXuat) {


            $ptvts = XuatHang::find($soToKhaiXuat)->ten_phuong_tien_vt;

            $lanXuats = NhapHang::where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
                ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $soToKhaiXuat)
                ->select(
                    'xuat_hang.ngay_dang_ky',
                    'xuat_hang_cont.phuong_tien_vt_nhap',
                    'xuat_hang_cont.*',
                    'hang_hoa.*',
                    'hang_trong_cont.ma_hang',
                    'hang_trong_cont.so_luong',
                    'hang_trong_cont.is_da_chuyen_cont',
                )
                ->get();

            $start = null;
            $end = null;

            $is_xuat_het = false;
            if ($nhapHang->trang_thai == 4 || $nhapHang->trang_thai == 7) {
                if (\Carbon\Carbon::parse($nhapHang->ngay_xuat_het)->isSameDay($tu_ngay)) {
                    $is_xuat_het = true;
                }
            }

            foreach ($lanXuats as $index => $item) {

                if (isset($seen[$item->ma_xuat_hang_cont])) {
                    continue;
                }
                if (isset($hangHoaArr[$item->ma_hang])) {
                    $hangHoaArr[$item->ma_hang] -= $item->so_luong_xuat;
                }

                $seen[$item->ma_xuat_hang_cont] = true;

                if (\Carbon\Carbon::parse($item->ngay_dang_ky)->isSameDay($tu_ngay)) {
                    foreach ($theoDoiTruLuis as $truLui) {
                        if (in_array($truLui->ma_theo_doi, $this->seenMaTheoDois)) {
                            continue; // Skip if already processed
                        }

                        $shouldIncrement = false;

                        if ($truLui->cong_viec == 1) {
                            $xuatHang = XuatHang::find($truLui->ma_yeu_cau);
                            if ($xuatHang && $xuatHang->trang_thai != 0) {
                                $shouldIncrement = true;
                                if (in_array($xuatHang->so_to_khai_xuat, $soToKhaiXuatTrongPhieus)) {
                                    $this->lanArray[] = $this->lan_phieu + 1; // +1 because we increment after
                                }
                            }
                        } else {
                            $shouldIncrement = true;
                        }

                        if ($shouldIncrement) {
                            $this->lan_phieu++;
                            $this->seenMaTheoDois[] = $truLui->ma_theo_doi;
                        }
                    }

                    if ($start === null) {
                        $start = $stt + 12; // First occurrence
                    }
                    $soTaus[] = $item->phuong_tien_vt_nhap;

                    if ($is_xuat_het == true) {
                        $soSeal = '';
                    } elseif (\Carbon\Carbon::parse($item->ngay_dang_ky)->greaterThanOrEqualTo($ngayCuoiCung)) {
                        $sealCuoiCung = NiemPhong::where('so_container', $item->so_container)->first()->so_seal ?? '';
                        $soSeal = $sealCuoiCung;
                    } else {
                        $soSeal = $item->so_seal_cuoi_ngay;
                    }

                    $result[] = [
                        $stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $hangHoaArr[$item->ma_hang] == 0 ? '0' : $hangHoaArr[$item->ma_hang],
                        $soSeal,
                        $item->phuong_tien_vt_nhap == $nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];

                    $sum += $item->so_luong_xuat;
                    $end = $stt - 1 + 12;
                    $soLuongTon = 0;
                    foreach ($hangHoaArr as $key => $value) {
                        $soLuongTon += $value;
                    }
                }
            }


            if ($start !== null && $end !== null) {
                $array[] = [$start, $end, $ptvts];
            }
        }
        if (count(array_unique($soTaus)) > 1) {
            $this->is_nhieu_tau = true;
        }
        // Remove duplicates from $array
        $tongLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->sum('hang_trong_cont.so_luong');
        $array = array_map("unserialize", array_unique(array_map("serialize", $array)));
        $result[] = ['', '', '', 'Tổng cộng', '', '', $sum, $soLuongTon == 0 ? '0' : $soLuongTon, '', '', ''];
        $result[] = ['', '', '', '', '', '', 'Tồn TK', $tongLuongTon == 0 ? '0' : $tongLuongTon, '', '', ''];



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

                $sheet->getDelegate()->getSheetView()->setZoomScale(85);

                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(20);

                // Auto-width columns
                $sheet->getColumnDimension('A')->setWidth(width: 7);
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
                $sheet->mergeCells('A5:B5');

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


                $sheet->getStyle('A' . ($lastStart - 4) . ':L' . ($lastStart + 1))->applyFromArray([
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

                $sheet->mergeCells('G' . $lastStart . ':H' . ($lastStart));
                $sheet->setCellValue('G' . $lastStart, "LẦN " . implode(',',  $this->lanArray));
                $sheet->getStyle('G' . $lastStart)->getFont()->setSize(22); // Increased font size



                $first = 0;
                for ($row = $secondTableStart; $row <= $lastStart - 3; $row++) {
                    if ($first == 1) {
                        $sheet->getRowDimension($row)->setRowHeight(height: 80);
                    }
                    $first = 1;
                }
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(12)->setRowHeight(30);

                $sheet->getRowDimension($lastStart - 3)->setRowHeight(40);
                $sheet->getRowDimension($lastStart - 4)->setRowHeight(40);

                if (mb_strlen($this->ten_hai_quan, 'UTF-8') > 40) {
                    $sheet->getRowDimension(7)->setRowHeight(50);
                    $sheet->getStyle('A7:L7')->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);
                }

                // Set left alignment for number columns
                // $sheet->getStyle('A13:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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
        $boldText->getFont()->setSize(20);

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
}
