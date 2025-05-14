<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\CongChuc;
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
use App\Models\XuatHang;
use App\Models\NhapCanh;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTieuHuy;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;

class BangKeCongViec implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $ma_cong_chuc;

    public function __construct($tu_ngay, $ma_cong_chuc)
    {
        $this->tu_ngay = $tu_ngay;
        $this->ma_cong_chuc = $ma_cong_chuc;
    }
    public function array(): array
    {
        // $tu_ngay = Carbon::createFromFormat('d-m-Y', $this->tu_ngay)->format('d-m-Y');
        // $den_ngay = Carbon::createFromFormat('d-m-Y', $this->den_ngay)->format('d-m-Y');
        $tenCongChuc = CongChuc::find($this->ma_cong_chuc)->ten_cong_chuc;
        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII'],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA'],
            ['Phụ lục I'],
            ['NHẬT KÝ CÔNG VIỆC VÀ ĐÁNH GIÁ CỦA CẤP CÓ THẨM QUYỀN'],
            ["Tháng " . Carbon::parse($this->tu_ngay)->month . " năm " . Carbon::parse($this->tu_ngay)->year],
            [''],
            ['1. Họ tên: '. $tenCongChuc],
            ['2. Vị trí, đơn vị công tác: Kiểm tra giám sát hàng hoá XNK'],
            ['3. Số ngày làm việc trong tháng: …../….. ngày. Số ngày nghỉ: Không/Nếu có thì ghi rõ số ngày nghỉ, có phép hay không có phép.'],
            ['4. Vi phạm kỷ luật, kỷ cương hành chính: Không/Nếu có thì ghi rõ hành vi và số lần vi phạm'],
            ['5. Kết quả thực hiện công việc:'],
            ['STT', 'Công việc được giao', '', '', 'Kết quả thực hiện', '', 'Lãnh đạo trực tiếp giao việc đánh giá kết quả thực hiện', '', '', '', '', '', 'Ý kiến phê duyệt của cấp có thẩm quyền'],
            ['', 'Nội dung công việc', 'Ngày giao việc', 'Ngày phải hoàn thành', 'Sản phẩm đầu ra/ Hoạt động thực hiện trong ngày', 'Ngày thực hiện', 'Ý kiến đánh giá', '', '', '', '', 'Ghi rõ họ tên, chức vụ'],
            ['', '', '', '', '', '', 'Về tiến độ', '', '', 'Về chất lượng'],
            ['', '', '', '', '', '', 'Vượt tiến độ', 'Đạt tiến độ', 'Chậm tiến độ', 'Đạt', 'Không đạt'],
            ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', '(11)', '(12)', '(13)']
        ];
        
        $nhapHangs = NhapHang::select('nhap_hang.created_at', 'nhap_hang.so_to_khai_nhap')
            ->whereMonth('nhap_hang.created_at', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->get();

        $xuatHangs = XuatHang::where('xuat_hang.trang_thai', '!=', 0)
            ->select('xuat_hang.ngay_dang_ky', 'xuat_hang.so_to_khai_xuat')
            ->whereMonth('xuat_hang.ngay_dang_ky', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('xuat_hang.so_to_khai_xuat')
            ->get();

        $xuatCanhs = XuatCanh::where('xuat_canh.trang_thai', '!=', 0)
            ->select('xuat_canh.ngay_dang_ky', 'xuat_canh.ma_xuat_canh')
            ->whereMonth('xuat_canh.ngay_dang_ky', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('xuat_canh.ma_xuat_canh')
            ->get();

        $nhapCanhs = NhapCanh::where('trang_thai', '!=', 0)
            ->select('ngay_dang_ky', 'ma_nhap_canh')
            ->whereMonth('ngay_dang_ky', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('ma_nhap_canh')
            ->get();

        $chuyenTauConts = YeuCauTauCont::where('yeu_cau_tau_cont.trang_thai', '!=', 0)
            ->select('yeu_cau_tau_cont.ngay_yeu_cau', 'yeu_cau_tau_cont.ma_yeu_cau')
            ->whereMonth('yeu_cau_tau_cont.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_tau_cont.ma_yeu_cau', 'yeu_cau_tau_cont.ngay_yeu_cau')
            ->get();

        $chuyenTaus = YeuCauChuyenTau::where('yeu_cau_chuyen_tau.trang_thai', '!=', 0)
            ->select('yeu_cau_chuyen_tau.ngay_yeu_cau', 'yeu_cau_chuyen_tau.ma_yeu_cau')
            ->whereMonth('yeu_cau_chuyen_tau.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_chuyen_tau.ma_yeu_cau', 'yeu_cau_chuyen_tau.ngay_yeu_cau')
            ->get();

        $kiemTras = YeuCauKiemTra::where('yeu_cau_kiem_tra.trang_thai', '!=', 0)
            ->select('yeu_cau_kiem_tra.ngay_yeu_cau', 'yeu_cau_kiem_tra.ma_yeu_cau')
            ->whereMonth('yeu_cau_kiem_tra.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_kiem_tra.ma_yeu_cau', 'yeu_cau_kiem_tra.ngay_yeu_cau')
            ->get();

        $chuyenConts = YeuCauChuyenContainer::where('yeu_cau_chuyen_container.trang_thai', '!=', 0)
            ->select('yeu_cau_chuyen_container.ngay_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->whereMonth('yeu_cau_chuyen_container.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_chuyen_container.ma_yeu_cau', 'yeu_cau_chuyen_container.ngay_yeu_cau')
            ->get();

        $tieuHuys = YeuCauTieuHuy::where('yeu_cau_tieu_huy.trang_thai', '!=', 0)
            ->select('yeu_cau_tieu_huy.ngay_yeu_cau', 'yeu_cau_tieu_huy.ma_yeu_cau')
            ->whereMonth('yeu_cau_tieu_huy.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_tieu_huy.ma_yeu_cau', 'yeu_cau_tieu_huy.ngay_yeu_cau')
            ->get();

        $hangVeKhos = YeuCauHangVeKho::where('yeu_cau_hang_ve_kho.trang_thai', '!=', 0)
            ->select('yeu_cau_hang_ve_kho.ngay_yeu_cau', 'yeu_cau_hang_ve_kho.ma_yeu_cau')
            ->whereMonth('yeu_cau_hang_ve_kho.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_hang_ve_kho.ma_yeu_cau', 'yeu_cau_hang_ve_kho.ngay_yeu_cau')
            ->get();

        $niemPhongs = YeuCauNiemPhong::where('yeu_cau_niem_phong.trang_thai', '!=', 0)
            ->select('yeu_cau_niem_phong.ngay_yeu_cau', 'yeu_cau_niem_phong.ma_yeu_cau')
            ->whereMonth('yeu_cau_niem_phong.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_niem_phong.ma_yeu_cau', 'yeu_cau_niem_phong.ngay_yeu_cau')
            ->get();

        $soLuongNiemPhong = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_niem_phong.ma_yeu_cau')
            ->where('yeu_cau_niem_phong.trang_thai', '!=', 0)
            ->whereMonth('yeu_cau_niem_phong.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('yeu_cau_niem_phong.ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_niem_phong_chi_tiet.ma_chi_tiet')
            ->count();

        $soLuongTauConts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_tau_cont.ma_yeu_cau')
            ->where('yeu_cau_tau_cont.trang_thai', '!=', 0)
            ->whereMonth('yeu_cau_tau_cont.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('yeu_cau_tau_cont.ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_tau_cont_chi_tiet.ma_chi_tiet')
            ->count();

        $soLuongChuyenConts = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->where('yeu_cau_chuyen_container.trang_thai', '!=', 0)
            ->whereMonth('yeu_cau_chuyen_container.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('yeu_cau_chuyen_container.ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_container_chi_tiet.ma_chi_tiet')
            ->count();

        $soLuongChuyenTaus = YeuCauChuyenTau::join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau.ma_yeu_cau')
            ->where('yeu_cau_chuyen_tau.trang_thai', '!=', 0)
            ->whereMonth('yeu_cau_chuyen_tau.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('yeu_cau_chuyen_tau.ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_chuyen_tau_chi_tiet.ma_chi_tiet')
            ->count();

        $soLuongKiemTras = YeuCauKiemTra::join('yeu_cau_kiem_tra_chi_tiet', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_kiem_tra.ma_yeu_cau')
            ->where('yeu_cau_kiem_tra.trang_thai', '!=', 0)
            ->whereMonth('yeu_cau_kiem_tra.ngay_yeu_cau', Carbon::parse($this->tu_ngay)->month)
            ->where('yeu_cau_kiem_tra.ma_cong_chuc', $this->ma_cong_chuc)
            ->groupBy('yeu_cau_kiem_tra_chi_tiet.ma_chi_tiet')
            ->count();

        $stt = 1;
        $ngayThucHiens = $nhapHangs->pluck('created_at')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($nhapHangs->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát tờ khai nhập',
                $ngayThucHiens,
                '',
                'Giám sát ' . $nhapHangs->count() . ' tờ khai',
            ];
        }



        $ngayThucHiens = $xuatHangs->pluck('ngay_dang_ky')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($xuatHangs->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát tờ khai xuất',
                $ngayThucHiens,
                '',
                'Giám sát ' . $xuatHangs->count() . ' tờ khai',
            ];
        }


        $ngayThucHiens = $nhapCanhs->pluck('ngay_dang_ky')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($nhapCanhs->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát tờ khai nhập cảnh',
                $ngayThucHiens,
                '',
                'Giám sát ' . $nhapCanhs->count() . ' tờ khai nhập cảnh',
            ];
        }

        $ngayThucHiens = $xuatCanhs->pluck('ngay_dang_ky')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($xuatCanhs->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát tờ khai xuất cảnh',
                $ngayThucHiens,
                '',
                'Giám sát ' . $xuatCanhs->count() . ' tờ khai xuất cảnh',
            ];
        }

        $ngayThucHiens = $niemPhongs->pluck('ngay_dang_ky')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($niemPhongs->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát niêm phong',
                $ngayThucHiens,
                '',
                'Giám sát ' . $niemPhongs->count() . ' yêu cầu niêm phong và niêm phong '.$soLuongNiemPhong.' container',
            ];
        }

        $ngayThucHiens = $chuyenTauConts->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($chuyenTauConts->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu chuyển tàu container',
                $ngayThucHiens,
                '',
                'Giám sát ' . $chuyenTauConts->count() . ' yêu cầu chuyển tàu container và chuyển ' . $soLuongTauConts . ' container',
            ];
        }


        $ngayThucHiens = $chuyenTaus->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($chuyenTaus->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu chuyển tàu',
                $ngayThucHiens,
                '',
                'Giám sát ' . $chuyenTaus->count() . ' yêu cầu chuyển tàu và chuyển ' . $soLuongChuyenTaus . ' lần chuyển tàu',
            ];
        }


        $ngayThucHiens = $chuyenConts->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($chuyenConts->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu chuyển container',
                $ngayThucHiens,
                '',
                'Giám sát ' . $chuyenConts->count() . ' yêu cầu chuyển container và chuyển ' . $soLuongChuyenConts . ' container',
            ];
        }


        $ngayThucHiens = $kiemTras->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($kiemTras->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu kiểm tra',
                $ngayThucHiens,
                '',
                'Giám sát ' . $kiemTras->count() . ' yêu cầu kiểm tra và kiểm tra ' . $soLuongKiemTras . ' container',
            ];
        }



        $ngayThucHiens = $tieuHuys->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($tieuHuys->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu tiêu hủy',
                $ngayThucHiens,
                '',
                'Giám sát ' . $tieuHuys->count() . ' yêu cầu tiêu hủy',
            ];
        }



        $ngayThucHiens = $hangVeKhos->pluck('ngay_yeu_cau')->map(function ($date) {
            return Carbon::parse($date)->format('d-m-Y');
        })->unique()->implode(', ');

        if ($hangVeKhos->count() != 0) {
            $result[] = [
                $stt++,
                'Giám sát yêu cầu hàng về kho',
                $ngayThucHiens,
                '',
                'Giám sát ' . $hangVeKhos->count() . ' yêu cầu hàng về kho',
            ];
        }


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
                    ->setPrintArea('A1:M' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);


                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                foreach (['B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 12);
                $sheet->getColumnDimension('D')->setWidth(width: 10);
                $sheet->getColumnDimension('E')->setWidth(width: 25);
                $sheet->getColumnDimension('F')->setWidth(width: 12);
                $sheet->getColumnDimension('L')->setWidth(width: 15);
                $sheet->getColumnDimension('M')->setWidth(width: 20);

                // $sheet->getStyle('B')->getNumberFormat()->setFormatCode('0'); // Apply format
                // $sheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $event->sheet->getDelegate()->getRowDimension(12)->setRowHeight(30);
                $event->sheet->getDelegate()->getRowDimension(15)->setRowHeight(30);

                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');

                $sheet->mergeCells('A3:M3');
                $sheet->mergeCells('A4:M4');
                $sheet->mergeCells('A5:M5');
                $sheet->mergeCells('A6:M6');
                $sheet->mergeCells('A7:M7');
                $sheet->mergeCells('A8:M8');
                $sheet->mergeCells('A9:M9');
                $sheet->mergeCells('A10:M10');
                $sheet->mergeCells('A11:M11');

                $sheet->mergeCells('B12:D12');
                $sheet->mergeCells('E12:F12');
                $sheet->mergeCells('G12:L12');

                $sheet->mergeCells('G13:K13');
                $sheet->mergeCells('G14:I14');
                $sheet->mergeCells('J14:K14');

                $sheet->mergeCells('A12:A15');
                $sheet->mergeCells('B13:B15');
                $sheet->mergeCells('C13:C15');
                $sheet->mergeCells('D13:D15');
                $sheet->mergeCells('E13:E15');
                $sheet->mergeCells('F13:F15');
                $sheet->mergeCells('L13:L15');
                $sheet->mergeCells('M12:M15');

                // Your existing styles
                $sheet->getStyle('A1:M6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:M6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A12:M16')->applyFromArray([
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

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A17:E' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A5:M5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true],
                ]);
                $sheet->getStyle('A16:M16')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A12:M' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                // $event->sheet->getDelegate()->getStyle('N1')->getFont()->setBold(true);
            },
        ];
    }
}
