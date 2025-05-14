<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\XuatCanh;
use App\Models\XuatHangCont;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BangTongHopKyLuat implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;

    public function __construct($tu_ngay)
    {
        $this->tu_ngay = $tu_ngay;
    }
    public function array(): array
    {
        // $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d-m-Y');
        // $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII'],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA'],
            ['Phụ lục II'],
            ['BẢNG TỔNG HỢP KẾT QUẢ CHẤP HÀNH KỶ LUẬT, KỶ CƯƠNG VÀ THỰC HIỆN CÔNG VIỆC HÀNG THÁNG'],
            ["Từ $this->tu_ngay"],
            [''],
            ['STT', 'Họ và Tên', 'Chức vụ', 'Đơn vị công tác', 'Số ngày làm việc trong tháng', 'Vi phạm kỷ cương, kỷ luật hành chính', '', 'Kết quả thực hiện công việc được giao', '', '', '', 'Ghi chú'],
            ['', '', '', '', '', 'Có', 'Không', 'Tổng số công việc được giao', 'CV hoàn thành bảo đảm tiến độ, chất lượng', 'CV hoàn thành nhưng chưa bảo đảm tiến độ hoặc chất lượng', 'CV chưa hoàn thành', '',],
            ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', '(11)', '(12)']
        ];

        return $result;
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

                foreach (['B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 15);
                $sheet->getColumnDimension('D')->setWidth(width: 15);
                $sheet->getColumnDimension('L')->setWidth(width: 15);
                $sheet->getColumnDimension('J')->setWidth(width: 12);
                $sheet->getColumnDimension('L')->setWidth(width: 20);

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $event->sheet->getDelegate()->getRowDimension(7)->setRowHeight(30);

                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                $sheet->mergeCells('A3:M3');
                $sheet->mergeCells('A4:M4');
                $sheet->mergeCells('A5:M5');
                $sheet->mergeCells('A6:M6');

                $sheet->mergeCells('F7:G7');
                $sheet->mergeCells('H7:K7');

                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');
                $sheet->mergeCells('C7:C8');
                $sheet->mergeCells('D7:D8');
                $sheet->mergeCells('E7:E8');
                $sheet->mergeCells('L7:L8');

                // Your existing styles
                $sheet->getStyle('A1:L6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:L6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A7:L9')->applyFromArray([
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

                $sheet->getStyle('A5:L5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true],
                ]);
                $sheet->getStyle('A9:L9')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A10:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
