<?php

namespace App\Exports;

use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\HangTrongCont;
use App\Models\PTVTXuatCanhCuaPhieuSua;
use App\Models\XuatHangSua;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;

class ToKhaiXuatExport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, WithDrawings
{
    protected $so_to_khai_xuat;
    protected $xuatHang;
    protected $ptvts;
    protected $data = [];
    protected $ma_yeu_cau;

    public function __construct($so_to_khai_xuat, $ma_yeu_cau = null)
    {
        $this->so_to_khai_xuat = $so_to_khai_xuat;
        $this->ma_yeu_cau = $ma_yeu_cau;
    }

    public function collection()
    {
        // Get the XuatHang record
        $xuatHang = XuatHang::find($this->so_to_khai_xuat);
        $this->ptvts = $xuatHang->ten_phuong_tien_vt;

        if ($this->ma_yeu_cau) {
            $xuatHangSua = XuatHangSua::find($this->ma_yeu_cau);
            $xuatHang->ma_loai_hinh = $xuatHangSua->ma_loai_hinh;
            $xuatHang->ten_doan_tau = $xuatHangSua->ten_doan_tau;
            // $this->ptvts = PTVTXuatCanhCuaPhieuSua::where('xuat_canh_sua.ma_yeu_cau', $this->ma_yeu_cau)
            //     ->join('xuat_canh_sua', 'ptvt_xuat_canh_cua_phieu_sua.ma_yeu_cau', '=', 'xuat_canh_sua.ma_yeu_cau')
            //     ->first()
            //     ->ten_phuong_tien_vt;
        }
        $this->xuatHang = $xuatHang;
        $sumSoLuongXuat = 0;
        $data = [];

        if ($this->ma_yeu_cau) {
            $xuatHangConts = XuatHangSua::join('xuat_hang_chi_tiet_sua', 'xuat_hang_sua.ma_yeu_cau', '=', 'xuat_hang_chi_tiet_sua.ma_yeu_cau')
                ->where('xuat_hang_sua.ma_yeu_cau', $this->ma_yeu_cau)
                ->join('hang_trong_cont', 'xuat_hang_chi_tiet_sua.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->get();
        } else {
            // First, get all XuatHangConts with their related hang_hoa and hang_trong_cont data
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $this->so_to_khai_xuat)
                ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->get();
        }


        // Build arrays for declared quantity, exported quantity, and remaining quantity keyed by ma_hang.
        // Use distinct ma_hang values from $xuatHangConts.
        $maHangList = $xuatHangConts->pluck('ma_hang')->unique();

        $soLuongKhaiBaoArr = [];
        $soLuongDaXuatArr   = [];
        $soLuongTonArr      = [];

        foreach ($maHangList as $ma_hang) {
            // Get the first record for this ma_hang from the initial collection
            $firstRecord = $xuatHangConts->firstWhere('ma_hang', $ma_hang);
            $soLuongKhaiBaoArr[$ma_hang] = $firstRecord->so_luong_khai_bao;
            $soLuongDaXuatArr[$ma_hang]  = 0;
            $soLuongTonArr[$ma_hang]     = $firstRecord->so_luong_khai_bao;
        }
        // Get all detailed XuatHangCont records for these ma_hang values in one query.
        // This avoids running a query for each ma_hang.



        if ($this->ma_yeu_cau) {
            $seen = [];
            $stt = 1;
            // Iterate over each group (each ma_hang)
            foreach ($maHangList as $ma_hang) {
                $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                    ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                    ->where('hang_hoa.ma_hang', $ma_hang)
                    ->where('xuat_hang.trang_thai', '!=', '0')
                    ->select(
                        'xuat_hang_cont.ma_xuat_hang_cont',
                        'xuat_hang_cont.ma_hang_cont',
                        'xuat_hang_cont.so_to_khai_nhap',
                        'xuat_hang_cont.so_luong_xuat',
                        'nhap_hang.ngay_thong_quan',
                        'nhap_hang.phuong_tien_vt_nhap',
                        'hang_hoa.ma_hang',
                        'hang_hoa.ten_hang',
                        'hang_hoa.don_vi_tinh',
                        'hang_hoa.so_luong_khai_bao',
                        'hang_trong_cont.so_luong',
                        'hang_trong_cont.so_container',
                        'xuat_hang.so_to_khai_xuat',
                        'xuat_hang.trang_thai',
                        'xuat_hang.ten_phuong_tien_vt'
                    )
                    ->get();

                foreach ($xuatHangs as $xuatHang2) {
                    if (isset($soLuongTonArr[$ma_hang])) {
                        $soLuongTonArr[$ma_hang] -= $xuatHang2->so_luong_xuat;
                        $soLuongDaXuatArr[$ma_hang] += $xuatHang2->so_luong_xuat;
                    }
                    if ($xuatHang->so_to_khai_xuat == $xuatHang2->so_to_khai_xuat) {
                        $seen[$ma_hang] = true;
                        $xuatHangSua = XuatHangSua::join('xuat_hang_chi_tiet_sua', 'xuat_hang_sua.ma_yeu_cau', '=', 'xuat_hang_chi_tiet_sua.ma_yeu_cau')
                            ->join('hang_trong_cont', 'xuat_hang_chi_tiet_sua.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                            ->join('ptvt_xuat_canh_cua_phieu_sua', 'xuat_hang_sua.ma_yeu_cau', '=', 'ptvt_xuat_canh_cua_phieu_sua.ma_yeu_cau')
                            ->join('ptvt_xuat_canh', 'ptvt_xuat_canh_cua_phieu_sua.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
                            ->where('xuat_hang_sua.ma_yeu_cau', $this->ma_yeu_cau)
                            ->where('hang_trong_cont.ma_hang', $ma_hang)
                            ->first();

                        $soLuongXuatSua = $xuatHangSua->so_luong_xuat;
                        $soLuongTon   = $soLuongTonArr[$ma_hang] + $xuatHang2->so_luong_xuat - $soLuongXuatSua;
                        $soLuongDaXuat = $soLuongDaXuatArr[$ma_hang] - $xuatHang2->so_luong_xuat;
                        $soLuongKhaiBao = $soLuongKhaiBaoArr[$ma_hang];

                        $data[] = [
                            $stt++,
                            $xuatHang2->so_to_khai_nhap,
                            Carbon::parse($xuatHang2->ngay_thong_quan)->format('d-m-Y'),
                            $xuatHang2->ten_hang,
                            $soLuongKhaiBao,
                            $xuatHang2->don_vi_tinh,
                            ($soLuongDaXuat == 0 ? '0' : $soLuongDaXuat),
                            $soLuongXuatSua,
                            ($soLuongTon == 0 ? '0' : $soLuongTon),
                            $xuatHang2->phuong_tien_vt_nhap,
                            $xuatHangSua->so_container,
                            $xuatHangSua->ten_phuong_tien_vt ?? '',
                        ];

                        $sumSoLuongXuat += $soLuongXuatSua;
                    }
                }
            }
            foreach ($maHangList as $maHang) {
                if (!isset($seen[$maHang])) {
                    $xuatHangSua = XuatHangSua::join('xuat_hang_chi_tiet_sua', 'xuat_hang_sua.ma_yeu_cau', '=', 'xuat_hang_chi_tiet_sua.ma_yeu_cau')
                        ->join('hang_trong_cont', 'xuat_hang_chi_tiet_sua.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                        ->where('hang_trong_cont.ma_hang', $maHang)
                        ->where('xuat_hang_sua.ma_yeu_cau', $this->ma_yeu_cau)
                        ->first();
                    $soLuongXuatSua = $xuatHangSua->so_luong_xuat;
                    $soLuongTon   = $soLuongTonArr[$ma_hang] - $soLuongXuatSua;
                    $soLuongDaXuat = $soLuongDaXuatArr[$ma_hang];
                    $soLuongKhaiBao = $soLuongKhaiBaoArr[$ma_hang];
                    $hangHoa = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                        ->where('ma_hang', $maHang)
                        ->first();
                    $data[] = [
                        $stt++,
                        $hangHoa->so_to_khai_nhap,
                        Carbon::parse($hangHoa->ngay_thong_quan)->format('d-m-Y'),
                        $hangHoa->ten_hang,
                        $soLuongKhaiBao,
                        $hangHoa->don_vi_tinh,
                        ($soLuongDaXuat == 0 ? '0' : $soLuongDaXuat),
                        $soLuongXuatSua,
                        ($soLuongTon == 0 ? '0' : $soLuongTon),
                        $hangHoa->phuong_tien_vt_nhap,
                        $xuatHangSua->so_container,
                        $xuatHangSua->ten_phuong_tien_vt ?? '',
                    ];
                    $sumSoLuongXuat += $soLuongXuatSua;
                }
            }
        } else {
            $allXHCCuaHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->whereIn('hang_hoa.ma_hang', $maHangList)
                ->where('xuat_hang.trang_thai', '!=', '0')
                ->select(
                    'xuat_hang_cont.ma_xuat_hang_cont',
                    'xuat_hang_cont.ma_hang_cont',
                    'xuat_hang_cont.so_to_khai_nhap',
                    'xuat_hang_cont.so_luong_xuat',
                    'nhap_hang.ngay_thong_quan',
                    'nhap_hang.phuong_tien_vt_nhap',
                    'hang_hoa.ma_hang',
                    'hang_hoa.ten_hang',
                    'hang_hoa.don_vi_tinh',
                    'hang_hoa.so_luong_khai_bao',
                    'hang_trong_cont.so_luong',
                    'hang_trong_cont.so_container',
                    'xuat_hang.so_to_khai_xuat',
                    'xuat_hang.trang_thai',
                    'xuat_hang.ten_phuong_tien_vt'
                )
                ->get();
            // Group the detailed records by ma_hang
            $groupedXHCCuaHang = $allXHCCuaHang->groupBy('ma_hang');
            $seen = [];
            $stt = 1;
            // Iterate over each group (each ma_hang)
            foreach ($groupedXHCCuaHang as $ma_hang => $group) {
                foreach ($group as $record) {
                    // Skip if this export detail has already been processed
                    if (isset($seen[$record->ma_xuat_hang_cont])) {
                        continue;
                    }
                    $seen[$record->ma_xuat_hang_cont] = true;

                    // Deduct the exported quantity from the remaining quantity for this ma_hang
                    if (isset($soLuongTonArr[$ma_hang])) {
                        $soLuongTonArr[$ma_hang] -= $record->so_luong_xuat;
                    }

                    // Only include records that belong to the current XuatHang (by so_to_khai_xuat)
                    if ($xuatHang->so_to_khai_xuat == $record->so_to_khai_xuat) {
                        $soLuongTon   = $soLuongTonArr[$ma_hang];
                        $soLuongDaXuat = $soLuongDaXuatArr[$ma_hang];
                        $soLuongKhaiBao = $soLuongKhaiBaoArr[$ma_hang];
                        $data[] = [
                            $stt++,
                            $record->so_to_khai_nhap,
                            Carbon::parse($record->ngay_thong_quan)->format('d-m-Y'),
                            $record->ten_hang,
                            $soLuongKhaiBao,
                            $record->don_vi_tinh,
                            ($soLuongDaXuat == 0 ? '0' : $soLuongDaXuat),
                            $record->so_luong_xuat,
                            ($soLuongTon == 0 ? '0' : $soLuongTon),
                            $record->phuong_tien_vt_nhap,
                            $record->so_container,
                            $record->ten_phuong_tien_vt ?? '',
                        ];

                        $sumSoLuongXuat += $record->so_luong_xuat;
                    }

                    // Update exported quantity for this ma_hang
                    if (isset($soLuongTonArr[$ma_hang])) {
                        $soLuongDaXuatArr[$ma_hang] += $record->so_luong_xuat;
                    }
                }
            }
        }





        $data[] = ['Tổng cộng', '', '', '', '', '', '', $sumSoLuongXuat];
        $data[] = ['Ghi chú: Công ty chúng tôi cam kết chịu trách nhiệm trước pháp luật đối với các nội dung thông tin khai báo như trên.'];

        $data[] = [''];
        $data[] = ['Đoàn:', $xuatHang->ten_doan_tau];
        $data[] = ['Cont:'];
        $this->data = $data;
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'STT',
            'Số tờ khai',
            'Ngày TK',
            'Tên hàng',
            'Số lượng',
            'Đơn vị tính',
            'Số lượng đã xuất',
            'Số lượng đăng ký xuất',
            'Số lượng tồn',
            'Số PTVT Việt Nam',
            'Số Container',
            'Số PTVT nước ngoài nhận hàng',
        ];
    }

    public function startCell(): string
    {
        return 'A5'; // Table headings start from row 4
    }

    public function styles(Worksheet $sheet)
    {
        // Set print settings first
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setHorizontalCentered(true)
            ->setPrintArea('A1:L' . $sheet->getHighestRow() + 6);

        // Set margins (in inches)
        $sheet->getPageMargins()
            ->setTop(0.5)
            ->setRight(0.5)
            ->setBottom(0.5)
            ->setLeft(0.5)
            ->setHeader(0.3)
            ->setFooter(0.3);

        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet->getParent()->getDefaultStyle()->getFont()->setSize(14);

        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year
        $ngay_dang_ky = Carbon::parse($this->xuatHang->ngay_dang_ky)->format('d/m/Y');

        $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');
        //1
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', "TÊN DOANH NGHIỆP: " . $this->xuatHang->doanhNghiep->ten_doanh_nghiep);
        //3
        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', "PHIẾU ĐĂNG KÝ KẾ HOẠCH XUẤT NHẬP KHẨU HÀNG HÓA");
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);
        //4
        $sheet->mergeCells('A4:L4');
        $sheet->setCellValue('A4', "Ngày $currentDate tháng $currentMonth năm $currentYear");
        //5
        $sheet->getRowDimension(5)->setRowHeight(height: 55);
        $sheet->getStyle('A5:L5')->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(width: 6);
        $sheet->getColumnDimension('B')->setWidth(width: 20);
        $sheet->getColumnDimension('C')->setWidth(width: 12);
        $sheet->getColumnDimension('D')->setWidth(width: 25);
        $sheet->getColumnDimension('E')->setWidth(width: 8);
        $sheet->getColumnDimension('F')->setWidth(width: 10);
        $sheet->getColumnDimension('G')->setWidth(width: 10);
        $sheet->getColumnDimension('H')->setWidth(width: 10);
        $sheet->getColumnDimension('I')->setWidth(width: 10);
        $sheet->getColumnDimension('J')->setWidth(width: 10);
        $sheet->getColumnDimension('K')->setWidth(width: 20);
        $sheet->getColumnDimension('L')->setWidth(width: 20);
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getStyle('L')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getRowDimension(1)->setRowHeight(20);

        $this->centerCell($sheet, "L2");




        $lastRow = $sheet->getHighestRow();
        $secondStart = null;
        for ($i = 1; $i <= $lastRow; $i++) {
            if ($sheet->getCell('A' . $i)->getValue() === 'Ghi chú: Công ty chúng tôi cam kết chịu trách nhiệm trước pháp luật đối với các nội dung thông tin khai báo như trên.') {
                $secondStart = $i;
                break;
            }
        }

        $totalPos = $secondStart - 1;
        $sheet->mergeCells("A{$secondStart}:L{$secondStart}");
        $sheet->mergeCells("A{$totalPos}:G{$totalPos}");
        $this->centerCell($sheet, 'A' . $totalPos . ':G' . $totalPos);


        $sheet->getStyle('A' . $secondStart . ':L' . $secondStart)->applyFromArray([
            'font' => ['italic' => true, 'size' => 14, 'name' => 'Times New Roman'],
        ]);

        $this->centerCell($sheet, 'A5:L' . $lastRow);

        $sheet->getStyle('A' . $secondStart . ':B' . ($secondStart + 3))->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);





        $lastTableRow = $secondStart - 1;
        $this->centerCell($sheet, "A3:L{$lastTableRow}");
        $this->applyBorder($sheet, "A5:L{$lastTableRow}");
        $this->leftCell($sheet, "D6:D{$lastTableRow}");




        // $sheet->mergeCells('B6' . ':B' . $lastTableRow);
        // $sheet->setCellValue('B6', $this->xuatHang->so_to_khai_nhap);
        // $sheet->mergeCells('C6' . ':C' . $lastTableRow);
        // $sheet->setCellValue('C6', $ngayTK);
        // $sheet->mergeCells('J6' . ':J' . $lastTableRow);
        // $sheet->setCellValue('J6', $this->xuatHang->nhapHang->phuong_tien_vt_nhap);
        // $sheet->mergeCells('L6' . ':L' . $lastTableRow);
        // $sheet->setCellValue('L6', $this->ptvts);


        $sheet->mergeCells("H" . ($secondStart + 1) . ":L" . ($secondStart + 1));
        $sheet->setCellValue("H" . ($secondStart + 1), "ĐẠI DIỆN DOANH NGHIỆP");
        $sheet->getStyle("H" . ($secondStart + 1))->getFont()->setBold(true);

        $sheet->mergeCells("H" . ($secondStart + 2) . ":L" . ($secondStart + 2));
        $sheet->setCellValue("H" . ($secondStart + 2), "(Ký, ghi rõ họ và tên)");
        $sheet->getStyle("H" . ($secondStart + 2))->applyFromArray([
            'font' => ['italic' => true],
        ]);
        $this->centerCell($sheet, "H" . ($secondStart + 1) . ":L" . ($secondStart + 2));


        $sheet->getStyle('A1:' . 'L' . $lastRow)->getAlignment()->setWrapText(true);
    }


