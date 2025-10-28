<?php

namespace App\Exports;

use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\TheoDoiHangHoa;
use App\Models\NhapHang;
use App\Models\CongChuc;
use App\Models\TheoDoiTruLui;
use App\Models\NiemPhong;
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


class BaoCaoTheoDoiTruLuiCuoiNgayExport implements FromArray, WithEvents, WithDrawings, WithTitle
{
    protected $tu_ngay;
    protected $so_to_khai_nhap;
    protected $theoDoi;
    protected $nhapHang;
    protected $sum = 0;
    protected $array = [];
    protected $hangHoaArr = [];
    protected $ten_hai_quan;
    protected $stt;
    protected $result;
    protected $ten_cong_chuc;
    protected $theoDoiTruLuis;
    protected $lan_phieu;
    protected $lanArray = [];
    protected $seenMaTheoDois = [];

    public function title(): string
    {
        return $this->so_to_khai_nhap;
    }
    public function __construct($so_to_khai_nhap, $tu_ngay)
    {
        $this->so_to_khai_nhap = $so_to_khai_nhap;
        $this->tu_ngay = $tu_ngay;
    }

    public function array(): array
    {
        $this->tu_ngay = Carbon::createFromFormat('Y-m-d', $this->tu_ngay);

        $day = $this->tu_ngay->format('d');  // Day of the month
        $month = $this->tu_ngay->format('m'); // Month number
        $year = $this->tu_ngay->format('Y');  // Year


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
            
        $this->theoDoiTruLuis = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->when(request('cong_viec') == 1, function ($query) {
                return $query->join('xuat_hang', 'xuat_hang.ma_xuat_hang', '=', 'theo_doi_tru_lui.ma_yeu_cau')
                    ->where('xuat_hang.trang_thai', '!=', 0);
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->cong_viec . '-' . $item->ma_yeu_cau;
            })
            ->map(function ($group) {
                return $group->first();
            })
            ->values()
            ->sort(function ($a, $b) {
                $dateComparison = strcmp($a->ngay_them, $b->ngay_them);
                if ($dateComparison === 0) {
                    return strcmp($a->ma_theo_doi, $b->ma_theo_doi);
                }
                return $dateComparison;
            })
            ->values();


        $this->result = [
            [''],
            [''],
            ['PHIẾU THEO DÕI, TRỪ LÙI HÀNG HÓA XUẤT KHẨU TỪNG LẦN'],
            [''],
            ['', '', '', '', '', '', '', 'Ngày ' . $day . ' Tháng ' . $month . ' Năm ' . $year],
            ['Tên Doanh Nghiệp: ' . $ten_doanh_nghiep],
            ['Số tờ khai: ' . $nhapHang->so_to_khai_nhap, '', '', 'Ngày đăng ký: Ngày ' . $date->format('d') . ' Tháng ' . $date->format('m') . ' Năm 20' . $date->format('y'), '', '', '', '', 'Hải quan đăng ký: ' . $this->ten_hai_quan],
            ['Tên hàng hóa: ' . $hangHoaLonNhat->ten_hang],
            ['Số lượng: ' . $tongSoLuongs . '; Đơn vị tính: ' . $hangHoaLonNhat->don_vi_tinh . '; Xuất xứ: ' . $hangHoaLonNhat->xuat_xu],
            [],
        ];


        $this->result[] = ['Số Tàu(Xà Lan):' . $nhapHang->ptvt_ban_dau, '', '', '', '', '', '', 'Số Container: ' . $nhapHang->container_ban_dau];
        $this->result[] = [
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
        $this->result[] = [
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
        $this->stt = 1;
        $hangHoas = HangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)->get();

        $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->sum('hang_trong_cont.so_luong');

        foreach ($hangHoas as $hangHoa) {
            $this->hangHoaArr[$hangHoa->ma_hang] = $hangHoa->so_luong_khai_bao;
        }

        $theoDois = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->groupBy('ma_yeu_cau', 'cong_viec')
            ->get();

        foreach ($theoDois as $theoDoi) {
            if ($theoDoi->cong_viec == 1) {
                $this->theoDoiXuat($theoDoi->ma_yeu_cau);
            } elseif ($theoDoi->cong_viec != 1 && \Carbon\Carbon::parse($theoDoi->ngay_them)->isSameDay($this->tu_ngay)) {
                $this->theoDoiCongViec($theoDoi);
            }
        }

        $this->array = array_map("unserialize", array_unique(array_map("serialize", $this->array)));
        $this->result[] = ['', '', '', 'Tổng cộng', '', '', $this->sum, $soLuongTon == 0 ? '0' : $soLuongTon, '', '', ''];
        $tongLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->sum('hang_trong_cont.so_luong');
        $this->result[] = ['', '', '', '', '', '', 'Tồn TK', $tongLuongTon == 0 ? '0' : $tongLuongTon, '', '', ''];



        $this->result[] = [
            [''],
            [''],
            ['', 'CÔNG CHỨC HẢI QUAN GIÁM SÁT', '', '', '', '', '', '', 'ĐẠI DIỆN DOANH NGHIỆP'],
            ['', '(Ký, đóng dấu công chức)', '', '', '', '', '', '', '(Ký, ghi rõ họ tên)']
        ];
        return $this->result;
    }

    public function theoDoiXuat($soToKhaiXuat)
    {
        $ptvts = XuatHang::find($soToKhaiXuat)->ten_phuong_tien_vt;

        $lanXuats = NhapHang::where('nhap_hang.so_to_khai_nhap', $this->so_to_khai_nhap)
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '!=', '0',)
            ->where('xuat_hang.so_to_khai_xuat', $soToKhaiXuat)
            ->select(
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang_cont.phuong_tien_vt_nhap',
                'xuat_hang_cont.*',
                'hang_hoa.*',
                'hang_trong_cont.ma_hang',
                'hang_trong_cont.so_luong',
                'hang_trong_cont.is_da_chuyen_cont',
            )
            ->get();

        $theoDoiCuoiCung = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->orderBy('ma_theo_doi', 'desc')
            ->where('cong_viec', '!=', '4')
            ->get()
            ->first();
        $ngayCuoiCung = $theoDoiCuoiCung->ngay_them ?? '2000-01-01';

        $start = null;
        $end = null;

        $is_xuat_het = false;
        if ($this->nhapHang->trang_thai == 4 || $this->nhapHang->trang_thai == 7) {
            if (\Carbon\Carbon::parse($this->nhapHang->ngay_xuat_het)->isSameDay($this->tu_ngay)) {
                $is_xuat_het = true;
            }
        }

        foreach ($lanXuats as $index => $item) {
            if (isset($seen[$item->ma_xuat_hang_cont])) {
                continue;
            }
            if (isset($this->hangHoaArr[$item->ma_hang])) {
                $this->hangHoaArr[$item->ma_hang] -= $item->so_luong_xuat;
            }

            $seen[$item->ma_xuat_hang_cont] = true;


            if (\Carbon\Carbon::parse($item->ngay_dang_ky)->isSameDay($this->tu_ngay)) {
                $soToKhaiXuatTrongPhieus = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_to_khai_nhap', $this->so_to_khai_nhap)
                    ->whereDate('xuat_hang.ngay_dang_ky',  Carbon::parse($this->tu_ngay)->toDateString())
                    ->pluck('xuat_hang.so_to_khai_xuat')
                    ->unique()
                    ->toArray();
                foreach ($this->theoDoiTruLuis as $truLui) {
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
                    $start = $this->stt + 12; // First occurrence
                }

                if ($is_xuat_het == true) {
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $this->hangHoaArr[$item->ma_hang] == 0 ? '0' : $this->hangHoaArr[$item->ma_hang],
                        '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } elseif (\Carbon\Carbon::parse($item->ngay_dang_ky)->greaterThanOrEqualTo($ngayCuoiCung)) {
                    $sealCuoiCung = NiemPhong::where('so_container', $item->so_container)->first()->so_seal ?? '';
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $this->hangHoaArr[$item->ma_hang] == 0 ? '0' : $this->hangHoaArr[$item->ma_hang],
                        $item->so_seal_cuoi_ngay ? $sealCuoiCung : '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } else {
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $this->hangHoaArr[$item->ma_hang] == 0 ? '0' : $this->hangHoaArr[$item->ma_hang],
                        $item->so_seal_cuoi_ngay ? $item->so_seal_cuoi_ngay : '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                }
                $this->ten_cong_chuc = CongChuc::find($item->ma_cong_chuc)->ten_cong_chuc ?? '';
                $this->sum += $item->so_luong_xuat;
            }


            $end = $this->stt - 1 + 12;
            $soLuongTon = 0;
            foreach ($this->hangHoaArr as $key => $value) {
                $soLuongTon += $value;
            }
        }



        if ($start !== null && $end !== null) {
            $this->array[] = [$start, $end, $ptvts, "Xuất hàng"];
        }
    }


    public function theoDoiCongViec($theoDoi)
    {
        $tenCongViec = "";
        $cong_viec = $theoDoi->cong_viec;
        if ($cong_viec == 1) {
            $tenCongViec = "Xuất hàng";
        } else if ($cong_viec == 2) {
            $tenCongViec = "Chuyển tàu cont ";
        } else if ($cong_viec == 3) {
            $tenCongViec = "Chuyển container";
        } else if ($cong_viec == 4) {
            $tenCongViec = "Chuyển tàu";
        } else if ($cong_viec == 5) {
            $tenCongViec = "Hàng về kho ban đầu";
        } else if ($cong_viec == 6) {
            $tenCongViec = "Tiêu hủy hàng";
        } else if ($cong_viec == 7) {
            $tenCongViec = "Kiểm tra hàng";
        } else if ($cong_viec == 9) {
            $tenCongViec = "Gỡ seal điện tử";
        }

        $congViecTrongPhieus = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->select('cong_viec', 'ma_yeu_cau')
            ->groupBy('cong_viec', 'ma_yeu_cau')
            ->whereDate('ngay_them',  Carbon::parse($this->tu_ngay)->toDateString())
            ->get()
            ->toArray();

        $theoDoiChiTiet = TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', '=', 'theo_doi_tru_lui.ma_theo_doi')
            ->where('theo_doi_tru_lui.ma_theo_doi', $theoDoi->ma_theo_doi)
            ->get();

        $theoDoiCuoiCung = TheoDoiTruLui::where('so_to_khai_nhap', $this->so_to_khai_nhap)
            ->orderBy('ma_theo_doi', 'desc')
            ->where('cong_viec', '!=', '4')
            ->get()
            ->first();

        $ngayCuoiCung = $theoDoiCuoiCung->ngay_them ?? '2000-01-01';
        $start = null;
        $end = null;
        $is_xuat_het = false;

        if ($this->nhapHang->ngay_xuat_het != null) {
            if (\Carbon\Carbon::parse($this->nhapHang->ngay_xuat_het)->isSameDay($this->tu_ngay)) {
                $is_xuat_het = true;
            }
        }

        // Process lan_phieu counting ONCE before the detail loop
        foreach ($this->theoDoiTruLuis as $truLui) {
            if (in_array($truLui->ma_theo_doi, $this->seenMaTheoDois)) {
                continue; // Skip if already processed
            }

            $shouldIncrement = false;

            if ($truLui->cong_viec == 1) {
                $xuatHang = XuatHang::find($truLui->ma_yeu_cau);
                if ($xuatHang && $xuatHang->trang_thai != 0) {
                    $shouldIncrement = true;
                    // Check if this export is in today's report
                    foreach ($congViecTrongPhieus as $congViec) {
                        if ($congViec['cong_viec'] == 1 && $congViec['ma_yeu_cau'] == $truLui->ma_yeu_cau) {
                            $this->lanArray[] = $this->lan_phieu + 1; // +1 because we increment after

                            break;
                        }
                    }
                }
            } else {
                $shouldIncrement = true;
                // Check if this work is in today's report
                foreach ($congViecTrongPhieus as $congViec) {
                    if ($congViec['cong_viec'] == $truLui->cong_viec && $congViec['ma_yeu_cau'] == $truLui->ma_yeu_cau) {
                        $this->lanArray[] = $this->lan_phieu + 1; // +1 because we increment after
                        $ma_cong_chuc = TheoDoiHangHoa::where('so_to_khai_nhap', $this->so_to_khai_nhap)
                            ->where('ma_yeu_cau', $truLui->ma_yeu_cau)
                            ->where('cong_viec', $truLui->cong_viec)
                            ->first()
                            ->ma_cong_chuc ?? '';
                        $this->ten_cong_chuc = CongChuc::find($ma_cong_chuc)->ten_cong_chuc ?? '';
                        break;
                    }
                }
            }

            if ($shouldIncrement) {
                $this->lan_phieu++;
                $this->seenMaTheoDois[] = $truLui->ma_theo_doi;
            }
        }

        // Now process the detail items
        foreach ($theoDoiChiTiet as $index => $item) {
            if ($start === null) {
                $start = $this->stt + 12;
            }

            if ($item->so_luong_chua_xuat != 0) {
                if ($is_xuat_het == true) {
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        $item->so_seal ? $item->so_seal : '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } elseif (\Carbon\Carbon::parse($item->ngay_dang_ky)->greaterThanOrEqualTo($ngayCuoiCung)) {
                    $sealCuoiCung = NiemPhong::where('so_container', $item->so_container)->first()->so_seal ?? '';
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        $item->so_seal ? $sealCuoiCung : '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                } else {
                    $this->result[] = [
                        $this->stt++,
                        '',
                        '',
                        $item->ten_hang,
                        '',
                        '',
                        $item->so_luong_xuat,
                        $item->so_luong_chua_xuat == 0 ? '0' : $item->so_luong_chua_xuat,
                        $item->so_seal ? $item->so_seal : '',
                        $item->phuong_tien_vt_nhap == $this->nhapHang->ptvt_ban_dau ? '' : $item->phuong_tien_vt_nhap,
                        $item->so_container == $this->nhapHang->container_ban_dau ? '' : $item->so_container,
                        '',
                    ];
                }
            }
        }

        $end = $this->stt - 1 + 12;

        if ($start !== null && $end !== null) {
            $this->array[] = [$start, $end, '', $tenCongViec];
        }
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
                $sheet->getColumnDimension('A')->setWidth(width: 5);
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
                $sheet->mergeCells('A5:B5');

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
                foreach ($this->array as $item) {
                    $sheet->mergeCells('B' . $item[0] . ':B' . $item[1]);
                    $sheet->setCellValue('B' . $item[0], $item[3]);
                    $sheet->mergeCells('C' . $item[0] . ':C' . $item[1]);
                    $sheet->setCellValue('C' . $item[0], $item[2]);
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

                // $sheet->mergeCells('B' . ($lastStart + 8) . ':C' . ($lastStart + 8));
                // $sheet->setCellValue('B' . ($lastStart + 8), $this->ten_cong_chuc);
                // $sheet->getStyle('B' . ($lastStart + 8))->getFont()->getColor()->setRGB('DDDDDD'); // hex for gray

                // $sheet->getStyle('B' . ($lastStart + 8) . ':C' . ($lastStart + 8))->applyFromArray([
                //     'font' => ['bold' => true],
                //     'alignment' => [
                //         'horizontal' => Alignment::HORIZONTAL_CENTER,
                //         'vertical' => Alignment::VERTICAL_CENTER,
                //     ]
                // ]);

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
                $finalRow = $sheet->getHighestRow();
                $sheet->getPageSetup()->setPrintArea('A1:L' . $finalRow);
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
