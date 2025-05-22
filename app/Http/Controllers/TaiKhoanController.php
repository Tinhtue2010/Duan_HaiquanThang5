<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\LienHe;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TaiKhoanController extends Controller
{
    public function dangNhap()
    {
        return view('dang-nhap');
    }

    public function submitDangNhap(Request $request)
    {
        $credentials = $request->validate([
            'ten_dang_nhap' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->loai_tai_khoan == "Cán bộ công chức") {
                if (!CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()) {
                    session()->flash('alert-danger', 'Tài khoản này chưa được gán cho cán bộ công chức nào');
                    return redirect()->back();
                }
                return redirect()->route('nhap-hang.quan-ly-nhap-hang');
            } elseif ($user->loai_tai_khoan == "Doanh nghiệp") {
                if (!DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()) {
                    session()->flash('alert-danger', 'Tài khoản này chưa được gán cho doanh nghiệp nào');
                    return redirect()->back();
                }
                return redirect()->route('nhap-hang.quan-ly-nhap-hang');
            } elseif ($user->loai_tai_khoan == "Thủ kho") {
                return redirect()->route('quan-ly-khac.danh-sach-chi-niem-phong');
            } elseif ($user->loai_tai_khoan == "Admin") {
                return redirect()->route('quan-ly-khac.danh-sach-hai-quan');
            } elseif ($user->loai_tai_khoan == "Lãnh đạo") {
                return redirect()->route('lanh-dao.quan-ly-duyet-xuat-hang');
            }
        }
        session()->flash('alert-danger', 'Tên đăng nhập hoặc tài khoản không đúng');
        return back();
    }

    public function dangXuat(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function danhSachTaiKhoan()
    {
        $data = TaiKhoan::orderBy('created_at', 'desc')->get();
        return view('quan-ly-khac.danh-sach-tai-khoan', data: compact(var_name: 'data'));
    }

    public function themTaiKhoan(Request $request)
    {
        if (!TaiKhoan::where('ten_dang_nhap', $request->ten_dang_nhap)->get()->isEmpty()) {
            session()->flash('alert-danger', 'Tên đăng nhập này đã tồn tại.');
            return redirect()->back();
        }
        if ($request->loai_tai_khoan == 'Công chức') {
            $loai_tai_khoan = 'Cán bộ công chức';
        } else {
            $loai_tai_khoan = $request->loai_tai_khoan;
        }
        TaiKhoan::create([
            'ten_dang_nhap' => $request->ten_dang_nhap,
            'mat_khau' => Hash::make(value: $request->mat_khau),
            'loai_tai_khoan' => $loai_tai_khoan,
        ]);
        session()->flash('alert-success', 'Thêm tài khoản mới thành công');
        return redirect()->back();
    }

    public function thayDoiMatKhau(Request $request)
    {
        return view('quan-ly-khac.thay-doi-mat-khau');
    }

    public function xoaTaiKhoan(Request $request)
    {
        if (TaiKhoan::find($request->ma_tai_khoan)) {
            TaiKhoan::find($request->ma_tai_khoan)->delete();
            session()->flash('alert-success', 'Xóa tài khoản thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }
    public function updateTaiKhoan(Request $request)
    {
        if (TaiKhoan::find($request->ma_tai_khoan)) {
            TaiKhoan::find($request->ma_tai_khoan)->update(['mat_khau' => Hash::make($request->mat_khau)]);;
            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }
    public function thayDoiMatKhauSubmit(Request $request)
    {
        if (TaiKhoan::find($request->ma_tai_khoan)) {
            $taiKhoan = TaiKhoan::find($request->ma_tai_khoan);
            if ($request->mat_khau != '') {
                $taiKhoan->update([
                    'mat_khau' => Hash::make($request->mat_khau)
                ]);
            }

            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }

    public function lienHeSubmit(Request $request)
    {
        LienHe::insert([
            'ten_ca_nhan' => $request->ten_ca_nhan,
            'email' => $request->email,
            'loi_nhan' => $request->loi_nhan,
            'ngay_tao' => now(),
        ]);
        session()->flash('alert-success', 'Gửi liên hệ thành công');
        return redirect()->back();
    }

    public function danhSachLienHe()
    {
        $data = LienHe::orderBy('ma_lien_he', 'desc')->get();

        return view('danh-sach-lien-he', data: compact(var_name: 'data'));
    }
}
