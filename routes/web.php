<?php

use App\Http\Controllers\NhapHangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaoCaoController;
use App\Http\Controllers\PTVanTaiController;
use App\Http\Controllers\QuanLyKhoController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\TraCuuController;
use App\Http\Controllers\WordReportController;
use App\Http\Controllers\XuatHangController;
use App\Http\Controllers\YeuCauTauContController;
use App\Http\Controllers\YeuCauContainerController;
use App\Http\Controllers\YeuCauChuyenTauController;
use App\Http\Controllers\YeuCauHangVeKhoController;
use App\Http\Controllers\YeuCauKiemTraController;
use App\Http\Controllers\YeuCauGiaHanController;
use App\Http\Controllers\YeuCauNiemPhongController;
use App\Http\Controllers\YeuCauTieuHuyController;
use App\Http\Controllers\ChuHangController;
use App\Http\Controllers\DoanhNghiepController;
use App\Http\Controllers\CongChucController;
use App\Http\Controllers\HaiQuanController;
use App\Http\Controllers\LoaiHangController;
use App\Http\Controllers\LoaiHinhController;
use App\Http\Controllers\SealNiemPhongController;
use App\Http\Controllers\ThuKhoController;
use App\Http\Controllers\XuatCanhController;
use App\Http\Controllers\BanGiaoHoSoController;
use App\Http\Controllers\DeviceLoginController;

Route::view('/', 'home-page');
Route::view('/huong-dan-bang', view: 'huong-dan.huong-dan-bang');
Route::view('/huong-dan-nhap-hang', view: 'huong-dan.huong-dan-nhap-hang');
Route::view('/huong-dan-xuat-hang', view: 'huong-dan.huong-dan-xuat-hang');
Route::view('/huong-dan-yeu-cau', view: 'huong-dan.huong-dan-yeu-cau');
Route::view('/lien-he', 'lien-he');
Route::get('/danh-sach-lien-he', [TaiKhoanController::class, 'danhSachLienHe'])->name('danh-sach-lien-he');
Route::post('/lien-he-submit', [TaiKhoanController::class, 'lienHeSubmit'])->name('lien-he-submit');
Route::get('/dang-nhap', [TaiKhoanController::class, 'dangNhap'])->name('login');
Route::post('/submit-dang-nhap', [TaiKhoanController::class, 'submitDangNhap'])->name('submit-dang-nhap');
Route::post('/dang-xuat', [TaiKhoanController::class, 'dangXuat'])->name('dang-xuat');

