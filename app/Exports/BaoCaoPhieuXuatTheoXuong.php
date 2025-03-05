<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
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
use DateTime;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BaoCaoPhieuXuatTheoXuong implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell
{
    protected $ma_doanh_nghiep;
    protected $xuatHang;
    protected $date;
    protected $so_ptvt_xuat_canh;
    protected $data = [];

    public function __construct($ma_doanh_nghiep, $so_ptvt_xuat_canh, $date)
    {
        $this->ma_doanh_nghiep = $ma_doanh_nghiep;
        $this->date = $date;
        $this->so_ptvt_xuat_canh = $so_ptvt_xuat_canh;
    }

    public function collection()
    {
        $xuatHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->join('ptvt_xuat_canh_cua_phieu', 'xuat_hang.so_to_khai_xuat', '=', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat')
            ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $this->so_ptvt_xuat_canh)
            ->where('nhap_hang.ma_doanh_nghiep', $this->ma_doanh_nghiep)
            ->where('xuat_hang.trang_thai', '!=', "Đã hủy")
            ->whereDate('xuat_hang.ngay_dang_ky', $this->date)
            ->select(
                'nhap_hang.so_to_khai_nhap',
                'so_luong_khai_bao',
                'so_luong_xuat',
                'so_luong_ton',
                'ngay_thong_quan',
                'ten_hang',
                'don_vi_tinh',
                'phuong_tien_vt_nhap',
                'xuat_hang_cont.so_container',
                'xuat_hang.updated_at',
            )
            ->get()
            ->sortBy('updated_at')
            ->values();

        $data = [];
        foreach ($xuatHangs as $index => $xuatHang) {
            $so_luong_da_xuat = $xuatHang->so_luong_khai_bao - $xuatHang->so_luong_xuat - $xuatHang->so_luong_ton;
            $data[] = [
                $index + 1,
                $xuatHang->so_to_khai_nhap,
                Carbon::parse($xuatHang->ngay_thong_quan)->format('d-m-Y'),
                $xuatHang->ten_hang,
                $xuatHang->so_luong_khai_bao,
                $xuatHang->don_vi_tinh,
                $so_luong_da_xuat == 0 ? '0' : ($so_luong_da_xuat ?? '0'),
                $xuatHang->so_luong_xuat,
                $xuatHang->so_luong_ton == 0 ? '0' : ($xuatHang->so_luong_ton ?? '0'),
                $xuatHang->phuong_tien_vt_nhap,
                $xuatHang->so_container,
                ''
            ];
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

        $this->date = new DateTime($this->date);

        $currentDate = $this->date->format('d');  // Day of the month
        $currentMonth = $this->date->format('m'); // Month number
        $currentYear = $this->date->format('Y');  // Year
        $tenDoanhNghiep = DoanhNghiep::find($this->ma_doanh_nghiep)->ten_doanh_nghiep;


        $sheet->getParent()->getDefaultStyle()->getFont()->setSize(12);
        $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0');
        //1
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', "TÊN DOANH NGHIỆP: " . $tenDoanhNghiep);
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
        $sheet->getColumnDimension('K')->setWidth(width: 15);
        $sheet->getColumnDimension('L')->setWidth(width: 20);
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getStyle('L')->getNumberFormat()->setFormatCode('0'); // Apply format
        $sheet->getRowDimension(1)->setRowHeight(20);




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


        $sheet->mergeCells('L6' . ':L' . $lastTableRow);
        $sheet->setCellValue('L6', PTVTXuatCanh::find($this->so_ptvt_xuat_canh)->ten_phuong_tien_vt);


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
