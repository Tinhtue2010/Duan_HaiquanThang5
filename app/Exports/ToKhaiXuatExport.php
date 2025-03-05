<?php

namespace App\Exports;

use App\Models\HangHoa;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanh;
use App\Models\PTVTXuatCanhCuaPhieu;
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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ToKhaiXuatExport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell, WithDrawings
{
    protected $so_to_khai_xuat;
    protected $xuatHang;
    protected $ptvts;
    protected $data = [];

    public function __construct($so_to_khai_xuat)
    {
        $this->so_to_khai_xuat = $so_to_khai_xuat;
    }

    public function collection()
    {
        // Get the XuatHang record
        $xuatHang = XuatHang::find($this->so_to_khai_xuat);
        $this->xuatHang = $xuatHang;
        $this->ptvts = $xuatHang->ten_phuong_tien_vt;

        $data = [];

        // First, get all XuatHangConts with their related hang_hoa and hang_trong_cont data
        $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $this->so_to_khai_xuat)
            ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->get();

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
        $allXHCCuaHang = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->whereIn('hang_hoa.ma_hang', $maHangList)
            ->where('xuat_hang.trang_thai', '!=', 'Đã hủy')
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
                        $record->ten_phuong_tien_vt,
                    ];
                }

                // Update exported quantity for this ma_hang
                if (isset($soLuongTonArr[$ma_hang])) {
                    $soLuongDaXuatArr[$ma_hang] += $record->so_luong_xuat;
                }
            }
        }



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

        $currentDate = Carbon::now()->format('d');  // Day of the month
        $currentMonth = Carbon::now()->format('m'); // Month number
        $currentYear = Carbon::now()->format('Y');  // Year
        $ngay_dang_ky = Carbon::parse($this->xuatHang->ngay_dang_ky)->format('d/m/Y');


        $sheet->getParent()->getDefaultStyle()->getFont()->setSize(12);
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
        $sheet->getRowDimension(5)->setRowHeight(height: 45);
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
        $sheet->mergeCells("A{$secondStart}:L{$secondStart}");


        $sheet->getStyle('A' . $secondStart . ':L' . $secondStart)->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'name' => 'Times New Roman'],
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
        $drawings = []; // Array to store both QR code and barcode drawings

        if (in_array($this->xuatHang->trang_thai, ["Đã duyệt", "Đã duyệt xuất hàng", "Đã thực xuất hàng", "Đã chọn phương tiện xuất cảnh"])) {
            // Generate QR Code
            $qrContent = 'Cán bộ công chức phê duyệt: ' . ($this->xuatHang->congChuc->ten_cong_chuc ?? '');
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrContent);
            $qrImageContent = file_get_contents($qrCodeUrl);

            // Save QR Code temporarily
            $qrTempPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($qrTempPath, $qrImageContent);

            // Create QR Code Drawing
            $qrDrawing = new Drawing();
            $qrDrawing->setName('QR Code');
            $qrDrawing->setDescription('QR Code');
            $qrDrawing->setPath($qrTempPath);
            $qrDrawing->setHeight(130);
            $lastRow = count($this->data) + 6;
            $qrDrawing->setCoordinates('A' . $lastRow);

            $drawings[] = $qrDrawing; // Add QR drawing to array
        }

        return $drawings; // Return both drawings
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
