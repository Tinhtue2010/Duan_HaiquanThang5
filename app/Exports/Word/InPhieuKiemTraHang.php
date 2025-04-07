<?php

namespace App\Exports\Word;


use App\Models\NhapHang;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauKiemTraChiTiet;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;


class InPhieuKiemTraHang
{
    public function inPhieuKiemTraHang($ma_yeu_cau)
    {
        $yeuCau = YeuCauKiemTra::find($ma_yeu_cau);

        $chiTiets = YeuCauKiemTra::join('yeu_cau_kiem_tra_chi_tiet', 'yeu_cau_kiem_tra.ma_yeu_cau', '=', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau')
            ->join('nhap_hang', 'yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->where('yeu_cau_kiem_tra.ma_yeu_cau', $ma_yeu_cau)
            ->pluck('yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap');

        $nhapHangs = NhapHang::whereIn('so_to_khai_nhap', $chiTiets)->get();
        $date = Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_yeu_cau)->format('dmy'); // Convert to ddmmYY
        $chiTiets = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();

        $currentDate = Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_yeu_cau);
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
        $cell1->addText($yeuCau->doanhNghiep->ten_doanh_nghiep ?? '', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell1->addText('Số : ' . $yeuCau->ma_yeu_cau . ' – ' . $date . ' /CV', ['size' => 12], ['alignment' => 'center']);
        $cell1->addLine(['weight' => 1, 'width' => 120, 'height' => 0, 'alignment' => 'center']);
        $cell1->addText('V/v: “kiểm tra hàng ”', ['italic' => true, 'size' => 12], ['alignment' => 'center']);

        // Second cell of the header
        $cell2 = $headerTable->addCell(6000);
        $cell2->addText('CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell2->addText('Độc lập - Tự do - Hạnh phúc', ['size' => 12], ['alignment' => 'center']);
        $cell2->addLine(['weight' => 1, 'width' => 120, 'height' => 0, 'alignment' => 'center']);
        $cell2->addText('Móng Cái, ngày ' . $currentDate->day . ' tháng ' . $currentDate->month . ' năm ' . $currentDate->year, ['italic' => true, 'size' => 12], ['alignment' => 'center']);

        // Add spacing
        $section->addTextBreak();

        // Add the title
        $section->addText('ĐỀ NGHỊ KIỂM TRA HÀNG', ['bold' => true, 'size' => 14], ['alignment' => 'center']);

        $section->addTextBreak(1);

        // Add body text
        $section->addText('          Công ty đã làm thủ tục tiếp nhận hồ sơ và hàng hóa tại HẢI QUAN CỬA KHẨU CẢNG VẠN GIA. Hiện hàng hóa đang nằm trong khu vực giám sát của cơ quan hải quan. Công ty đề nghị được kiểm tra hàng, cụ thể như sau:', [], 'justify');
        // Add a table with borders
        $phpWord->addTableStyle('borderedTable', [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
        ]);
        $table = $section->addTable('borderedTable');
        $table->addRow();
        $table->addCell(800, ['valign' => 'center'])->addText('STT', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(3000, ['valign' => 'center'])->addText('Số Tờ Khai', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(2000, ['valign' => 'center'])->addText('Số tàu', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(3000, ['valign' => 'center'])->addText('Số container', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(2000, ['valign' => 'center'])->addText('Ngày Tờ Khai', ['bold' => true], ['alignment' => 'center']);
        $table->addCell(3000, ['valign' => 'center'])->addText('Tên Hàng', ['bold' => true], ['alignment' => 'center']);

        $stt = 1;
        foreach ($chiTiets as $chiTiet) {
            $table->addRow();
            $table->addCell(800, ['valign' => 'center'])->addText($stt++, [], ['alignment' => 'center']);
            $table->addCell(3000, ['valign' => 'center'])->addText($chiTiet->so_to_khai_nhap, [], ['alignment' => 'center']);
            $table->addCell(2000, ['valign' => 'center'])->addText($chiTiet->so_tau, [], ['alignment' => 'center']);
            $table->addCell(3000, ['valign' => 'center'])->addText($chiTiet->so_container, [], ['alignment' => 'center']);
            $date = Carbon::createFromFormat('Y-m-d', $chiTiet->ngay_dang_ky)->format('d-m-Y');
            $table->addCell(2000, ['valign' => 'center'])->addText($date, [], ['alignment' => 'center']);
            $cell = $table->addCell(5000, ['valign' => 'center']);
            $lines = explode('<br>', $chiTiet->ten_hang);

            // Filter out empty lines and trim whitespace
            $lines = array_filter(array_map('trim', $lines));

            foreach ($lines as $line) {
                $line = htmlspecialchars($line, ENT_XML1, 'UTF-8'); // Escape special characters

                $cell->addText(
                    $line,
                    [],
                    ['alignment' => 'center']
                );
            }
        }

        if ($yeuCau->ngay_hoan_thanh) {
            $date = Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_hoan_thanh)->format('d-m-Y');
        } else {
            $date = '';
        }
        $section->addText('          Đoàn tàu số: ' . $yeuCau->ten_doan_tau, 'justify');
        $section->addText('          Thời gian thực hiện: Ngày ' . $date, 'justify');
        $section->addText('          Đề nghị quý cơ quan tạo điều kiện thuận lợi để Công ty thực hiện nội dung công việc như trên, chúng tôi cam kết chịu trách nhiệm bảo quản nguyên trạng hàng hóa, niêm phong hải quan theo đúng quy định.', [], 'justify');
        $section->addText('Xin chân thành cảm ơn!', [], 'justify');
        $section->addTextBreak(1);

        $headerTable2 = $section->addTable(['cellMargin' => 0]);
        // First cell of the header
        $headerTable2->addRow();
        $cell1 = $headerTable2->addCell(6000);
        $cell1->addText('Nơi nhận:', ['bold' => true]);
        $cell1->addText('- HQCK cảng Vạn Gia (để b/c);');
        $cell1->addText('- Lưu văn thư: 01 bản.');

        if ($yeuCau->ngay_hoan_thanh) {
            $qrCodeText = 'Yêu cầu số ' . $yeuCau->ma_yeu_cau . ' được duyệt vào ngày ' . Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_hoan_thanh)->format('d-m-Y') . ', bởi công chức ' . ($yeuCau->congChuc->ten_cong_chuc ?? '');
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrCodeText);

            $section->addImage($qrCodeUrl, [
                'width' => 100,
                'height' => 100,
            ]);
        }


        // Second cell of the header
        $cell2 = $headerTable2->addCell(6000);
        $cell2->addText($yeuCau->doanhNghiep->ten_doanh_nghiep ?? '', ['bold' => true], ['alignment' => 'center']);


        $section->addTextBreak();
        $section->addText('LĐ Đội KTGS và KSHQ:');
        $congChuc =  $yeuCau->congChuc->ten_cong_chuc ?? '';
        $section->addText('- Phân công Đ/C ' . $congChuc . ' thực hiện.');

        $headerTable3 = $section->addTable(['cellMargin' => 0]);

        // First cell of the header
        $headerTable3->addRow();
        $cell1 = $headerTable3->addCell(6000);

        // Second cell of the header
        $cell2 = $headerTable3->addCell(6000);
        // $cell2->addText('KÝ, GHI RÕ HỌ TÊN', ['size' => 12], ['alignment' => 'center']);

        // Save the document
        $fileName = 'Phiếu yêu cầu kiểm tra hàng.docx';
        $tempFilePath = storage_path($fileName);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFilePath);

        // Return the file as a response
        return response()->download($tempFilePath)->deleteFileAfterSend(true);
    }
}
