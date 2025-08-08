<?php

namespace App\Exports\Word;

use App\Models\YeuCauChuyenTau;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;

class InPhieuChuyenTau
{
    public function inPhieuChuyenTau($ma_yeu_cau)
    {
        $yeuCau = YeuCauChuyenTau::find($ma_yeu_cau);

        $chiTiets = YeuCauChuyenTau::join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_chuyen_tau.ma_yeu_cau', $ma_yeu_cau)
            ->get();
        $date = Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_yeu_cau)->format('dmy');
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
        $cell1->addText('V/v: “chuyển hàng sang tàu mới”', ['italic' => true, 'size' => 12], ['alignment' => 'center']);

        // Second cell of the header
        $cell2 = $headerTable->addCell(6000);
        $cell2->addText('CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM', ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        $cell2->addText('Độc lập - Tự do - Hạnh phúc', ['size' => 12], ['alignment' => 'center']);
        $cell2->addLine(['weight' => 1, 'width' => 120, 'height' => 0, 'alignment' => 'center']);
        $cell2->addText('Móng Cái, ngày ' . $currentDate->day . ' tháng ' . $currentDate->month . ' năm ' . $currentDate->year, ['italic' => true, 'size' => 12], ['alignment' => 'center']);


        // Add spacing
        $section->addTextBreak();

        // Add the title
        $section->addText('ĐỀ NGHỊ CHUYỂN HÀNG SANG TÀU MỚI', ['bold' => true, 'size' => 14], ['alignment' => 'center']);

        $section->addTextBreak(1);

        // Add body text
        $section->addText('          Công ty đã làm thủ tục tiếp nhận hồ sơ và hàng hóa tại HẢI QUAN CỬA KHẨU CẢNG VẠN GIA. Hiện hàng hóa đang nằm trong khu vực giám sát của cơ quan hải quan. Công ty đề nghị được chuyển hàng sang phương tiện vận tải mới, cụ thể như sau:', [], 'justify');
        // Add a table with borders
        $phpWord->addTableStyle('borderedTable', [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
        ]);
        $table = $section->addTable('borderedTable');
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
            'Số tờ khai',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'Số container',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'Tàu hiện tại',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $table->addCell(3000, [
            'valign' => 'center',
            'vMerge' => 'restart'
        ])->addText(
            'Tàu mới',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $stt = 1;
        foreach ($chiTiets as $chiTiet) {
            $table->addRow();
            $table->addCell(500)->addText($stt++, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($chiTiet->so_to_khai_nhap, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($chiTiet->so_container, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($chiTiet->tau_goc, [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($chiTiet->tau_dich, [], ['alignment' => 'center']);
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
            $qrCodeText = 'Yêu cầu số ' . $yeuCau->ma_yeu_cau . ' được duyệt vào ngày ' .
                Carbon::createFromFormat('Y-m-d', $yeuCau->ngay_hoan_thanh)->format('d-m-Y') .
                ', bởi công chức ' . ($yeuCau->congChuc->ten_cong_chuc ?? '');

            // Create the QR code
            $qrCode = QrCode::create($qrCodeText)
                ->setSize(150);

            // Generate the QR code image
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Get the image binary data
            $imageData = $result->getString();

            // Embed the image directly into the Word document
            $section->addImage($imageData, [
                'width' => 150,
                'height' => 150,
            ]);
        }
        // Second cell of the header
        $cell2 = $headerTable2->addCell(6000);
        $cell2->addText($yeuCau->doanhNghiep->ten_doanh_nghiep ?? '', ['bold' => true], ['alignment' => 'center']);


        // Save the document
        $fileName = 'Phiếu yêu cầu chuyển tàu.docx';
        $tempFilePath = storage_path($fileName);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFilePath);

        // Return the file as a response
        return response()->download($tempFilePath)->deleteFileAfterSend(true);
    }
}
