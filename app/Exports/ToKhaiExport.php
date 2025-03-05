<?php

namespace App\Exports;

use App\Models\HangHoa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ToKhaiExport implements FromCollection, WithHeadings, WithStyles, WithCustomStartCell
{    
    protected $nhapHang;
    protected $hangHoaRows;

    public function __construct($nhapHang, $hangHoaRows)
    {
        $this->nhapHang = $nhapHang;
        $this->hangHoaRows = $hangHoaRows;
    }

    public function collection()
    {
        $data = [];

        // Loop through each row of HangHoa to prepare data
        foreach ($this->hangHoaRows as $index => $hangHoa) {
            $data[] = [
                $index + 1,
                $hangHoa->ten_hang,
                $hangHoa->loai_hang,
                $hangHoa->xuat_xu,
                $hangHoa->so_luong_khai_bao,
                $hangHoa->don_vi_tinh,
                $hangHoa->don_gia,
                $hangHoa->tri_gia,
                $hangHoa->so_to_khai_ptvt,
                $hangHoa->so_container,
            ];
        }

        // Add the totals row at the end of the data
        $soLuongSum = $this->hangHoaRows->sum('so_luong_khai_bao');
        $triGiaSum = $this->hangHoaRows->sum('tri_gia');

        $data[] = [
            'Tổng cộng',  // You can put any text here for the last row
            '', '', '',$soLuongSum, '', '', $triGiaSum, '', '',  // Add totals
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên hàng',
            'Loại hàng',
            'Xuất xứ',
            'Số lượng',
            'Đơn vị tính',
            'Đơn giá (USD)',
            'Trị giá (USD)',
            'Phương tiện vận tải',
            'Số container',
        ];
    }

    public function startCell(): string
    {
        return 'A4'; // Table headings start from row 4
    }

    public function styles(Worksheet $sheet)
    {
        // Add custom rows for header with font size 14
        $sheet->mergeCells('A1:J1'); // Merge for the first row
        $sheet->setCellValue('A1', $this->nhapHang->doanhNghiep ? $this->nhapHang->doanhNghiep->ten_doanh_nghiep : 'Unknown');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);
    
        $sheet->mergeCells('A2:J2'); // Merge for the second row
        $sheet->setCellValue('A2', "Số: {$this->nhapHang->so_to_khai_nhap} ngày {$this->nhapHang->ngay_dang_ky} Đăng ký tại: " . ($this->nhapHang->haiQuan ? $this->nhapHang->haiQuan->ten_hai_quan : $this->nhapHang->ma_hai_quan));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);
    
        // Calculate last row of the table (including totals row)
        $lastRow = count($this->hangHoaRows) + 5;
    
        // Style for the entire table (headers + data + totals row)
        $sheet->getStyle('A4:K' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);
    
        // Table headings with font size 10
        $sheet->getStyle('A4:K4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);
    
        // Set font size for all table data rows to 10
        $sheet->getStyle('A5:K' . ($lastRow - 1))->applyFromArray([
            'font' => ['size' => 10, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);
    
        // Auto-fit columns for all data
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        // Format totals row (the last row)
        $sheet->getStyle('A' . $lastRow . ':K' . $lastRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => 'center'],
        ]);

    }
}
