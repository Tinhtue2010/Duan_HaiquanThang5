<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\XuatHang;
use App\Models\XuatCanh;
use App\Models\NhapCanh;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTieuHuy;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PhanCongNhiemVuGiamSat implements FromArray, WithEvents
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }
    public function array(): array
    {
        $nhapHangs = NhapHang::join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'nhap_hang.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(nhap_hang.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('nhap_hang.created_at', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $xuatHangs = XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'xuat_hang.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT xuat_hang_cont.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('xuat_hang.ngay_dang_ky', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $xuatCanhs = XuatCanh::join('doanh_nghiep', 'xuat_canh.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'xuat_canh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->join('xuat_canh_chi_tiet', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_canh_chi_tiet.so_to_khai_xuat')
            ->join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_canh.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT xuat_hang_cont.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('xuat_hang.ngay_dang_ky', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();
        

        $chuyenTauConts = YeuCauTauCont::join('yeu_cau_tau_cont_chi_tiet', 'yeu_cau_tau_cont.ma_yeu_cau', '=', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_tau_cont.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_tau_cont.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_tau_cont.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_tau_cont.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $chuyenTaus = YeuCauChuyenTau::join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_chuyen_tau.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_chuyen_tau.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_chuyen_tau.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_chuyen_tau.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $kiemTras = YeuCauKiemTra::join('yeu_cau_kiem_tra_chi_tiet', 'yeu_cau_kiem_tra.ma_yeu_cau', '=', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_kiem_tra.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_kiem_tra.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_kiem_tra.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_kiem_tra.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $chuyenConts = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', '=', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_chuyen_container.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_chuyen_container.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_chuyen_container.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_container_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_chuyen_container.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $tieuHuys = YeuCauTieuHuy::join('yeu_cau_tieu_huy_chi_tiet', 'yeu_cau_tieu_huy.ma_yeu_cau', '=', 'yeu_cau_tieu_huy_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_tieu_huy.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_tieu_huy.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_tieu_huy.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_tieu_huy.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();

        $hangVeKhos = YeuCauHangVeKho::join('yeu_cau_hang_ve_kho_chi_tiet', 'yeu_cau_hang_ve_kho.ma_yeu_cau', '=', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau')
            ->join('doanh_nghiep', 'yeu_cau_hang_ve_kho.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('cong_chuc', 'yeu_cau_hang_ve_kho.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->where('yeu_cau_hang_ve_kho.trang_thai', '!=', 0)
            ->selectRaw('
                cong_chuc.ma_cong_chuc,
                cong_chuc.ten_cong_chuc,
                doanh_nghiep.ma_doanh_nghiep,
                doanh_nghiep.ten_doanh_nghiep,
                GROUP_CONCAT(DISTINCT yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap SEPARATOR ", ") as danh_sach_so_to_khai
            ')
            ->whereDate('yeu_cau_hang_ve_kho.ngay_yeu_cau', $this->date)
            ->groupBy('cong_chuc.ma_cong_chuc', 'doanh_nghiep.ma_doanh_nghiep', 'cong_chuc.ten_cong_chuc', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();




        $date = Carbon::createFromFormat('Y-m-d', $this->date)->format('d-m-Y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', ''],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['BẢNG PHÂN CÔNG NHIỆM VỤ KIỂM TRA GIÁM SÁT', '', '', '', '', '', ''],
            ["Ngày: {$date}", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['1. TIẾP NHẬN HỒ SƠ:'],
            ['2. TIẾP NHẬN HÀNG HÓA, SEAL ĐIỆN TỬ:'],
            ['3. KIỂM TRA GIÁM SÁT HÀNG HÓA:'],
            ['STT', 'CÔNG CHỨC', 'TÊN DOANH NGHIỆP', 'SỐ TỜ KHAI', 'GHI CHÚ'],
        ];
        $stt = 1;
        foreach ($nhapHangs as $nhapHang) {
            $result[] = [
                $stt++,
                $nhapHang->ten_cong_chuc,
                $nhapHang->ten_doanh_nghiep,
                $nhapHang->danh_sach_so_to_khai,
                'Tiếp nhận tờ khai nhập'
            ];
        }
        foreach ($xuatHangs as $xuatHang) {
            $result[] = [
                $stt++,
                $xuatHang->ten_cong_chuc,
                $xuatHang->ten_doanh_nghiep,
                $xuatHang->danh_sach_so_to_khai,
                'Giám sát xuất hàng'
            ];
        }
        foreach ($xuatCanhs as $xuatCanh) {
            $result[] = [
                $stt++,
                $xuatCanh->ten_cong_chuc,
                $xuatCanh->ten_doanh_nghiep,
                $xuatCanh->danh_sach_so_to_khai,
                'Giám sát xuất cảnh'
            ];
        }
        foreach ($chuyenTauConts as $chuyenTauCont) {
            $result[] = [
                $stt++,
                $chuyenTauCont->ten_cong_chuc,
                $chuyenTauCont->ten_doanh_nghiep,
                $chuyenTauCont->danh_sach_so_to_khai,
                'Giám sát chuyển tàu và container'
            ];
        }
        foreach ($chuyenTaus as $chuyenTau) {
            $result[] = [
                $stt++,
                $chuyenTau->ten_cong_chuc,
                $chuyenTau->ten_doanh_nghiep,
                $chuyenTau->danh_sach_so_to_khai,
                'Giám sát chuyển tàu'
            ];
        }
        foreach ($chuyenConts as $chuyenCont) {
            $result[] = [
                $stt++,
                $chuyenCont->ten_cong_chuc,
                $chuyenCont->ten_doanh_nghiep,
                $chuyenCont->danh_sach_so_to_khai,
                'Giám sát chuyển container'
            ];
        }

        foreach ($kiemTras as $kiemTra) {
            $result[] = [
                $stt++,
                $kiemTra->ten_cong_chuc,
                $kiemTra->ten_doanh_nghiep,
                $kiemTra->danh_sach_so_to_khai,
                'Giám sát kiểm tra'
            ];
        }
        foreach ($tieuHuys as $tieuHuy) {
            $result[] = [
                $stt++,
                $tieuHuy->ten_cong_chuc,
                $tieuHuy->ten_doanh_nghiep,
                $tieuHuy->danh_sach_so_to_khai,
                'Giám sát tiêu hủy'
            ];
        }
        foreach ($hangVeKhos as $hangVeKho) {
            $result[] = [
                $stt++,
                $hangVeKho->ten_cong_chuc,
                $hangVeKho->ten_doanh_nghiep,
                $hangVeKho->danh_sach_so_to_khai,
                'Giám sát hàng về kho'
            ];
        }




        $result[] = [
            ['4. SỬ DỤNG MÁY THỬ MA TÚY:'],
            ['5. CAMERA GIÁM SÁT, TRỰC BAN TRỰC TUYẾN:'],
            ['6. TRỰC QUẢN LÝ CA NÔ, XUỒNG:'],
            ['7. TRỰC ĐÊM CẢNG NỔI:'],
        ];


        $result[] = [
            '',
            '',
            '',
            '',
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
                    ->setPrintArea('A1:E' . $sheet->getHighestRow());

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

                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 25);
                $sheet->getColumnDimension('C')->setWidth(width: 25);
                $sheet->getColumnDimension('D')->setWidth(width: 35);
                $sheet->getColumnDimension('E')->setWidth(width: 25);
                $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0'); // Apply format

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');

                $sheet->mergeCells('A4:E4');
                $sheet->mergeCells('A5:E5');
                $sheet->mergeCells('A6:E6');
                $sheet->mergeCells('A7:E7');
                $sheet->mergeCells('A8:E8');
                $sheet->mergeCells('A9:E9');


                // Your existing styles
                $sheet->getStyle('A1:E6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:E6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);



                $secondPart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === '4. SỬ DỤNG MÁY THỬ MA TÚY:') {
                        $secondPart = $i;
                        break;
                    }
                }
                $thirdPart = null;
                for ($i = 1; $i <= $lastRow; $i++) {
                    if ($sheet->getCell('A' . $i)->getValue() === '7. TRỰC ĐÊM CẢNG NỔI:') {
                        $thirdPart = $i;
                        break;
                    }
                }

                if ($secondPart !== null && $thirdPart !== null) {
                    for ($i = $secondPart; $i < $thirdPart + 1; $i++) {
                        $sheet->mergeCells("A{$i}:E{$i}");
                    }
                }
                $sheet->mergeCells("D" . ($thirdPart + 1) . ":E" . ($thirdPart + 1));
                $sheet->mergeCells("D" . ($thirdPart + 2) . ":E" . ($thirdPart + 2));
                $sheet->mergeCells("D" . ($thirdPart + 5) . ":E" . ($thirdPart + 5));


                $sheet->setCellValue('D' . $thirdPart + 1, "KT. ĐỘI TRƯỞNG");
                $sheet->setCellValue('D' . $thirdPart + 2, "PHÓ ĐỘI TRƯỞNG");
                $sheet->setCellValue('D' . $thirdPart + 5, "Lê Thanh Bình");


                // $sheet->getStyle('A8:D8')->applyFromArray([
                //     'font' => ['bold' => true],
                //     'alignment' => [
                //         'horizontal' => Alignment::HORIZONTAL_CENTER,
                //         'vertical' => Alignment::VERTICAL_CENTER,
                //     ],
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => Border::BORDER_THIN,
                //         ],
                //     ],
                // ]);
                $sheet->getStyle('A10:E' . $secondPart - 1)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $sheet->getStyle('A5:D5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);
                $sheet->getStyle('A7:A9')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('A' . $secondPart . ':A' . $thirdPart)->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                $sheet->getStyle('A10' . ':E' . $secondPart - 1)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $sheet->getStyle('A' . ($thirdPart + 1) . ':E' . $thirdPart + 5)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }
}