    public function drawings()
    {
        $drawings = [];

        if (in_array($this->xuatHang->trang_thai, ["2", "12", "13", "11"])) {
            $qrCodeText = 'Cán bộ công chức phê duyệt: ' . ($this->xuatHang->congChuc->ten_cong_chuc ?? '');

            // Create the QR code
            $qrCode = QrCode::create($qrCodeText)->setSize(150);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $imageData = $result->getString();

            // Save QR Code temporarily
            $qrTempPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($qrTempPath, $imageData);

            // Create QR Code Drawing
            $qrDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $qrDrawing->setName('QR Code');
            $qrDrawing->setDescription('QR Code');
            $qrDrawing->setPath($qrTempPath);
            $qrDrawing->setHeight(150);
            $lastRow = count($this->data) + 6;
            $qrDrawing->setCoordinates('A' . $lastRow);

            $drawings[] = $qrDrawing;
        }

        return $drawings;
    }

    // Optional: Clean up temporary file after export
    public function __destruct()
    {
        $tempPath = storage_path('app/temp-qr-' . $this->xuatHang->id . '.png');
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        if (file_exists(storage_path('app/temp-barcode-tk-xuat.png'))) {
            unlink(storage_path('app/temp-barcode-tk-xuat.png'));
        }
    }
    function leftCell($sheet, string $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
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
}
