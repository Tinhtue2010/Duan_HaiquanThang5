<?php

namespace Database\Seeders;
use App\Models\ChuHang;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\PhuongTienVanTai;
use App\Models\PTVTXuatCanh;
use App\Models\TheoDoiTruLui;
use App\Models\TienTrinh;
use App\Models\ToKhaiPhuongTienVT;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SecondDB\NhapHangSecond;
use App\Models\SecondDB\HangHoaSecond;
use App\Models\SecondDB\HangTrongContSecond;
use App\Models\SecondDB\XuatHangContSecond;
use App\Models\SecondDB\XuatHangSecond;
use App\Models\TaiKhoan;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function moveDatabase($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        $hangHoas = HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->get();
        $xuatHangs = XuatHang::where('so_to_khai_nhap', $so_to_khai_nhap)->get();
        $xuatHangConts = XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();
        $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();

        DB::connection('mysql2')->transaction(function () use ($nhapHang, $hangHoas, $xuatHangs, $xuatHangConts, $hangTrongConts) {
            foreach ($hangHoas as $hangHoa) {
                HangHoaSecond::insert([
                    'ma_hang' => $hangHoa->ma_hang,
                    'ten_hang' => $hangHoa->ten_hang,
                    'loai_hang' => $hangHoa->loai_hang,
                    'xuat_xu' => $hangHoa->xuat_xu,
                    'trong_luong' => $hangHoa->trong_luong,
                    'don_vi_tinh' => $hangHoa->don_vi_tinh,
                    'don_gia' => $hangHoa->don_gia,
                    'tri_gia' => $hangHoa->tri_gia,
                    'so_luong_khai_bao' => $hangHoa->so_luong_khai_bao,
                    'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                ]);
            }
            foreach ($xuatHangConts as $xuatHangCont) {
                XuatHangContSecond::insert([
                    'ma_xuat_hang_cont' => $xuatHangCont->ma_xuat_hang_cont,
                    'so_to_khai_xuat' => $xuatHangCont->so_to_khai_xuat,
                    'ma_hang_cont' => $xuatHangCont->ma_xuat_hang_cont,
                    'so_luong_xuat' => $xuatHangCont->so_luong_xuat,
                    'so_luong_ton' => $xuatHangCont->so_luong_ton,
                    'so_luong_hien_tai' => $xuatHangCont->so_luong_hien_tai,
                    'so_container' => $xuatHangCont->so_container,
                    'tri_gia' => $xuatHangCont->tri_gia,
                ]);
            }
            foreach ($hangTrongConts as $hangTrongCont) {
                HangTrongContSecond::insert([
                    'ma_hang_cont' => $hangTrongCont->ma_hang_cont,
                    'ma_hang' => $hangTrongCont->ma_hang,
                    'so_container' => $hangTrongCont->so_container,
                    'so_luong' => $hangTrongCont->so_luong,
                    'tinh_trang' => $hangTrongCont->tinh_trang,
                ]);
            }
            foreach ($xuatHangs as $xuatHang) {
                XuatHangSecond::insert([
                    'so_to_khai_xuat' => $xuatHang->so_to_khai_xuat,
                    'so_to_khai_nhap' => $xuatHang->so_to_khai_nhap,
                    'ma_loai_hinh' => $xuatHang->ma_loai_hinh,
                    'lan_xuat_canh' => $xuatHang->lan_xuat_canh,
                    'ngay_dang_ky' => $xuatHang->ngay_dang_ky,
                    'ngay_het_han' => $xuatHang->ngay_het_han,
                    'ngay_thong_quan' => $xuatHang->ngay_thong_quan,
                    'ngay_xuat_canh' => $xuatHang->ngay_xuat_canh,
                    'trang_thai' => $xuatHang->trang_thai,
                    'so_to_khai_ptvt' => $xuatHang->so_to_khai_ptvt,
                    'ghi_chu' => $xuatHang->ghi_chu,
                ]);
            }
            NhapHangSecond::create([
                'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
                'ma_hai_quan' => $nhapHang->ma_hai_quan,
                'ma_doanh_nghiep' => $nhapHang->ma_doanh_nghiep,
                'ma_chu_hang' => $nhapHang->ma_chu_hang,
                'ma_loai_hinh' => $nhapHang->ma_loai_hinh,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ngay_thong_quan' => $nhapHang->ngay_thong_quan,
                'ngay_xuat_het' => $nhapHang->ngay_xuat_het,
                'trang_thai' => $nhapHang->trang_thai,
                'ghi_chu' => $nhapHang->ghi_chu,
                'so_container' => $nhapHang->so_container,
                'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
                'ptvt_ban_dau' => $nhapHang->ptvt_ban_dau,
                'ma_cong_chuc' => $nhapHang->ma_cong_chuc,
            ]);
        });


        HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
            ->delete();
        XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->delete();
        NhapHang::find($so_to_khai_nhap)->delete();
        HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
        XuatHang::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
    }

    public function run(): void
    {
        $this->moveDatabase(100001);
    }
}
