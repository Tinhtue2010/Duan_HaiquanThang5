<?php

namespace App\Exports\Word;

use App\Models\LoaiHang;
use App\Models\NhapHang;
use App\Models\XuatHang;
use App\Models\HangHoa;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaoCaoHangHoaXuatNhapKhau
{
    public function baoCaoHangHoaXuatNhapKhau(Request $request)
    {
        $tu_ngay = Carbon::createFromFormat('d/m/Y', $request->tu_ngay)->format('Y-m-d');
        $den_ngay = Carbon::createFromFormat('d/m/Y', $request->den_ngay)->format('Y-m-d');

        $currentDate = Carbon::now();
        $phpWord = new PhpWord();

        // Set A4 page size and orientation (portrait)
        $sectionStyle = [
            'pageSizeW' => 11906, // A4 width in twips (11906 twips = 210mm)
            'pageSizeH' => 16838, // A4 height in twips (16838 twips = 297mm)
            'marginLeft' => 720,  // 0.5 inch margin (half of 1440)
            'marginRight' => 720, // 0.5 inch margin
            'marginTop' => 720,   // 0.5 inch margin
            'marginBottom' => 720, // 0.5 inch margin
        ];

        // Create a new section with A4 size
        $section = $phpWord->addSection($sectionStyle);

        // Set default font
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Create the header table
        $headerTable = $section->addTable(['cellMargin' => 0]);

        // First cell of the header
        $headerTable->addRow();
        $cell1 = $headerTable->addCell(6000);
        $cell1->addText('CHI CỤC HẢI QUAN KHU VỰC VIII', ['bold' => false, 'size' => 12], ['alignment' => 'center']);
        $cell1->addText('HẢI QUAN CỬA KHẨU CẢNG VẠN GIA', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell1->addText('Số:        /BC-HQ', ['size' => 12], ['alignment' => 'center']);

        // Second cell of the header
        $cell2 = $headerTable->addCell(6000);
        $cell2->addText('CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell2->addText('Độc lập - Tự do - Hạnh phúc', ['size' => 12], ['alignment' => 'center']);
        $cell2->addText("Móng Cái, ngày " . Carbon::now()->format('d') . " tháng " . Carbon::now()->format('m') . " năm " . Carbon::now()->format('Y'), ['size' => 12], ['alignment' => 'center']);

        // Add spacing
        $section->addTextBreak();

        // Add the title
        $section->addText('BÁO CÁO HÀNG HOÁ XUẤT NHẬP KHẨU', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addText('TỪ NGÀY ' . $request->tu_ngay . ' ĐẾN NGÀY ' . $request->den_ngay, ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addTextBreak(1);





        $phpWord->addTableStyle('borderedTable', [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
        ]);

        $table = $section->addTable('borderedTable');
        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 5])->addText('I/HÀNG HOÁ TIẾP NHẬN', ['bold' => true], ['alignment' => 'center']);

        $table->addRow();
        $table->addCell(2000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'STT',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'NỘI DUNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG (CONTAINER)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'TRỊ GIÁ (USD)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );


        $nhapHangs = NhapHang::whereBetween('created_at', [
            Carbon::parse($tu_ngay)->startOfDay(),
            Carbon::parse($den_ngay)->endOfDay()
        ])->get();

        $soLuongToKhai = $nhapHangs->count() ?? 0;
        $soLuongToKhai = number_format($soLuongToKhai, 0);

        // $totalTriGia = NhapHang::whereBetween('created_at', [
        //     Carbon::parse($tu_ngay)->startOfDay(),
        //     Carbon::parse($den_ngay)->endOfDay()
        // ])
        //     ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
        //     ->sum('tri_gia');
        // $totalTriGia = number_format($totalTriGia, 0);

        // $tongSoContainer = $this->getTongContainerNhap($tu_ngay, $den_ngay);

        $tongSoContainerx = 0;
        $tongTriGiax = 0;

        $table->addRow();
        $table->addCell(500)->addText('1', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỜ KHAI', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($soLuongToKhai, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);

        $stt = 2;
        $totalTotalSoLuong = 0;
        $loaiHangs = LoaiHang::all();
        foreach ($loaiHangs as $loaiHang) {
            $data1 = $this->getDataNhap($loaiHang, $tu_ngay, $den_ngay);
            $totalSoLuong = $data1->total_so_luong ?? 0;
            $totalTriGia = $data1->total_tri_gia ?? 0;
            //Bỏ ra sẽ lỗi
            if ($totalSoLuong != 0) {
                $totalTotalSoLuong += $totalSoLuong;
            }
            $totalSoLuong = number_format($data1->total_so_luong, 0);
            $totalTriGia = number_format($data1->total_tri_gia, 0);

            $tongSoContainerx += $data1->total_so_container;
            $tongTriGiax += $data1->total_tri_gia;

            $table->addRow();
            $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($loaiHang->ten_loai_hang, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($totalSoLuong, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($data1->total_so_container ? $data1->total_so_container : '0', [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($totalTriGia, [], ['alignment' => 'center']);
        }

        $totalTotalSoLuong = number_format($totalTotalSoLuong, 0);
        $tongSoContainerx = number_format($tongSoContainerx, 0);
        $tongTriGiax = number_format($tongTriGiax, 0);
        //Tổng Cộng
        $table->addRow();
        $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỔNG CỘNG', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($totalTotalSoLuong, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongSoContainerx, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongTriGiax, [], ['alignment' => 'center']);


        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 5])->addText('II/HÀNG HOÁ XUẤT KHẨU', ['bold' => true], ['alignment' => 'center']);

        $table->addRow();
        $table->addCell(2000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'STT',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'NỘI DUNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG (CONTAINER)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'TRỊ GIÁ (USD)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        // $soLuongToKhaiXuat1 = XuatHang::whereBetween('ngay_dang_ky', [
        //     Carbon::parse($tu_ngay)->startOfDay(),
        //     Carbon::parse($den_ngay)->endOfDay()
        // ])
        //     ->where('xuat_hang.trang_thai', '!=', '0')->count();

        // $soLuongToKhaiXuat = $soLuongToKhaiXuat1;
        // $soLuongToKhaiXuat = number_format($soLuongToKhaiXuat, 0);

        // $totalTriGiaXuat1 = XuatHang::whereBetween('ngay_dang_ky', [
        //     Carbon::parse($tu_ngay)->startOfDay(),
        //     Carbon::parse($den_ngay)->endOfDay()
        // ])
        //     ->where('xuat_hang.trang_thai', '!=', '0')
        //     ->join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
        //     ->sum('xuat_hang_cont.tri_gia');

        $tongTkXuat = $this->getTongContXuat($tu_ngay, $den_ngay)->total_tk_xuat;
        $tongTkXuat = number_format($tongTkXuat, 0);

        // $tongSoContainer = $this->getTongContXuat($tu_ngay, $den_ngay)->total_so_container;
        // $tongSoContainer = number_format($tongSoContainer, 0);
        // $totalTriGiaXuat = number_format($totalTriGiaXuat1, 0);
        $tongSoContainerx = 0;
        $tongTriGiax = 0;

        $table->addRow();
        $table->addCell(500)->addText('1', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỜ KHAI', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongTkXuat, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);

        $stt = 2;

        $loaiHangs = LoaiHang::all();
        $totalSoLuong = 0;
        foreach ($loaiHangs as $loaiHang) {
            $data1 = $this->getDataXuat($loaiHang, $tu_ngay, $den_ngay);

            $totalSoLuongXuat = $data1->total_so_luong_xuat ?? 0;
            $totalTriGia = $data1->total_tri_gia;
            $totalSoLuong +=  $totalSoLuongXuat;

            $totalTriGia = number_format($totalTriGia, 0);
            $totalSoLuongXuat = number_format($totalSoLuongXuat, 0);
            $tongSoContainer = number_format($data1->total_so_container, 0);

            $tongSoContainerx += $data1->total_so_container;
            $tongTriGiax += $data1->total_tri_gia;

            $table->addRow();
            $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($loaiHang->ten_loai_hang, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($totalSoLuongXuat, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($tongSoContainer, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($totalTriGia, [], ['alignment' => 'center']);
        }
        $totalSoLuong = number_format($totalSoLuong, 0);
        $tongTriGiax = number_format($tongTriGiax, 0);
        $tongSoContainerx = number_format($tongSoContainerx, 0);
        //Tổng Cộng
        $table->addRow();
        $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỔNG CỘNG', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($totalSoLuong, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongSoContainerx, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongTriGiax, [], ['alignment' => 'center']);


        //3.Hàng lưu tại cửa khẩu
        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 5])->addText('III/HÀNG LƯU TẠI CỬA KHẨU', ['bold' => true], ['alignment' => 'center']);

        $table->addRow();
        $table->addCell(2000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'STT',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'NỘI DUNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'SỐ LƯỢNG (CONTAINER)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'TRỊ GIÁ (USD)',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $soLuongToKhaiLuu = NhapHang::where('trang_thai', '2')->count();
        $totalTriGiaLuu = NhapHang::where('trang_thai', '2')
            ->join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->selectRaw('SUM(hang_trong_cont.so_luong * hang_hoa.don_gia) as total_tri_gia')
            ->value('total_tri_gia');
        $tongSoContainer = $this->getTongContainerHienTai($tu_ngay, $den_ngay);


        $totalTriGiaLuu = number_format($totalTriGiaLuu, 0);
        $soLuongToKhaiLuu = number_format($soLuongToKhaiLuu, 0);
        $tongSoContainer = number_format($tongSoContainer, 0);


        $tongTriGiax = 0;
        $tongSoContainerx = 0;

        $table->addRow();
        $table->addCell(500)->addText('1', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỜ KHAI', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($soLuongToKhaiLuu, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('', [], ['alignment' => 'center']);

        $stt = 2;
        $totalTotalSoLuong = 0;
        $loaiHangs = LoaiHang::all();
        foreach ($loaiHangs as $loaiHang) {
            $data = $this->getDataHienTai($loaiHang, $tu_ngay, $den_ngay);

            $totalTotalSoLuong += $data->total_so_luong;

            $total_so_luong = number_format($data->total_so_luong, 0);
            $total_tri_gia = number_format($data->total_tri_gia, 0);
            $total_so_container = number_format($data->total_so_container, 0);

            $tongTriGiax += $data->total_tri_gia;
            $tongSoContainerx += $data->total_so_container;

            $table->addRow();
            $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($loaiHang->ten_loai_hang, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($total_so_luong, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($total_so_container, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($total_tri_gia, [], ['alignment' => 'center']);
        }
        $totalTotalSoLuong = number_format($totalTotalSoLuong, 0);
        $tongTriGiax = number_format($tongTriGiax, 0);
        $tongSoContainerx = number_format($tongSoContainerx, 0);
        //Tổng Cộng
        $table->addRow();
        $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText('TỔNG CỘNG', [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($totalTotalSoLuong, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongSoContainer, [], ['alignment' => 'center']);
        $table->addCell(3000)->addText($tongTriGiax, [], ['alignment' => 'center']);


        $section->addTextBreak(1);
        $headerTable3 = $section->addTable(['cellMargin' => 0]);
        $headerTable3->addRow();
        $cell1 = $headerTable3->addCell(6000);
        $cell2 = $headerTable3->addCell(6000);
        $cell2->addText('NGƯỜI BÁO CÁO', ['size' => 12], ['alignment' => 'center']);

        // Save the document
        // $fileName = 'Báo cáo hàng hóa xuất nhập khẩu từ ngày '. $request->tu_ngay .' đến ngày '.$request->den_ngay.'.docx';

        $fileName = 'Báo cáo hàng hóa xuất nhập khẩu.docx';
        $tempFilePath = storage_path($fileName);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFilePath);

        // Return the file as a response
        return response()->download($tempFilePath)->deleteFileAfterSend(true);
    }


    public function getDataXuat($loaiHang, $tu_ngay, $den_ngay)
    {
        $subQuery = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->where('hang_hoa.loai_hang', $loaiHang->ten_loai_hang)
            ->whereBetween('xuat_hang.ngay_dang_ky', [$tu_ngay, $den_ngay])
            ->groupBy('hang_hoa.so_to_khai_nhap')
            ->select(
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as total_so_luong_xuat'),
                DB::raw('SUM(hang_hoa.don_gia * xuat_hang_cont.so_luong_xuat) as total_tri_gia'),
                DB::raw('COUNT(DISTINCT xuat_hang_cont.so_container) as total_so_container')
            );

        return DB::table(DB::raw("({$subQuery->toSql()}) as grouped_data"))
            ->mergeBindings($subQuery->getQuery())
            ->select(
                DB::raw('SUM(total_so_luong_xuat) as total_so_luong_xuat'),
                DB::raw('SUM(total_tri_gia) as total_tri_gia'),
                DB::raw('SUM(total_so_container) as total_so_container')
            )
            ->first();
    }

    public function getTongContXuat($tu_ngay, $den_ngay)
    {
        $subQuery = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '!=', '0')
            ->whereBetween('xuat_hang.ngay_dang_ky', [$tu_ngay, $den_ngay])
            ->groupBy('hang_hoa.so_to_khai_nhap')
            ->select(
                DB::raw('COUNT(DISTINCT xuat_hang_cont.so_container) as total_so_container'),
                DB::raw('COUNT(DISTINCT xuat_hang_cont.so_to_khai_xuat) as total_tk_xuat')
            );

        return DB::table(DB::raw("({$subQuery->toSql()}) as grouped_data"))
            ->mergeBindings($subQuery->getQuery())
            ->select(
                DB::raw('SUM(total_so_container) as total_so_container'),
                DB::raw('SUM(total_tk_xuat) as total_tk_xuat')
            )
            ->first();
    }
    public function getDataNhap($loaiHang, $tu_ngay, $den_ngay)
    {
        return HangHoa::where('loai_hang', $loaiHang->ten_loai_hang)
            ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->whereBetween('nhap_hang.created_at', [
                Carbon::parse($tu_ngay)->startOfDay(),
                Carbon::parse($den_ngay)->endOfDay()
            ])
            ->selectRaw('
            SUM(hang_hoa.tri_gia) as total_tri_gia,
            SUM(hang_hoa.so_luong_khai_bao) as total_so_luong,
            COUNT(DISTINCT hang_hoa.so_container_khai_bao) as total_so_container
        ')
            ->first();
    }

    public function getTongContainerNhap($tu_ngay, $den_ngay)
    {
        $result = HangHoa::join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->whereBetween('nhap_hang.created_at', [
                Carbon::parse($tu_ngay)->startOfDay(),
                Carbon::parse($den_ngay)->endOfDay()
            ])
            ->where('nhap_hang.trang_thai', '2')
            ->where('hang_trong_cont.so_luong', '>', 0)
            ->distinct()
            ->count('hang_trong_cont.so_container');
        return $result;
    }

    public function getDataHienTai($loaiHang, $tu_ngay, $den_ngay)
    {
        $subQuery = HangHoa::where('loai_hang', $loaiHang->ten_loai_hang)
            ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->where('hang_trong_cont.so_luong', '>', 0)
            ->groupBy('nhap_hang.so_to_khai_nhap')
            ->select(
                DB::raw('SUM(hang_trong_cont.so_luong * hang_hoa.don_gia) as total_tri_gia'),
                DB::raw('SUM(hang_trong_cont.so_luong) as total_so_luong')
            );

        $result = DB::table(DB::raw("({$subQuery->toSql()}) as grouped_data"))
            ->mergeBindings($subQuery->getQuery())
            ->select(
                DB::raw('SUM(total_tri_gia) as total_tri_gia'),
                DB::raw('SUM(total_so_luong) as total_so_luong')
            )
            ->first();

        // Count distinct containers separately
        $totalContainers = HangHoa::where('loai_hang', $loaiHang->ten_loai_hang)
            ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->where('hang_trong_cont.so_luong', '>', 0)
            ->distinct()
            ->count('hang_trong_cont.so_container');

        $result->total_so_container = $totalContainers;

        return $result;
    }

    public function getTongContainerHienTai($tu_ngay, $den_ngay)
    {
        $result = HangHoa::join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.trang_thai', '2')
            ->where('hang_trong_cont.so_luong', '>', 0)
            ->distinct()
            ->count('hang_trong_cont.so_container');

        return $result;
    }
}