Route::middleware([\App\Http\Middleware\CheckRoleCongChucDoanhNghiep::class])->group(function () {
    Route::name('nhap-hang.')->group(function () {
        Route::get('/quan-ly-nhap-hang', [NhapHangController::class, 'danhSachToKhai'])->name('quan-ly-nhap-hang');
        Route::get('/to-khai-da-nhap-hang', [NhapHangController::class, 'toKhaiDaNhapHang'])->name('to-khai-da-nhap-hang');
        Route::get('/to-khai-nhap-da-huy', [NhapHangController::class, 'toKhaiDaHuy'])->name('to-khai-da-huy');
        Route::post('/submit-to-khai-nhap', [NhapHangController::class, 'themToKhaiNhapSubmit'])->name('submit-to-khai-nhap');
        Route::get('/nhap-to-khai-nhap', [NhapHangController::class, 'themToKhaiNhap'])->name('nhap-to-khai-nhap');
        Route::get('/thong-tin-nhap-hang/{so_to_khai_nhap}', [NhapHangController::class, 'thongTinToKhai'])->name('show');
        Route::get('/thong-tin-nhap-hang-huy/{id_huy}', [NhapHangController::class, 'thongTinToKhaiHuy'])->name('show-huy');
        Route::get('/vi-tri-hien-tai/{so_to_khai_nhap}', [NhapHangController::class, 'viTriHangHienTai'])->name('vi-tri-hang-hien-tai');
        Route::get('/phieu-xuat-cua-to-khai/{so_to_khai_nhap}', [NhapHangController::class, 'phieuXuatCuaToKhai'])->name('phieu-xuat-cua-to-khai');
        Route::post('/duyet-to-khai-nhap', [NhapHangController::class, 'duyetToKhaiNhap'])->name('duyet-to-khai-nhap');

        Route::get('/sua-to-khai-nhap-cong-chuc/{so_to_khai_nhap}', [NhapHangController::class, 'suaToKhaiNhapCongChuc'])->name('sua-to-khai-nhap-cong-chuc');
        Route::get('/sua-to-khai-nhap/{so_to_khai_nhap}', [NhapHangController::class, 'suaToKhaiNhap'])->name('sua-to-khai-nhap');
        Route::post('/submit-sua-to-khai-nhap', [NhapHangController::class, 'suaToKhaiNhapSubmit'])->name('submit-sua-to-khai-nhap');
        Route::post('/huy-to-khai-nhap/{so_to_khai_nhap}', [NhapHangController::class, 'huyToKhai'])->name('huy-to-khai-nhap');

        Route::get('/export-tokhai/{so_to_khai_nhap}', [NhapHangController::class, 'exportToKhaiNhap'])->name('export-to-khai');
        Route::post('/upload-file-nhap', [NhapHangController::class, 'uploadFileNhapAjax'])->name('upload-file-nhap');

        Route::get('/lich-su-sua-nhap/{so_to_khai_nhap}', [NhapHangController::class, 'lichSuSuaNhap'])->name('lich-su-sua-nhap');
        Route::get('/xem-sua-nhap-theo-lan/{ma_nhap_sua}', [NhapHangController::class, 'xemSuaNhapTheoLan'])->name('xem-sua-nhap-theo-lan');
        Route::get('/xem-sua-to-khai-nhap/{so_to_khai_nhap}', [NhapHangController::class, 'xemSuaToKhai'])->name('xem-sua-to-khai-nhap');
        Route::post('/duyet-sua-to-khai-nhap', [NhapHangController::class, 'duyetSuaYeuCau'])->name('duyet-sua-to-khai-nhap');
        Route::post('/huy-sua-to-khai-nhap', [NhapHangController::class, 'huySuaYeuCau'])->name('huy-sua-to-khai-nhap');


        Route::get('/getNhapHangDaDuyets', [NhapHangController::class, 'getNhapHangDaDuyets'])->name('getNhapHangDaDuyets');
    });

    Route::name('xuat-hang.')->group(function () {
        Route::get('/quan-ly-xuat-hang', [XuatHangController::class, 'danhSachToKhai'])->name('quan-ly-xuat-hang');
        Route::get('/to-khai-da-xuat-hang', [XuatHangController::class, 'listToKhaiDaXuatHang'])->name('to-khai-da-xuat-hang');
        Route::get('/to-khai-xuat-da-huy', [XuatHangController::class, 'listToKhaiDaHuy'])->name('to-khai-xuat-da-huy');

        Route::get('/duyet-nhanh-phieu-xuat', [XuatHangController::class, 'duyetNhanhPhieuXuat'])->name('duyet-nhanh-phieu-xuat');
        Route::post('/duyet-nhanh-phieu-xuat-submit', [XuatHangController::class, 'duyetNhanhPhieuXuatSubmit'])->name('duyet-nhanh-phieu-xuat-submit');
        Route::get('/duyet-nhanh-thuc-xuat', [XuatHangController::class, 'duyetNhanhThucXuat'])->name('duyet-nhanh-thuc-xuat');
        Route::post('/duyet-nhanh-thuc-xuat-submit', [XuatHangController::class, 'duyetNhanhThucXuatSubmit'])->name('duyet-nhanh-thuc-xuat-submit');


        Route::get('/them-to-khai-xuat', [XuatHangController::class, 'themToKhaiXuat'])->name('them-to-khai-xuat');
        Route::post('/them-to-khai-xuat-submit', [XuatHangController::class, 'themToKhaiXuatSubmit'])->name('them-to-khai-xuat-submit');

        Route::get('/sua-to-khai-xuat/{so_to_khai_xuat}', [XuatHangController::class, 'suaToKhaiXuat'])->name('sua-to-khai-xuat');
        Route::post('/sua-to-khai-xuat-submit', [XuatHangController::class, 'suaToKhaiXuatSubmit'])->name('sua-to-khai-xuat-submit');
        Route::get('/xem-yeu-cau-sua/{so_to_khai_xuat}/{ma_yeu_cau}', [XuatHangController::class, 'xemYeuCauSua'])->name('xem-yeu-cau-sua');
        Route::post('/huy-yeu-cau-sua/{ma_yeu_cau}', [XuatHangController::class, 'huyYeuCauSua'])->name('huy-yeu-cau-sua');
        Route::post('/duyet-yeu-cau-sua/{ma_yeu_cau}', [XuatHangController::class, 'duyetYeuCauSua'])->name('duyet-yeu-cau-sua');


        Route::post('/update-duyet-to-khai', [XuatHangController::class, 'updateDuyetToKhai'])->name('updateDuyetToKhai');
        Route::post('/huy-to-khai', [XuatHangController::class, 'huyToKhai'])->name(name: 'huy-to-khai');
        Route::post('/yeu-cau-huy-to-khai', [XuatHangController::class, 'yeuCauHuyToKhai'])->name(name: 'yeu-cau-huy-to-khai');
        Route::post('/thu-hoi-yeu-cau-huy', [XuatHangController::class, 'thuHoiYeuCauHuy'])->name(name: 'thu-hoi-yeu-cau-huy');
        Route::post('/duyet-yeu-cau-huy', [XuatHangController::class, 'duyetYeuCauHuy'])->name(name: 'duyet-yeu-cau-huy');


        Route::post('/to-khai-xuat-submit/{ma_loai_hinh}/submit', [XuatHangController::class, 'themToKhaiSubmit'])->name('to-khai-xuat-submit');
        Route::get('/thong-tin-xuat-hang/{so_to_khai_xuat}', [XuatHangController::class, 'thongTinXuatHang'])->name('thong-tin-xuat-hang');
        Route::get('/lich-su-sua-xuat-hang/{so_to_khai_xuat}', [XuatHangController::class, 'lichSuSuaXuatHang'])->name('lich-su-sua-xuat-hang');
        Route::get('/xem-sua-xuat-hang-theo-lan/{ma_yeu_cau}', [XuatHangController::class, 'xemSuaXuatHangTheoLan'])->name('xem-sua-xuat-hang-theo-lan');

        Route::get('/export-to-khai-xuat', [XuatHangController::class, 'exportToKhaiXuat'])->name('export-to-khai-xuat');
        Route::get('/kiem-tra-qua-han', [XuatHangController::class, 'kiemTraQuaHan'])->name('kiem-tra-qua-han');
        Route::post('/upload-file-xuat', [XuatHangController::class, 'uploadFileXuatAjax'])->name('upload-file-xuat');
        Route::post('/thay-doi-cong-chuc-xuat', [XuatHangController::class, 'thayDoiCongChucXuat'])->name('thay-doi-cong-chuc-xuat');


        Route::get('/getPhieuXuatChoDuyetCuaPTVT', [XuatHangController::class, 'getPhieuXuatChoDuyetCuaPTVT'])->name('getPhieuXuatChoDuyetCuaPTVT');
        Route::get('/getPhieuXuatDaXuatHangCuaPTVT', [XuatHangController::class, 'getPhieuXuatDaXuatHangCuaPTVT'])->name('getPhieuXuatDaXuatHangCuaPTVT');

        Route::get('/getXuatHangChoDuyets', [XuatHangController::class, 'getXuatHangChoDuyets'])->name('getXuatHangChoDuyets');
        Route::get('/getXuatHangDaDuyets', [XuatHangController::class, 'getXuatHangDaDuyets'])->name('getXuatHangDaDuyets');
    });

    Route::name('xuat-canh.')->group(function () {
        Route::get('/quan-ly-xuat-canh', [XuatCanhController::class, 'danhSachToKhai'])->name('quan-ly-xuat-canh');

        Route::get('/them-to-khai-xuat-canh', [XuatCanhController::class, 'themToKhai'])->name('them-to-khai-xuat-canh');
        Route::post('/them-to-khai-xuat-canh-submit', [XuatCanhController::class, 'themXuatCanhSubmit'])->name('them-to-khai-xuat-canh-submit');

        Route::get('/thong-tin-xuat-canh/{ma_xuat_canh}', [XuatCanhController::class, 'thongTinXuatCanh'])->name('thong-tin-xuat-canh');
        Route::post('/duyet-to-khai-xc', [XuatCanhController::class, 'duyetXuatCanh'])->name('duyet-to-khai-xc');
        Route::get('/sua-to-khai-xc/{ma_xuat_canh}', [XuatCanhController::class, 'suaXuatCanh'])->name('sua-to-khai-xc');
        Route::post('/sua-to-khai-xc-submit', [XuatCanhController::class, 'suaXuatCanhSubmit'])->name('sua-to-khai-xc-submit');
        Route::post('/huy-to-khai-xc', [XuatCanhController::class, 'huyXuatCanh'])->name(name: 'huy-to-khai-xc');
        Route::post('/yeu-cau-huy-to-khai-xc', [XuatCanhController::class, 'yeuCauHuyXuatCanh'])->name(name: 'yeu-cau-huy-to-khai-xc');
        Route::post('/thu-hoi-yeu-cau-huy-xc', [XuatCanhController::class, 'thuHoiYeuCauHuyXuatCanh'])->name(name: 'thu-hoi-yeu-cau-huy-xc');
        Route::post('/duyet-thuc-xuat-xc', [XuatCanhController::class, 'duyetThucXuat'])->name('duyet-thuc-xuat-xc');
        Route::post('/thay-doi-cong-chuc-xuat-canh', [XuatCanhController::class, 'thayDoiCongChucXuatCanh'])->name('thay-doi-cong-chuc-xuat-canh');
        Route::get('/xem-yeu-cau-sua-xuat-canh/{ma_xuat_canh}', [XuatCanhController::class, 'xemYeuCauSuaXuatCanh'])->name('xem-yeu-cau-sua-xuat-canh');
        Route::post('/huy-yeu-cau-sua-xuat-canh/{ma_yeu_cau}', [XuatCanhController::class, 'huyYeuCauSuaXuatCanh'])->name('huy-yeu-cau-sua-xuat-canh');
        Route::post('/duyet-yeu-cau-sua-xuat-canh/{ma_yeu_cau}', [XuatCanhController::class, 'duyetSuaXuatCanh'])->name('duyet-yeu-cau-sua-xuat-canh');

        Route::get('/getPhieuXuats', [XuatCanhController::class, 'getPhieuXuats'])->name('getPhieuXuats');
        Route::get('/getDoanhNghiepsTrongCacPhieu', [XuatCanhController::class, 'getDoanhNghiepsTrongCacPhieu'])->name('getDoanhNghiepsTrongCacPhieu');

        Route::get('/getXuatCanhs', [XuatCanhController::class, 'getXuatCanhs'])->name('getXuatCanhs');

        Route::get('/export-to-khai-xuat-canh', [XuatCanhController::class, 'exportToKhaiXuatCanh'])->name('export-to-khai-xuat-canh');
    });
    Route::name('ban-giao.')->group(function () {
        Route::get('/quan-ly-ban-giao-ho-so', [BanGiaoHoSoController::class, 'danhSachBanGiaoHoSo'])->name('quan-ly-ban-giao-ho-so');
        Route::get('/them-ban-giao-ho-so', [BanGiaoHoSoController::class, 'themBanGiaoHoSo'])->name('them-ban-giao-ho-so');
        Route::post('/them-ban-giao-ho-so-submit', [BanGiaoHoSoController::class, 'themBanGiaoHoSoSubmit'])->name('them-ban-giao-ho-so-submit');
        Route::get('/thong-tin-ban-giao-ho-so/{ma_ban_giao}', [BanGiaoHoSoController::class, 'thongTinBanGiaoHoSo'])->name('thong-tin-ban-giao-ho-so');


        Route::get('/export-bien-ban-ban-giao/{ma_ban_giao}', [BanGiaoHoSoController::class, 'exportBienBanBanGiao'])->name('export-bien-ban-ban-giao');
        Route::get('/getToKhaiDaXuatHet', [BanGiaoHoSoController::class, 'getToKhaiDaXuatHet'])->name('getToKhaiDaXuatHet');
    });


    // Quản lý kho Routes Group
    Route::name('quan-ly-kho.')->group(function () {
        Route::get('/tra-cuu-container', [QuanLyKhoController::class, 'traCuuContainerIndex'])->name('tra-cuu-container-index');
        Route::post('/them-container', [QuanLyKhoController::class, 'themContainer'])->name('them-container');
        Route::get('/to-khai-trong-container/{so_container}', [QuanLyKhoController::class, 'danhSachToKhaiTrongContainer'])->name('to-khai-trong-container');
        Route::get('/thong-tin-hang', [QuanLyKhoController::class, 'thongTinHang'])->name('thong-tin-hang');
        Route::get('/getTraCuuContainer', [QuanLyKhoController::class, 'getTraCuuContainer'])->name('getTraCuuContainer');

        Route::get('/danh-sach-yeu-cau-tau-cont', [YeuCauTauContController::class, 'danhSachYeuCauTauCont'])->name('danh-sach-yeu-cau-tau-cont');
        Route::get('/them-yeu-cau-tau-cont', [YeuCauTauContController::class, 'themYeuCauTauCont'])->name('them-yeu-cau-tau-cont');
        Route::post('/them-yeu-cau-tau-cont-submit', [YeuCauTauContController::class, 'themYeuCauTauContSubmit'])->name('them-yeu-cau-tau-cont-submit');
        Route::get('/thong-tin-yeu-cau-tau-cont/{ma_yeu_cau}', [YeuCauTauContController::class, 'thongTinYeuCauTauCont'])->name('thong-tin-yeu-cau-tau-cont');
        Route::post('/duyet-yeu-cau-tau-cont', [YeuCauTauContController::class, 'duyetYeuCauTauCont'])->name('duyet-yeu-cau-tau-cont');
        Route::post('/huy-yeu-cau-tau-cont', [YeuCauTauContController::class, 'huyYeuCauTauCont'])->name(name: 'huy-yeu-cau-tau-cont');
        Route::get('/sua-yeu-cau-tau-cont/{ma_yeu_cau}', [YeuCauTauContController::class, 'suaYeuCauTauCont'])->name('sua-yeu-cau-tau-cont');
        Route::post('/sua-yeu-cau-tau-cont-submit', [YeuCauTauContController::class, 'suaYeuCauTauContSubmit'])->name(name: 'sua-yeu-cau-tau-cont-submit');
        // Route::post('/sua-seal-tau-cont', [YeuCauTauContController::class, 'suaSealTauCont'])->name(name: 'sua-seal-tau-cont');
        Route::get('/download-yeu-cau-tau-cont/{ma_yeu_cau}', [YeuCauTauContController::class, 'downloadFile'])->name('download-yeu-cau-tau-cont');
        Route::get('/xem-sua-yeu-cau-tau-cont/{ma_yeu_cau}', [YeuCauTauContController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-tau-cont');
        Route::post('/duyet-sua-yeu-cau-tau-cont', [YeuCauTauContController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-tau-cont');
        Route::post('/huy-sua-yeu-cau-tau-cont', [YeuCauTauContController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-tau-cont');
        Route::post('/huy-huy-yeu-cau-tau-cont', [YeuCauTauContController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-tau-cont');
        Route::post('/thay-doi-cong-chuc-tau-cont', [YeuCauTauContController::class, 'thayDoiCongChucTauCont'])->name('thay-doi-cong-chuc-tau-cont');
        Route::get('/getYeuCauTauCont', [YeuCauTauContController::class, 'getYeuCauTauCont'])->name('getYeuCauTauCont');


        Route::get('/danh-sach-yeu-cau-container', [YeuCauContainerController::class, 'danhSachYeuCauContainer'])->name('danh-sach-yeu-cau-container');
        Route::get('/them-yeu-cau-container', [YeuCauContainerController::class, 'themYeuCauContainer'])->name('them-yeu-cau-container');
        Route::post('/them-yeu-cau-container-submit', [YeuCauContainerController::class, 'themYeuCauContainerSubmit'])->name('them-yeu-cau-container-submit');
        Route::get('/thong-tin-yeu-cau/{ma_yeu_cau}', [YeuCauContainerController::class, 'thongTinYeuCauContainer'])->name('thong-tin-yeu-cau');
        Route::post('/duyet-yeu-cau-container', [YeuCauContainerController::class, 'duyetYeuCauContainer'])->name('duyet-yeu-cau-container');
        Route::post('/huy-yeu-cau-container', [YeuCauContainerController::class, 'huyYeuCauContainer'])->name(name: 'huy-yeu-cau-container');
        Route::get('/sua-yeu-cau-container/{ma_yeu_cau}', [YeuCauContainerController::class, 'suaYeuCauContainer'])->name('sua-yeu-cau-container');
        Route::post('/sua-yeu-cau-container-submit', [YeuCauContainerController::class, 'suaYeuCauContainerSubmit'])->name(name: 'sua-yeu-cau-container-submit');
        Route::get('/download-yeu-cau-container/{ma_yeu_cau}', [YeuCauContainerController::class, 'downloadFile'])->name('download-yeu-cau-container');
        Route::get('/xem-sua-yeu-cau-container/{ma_yeu_cau}', [YeuCauContainerController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-container');
        Route::post('/duyet-sua-yeu-cau-container', [YeuCauContainerController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-container');
        Route::post('/huy-sua-yeu-cau-container', [YeuCauContainerController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-container');
        Route::post('/huy-huy-yeu-cau-container', [YeuCauContainerController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-container');
        Route::post('/thay-doi-cong-chuc-container', [YeuCauContainerController::class, 'thayDoiCongChucContainer'])->name('thay-doi-cong-chuc-container');
        Route::get('/getYeuCauContainer', [YeuCauContainerController::class, 'getYeuCauContainer'])->name('getYeuCauContainer');


        Route::get('/danh-sach-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'danhSachYeuCauChuyenTau'])->name('danh-sach-yeu-cau-chuyen-tau');
        Route::get('/them-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'themYeuCauChuyenTau'])->name('them-yeu-cau-chuyen-tau');
        Route::post('/them-yeu-cau-chuyen-tau-submit', [YeuCauChuyenTauController::class, 'themYeuCauChuyenTauSubmit'])->name('them-yeu-cau-chuyen-tau-submit');
        Route::get('/thong-tin-yeu-cau-chuyen-tau/{ma_yeu_cau}', [YeuCauChuyenTauController::class, 'thongTinYeuCauChuyenTau'])->name('thong-tin-yeu-cau-chuyen-tau');
        Route::post('/duyet-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'duyetYeuCauChuyenTau'])->name('duyet-yeu-cau-chuyen-tau');
        Route::post('/huy-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'huyYeuCauChuyenTau'])->name(name: 'huy-yeu-cau-chuyen-tau');
        Route::get('/sua-yeu-cau-chuyen-tau/{ma_yeu_cau}', [YeuCauChuyenTauController::class, 'suaYeuCauChuyenTau'])->name('sua-yeu-cau-chuyen-tau');
        Route::post('/sua-yeu-cau-chuyen-tau-submit', [YeuCauChuyenTauController::class, 'suaYeuCauChuyenTauSubmit'])->name(name: 'sua-yeu-cau-chuyen-tau-submit');
        Route::get('/download-yeu-cau-chuyen-tau/{ma_yeu_cau}', [YeuCauChuyenTauController::class, 'downloadFile'])->name('download-yeu-cau-chuyen-tau');
        Route::get('/xem-sua-yeu-cau-chuyen-tau/{ma_yeu_cau}', [YeuCauChuyenTauController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-chuyen-tau');
        Route::post('/duyet-sua-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-chuyen-tau');
        Route::post('/huy-sua-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-chuyen-tau');
        Route::post('/huy-huy-yeu-cau-chuyen-tau', [YeuCauChuyenTauController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-chuyen-tau');
        Route::post('/thay-doi-cong-chuc-chuyen-tau', [YeuCauChuyenTauController::class, 'thayDoiCongChucChuyenTau'])->name('thay-doi-cong-chuc-chuyen-tau');
        Route::get('/getYeuCauChuyenTau', [YeuCauChuyenTauController::class, 'getYeuCauChuyenTau'])->name('getYeuCauChuyenTau');


        Route::get('/danh-sach-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'danhSachYeuCauKiemTra'])->name('danh-sach-yeu-cau-kiem-tra');
        Route::get('/them-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'themYeuCauKiemTra'])->name('them-yeu-cau-kiem-tra');
        Route::post('/them-yeu-cau-kiem-tra-submit', [YeuCauKiemTraController::class, 'themYeuCauKiemTraSubmit'])->name('them-yeu-cau-kiem-tra-submit');
        Route::get('/thong-tin-yeu-cau-kiem-tra/{ma_yeu_cau}', [YeuCauKiemTraController::class, 'thongTinYeuCauKiemTra'])->name('thong-tin-yeu-cau-kiem-tra');
        Route::post('/duyet-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'duyetYeuCauKiemTra'])->name('duyet-yeu-cau-kiem-tra');
        Route::post('/huy-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'huyYeuCauKiemTra'])->name(name: 'huy-yeu-cau-kiem-tra');
        Route::get('/sua-yeu-cau-kiem-tra/{ma_yeu_cau}', [YeuCauKiemTraController::class, 'suaYeuCauKiemTra'])->name('sua-yeu-cau-kiem-tra');
        Route::post('/sua-yeu-cau-kiem-tra-submit', [YeuCauKiemTraController::class, 'suaYeuCauKiemTraSubmit'])->name(name: 'sua-yeu-cau-kiem-tra-submit');
        // Route::post('/sua-seal-kiem-tra', [YeuCauKiemTraController::class, 'suaSealKiemTra'])->name(name: 'sua-seal-kiem-tra');
        Route::get('/download-yeu-cau-kiem-tra/{ma_yeu_cau}', [YeuCauKiemTraController::class, 'downloadFile'])->name('download-yeu-cau-kiem-tra');
        Route::get('/xem-sua-yeu-cau-kiem-tra/{ma_yeu_cau}', [YeuCauKiemTraController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-kiem-tra');
        Route::post('/duyet-sua-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-kiem-tra');
        Route::post('/huy-sua-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-kiem-tra');
        Route::post('/huy-huy-yeu-cau-kiem-tra', [YeuCauKiemTraController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-kiem-tra');
        Route::post('/thay-doi-cong-chuc-kiem-tra', [YeuCauKiemTraController::class, 'thayDoiCongChucKiemTra'])->name('thay-doi-cong-chuc-kiem-tra');
        Route::get('/getYeuCauKiemTra', [YeuCauKiemTraController::class, 'getYeuCauKiemTra'])->name('getYeuCauKiemTra');

        

        Route::get('/danh-sach-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'danhSachYeuCauNiemPhong'])->name('danh-sach-yeu-cau-niem-phong');
        Route::get('/them-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'themYeuCauNiemPhong'])->name('them-yeu-cau-niem-phong');
        Route::post('/them-yeu-cau-niem-phong-submit', [YeuCauNiemPhongController::class, 'themYeuCauNiemPhongSubmit'])->name('them-yeu-cau-niem-phong-submit');
        Route::get('/thong-tin-yeu-cau-niem-phong/{ma_yeu_cau}', [YeuCauNiemPhongController::class, 'thongTinYeuCauNiemPhong'])->name('thong-tin-yeu-cau-niem-phong');
        Route::post('/duyet-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'duyetYeuCauNiemPhong'])->name('duyet-yeu-cau-niem-phong');
        Route::post('/huy-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'huyYeuCauNiemPhong'])->name(name: 'huy-yeu-cau-niem-phong');
        Route::get('/sua-yeu-cau-niem-phong/{ma_yeu_cau}', [YeuCauNiemPhongController::class, 'suaYeuCauNiemPhong'])->name('sua-yeu-cau-niem-phong');
        Route::post('/sua-yeu-cau-niem-phong-submit', [YeuCauNiemPhongController::class, 'suaYeuCauNiemPhongSubmit'])->name('sua-yeu-cau-niem-phong-submit');
        Route::post('/sua-seal-niem-phong', [YeuCauNiemPhongController::class, 'suaSealNiemPhong'])->name(name: 'sua-seal-niem-phong');
        Route::post('/huy-huy-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-niem-phong');
        Route::get('/get-so-container', [YeuCauNiemPhongController::class, 'getSoContainer'])->name('get-so-container');
        Route::get('/in-yeu-cau-niem-phong', [YeuCauNiemPhongController::class, 'inYeuCauNiemPhong'])->name('in-yeu-cau-niem-phong');
        Route::get('/getYeuCauNiemPhong', [YeuCauNiemPhongController::class, 'getYeuCauNiemPhong'])->name('getYeuCauNiemPhong');



        Route::get('/danh-sach-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'danhSachYeuCauTieuHuy'])->name('danh-sach-yeu-cau-tieu-huy');
        Route::get('/them-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'themYeuCauTieuHuy'])->name('them-yeu-cau-tieu-huy');
        Route::post('/them-yeu-cau-tieu-huy-submit', [YeuCauTieuHuyController::class, 'themYeuCauTieuHuySubmit'])->name('them-yeu-cau-tieu-huy-submit');
        Route::get('/thong-tin-yeu-cau-tieu-huy/{ma_yeu_cau}', [YeuCauTieuHuyController::class, 'thongTinYeuCauTieuHuy'])->name('thong-tin-yeu-cau-tieu-huy');
        Route::post('/duyet-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'duyetYeuCauTieuHuy'])->name('duyet-yeu-cau-tieu-huy');
        Route::post('/huy-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'huyYeuCauTieuHuy'])->name(name: 'huy-yeu-cau-tieu-huy');
        Route::get('/sua-yeu-cau-tieu-huy/{ma_yeu_cau}', [YeuCauTieuHuyController::class, 'suaYeuCauTieuHuy'])->name('sua-yeu-cau-tieu-huy');
        Route::post('/sua-yeu-cau-tieu-huy-submit', [YeuCauTieuHuyController::class, 'suaYeuCauTieuHuySubmit'])->name(name: 'sua-yeu-cau-tieu-huy-submit');
        Route::get('/download-yeu-cau-tieu-huy/{ma_yeu_cau}', [YeuCauTieuHuyController::class, 'downloadFile'])->name('download-yeu-cau-tieu-huy');
        Route::get('/xem-sua-yeu-cau-tieu-huy/{ma_yeu_cau}', [YeuCauTieuHuyController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-tieu-huy');
        Route::post('/duyet-sua-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-tieu-huy');
        Route::post('/huy-sua-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-tieu-huy');
        Route::post('/huy-huy-yeu-cau-tieu-huy', [YeuCauTieuHuyController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-tieu-huy');
        Route::post('/duyet-hoan-thanh-tieu-huy', [YeuCauTieuHuyController::class, 'duyetHoanThanh'])->name('duyet-hoan-thanh-tieu-huy');
        Route::post('/thay-doi-cong-chuc-tieu-huy', [YeuCauTieuHuyController::class, 'thayDoiCongChucTieuHuy'])->name('thay-doi-cong-chuc-tieu-huy');
        Route::get('/getYeuCauTieuHuy', [YeuCauTieuHuyController::class, 'getYeuCauTieuHuy'])->name('getYeuCauTieuHuy');


        Route::get('/danh-sach-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'danhSachYeuCauHangVeKho'])->name('danh-sach-yeu-cau-hang-ve-kho');
        Route::get('/them-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'themYeuCauHangVeKho'])->name('them-yeu-cau-hang-ve-kho');
        Route::post('/them-yeu-cau-hang-ve-kho-submit', [YeuCauHangVeKhoController::class, 'themYeuCauHangVeKhoSubmit'])->name('them-yeu-cau-hang-ve-kho-submit');
        Route::get('/thong-tin-yeu-cau-hang-ve-kho/{ma_yeu_cau}', [YeuCauHangVeKhoController::class, 'thongTinYeuCauHangVeKho'])->name('thong-tin-yeu-cau-hang-ve-kho');
        Route::post('/duyet-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'duyetYeuCauHangVeKho'])->name('duyet-yeu-cau-hang-ve-kho');
        Route::post('/huy-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'huyYeuCauHangVeKho'])->name(name: 'huy-yeu-cau-hang-ve-kho');
        Route::get('/sua-yeu-cau-hang-ve-kho/{ma_yeu_cau}', [YeuCauHangVeKhoController::class, 'suaYeuCauHangVeKho'])->name('sua-yeu-cau-hang-ve-kho');
        Route::post('/sua-yeu-cau-hang-ve-kho-submit', [YeuCauHangVeKhoController::class, 'suaYeuCauHangVeKhoSubmit'])->name(name: 'sua-yeu-cau-hang-ve-kho-submit');
        Route::get('/download-yeu-cau-hang-ve-kho/{ma_yeu_cau}', [YeuCauHangVeKhoController::class, 'downloadFile'])->name('download-yeu-cau-hang-ve-kho');
        Route::get('/xem-sua-yeu-cau-hang-ve-kho/{ma_yeu_cau}', [YeuCauHangVeKhoController::class, 'xemSuaYeuCau'])->name('xem-sua-yeu-cau-hang-ve-kho');
        Route::post('/duyet-sua-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'duyetSuaYeuCau'])->name('duyet-sua-yeu-cau-hang-ve-kho');
        Route::post('/huy-sua-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'huySuaYeuCau'])->name('huy-sua-yeu-cau-hang-ve-kho');
        Route::post('/huy-huy-yeu-cau-hang-ve-kho', [YeuCauHangVeKhoController::class, 'huyHuyYeuCau'])->name('huy-huy-yeu-cau-hang-ve-kho');
        Route::post('/thay-doi-cong-chuc-hang-ve-kho', [YeuCauHangVeKhoController::class, 'thayDoiCongChucHangVeKho'])->name('thay-doi-cong-chuc-hang-ve-kho');
        Route::get('/getYeuCauHangVeKho', [YeuCauHangVeKhoController::class, 'getYeuCauHangVeKho'])->name('getYeuCauHangVeKho');


        Route::get('/in-phieu-chuyen-tau-cont/{ma_yeu_cau}', [WordReportController::class, 'inPhieuChuyenTauCont'])->name('in-phieu-chuyen-tau-cont');
        Route::get('/in-phieu-chuyen-container/{ma_yeu_cau}', [WordReportController::class, 'inPhieuChuyenContainer'])->name('in-phieu-chuyen-container');
        Route::get('/in-phieu-chuyen-tau/{ma_yeu_cau}', [WordReportController::class, 'inPhieuChuyenTau'])->name('in-phieu-chuyen-tau');
        Route::get('/in-phieu-kiem-tra-hang/{ma_yeu_cau}', [WordReportController::class, 'inPhieuKiemTraHang'])->name('in-phieu-kiem-tra-hang');

        Route::get('/danh-sach-yeu-cau-gia-han', [YeuCauGiaHanController::class, 'danhSachYeuCauGiaHan'])->name('danh-sach-yeu-cau-gia-han');
        Route::get('/them-yeu-cau-gia-han', [YeuCauGiaHanController::class, 'themYeuCauGiaHan'])->name('them-yeu-cau-gia-han');
        Route::post('/them-yeu-cau-gia-han-submit', [YeuCauGiaHanController::class, 'themYeuCauGiaHanSubmit'])->name('them-yeu-cau-gia-han-submit');
        Route::get('/thong-tin-yeu-cau-gia-han/{ma_yeu_cau}', [YeuCauGiaHanController::class, 'thongTinYeuCauGiaHan'])->name('thong-tin-yeu-cau-gia-han');
        Route::post('/duyet-yeu-cau-gia-han', [YeuCauGiaHanController::class, 'duyetYeuCauGiaHan'])->name('duyet-yeu-cau-gia-han');
        Route::post('/huy-yeu-cau-gia-han', [YeuCauGiaHanController::class, 'huyYeuCauGiaHan'])->name(name: 'huy-yeu-cau-gia-han');
        Route::get('/sua-yeu-cau-gia-han/{ma_yeu_cau}', [YeuCauGiaHanController::class, 'suaYeuCauGiaHan'])->name('sua-yeu-cau-gia-han');
        Route::post('/sua-yeu-cau-gia-han-submit', [YeuCauGiaHanController::class, 'suaYeuCauGiaHanSubmit'])->name(name: 'sua-yeu-cau-gia-han-submit');

        Route::get('/get-to-khai-items', [QuanLyKhoController::class, 'getToKhaiItems'])->name('getToKhaiItems');
        Route::get('/get-so_luong-trong-container/{soContainerMoi}', [QuanLyKhoController::class, 'getSoLuongTrongContainer'])->name('getSoLuongTrongContainer');
        Route::get('/get-to-khai-items2', [QuanLyKhoController::class, 'getToKhaiItems2'])->name('getToKhaiItems2');
        Route::get('/get-to-khai-kiem-tra', [QuanLyKhoController::class, 'getToKhaiKiemTra'])->name('getToKhaiKiemTra');
        Route::get('/get-to-khai-trong-cont', [QuanLyKhoController::class, 'getToKhaiTrongCont'])->name('getToKhaiTrongCont');
        Route::get('/get-to-khai-trong-tau-cont', [QuanLyKhoController::class, 'getToKhaiTrongTauCont'])->name('getToKhaiTrongTauCont');
        Route::get('/kiem-tra-container-dang-chuyen', [QuanLyKhoController::class, 'kiemTraContainerDangChuyen'])->name('kiemTraContainerDangChuyen');
        Route::get('/kiem-tra-container-dang-chuyen-sua', [QuanLyKhoController::class, 'kiemTraContainerDangChuyenSua'])->name('kiemTraContainerDangChuyenSua');
        Route::get('/get-hang-trong-to-khai', [QuanLyKhoController::class, 'getHangTrongToKhai'])->name('getHangTrongToKhai');


        Route::get('/get-ten-ptvt', [QuanLyKhoController::class, 'getTenPTVT'])->name('getTenPTVT');
        Route::get('/get-seals', [QuanLyKhoController::class, 'getSeals'])->name('getSeals');


        Route::post('/move-db/{so_to_khai_nhap}', [PTVanTaiController::class, 'moveDatabase']);
    });



    // Báo cáo Routes Group
    Route::name('export.')->group(function () {
        // Báo cáo
        Route::get('/phieu-xuat-theo-xuong', [BaoCaoController::class, 'phieuXuatTheoXuong'])->name('phieu-xuat-theo-xuong');
        Route::get('/bao-cao-hang-ton', [BaoCaoController::class, 'index'])->name('bao-cao-hang-ton');
        Route::get('/hang-ton-chu-hang', [BaoCaoController::class, 'hangTonChuHang'])->name('hang-ton-chu-hang');
        Route::get('/hang-ton-theo-to-khai', [BaoCaoController::class, 'hangTonTheoToKhai'])->name('hang-ton-theo-to-khai');
        Route::get('/theo-doi-tru-lui', [BaoCaoController::class, 'theoDoiTruLui'])->name('theo-doi-tru-lui');
        Route::get('/tiep-nhan-hang-ngay', [BaoCaoController::class, 'tiepNhanHangNgay'])->name('tiep-nhan-hang-ngay');
        Route::get('/chi-tiet-xnk-trong-ngay', [BaoCaoController::class, 'chiTietXNKTrongNgay'])->name('chi-tiet-xnk-trong-ngay');
        Route::get('/doanh-nghiep-xnk', [BaoCaoController::class, 'doanhNghiepXNK'])->name('doanh-nghiep-xnk');
        Route::get('/chuyen-cua-khau-xuat', [BaoCaoController::class, 'chuyenCuaKhauXuat'])->name('chuyen-cua-khau-xuat');
        Route::get('/hang-ton-tai-cang', [BaoCaoController::class, 'hangTonTaiCang'])->name('hang-ton-tai-cang');
        Route::get('/so-luong-container-tai-cang', [BaoCaoController::class, 'containerLuuTaiCang'])->name('so-luong-container-tai-cang');
        Route::get('/so-luong-tau-tai-cang', [BaoCaoController::class, 'tauLuuTaiCang'])->name('so-luong-tau-tai-cang');
        Route::get('/so-luong-container-theo-cont', [BaoCaoController::class, 'containerLuuTaiCangTheoCont'])->name('so-luong-container-theo-cont');
        Route::get('/hang-hoa-chua-thuc-xuat', [BaoCaoController::class, 'hangHoaChuaThucXuat'])->name('hang-hoa-chua-thuc-xuat');
        Route::get('/bao-cao-hang-hoa-xuat-nhap-khau', [WordReportController::class, 'baoCaoHangHoaXuatNhapKhau'])->name('bao-cao-hang-hoa-xuat-nhap-khau');
        Route::get('/so-luong-to-khai-xuat-het', [BaoCaoController::class, 'soLuongToKhaiXuatHet'])->name('so-luong-to-khai-xuat-het');
        Route::get('/ban-giao-ho-so', [BaoCaoController::class, 'BanGiaoHoSo'])->name('ban-giao-ho-so');
        Route::get('/theo-doi-hang-hoa', [BaoCaoController::class, 'theoDoiHangHoa'])->name('theo-doi-hang-hoa');
        Route::get('/theo-doi-hang-hoa-tong', [BaoCaoController::class, 'theoDoiHangHoaTong'])->name('theo-doi-hang-hoa-tong');
        Route::get('/dang-ky-xuat-khau-hang-hoa', [BaoCaoController::class, 'baoCaoDangKyXuatKhauHangHoa'])->name('dang-ky-xuat-khau-hang-hoa');
        Route::get('/bao-cao-sang-cont-chuyen-tau', [BaoCaoController::class, 'sangContChuyenTau'])->name('bao-cao-sang-cont-chuyen-tau');
        Route::get('/bao-cao-giam-sat-xuat-khau', [BaoCaoController::class, 'giamSatXuatKhau'])->name('bao-cao-giam-sat-xuat-khau');
        Route::get('/bao-cao-su-dung-seal', [BaoCaoController::class, 'suDungSeal'])->name('bao-cao-su-dung-seal');

        //DoanhNghiep
        Route::get('/theo-doi-tru-lui-tung-lan', [BaoCaoController::class, 'theoDoiTruLuiTungLan'])->name('theo-doi-tru-lui-tung-lan');
        Route::get('/theo-doi-tru-lui-theo-ngay', [BaoCaoController::class, 'theoDoiTruLuiTheoNgay'])->name('theo-doi-tru-lui-theo-ngay');
        Route::get('/theo-doi-tru-lui-theo-ngay-zip', [BaoCaoController::class, 'theoDoiTruLuiTheoNgayZip'])->name('theo-doi-tru-lui-theo-ngay-zip');
        Route::get('/theo-doi-tru-lui-tat-ca', [BaoCaoController::class, 'theoDoiTruLuiTatCa'])->name('theo-doi-tru-lui-tat-ca');
        Route::get('/theo-doi-tru-lui-cuoi-ngay', [BaoCaoController::class, 'theoDoiTruLuiCuoiNgay'])->name('theo-doi-tru-lui-cuoi-ngay');
        Route::get('/bao-cao-hang-theo-doanh-nghiep', [BaoCaoController::class, 'baoCaoTheoDoanhNghiep'])->name('bao-cao-theo-doanh-nghiep');
        Route::get('/chi-tiet-xnk-theo-dn', [BaoCaoController::class, 'chiTietXNKTheoDN'])->name('chi-tiet-xnk-theo-dn');
        Route::get('/doanh-nghiep-xnk-theo-dn', [BaoCaoController::class, 'doanhNghiepXNKTheoDN'])->name('doanh-nghiep-xnk-theo-dn');
        Route::get('/hang-ton-doanh-nghiep', [BaoCaoController::class, 'hangTonDoanhNghiep'])->name('hang-ton-doanh-nghiep');
        Route::get('/phieu-xuat-theo-doanh-nghiep', [BaoCaoController::class, 'phieuXuatTheoDoanhNghiep'])->name('phieu-xuat-theo-doanh-nghiep');
        Route::get('/bao-cao-cap-hai', [BaoCaoController::class, 'baoCaoCapHai'])->name('bao-cao-cap-hai');
        Route::get('/to-khai-xuat-het-doanh-nghiep', [BaoCaoController::class, 'toKhaiXuatHetDoanhNghiep'])->name('to-khai-xuat-het-doanh-nghiep');

        //API
        Route::get('/get-hang-hoa/{so_to_khai_nhap}', [BaoCaoController::class, 'getHangHoa']);
        Route::get('/get-lan-tru-lui/{so_to_khai_nhap}', [BaoCaoController::class, 'getLanTruLui']);
    });

    // Quản lý phương tiện vận tải
    Route::name('phuong-tien-vt.')->group(function () {
        Route::get('/danh-sach-ptvt-xc', [PTVanTaiController::class, 'danhsachPTVTXC'])->name('danh-sach-ptvt-xc');
        Route::get('/them-to-khai-ptvt-xc', [PTVanTaiController::class, 'themPTVTXC'])->name('them-to-khai-ptvt-xc');
        Route::post('/them-to-khai-ptvt-xc-submit', [PTVanTaiController::class, 'themPTVTXCSubmit'])->name('them-to-khai-ptvt-xc-submit');
        Route::get('/sua-to-khai-ptvt-xc/{so_ptvt_xuat_canh}', [PTVanTaiController::class, 'suaPTVTXC'])->name('sua-to-khai-ptvt-xc');
        Route::post('/sua-to-khai-ptvt-xc-submit', [PTVanTaiController::class, 'suaPTVTXCSubmit'])->name('sua-to-khai-ptvt-xc-submit');
        Route::get('/thong-tin-ptvt-xc/{so_ptvt_xuat_canh}', [PTVanTaiController::class, 'thongTinPTVTXC'])->name('thong-tin-ptvt-xc');
        Route::post('/huy-to-khai-ptvt-xc', [PTVanTaiController::class, 'huyPTVTXC'])->name('huy-to-khai-ptvt-xc');

        Route::get('/danh-sach-to-khai-ptvt', [PTVanTaiController::class, 'danhsachPTVT'])->name('danh-sach-to-khai-ptvt');
        Route::get('/them-to-khai-ptvt', [PTVanTaiController::class, 'themToKhaiPTVT'])->name('them-to-khai-ptvt');
        Route::post('/submit-to-khai-ptvt', [PTVanTaiController::class, 'themToKhaiPTVTSubmit'])->name('them-to-khai-ptvt-submit');
        Route::get('/thong-tin-to-khai-ptvt/{so_to_khai_ptvt}', [PTVanTaiController::class, 'thongTinPTVT'])->name('thong-tin-to-khai-ptvt');
        Route::post('/duyet-to-khai-ptvt/{so_to_khai_ptvt}', [PTVanTaiController::class, 'duyetToKhaiPTVT'])->name('duyet-to-khai-ptvt');
        Route::post('/huy-to-khai-ptvt', [PTVanTaiController::class, 'huyToKhaiPTVT'])->name('huy-to-khai-ptvt');
        Route::post('/xin-huy-to-khai-ptvt', [PTVanTaiController::class, 'xinHuyToKhaiPTVT'])->name('xin-huy-to-khai-ptvt');
        Route::post('/to-choi-huy-to-khai-ptvt', [PTVanTaiController::class, 'tuChoiHuyToKhaiPTVT'])->name('tu-choi-huy-to-khai-ptvt');
        Route::post('/hoan-thanh-to-khai-ptvt', [PTVanTaiController::class, 'hoanThanhToKhaiPTVT'])->name('hoan-thanh-to-khai-ptvt');
    });
});
Route::middleware([\App\Http\Middleware\CheckRoleThuKho::class])->group(function () {
    Route::name('quan-ly-khac.')->group(function () {
        Route::get('/quan-ly-seal-dien-tu', [SealNiemPhongController::class, 'danhSachSealDienTu'])->name('danh-sach-seal-dien-tu');
        Route::get('/quan-ly-chi-niem-phong', [SealNiemPhongController::class, 'danhSachChiNiemPhong'])->name('danh-sach-chi-niem-phong');
        Route::get('/getChiNiemPhong', [SealNiemPhongController::class, 'getChiNiemPhong'])->name('getChiNiemPhong');

        

        Route::post('/xoa-seal', [SealNiemPhongController::class, 'xoaSeal'])->name('xoa-seal');
        Route::post('/them-chi-niem-phong', [SealNiemPhongController::class, 'themChiNiemPhong'])->name('them-chi-niem-phong');
        Route::get('/get-seal-items', [SealNiemPhongController::class, 'getSealItems']);
    });
});
// Route::middleware([\App\Http\Middleware\CheckRoleLanhDao::class])->group(function () {
//     Route::name('lanh-dao.')->group(function () {
//         Route::get('/quan-ly-duyet-lan-hai', [DuyetLanHaiController::class, 'quanLyDuyetLanHai'])->name('quan-ly-duyet-lan-hai');
//     });
// });

Route::middleware([\App\Http\Middleware\CheckRoleAdmin::class])->group(function () {
    Route::name('quan-ly-khac.')->group(function () {
        Route::get('/quan-ly-hai-quan', [HaiQuanController::class, 'danhSachHaiQuan'])->name('danh-sach-hai-quan');
        Route::post('/them-hai-quan', [HaiQuanController::class, 'themHaiQuan'])->name('them-hai-quan');
        Route::post('/xoa-hai-quan', [HaiQuanController::class, 'xoaHaiQuan'])->name('xoa-hai-quan');



        Route::get('/quan-ly-doanh-nghiep', [DoanhNghiepController::class, 'danhSachDoanhNghiep'])->name('danh-sach-doanh-nghiep');
        Route::post('/them-doanh-nghiep', [DoanhNghiepController::class, 'themDoanhNghiep'])->name('them-doanh-nghiep');
        Route::post('/update-doanh-nghiep', [DoanhNghiepController::class, 'updateDoanhNghiep'])->name('update-doanh-nghiep');
        Route::post('/xoa-doanh-nghiep', [DoanhNghiepController::class, 'xoaDoanhNghiep'])->name('xoa-doanh-nghiep');

        Route::get('/quan-ly-chu-hang', [ChuHangController::class, 'danhSachChuHang'])->name('danh-sach-chu-hang');
        Route::post('/them-chu-hang', [ChuHangController::class, 'themChuHang'])->name('them-chu-hang');
        Route::post('/update-chu-hang', [ChuHangController::class, 'updateChuHang'])->name('update-chu-hang');
        Route::post('/xoa-chu-hang', [ChuHangController::class, 'xoaChuHang'])->name('xoa-chu-hang');

        Route::get('/quan-ly-tai-khoan', [TaiKhoanController::class, 'danhSachTaiKhoan'])->name('danh-sach-tai-khoan');
        Route::post('/them-tai-khoan', [TaiKhoanController::class, 'themTaiKhoan'])->name('them-tai-khoan');
        Route::post('/update-tai-khoan', [TaiKhoanController::class, 'updateTaiKhoan'])->name('update-tai-khoan');
        Route::post('/xoa-tai-khoan', [TaiKhoanController::class, 'xoaTaiKhoan'])->name('xoa-tai-khoan');

        Route::get('/quan-ly-cong-chuc', [CongChucController::class, 'danhSachCongChuc'])->name('danh-sach-cong-chuc');
        Route::post('/them-cong-chuc', [CongChucController::class, 'themCongChuc'])->name('them-cong-chuc');
        Route::post('/update-cong-chuc', [CongChucController::class, 'updateCongChuc'])->name('update-cong-chuc');
        Route::post('/xoa-cong-chuc', [CongChucController::class, 'xoaCongChuc'])->name('xoa-cong-chuc');
        Route::post('/phan-quyen-bao-cao', [CongChucController::class, 'phanQuyenBaoCao'])->name('phan-quyen-bao-cao');
        Route::get('/get-phan-quyen-bao-cao', [CongChucController::class, 'getPhanQuyenBaoCao'])->name('get-phan-quyen-bao-cao');

        Route::get('/quan-ly-thu-kho', [ThuKhoController::class, 'danhSachThuKho'])->name('danh-sach-thu-kho');
        Route::post('/them-thu-kho', [ThuKhoController::class, 'themThuKho'])->name('them-thu-kho');
        Route::post('/update-thu-kho', [ThuKhoController::class, 'updateThuKho'])->name('update-thu-kho');
        Route::post('/xoa-thu-kho', [ThuKhoController::class, 'xoaThuKho'])->name('xoa-thu-kho');

        Route::get('/quan-ly-loai-hang', [LoaiHangController::class, 'danhSachLoaiHang'])->name('danh-sach-loai-hang');
        Route::post('/them-loai-hang', [LoaiHangController::class, 'themLoaiHang'])->name('them-loai-hang');
        Route::post('/xoa-loai-hang', [LoaiHangController::class, 'xoaLoaiHang'])->name('xoa-loai-hang');

        Route::get('/quan-ly-loai-hinh', [LoaiHinhController::class, 'danhSachLoaiHinh'])->name('danh-sach-loai-hinh');
        Route::post('/them-loai-hinh', [LoaiHinhController::class, 'themLoaiHinh'])->name('them-loai-hinh');
        Route::post('/xoa-loai-hinh', [LoaiHinhController::class, 'xoaLoaiHinh'])->name('xoa-loai-hinh');

        Route::get('/quan-ly-ttest', [LoaiHinhController::class, 'ttest']);
        Route::post('/xoa-theo-doi-hang', [LoaiHinhController::class, 'xoaTheoDoiHang'])->name('xoa-theo-doi-hang');
        Route::post('/xoa-theo-doi-tru-lui', [LoaiHinhController::class, 'xoaTheoDoiTruLui'])->name('xoa-theo-doi-tru-lui');

        Route::get('/quan-ly-doanh-nghiep-ql/{ma_doanh_nghiep}', [DoanhNghiepController::class, 'danhSachDoanhNghiepQL'])->name('danh-sach-doanh-nghiep-ql');
        Route::post('/them-doanh-nghiep-ql', [DoanhNghiepController::class, 'themDoanhNghiepQL'])->name('them-doanh-nghiep-ql');
        Route::post('/xoa-doanh-nghiep-ql', [DoanhNghiepController::class, 'xoaDoanhNghiepQL'])->name('xoa-doanh-nghiep-ql');

        Route::get('/quan-ly-thiet-bi-dang-nhap', [DeviceLoginController::class, 'danhSachDangNhap'])->name('quan-ly-thiet-bi-dang-nhap');
        Route::post('/update-timeout', [DeviceLoginController::class, 'updateTimeout'])->name('update-timeout');


    });
});
Route::name('tai-khoan.')->group(function () {
    Route::get('/thay-doi-mat-khau', [TaiKhoanController::class, 'thayDoiMatKhau'])->name('thay-doi-mat-khau');
    Route::post('/thay-doi-mat-khau-submit', [TaiKhoanController::class, 'thayDoiMatKhauSubmit'])->name('thay-doi-mat-khau-submit');
});
