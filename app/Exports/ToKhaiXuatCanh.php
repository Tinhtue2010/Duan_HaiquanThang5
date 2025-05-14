<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\XuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\XuatHangCont;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\ThuyenTruong;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use DateTime;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Endroid\QrCode\QrCode;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Endroid\QrCode\Writer\PngWriter;

class ToKhaiXuatCanh implements FromArray, WithEvents, WithDrawings
{
    protected $ma_xuat_canh;
    protected $data = [];

    public function __construct($ma_xuat_canh)
    {
        $this->ma_xuat_canh = $ma_xuat_canh;
    }

    public function array(): array
    {
        $result = [
            ['', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['Độc lập - Tự do - Hạnh phúc'],
            ['Socialist Republic of Vietnam'],
            ['Independence - Freedom - Happiness'],
            ['---------------'],
            ['BẢN KHAI CHUNG'],
            ['GENERAL DECLARATION'],
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
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setHorizontalCentered(true)
                    ->setPrintArea('A1:J72');

                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);

                $currentDate = Carbon::now();
                $xuatCanh = XuatCanh::find($this->ma_xuat_canh);
                $doanhNghiepKhaiBao = DoanhNghiep::find($xuatCanh->ma_doanh_nghiep);
                $doanhNghiepDuocChon = DoanhNghiep::find($xuatCanh->ma_doanh_nghiep_chon);
                $cacCongTy = "";
                $uniqueMaDoanhNghieps = XuatCanhChiTiet::with('xuatHang')
                    ->whereHas('xuatHang')
                    ->where('ma_xuat_canh', $this->ma_xuat_canh)
                    ->get()
                    ->pluck('xuatHang.ma_doanh_nghiep')
                    ->unique()
                    ->values();

                $hangHoaLonNhat = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->join('xuat_hang_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                    ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                    ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
                    ->where('xuat_canh.ma_xuat_canh', $this->ma_xuat_canh)
                    ->orderByDesc('xuat_hang_cont.so_luong_xuat') // Order by the highest quantity
                    ->select('hang_hoa.*', 'xuat_hang_cont.so_luong_xuat') // Select relevant fields
                    ->first();
                $tongSoHangXuat = XuatHangCont::join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                    ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
                    ->where('xuat_canh.ma_xuat_canh', $this->ma_xuat_canh)
                    ->sum('xuat_hang_cont.so_luong_xuat');

                // $tongTrongLuongXuat = NhapHang::join('xuat_hang', 'xuat_hang.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
                //     ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                //     ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
                //     ->where('xuat_canh.ma_xuat_canh', $this->ma_xuat_canh)
                //     ->sum('nhap_hang.trong_luong');

                // $tongTrongLuongXuat = number_format($tongTrongLuongXuat, 1, '.', '');

                $first = true;
                foreach ($uniqueMaDoanhNghieps as $maDoanhNghiep) {
                    $soLuongKien = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
                        ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                        ->join('xuat_hang_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                        ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                        ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                        ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
                        ->where('nhap_hang.ma_doanh_nghiep', $maDoanhNghiep)
                        ->where('xuat_canh.ma_xuat_canh', $this->ma_xuat_canh)
                        ->sum('xuat_hang_cont.so_luong_xuat');

                    $doanhNghiep = DoanhNghiep::find($maDoanhNghiep);

                    $tenDoanhNghiep = $doanhNghiep->ten_doanh_nghiep;
                    if ($first) {
                        $cacCongTy .= $tenDoanhNghiep . " " . $soLuongKien . " Kiện";
                        $first = false;
                    } else {
                        $cacCongTy .= " + " . $tenDoanhNghiep . " " . $soLuongKien . " Kiện ";
                    }
                }

                $doanhNghiep = DoanhNghiep::find($xuatCanh->ma_doanh_nghiep);


                // Set font for entire sheet
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
                $sheet->getStyle('B1')->getAlignment()->setWrapText(true);

                $sheet->getColumnDimension('A')->setWidth(width: 20);
                $sheet->getColumnDimension('B')->setWidth(width: 10);
                $sheet->getColumnDimension('C')->setWidth(width: 10);
                $sheet->getColumnDimension('D')->setWidth(width: 20);
                $sheet->getColumnDimension('E')->setWidth(width: 10);
                $sheet->getColumnDimension('F')->setWidth(width: 21);
                $sheet->getColumnDimension('G')->setWidth(width: 20);
                $sheet->getColumnDimension('H')->setWidth(width: 12);
                $sheet->getColumnDimension('I')->setWidth(width: 21);
                $sheet->getColumnDimension('J')->setWidth(width: 20);


                $lastRow = $sheet->getHighestRow();
                $sheet->setCellValue('A1', $doanhNghiep->chuHang->ten_rut_gon ?? '');

                $sheet->mergeCells('B1:I1');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('B1')->getFont()->setBold(true);
                $sheet->mergeCells('A2:J2');
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->mergeCells('A3:J3');
                $sheet->mergeCells('A4:J4');
                $sheet->mergeCells('A5:J5');
                $sheet->getStyle('A5')->getFont()->setBold(true);
                $sheet->mergeCells('A6:J6');
                $sheet->getStyle('A6')->getFont()->setBold(true);
                $sheet->mergeCells('A7:J7');
                $sheet->getStyle('A7')->getFont()->setBold(true);
                $sheet->mergeCells('A8:J8');

                $sheet->getStyle('A1:J8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                //9
                $this->applyBorder($sheet, 'E9');
                $sheet->setCellValue('F9', "Đến \nArrival");
                $sheet->setCellValue('H9', "X");
                $this->centerCell($sheet, "H9");


                $this->applyBorder($sheet, 'H9');
                $sheet->setCellValue('I9', "Rời \nDeparture");

                //10-11
                $sheet->mergeCells('A10:B10');
                $sheet->mergeCells('A11:B11');
                $sheet->mergeCells('C10:D11');
                $this->applyOuterBorder($sheet, 'A10:D11');
                $sheet->setCellValue('A10', "1.1. Tên và loại tàu:");
                $sheet->setCellValue('A11', "Name and type of ship");
                $sheet->getStyle('A10')->getAlignment()->setWrapText(true);
                $sheet->setCellValue('C10', $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt);
                $sheet->getStyle('C10')->getFont()->setBold(true);
                $this->centerCell($sheet, "C10:D11");

                //12-13
                $sheet->mergeCells('A12:B12');
                $sheet->mergeCells('A13:B13');
                $sheet->mergeCells('C12:D13');
                $this->applyOuterBorder($sheet, 'A12:D13');
                $sheet->setCellValue('A12', "1.2. Số IMO:");
                $sheet->setCellValue('A13', "IMO number");
                //14-15
                $sheet->mergeCells('A14:B14');
                $sheet->mergeCells('A15:B15');
                $sheet->mergeCells('C14:D15');
                $this->applyOuterBorder($sheet, 'A14:D15');
                $sheet->setCellValue('A14', "1.3. Hô hiệu:");
                $sheet->setCellValue('A15', "Call sign");
                //16-17
                $sheet->mergeCells('A16:B16');
                $sheet->mergeCells('A17:B17');
                $sheet->mergeCells('C16:D17');
                $this->applyOuterBorder($sheet, 'A16:D17');
                $sheet->setCellValue('A16', "1.4. Số đăng ký hành chính:");
                $sheet->setCellValue('A17', "Official number");

                //18-19
                $sheet->mergeCells('A18:B18');
                $sheet->mergeCells('A19:B19');
                $sheet->mergeCells('C18:D19');
                $this->applyOuterBorder($sheet, 'A18:D19');
                $sheet->setCellValue('A18', "1.5. Số chuyến đi:");
                $sheet->setCellValue('A19', "Voyage number");


                //12-19 
                $sheet->mergeCells('E12:G12');
                $sheet->mergeCells('E13:G13');
                $sheet->mergeCells('E14:G15');
                $sheet->setCellValue('E12', "2. Cảng đến/rời");
                $sheet->setCellValue('E13', "Port of arrival/departure");
                $sheet->setCellValue('E14', "VẠN GIA, VIỆT NAM");
                $this->centerCell($sheet, 'E14');
                $sheet->getStyle('E14')->getFont()->setBold(true);
                $this->applyOuterBorder($sheet, 'E12:G19');
                //12-19
                $sheet->mergeCells('H12:J12');
                $sheet->mergeCells('H13:J13');
                $sheet->mergeCells('H14:J15');
                $sheet->setCellValue('H12', "3. Thời gian đến/rời cảng");
                $sheet->setCellValue('H13', "Date - time of arrival/departure");
                $sheet->setCellValue('H14', Carbon::now()->format('d/m/Y'));
                $this->centerCell($sheet, 'H14');
                $this->applyOuterBorder($sheet, 'H12:J19');
                $sheet->getStyle('H14')->getFont()->setBold(true);
                //20-22
                $sheet->mergeCells('A20:B20');
                $sheet->mergeCells('A21:B21');
                $sheet->mergeCells('A22:B22');
                $this->applyOuterBorder($sheet, 'A20:B22');
                $this->applyOuterBorder($sheet, 'C20:D22');
                $sheet->setCellValue('A20', "4. Quốc tịch tàu:");
                $sheet->setCellValue('A21', "Flag State of ship");
                $sheet->setCellValue('A22', "TRUNG QUỐC");

                $sheet->mergeCells('C20:D20');
                $sheet->mergeCells('C21:D21');
                $sheet->mergeCells('C22:D22');
                $sheet->setCellValue('C20', "5. Tên thuyền trưởng:");
                $sheet->setCellValue('C21', "Name of master");
                $sheet->setCellValue('C22', $xuatCanh->ten_thuyen_truong);
                $sheet->getRowDimension(22)->setRowHeight(20);
                $this->centerCell($sheet, 'A22:J22');

                $sheet->mergeCells('E20:J20');
                $sheet->mergeCells('E21:J21');
                $sheet->mergeCells('E22:J22');
                $this->applyOuterBorder($sheet, 'E20:J22');
                $sheet->setCellValue('E20', "6.Cảng rời cuối cùng/cảng đích:");
                $sheet->setCellValue('E21', "Last port of call/next port of call");
                $sheet->setCellValue('E22', "PHÒNG THÀNH - TRUNG QUỐC");
                $sheet->getStyle('A22:J22')->getFont()->setBold(true);

                //23-26
                $sheet->mergeCells('A23:B23');
                $sheet->mergeCells('A24:B24');
                $sheet->mergeCells('A25:B25');
                $sheet->mergeCells('A26:B26');
                $sheet->setCellValue('A23', "7. Giấy chứng nhận đăng ký (Số,");
                $sheet->setCellValue('A24', "ngày cấp, cảng)");
                $sheet->setCellValue('A25', "Certificate of registy (Port, date,");
                $sheet->setCellValue('A26', "number)");
                $this->applyOuterBorder($sheet, 'A23:B26');

                $sheet->mergeCells('C23:D26');
                $sheet->setCellValue('C23',  $xuatCanh->PTVTXuatCanh->so_giay_chung_nhan);
                $sheet->getStyle('C23')->getFont()->setBold(true);
                $this->applyOuterBorder($sheet, 'C23:D26');

                //27-28
                $sheet->setCellValue('A27', "9. Tổng dung tích:");
                $sheet->setCellValue('A28', "Gross tonnage");
                $this->applyOuterBorder($sheet, 'A27:A28');

                $sheet->mergeCells('B27:C27');
                $sheet->mergeCells('B28:C28');
                $sheet->mergeCells('D27:D28');
                $sheet->setCellValue('B27', "10. Trọng tải toàn phần:");
                $sheet->setCellValue('B28', "Deadweight(DWT)");
                $sheet->setCellValue('D27', $xuatCanh->PTVTXuatCanh->dwt_roi);
                $sheet->getStyle('D27')->getFont()->setBold(true);

                $this->applyOuterBorder($sheet, 'B27:D28');

                //29-30
                $sheet->mergeCells('A29:D29');
                $sheet->mergeCells('A30:D30');
                $sheet->setCellValue('A29', "11. Số đăng kiểm:");
                $sheet->setCellValue('A30', "Registry number");
                $this->applyOuterBorder($sheet, 'A29:D30');
                //23-30
                $sheet->mergeCells('E23:J23');
                $sheet->mergeCells('E24:J24');
                $sheet->mergeCells('E25:J25');
                $sheet->mergeCells('E26:J26');
                $sheet->setCellValue('E24', "8. Tên và địa chỉ liên lạc của người làm thủ tục");
                $sheet->setCellValue('E25', "Name and contact details of the prcedurer");

                $sheet->mergeCells('E27:J28');
                $sheet->mergeCells('E29:J30');
                $sheet->setCellValue('E27', $doanhNghiep->chuHang->ten_day_du ?? '');
                $sheet->setCellValue('E29', "ĐC: " . $doanhNghiep?->chuHang?->dia_chi ?? "");
                $sheet->getStyle('E27:J30')->getFont()->setBold(true);
                $this->centerCell($sheet, 'E27:J30');
                $this->applyOuterBorder($sheet, 'E23:J30');

                $this->centerCell($sheet, 'A23:D28');
                //31-33
                $sheet->mergeCells('A31:J31');
                $sheet->mergeCells('A32:J32');
                $sheet->mergeCells('A33:J33');
                $sheet->setCellValue('A31', "12. Đặc điểm chính của chuyến đi (Các cảng trước, Các cảng sẽ đến, Các cảng sẽ dỡ hàng, Số hàng còn lại)");
                $sheet->setCellValue('A32', "Brief particulars of voyage (Previous ports of call, Subsequent ports of call, Ports where remaining cargo will be discharged, Remaining cargo)");
                $sheet->setCellValue('A33', "PHÒNG THÀNH, TRUNG QUỐC - VẠN GIA, VIỆT NAM - PHÒNG THÀNH, TRUNG QUỐC");
                $sheet->getStyle('A33')->getFont()->setBold(true);
                $this->centerCell($sheet, 'A31:J33');
                $this->applyOuterBorder($sheet, 'A31:J33');
                //34-36
                $sheet->mergeCells('A34:J34');
                $sheet->mergeCells('A35:J35');
                $sheet->mergeCells('C36:I36');
                $sheet->setCellValue('A34', "13. Thông tin về hàng hóa vận chuyển trên tàu");
                $sheet->setCellValue('A35', "Description of the cargo");
                $sheet->setCellValue('A36', $tongSoHangXuat . " Kiện");
                $sheet->setCellValue('B36',  "       Tấn");
                $sheet->setCellValue('C36', $cacCongTy);
                $sheet->getStyle('A36:J36')->getFont()->setBold(true);
                $this->centerCell($sheet, 'A34:J36');
                $sheet->getRowDimension(36)->setRowHeight(42);
                $this->applyOuterBorder($sheet, 'A34:J36');
                //37-42
                for ($row = 37; $row <= 42; $row++) {
                    if ($row == 39) {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->mergeCells("D{$row}:E{$row}");
                        $sheet->mergeCells("H{$row}:J{$row}");
                    } else {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->mergeCells("D{$row}:E{$row}");
                        $sheet->mergeCells("F{$row}:G{$row}");
                        $sheet->mergeCells("H{$row}:J{$row}");
                    }
                }

                $sheet->setCellValue('A37', "Loại hàng hóa");
                $sheet->setCellValue('D37', "Tên hàng hóa");
                $sheet->setCellValue('F37', "Số lượng hàng hóa");
                $sheet->setCellValue('H37', "Đơn vị tính");

                $sheet->setCellValue('A38', "Kind of cargo");
                $sheet->setCellValue('D38', "Cargo name");
                $sheet->setCellValue('F38', "The quantity of cargo");
                $sheet->setCellValue('H38', "Unit");

                $sheet->setCellValue('A39', "Xuất khẩu(Export cargo)");
                $sheet->setCellValue('D39', $hangHoaLonNhat->loai_hang ?? "");
                $sheet->setCellValue('F39', "(" . $tongSoHangXuat . " Kiện)");
                $sheet->setCellValue('G39', "      TẤN");
                $sheet->setCellValue('H39', "KIỆN/TẤN");

                $sheet->setCellValue('A40', "Nhập khẩu(Import cargo)");
                $sheet->setCellValue('D40', "NIL");
                $sheet->setCellValue('F40', "NIL");
                $sheet->setCellValue('H40', "NIL");

                $sheet->setCellValue('A41', "Nội địa(Domestic cargo)");
                $sheet->setCellValue('D41', "NIL");
                $sheet->setCellValue('F41', "NIL");
                $sheet->setCellValue('H41', "NIL");

                $sheet->setCellValue('A42', "Hàng trung chuyển (Transshipment cargo)");
                $sheet->setCellValue('D42', "NIL");
                $sheet->setCellValue('F42', "NIL");
                $sheet->setCellValue('H42', "NIL");


                $this->applyOuterBorder($sheet, "A37:C38");
                $this->applyOuterBorder($sheet, "D37:E38");
                $this->applyOuterBorder($sheet, "F37:G38");
                $this->applyOuterBorder($sheet, "H37:J38");

                for ($row = 39; $row <= 42; $row++) {
                    $this->applyOuterBorder($sheet, "A{$row}:C{$row}");
                    $this->applyOuterBorder($sheet, "D{$row}:E{$row}");
                    $this->applyOuterBorder($sheet, "F{$row}:G{$row}");
                    $this->applyOuterBorder($sheet, "H{$row}:J{$row}");
                }

                $this->centerCell($sheet, 'A37:J42');
                $this->leftCell($sheet, 'A39:A42');
                $sheet->getStyle('D39:J42')->getFont()->setBold(true);

                //43
                $sheet->mergeCells("A43:J43");
                $sheet->setCellValue('A43', "Thông tin về hàng hóa quá cảnh. Description of the cargo in transit");
                $this->applyOuterBorder($sheet, "A43:J43");
                $this->leftCell($sheet, 'A43');

                //44 D->E  F

                $sheet->mergeCells("B44:E44");
                $sheet->mergeCells("G44:H44");
                $sheet->mergeCells("I44:J44");

                $sheet->setCellValue('A44', "Loại hàng\nKind of cargo");
                $sheet->setCellValue('B44', "Tên hàng hóa\nCargo name");
                $sheet->setCellValue('F44', "Số lượng hàng hóa\nThe quantity of cargo");
                $sheet->setCellValue('G44', "Số lượng hàng hóa quá cảnh xếp dỡ tại cảng (The quantity of cargo in transit loading, discharging)");
                $sheet->setCellValue('I44', "Đơn vị tính\nUnit");

                $sheet->getRowDimension(39)->setRowHeight(25);
                for ($row = 40; $row <= 48; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                $sheet->getRowDimension(44)->setRowHeight(45);

                $this->applyBorder($sheet, "A44:J44");
                //45
                $sheet->mergeCells("A45:J45");
                $sheet->setCellValue('A45', "Hàng quá cảnh xếp dỡ tại cảng. The quantity of cargo in transit loading, discharing at port");
                $this->applyOuterBorder($sheet, "A45:J45");
                //46
                $sheet->mergeCells("B46:E46");
                $sheet->mergeCells("G46:H46");
                $sheet->mergeCells("I46:J46");

                $sheet->setCellValue('A46', "NIL");
                $sheet->setCellValue('B46', "NIL");
                $sheet->setCellValue('F46', "NIL");
                $sheet->setCellValue('G46', "NIL");
                $sheet->setCellValue('I46', "NIL");

                $sheet->getStyle('A46:J46')->getFont()->setBold(true);
                //47
                $sheet->mergeCells("A47:J47");
                $sheet->setCellValue('A47', "Hàng quá cảnh không xếp dỡ. The quantity of cargo in transit");
                $this->applyOuterBorder($sheet, "A47:J47");
                //48
                $sheet->mergeCells("B48:E48");
                $sheet->mergeCells("G48:H48");
                $sheet->mergeCells("I48:J48");

                $sheet->setCellValue('A48', "NIL");
                $sheet->setCellValue('B48', "NIL");
                $sheet->setCellValue('F48', "NIL");
                $sheet->setCellValue('G48', "NIL");
                $sheet->setCellValue('I48', "NIL");
                $sheet->getStyle('A48:J48')->getFont()->setBold(true);

                //44-48
                $this->centerCell($sheet, 'A44:J48');
                $this->leftCell($sheet, 'A47');
                $this->leftCell($sheet, 'A45');


                for ($row = 45; $row <= 48; $row++) {
                    $this->applyBorder($sheet, "A{$row}:J{$row}");
                }
                //49-51
                for ($row = 49; $row <= 51; $row++) {
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->mergeCells("D{$row}:E{$row}");
                }

                $A50 = $this->createRichText("(gồm cả thuyền trưởng) ", "02 Prs");
                $D50 = $this->createRichText("Number of passenger ", "NIL");
                $sheet->setCellValue('A49', "14. Số thuyền viên");
                $sheet->setCellValue('A50', $A50);
                $sheet->setCellValue('A51', "Number of crew (inl. master)");
                $sheet->setCellValue('D49', "15. Số hành khách");
                $sheet->setCellValue('D50', $D50);
                $this->centerCell($sheet, 'A49:E51');
                $this->applyOuterBorder($sheet, "A49:E51");

                //52-53
                $sheet->mergeCells("A52:E52");
                $sheet->mergeCells("A53:E53");
                $sheet->setCellValue('A52', "Tài liệu đính kèm (ghi rõ số bản)");
                $sheet->setCellValue('A53', "Attached documents (indicate number of copies)");
                $this->centerCell($sheet, 'A52:E53');
                $this->applyOuterBorder($sheet, "A52:E53");

                //54-55
                for ($row = 54; $row <= 60; $row++) {
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->mergeCells("D{$row}:E{$row}");
                }
                $this->leftCell($sheet, "A54:E63");
                $A54 = $this->createRichText("17. Bản khai hàng hóa:  ", "01");
                $D55 = $this->createRichText("Ship's Stores Declaration ", "NIL");
                $sheet->setCellValue('A54', $A54);
                $sheet->setCellValue('A55', "Cargo Declaration");
                $this->applyOuterBorder($sheet, "A54:C55");

                $sheet->setCellValue('D54', "18. Bản khai dự trữ của tàu");
                $sheet->setCellValue('D55', $D55);
                $this->applyOuterBorder($sheet, "D54:E55");

                //56-58

                $A57 = $this->createRichText("Crew List ", "NIL");
                $D57 = $this->createRichText("Passenger List ", "NIL");

                $sheet->setCellValue('A56', "19. Danh sách thuyền viên");
                $sheet->setCellValue('A57', $A57);
                $this->applyOuterBorder($sheet, "A56:C58");

                $sheet->setCellValue('D56', "20. Danh sách hành khách");
                $sheet->setCellValue('D57', $D57);
                $this->applyOuterBorder($sheet, "D56:E58");

                //59-61
                $A59 = $this->createRichText("22. Bản khai hành lý thuyền viên", "(*)");
                $A60 = $this->createRichText("Crew's Effects Declaration", "(*)NIL");
                $D59 = $this->createRichText("23. Bản khai kiểm dịch y tế", "(*)");
                $D61 = $this->createRichText("Heath", "(*)01");

                $sheet->setCellValue('A59', $A59);
                $sheet->setCellValue('A60', $A60);
                $this->applyOuterBorder($sheet, "A59:C61");

                $sheet->setCellValue('D59', $D59);
                $sheet->setCellValue('D60', "Maritime Declaration of ");
                $sheet->setCellValue('D61', $D61);
                $this->applyOuterBorder($sheet, "D59:E61");
                $sheet->getRowDimension(51)->setRowHeight(18);

                //62-63
                $sheet->mergeCells("A62:E62");
                $sheet->mergeCells("A63:E63");

                $A62 = $this->createRichText("24. Mã số Giấy phép rời cảng", "(*)");
                $sheet->setCellValue('A62', $A62);
                $sheet->setCellValue('A63', "Number of port clearance");
                $this->applyOuterBorder($sheet, "A62:E63");

                for ($row = 52; $row <= 60; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                $sheet->getRowDimension(57)->setRowHeight(12);

                //49-58 Right
                // $sheet->setCellValue('F49', "16.Ghi chú(Remarks):");
                // $sheet->setCellValue('F50', "Mớn nước mũi, lái:");
                // $sheet->setCellValue('F51', "Chiều dài lớn nhất:");
                // $sheet->setCellValue('F52', "Chiều rộng lớn nhất");
                // $sheet->setCellValue('F53', "Chiều cao tĩnh không:");

                // $this->applyOuterBorder($sheet, "F49:J58");
                // $sheet->setCellValue('G50', $this->createRichText('', "Draft F/A"));
                // $sheet->setCellValue('G51', $this->createRichText('', "LOA"));
                // $sheet->setCellValue('G52', $this->createRichText('', "Breadth"));

                // $sheet->setCellValue('H50', $this->createRichText('', "0,8/1,0"));
                // $sheet->setCellValue('H51', $this->createRichText('', "15.88 M"));
                // $sheet->setCellValue('H52', $this->createRichText('', "3.32 M"));
                // $sheet->setCellValue('H53', $this->createRichText('', "Air draft"));


                $sheet->setCellValue('F49', "16.Ghi chú(Remarks):");
                $sheet->setCellValue('F50', "Mớn nước mũi, lái:");
                $sheet->setCellValue('F51', "Chiều dài lớn nhất:");
                $sheet->setCellValue('F52', "Chiều rộng lớn nhất");
                $sheet->setCellValue('F53', "Chiều cao tĩnh không:");

                $this->applyOuterBorder($sheet, "F49:J58");
                $sheet->mergeCells("F49:G52");
                $sheet->mergeCells("H49:J52");
                $richText = new RichText();
                $richText->createText("16.Ghi chú(Remarks):"); // Normal text

                $richText->createText("\nMớn nước mũi, lái:       ");
                $boldText = $richText->createTextRun("Draft F/A");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\nChiều dài lớn nhất:       ");
                $boldText = $richText->createTextRun("LOA");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\nChiều rộng lớn nhất:    ");
                $boldText = $richText->createTextRun("Breadth");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\nChiều cao tĩnh không:   ");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);
                $sheet->setCellValue('F49', $richText);

                ///
                $richText = new RichText();
                $richText->createText("");

                $boldText = $richText->createTextRun($xuatCanh->PTVTXuatCanh->draft_roi);
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\n");
                $boldText = $richText->createTextRun($xuatCanh->PTVTXuatCanh->loa_roi);
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\n");
                $boldText = $richText->createTextRun($xuatCanh->PTVTXuatCanh->breadth_roi);
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $richText->createText("\n");
                $boldText = $richText->createTextRun("Air draft");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);

                $sheet->setCellValue('H49', $richText);

                //53-54
                $sheet->mergeCells("F53:J55");
                $richText = new RichText();
                $richText->createText("Loại nhiên liệu sử dụng trên tàu: Type of fuel"); // Normal text
                $richText->createText("\nLượng nhiên liệu trên tàu: Remain on board(R.O.B)");
                $richText->createText("\nTên và địa chỉ chủ tàu:            ");
                $boldText = $richText->createTextRun("GUAN TING LONG");
                $boldText->getFont()->setBold(true)->setName('Times New Roman');
                $boldText->getFont()->setSize(10);
                $richText->createText("                       :Quảng Tây - Trung Quốc");
                $richText->createText("\nCác thông tin cần thiết khác(nếu có) And others (If any)");
                $sheet->setCellValue('F53', $richText);

                //56-58
                $sheet->mergeCells("G56:J56");
                $sheet->mergeCells("G57:J58");
                $sheet->setCellValue('F57', "CHỦ HÀNG");
                $sheet->setCellValue('G56', $doanhNghiepDuocChon->ten_doanh_nghiep ?? '');
                $sheet->setCellValue('G57', $doanhNghiepDuocChon->dia_chi ?? '');

                $sheet->getStyle('F56:J58')->getFont()->setBold(true);
                $this->centerCell($sheet, "F56:J60");


                //59
                $sheet->mergeCells("F59:J59");
                $sheet->mergeCells("F60:J60");
                $sheet->setCellValue('F59', "21. Yêu cầu về phương tiện tiếp nhận và xử lý chất thải ");
                $sheet->setCellValue('F60', "The ship's requirements in term of waste and residue reception facilities");

                //64
                $sheet->mergeCells("F64:J64");
                $sheet->mergeCells("F65:J65");
                $sheet->mergeCells("F66:J66");
                $sheet->setCellValue('F64', 'Vạn Gia, ngày ' . $currentDate->day . ' tháng ' . $currentDate->month . ' năm ' . $currentDate->year);
                $sheet->getStyle('F64')->getFont()->setItalic(true);
                $sheet->setCellValue('F65', "Thuyền trưởng(đại lý hoặc sỹ quan được ủy quyền)");
                $sheet->getStyle('F65:J65')->getFont()->setBold(true);
                $sheet->setCellValue('F66', "Master (or authorized agent or officer)");

                $sheet->mergeCells("F72:J72");
                $sheet->setCellValue('F72', $xuatCanh->ten_thuyen_truong);
                $sheet->getStyle('F72:J72')->getFont()->setBold(true);

                for ($row = 64; $row <= 66; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                $this->centerCell($sheet, "F64:F72");
                $this->applyOuterBorder($sheet, "F59:J63");
                $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
            },
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $xuatCanh = XuatCanh::find($this->ma_xuat_canh);
        if (in_array($xuatCanh->trang_thai, ["2"])) {
            $qrCodeText = 'Số tờ khai nhập cảnh: ' . $xuatCanh->ma_nhap_canh .
                ', cán bộ công chức phê duyệt: ' . ($nhapCanh->congChuc->ten_cong_chuc ?? '');
            $qrCode = QrCode::create($qrCodeText)->setSize(150);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $imageData = $result->getString();

            $qrTempPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
            file_put_contents($qrTempPath, $imageData);

            // Create QR Code Drawing
            $qrDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $qrDrawing->setName('QR Code');
            $qrDrawing->setDescription('QR Code');
            $qrDrawing->setPath($qrTempPath);
            $qrDrawing->setHeight(130);
            $qrDrawing->setCoordinates('A65');

            $drawings[] = $qrDrawing;
        }
        return $drawings;
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
    function applyOuterBorder($sheet, string $range)
    {
        // Apply outer border only
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
                'left'   => ['borderStyle' => Border::BORDER_THIN],
                'right'  => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }
}
