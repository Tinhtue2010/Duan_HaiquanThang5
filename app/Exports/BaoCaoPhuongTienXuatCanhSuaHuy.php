<?php

namespace App\Exports;

use App\Models\NhapHang;
use App\Models\HangHoa;
use App\Models\NhapCanh;
use App\Models\PTVTXuatCanh;
use App\Models\XuatNhapCanh;
use App\Models\XuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\XuatHangSua;
use App\Models\XuatCanhSua;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BaoCaoPhuongTienXuatCanhSuaHuy implements FromArray, WithEvents
{
    protected $tu_ngay;
    protected $den_ngay;

    public function __construct($tu_ngay, $den_ngay)
    {
        $this->tu_ngay = $tu_ngay;
        $this->den_ngay = $den_ngay;
    }
    public function array(): array
    {
        $tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay)->format('d/m/y');
        $den_ngay = Carbon::createFromFormat('Y-m-d', $this->den_ngay)->format('d/m/y');

        $result = [
            ['CHI CỤC HẢI QUAN KHU VỰC VIII', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM'],
            ['HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Độc lập - Tự do - Hạnh phúc'],
            ['', '', '', '', '', ''],
            ['BÁO CÁO PHƯƠNG TIỆN XUẤT CẢNH HỦY, SỬA', '', '', '', '', ''],
            ["Từ $tu_ngay đến $den_ngay ", '', '', '', '', ''],
            ['', '', '', '', '', ''],
            ['STT', 'Tên tàu', 'Quốc tịch tàu', 'Họ tên thuyền trưởng', 'Nhập cảnh', '', '', 'Xuất cảnh', '', '' . '', '', '', '', '', '', '', '', 'Công chức làm thủ tục', 'Tên công ty xuất hàng', 'Tên đại lý làm thủ tục', 'Khác', 'Ghi chú'],
            ['', '', '', '', 'Cảng đi', 'Ngày nhập cảnh', 'Giờ nhập cảnh', 'Ngày xuất cảnh', 'Giờ xuất cảnh', 'Loại hàng', 'Số lượng hàng hóa', '', '', '', '', '', 'Cảng đến'],
            ['', '', '', '', '', '', '', '', '', '', 'Thuốc lá', 'Rượu (kiện)', 'Đông lạnh', '', 'Hàng khác', ''],
            ['', '', '', '', '', '', '', '', '', '', '', '', 'Kiện', 'Tấn', 'Kiện', 'Tấn'],
        ];

        $data = XuatCanh::leftJoin('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.ma_xuat_canh', '=', 'xuat_canh.ma_xuat_canh')
            ->leftJoin('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_canh_chi_tiet.so_to_khai_xuat')
            ->leftJoin('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->leftJoin('ptvt_xuat_canh', 'xuat_canh.so_ptvt_xuat_canh', '=', 'ptvt_xuat_canh.so_ptvt_xuat_canh')
            ->leftJoin('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->leftJoin('hang_hoa', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->leftJoin('cong_chuc', 'xuat_canh.ma_cong_chuc', 'cong_chuc.ma_cong_chuc')
            ->leftJoin('nhap_canh', 'xuat_canh.so_ptvt_xuat_canh', 'nhap_canh.so_ptvt_xuat_canh')
            ->leftJoin('doanh_nghiep', 'xuat_canh.ma_doanh_nghiep_chon', 'doanh_nghiep.ma_doanh_nghiep')
            ->leftJoin('chu_hang', 'doanh_nghiep.ma_chu_hang', 'chu_hang.ma_chu_hang')
            ->whereBetween('xuat_canh.ngay_dang_ky', [$this->tu_ngay, $this->den_ngay])
            ->where('xuat_canh.trang_thai', '!=', 1)
            ->where(function ($query) {
                $query->whereNull('xuat_hang.trang_thai')
                    ->orWhere('xuat_hang.trang_thai', '!=', 0);
            })
            ->select(
                'xuat_canh.ma_xuat_canh',
                'xuat_canh.ten_thuyen_truong',
                'xuat_canh.ngay_dang_ky',
                'cong_chuc.ten_cong_chuc',
                'doanh_nghiep.ten_doanh_nghiep',
                'chu_hang.ten_chu_hang',
                'hang_hoa.loai_hang',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.ma_xuat_hang_cont',
                'xuat_canh.so_ptvt_xuat_canh',
            )
            ->get()
            ->groupBy('ma_xuat_canh')
            ->map(function ($items, $ma_xuat_canh) {
                $first = $items->first();

                $uniqueItems = collect();
                $seen = [];

                foreach ($items as $item) {
                    $key = $item->ma_xuat_hang_cont;

                    if (is_null($key) || !isset($seen[$key])) {
                        $uniqueItems->push($item);
                        if (!is_null($key)) {
                            $seen[$key] = true;
                        }
                    }
                }

                // Calculate loai_hang_sums for all items in this filtered collection
                $loaiHangSummary = $uniqueItems->groupBy('loai_hang')->map(function ($group) {
                    return $group->sum('so_luong_xuat');
                });

                return [
                    'ma_xuat_canh' => $ma_xuat_canh,
                    'ten_thuyen_truong' => $first->ten_thuyen_truong,
                    'ngay_dang_ky' => $first->ngay_dang_ky,
                    'ten_cong_chuc' => $first->ten_cong_chuc,
                    'ten_doanh_nghiep' => $first->ten_doanh_nghiep,
                    'ten_chu_hang' => $first->ten_chu_hang,
                    'loai_hang' => $first->loai_hang,
                    'loai_hang_sums' => $loaiHangSummary,
                    'so_ptvt_xuat_canh' => $first->so_ptvt_xuat_canh,
                ];
            })
            ->values();

        $sumThuocLa = 0;
        $sumRuou = 0;
        $sumDongLanh = 0;
        $sumKhac = 0;
        $sumTrongLuongDongLanh = 0;
        $sumTrongLuongKhac = 0;

        foreach ($data as $key => $value) {
            $ptvt = PTVTXuatCanh::find($value['so_ptvt_xuat_canh']);
            $ten_phuong_tien_vt = $ptvt->ten_phuong_tien_vt ?? '';
            $quoc_tich_tau = $ptvt->quoc_tich_tau ?? '';
            $cang_den = $ptvt->cang_den ?? '';

            $uniqueMaDoanhNghieps = XuatCanhChiTiet::with('xuatHang')
                ->whereHas('xuatHang')
                ->where('ma_xuat_canh', $value['ma_xuat_canh'])
                ->get()
                ->pluck('xuatHang.ma_doanh_nghiep')
                ->unique()
                ->values();

            $totalTrongLuong = 0;
            $totalSoLuongXuat = 0;
            $ratio = 0;

            foreach ($uniqueMaDoanhNghieps as $maDoanhNghiep) {
                $xuatHangConts = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->join('xuat_hang_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
                    ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
                    ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', '=', 'xuat_canh_chi_tiet.ma_xuat_canh')
                    ->where('xuat_hang.trang_thai', '!=', '0')
                    ->where('nhap_hang.ma_doanh_nghiep', $maDoanhNghiep)
                    ->where('xuat_canh.ma_xuat_canh', $value['ma_xuat_canh'])
                    ->select('nhap_hang.trong_luong', 'xuat_hang_cont.so_luong_xuat', 'nhap_hang.so_to_khai_nhap', 'xuat_hang.so_to_khai_xuat')
                    ->get();
                foreach ($xuatHangConts as $xuatHangCont) {
                    $total_so_luong_khai_bao = HangHoa::where('so_to_khai_nhap', $xuatHangCont->so_to_khai_nhap)->sum('so_luong_khai_bao');
                    $ratio = $xuatHangCont->trong_luong / $total_so_luong_khai_bao;
                    $totalTrongLuong += $xuatHangCont->so_luong_xuat * $ratio;
                    $totalSoLuongXuat += $xuatHangCont->so_luong_xuat;
                }
            }
            $totalTrongLuong = number_format($totalTrongLuong, 2, '.', '');

            $thuocLa = $value['loai_hang_sums']['Thuốc lá'] ?? 0;
            $thuocLa2 = $value['loai_hang_sums']['THUỐC LÁ ESSE'] ?? 0;
            $totalThuocLa = $thuocLa + $thuocLa2;
            $totalKhac = $value['loai_hang_sums']['Khác'] ?? 0;

            $ngayDangKys = NhapCanh::where('so_ptvt_xuat_canh', $value['so_ptvt_xuat_canh'])
                ->pluck('ngay_dang_ky')
                ->toArray();

            $targetDate = $value['ngay_dang_ky'];

            if (!empty($ngayDangKys)) {
                $nearestDate = collect($ngayDangKys)
                    ->filter(function ($date) use ($targetDate) {
                        return $date <= $targetDate; // string comparison works for 'Y-m-d'
                    })
                    ->sortDesc()
                    ->first();

                $value['ngay_dang_ky_nearest'] = $nearestDate ?? null;
            } else {
                $value['ngay_dang_ky_nearest'] = null;
            }

            $xnc = XuatNhapCanh::where('so_ptvt_xuat_canh', $value['so_ptvt_xuat_canh'])
                ->where('ngay_them', $value['ngay_dang_ky_nearest'])
                ->first();
            $xuatCanh = XuatCanh::find($value['ma_xuat_canh']);
            $isChiTietExist = XuatCanhChiTiet::where('ma_xuat_canh', $value['ma_xuat_canh'])->exists();
            $isEdited = XuatHangSua::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang_sua.so_to_khai_xuat')
                ->where('trang_thai_phieu_xuat', 12)
                ->where('ma_xuat_canh', $value['ma_xuat_canh'])
                ->exists();
            $xuatCanhSua = XuatCanhSua::where('ma_xuat_canh', $value['ma_xuat_canh'])->exists();
            $content = null;
            if ($xuatCanh->trang_thai == 0) {
                $content = "Đã hủy";
            } else if (($xuatCanh->ma_doanh_nghiep_chon != 0 && $isChiTietExist == false) || $isEdited) {
                $content = 'Đã sửa phiếu xuất sau khi duyệt';
            } else if ($xuatCanhSua) {
                $content = 'Đã yêu cầu sửa nội dung TK';
            }
            if ($content != null) {
                $result[] = [
                    $key + 1,
                    $ten_phuong_tien_vt,
                    $quoc_tich_tau,
                    $value['ten_thuyen_truong'],
                    'Vạn Gia',
                    !empty($nearestDate) ? Carbon::createFromFormat('Y-m-d', $nearestDate)->format('d/m/y') : null,
                    $xnc->thoi_gian_nhap_canh ?? '',
                    Carbon::createFromFormat('Y-m-d', $value['ngay_dang_ky'])->format('d/m/y'),
                    $xnc->thoi_gian_xuat_canh ?? '',
                    $value['loai_hang'],
                    $totalThuocLa == 0 ? '0' : $totalThuocLa,
                    $value['loai_hang_sums']['Rượu'] ?? '0',
                    $value['loai_hang_sums']['Đông lạnh'] ?? '0',
                    ($value['loai_hang'] == 'Đông lạnh') ? $totalTrongLuong  : '0',
                    $totalKhac == 0 ? '0' : $totalKhac,
                    ($value['loai_hang'] == 'Khác') ? $totalTrongLuong : '0',
                    $cang_den,
                    $value['ten_cong_chuc'],
                    $value['loai_hang'] ? $value['ten_doanh_nghiep'] : '',
                    $value['loai_hang'] ? $value['ten_chu_hang'] : '',
                    '',
                    $content,
                    $value['ma_xuat_canh'],
                ];
            }


            $sumThuocLa += $totalThuocLa;
            $sumRuou += $value['loai_hang_sums']['Rượu'] ?? 0;
            $sumDongLanh += $value['loai_hang_sums']['Đông lạnh'] ?? 0;
            $sumKhac += $totalKhac;
            $sumTrongLuongDongLanh += ($value['loai_hang'] == 'Đông lạnh') ? $totalTrongLuong : 0;
            $sumTrongLuongKhac += ($value['loai_hang'] == 'Khác') ? $totalTrongLuong : 0;
        }
        $result[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $sumThuocLa == 0 ? '0' : $sumThuocLa,
            $sumRuou == 0 ? '0' : $sumRuou,
            $sumDongLanh == 0 ? '0' : $sumDongLanh,
            $sumTrongLuongDongLanh == 0 ? '0' : $sumTrongLuongDongLanh,
            $sumKhac == 0 ? '0' : $sumKhac,
            $sumTrongLuongKhac == 0 ? '0' : $sumTrongLuongKhac,
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
                    ->setPrintArea('A1:W' . $sheet->getHighestRow());

                // Set margins (in inches)
                $sheet->getPageMargins()
                    ->setTop(0.5)
                    ->setRight(0.5)
                    ->setBottom(0.5)
                    ->setLeft(0.5)
                    ->setHeader(0.3)
                    ->setFooter(0.3);


                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman');

                foreach (['B', 'C', 'D', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth(width: 10);
                }
                $sheet->getColumnDimension('A')->setWidth(width: 7);
                $sheet->getColumnDimension('B')->setWidth(width: 15);
                $sheet->getColumnDimension('C')->setWidth(width: 10);
                $sheet->getColumnDimension('D')->setWidth(width: 10);
                $sheet->getColumnDimension('E')->setWidth(width: 12);
                $sheet->getColumnDimension('Q')->setWidth(width: 10);
                $sheet->getColumnDimension('S')->setWidth(width: 15);
                $sheet->getColumnDimension('R')->setWidth(width: 10);
                $sheet->getColumnDimension('V')->setWidth(width: 20);

                $sheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0');

                $lastRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A1:' . $highestColumn . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('R1:V1');
                $sheet->mergeCells('R2:V2');

                $sheet->mergeCells('A4:V4');
                $sheet->mergeCells('A5:V5');

                $sheet->mergeCells('E7:G7');
                $sheet->mergeCells('H7:Q7');

                $sheet->mergeCells('K8:P8');
                $sheet->mergeCells('M9:N9');
                $sheet->mergeCells('O9:P9');

                $sheet->mergeCells('Z7:Z10');
                $sheet->mergeCells('A7:A10');
                $sheet->mergeCells('B7:B10');
                $sheet->mergeCells('C7:C10');
                $sheet->mergeCells('D7:D10');
                $sheet->mergeCells('E8:E10');
                $sheet->mergeCells('F8:F10');
                $sheet->mergeCells('G8:G10');
                $sheet->mergeCells('H8:H10');
                $sheet->mergeCells('I8:I10');
                $sheet->mergeCells('J8:J10');
                $sheet->mergeCells('K9:K10');
                $sheet->mergeCells('L9:L10');
                $sheet->mergeCells('Q8:Q10');
                $sheet->mergeCells('R7:R10');
                $sheet->mergeCells('S7:S10');
                $sheet->mergeCells('T7:T10');
                $sheet->mergeCells('U7:U10');
                $sheet->mergeCells('V7:V10');

                // Your existing styles
                $sheet->getStyle('A1:V6')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A2:V6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A11:V' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                $sheet->getStyle('A5:V5')->applyFromArray([
                    'font' => ['italic' => true, 'bold' => false],
                ]);

                $sheet->getStyle('A7:V10')->applyFromArray([
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

                $sheet->getStyle('A7:V' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                $sheet->getStyle('A' . $lastRow . ':V' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true],
                ]);

                $event->sheet->getDelegate()->getStyle('N1')->getFont()->setBold(true);
            },
        ];
    }
}
