<?php

namespace App\Http\Controllers;


use App\Models\DoanhNghiep;
use App\Models\DoanhNghiepQL;
use Illuminate\Http\Request;
use App\Models\NhapHang;
use App\Models\ChuHang;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

class DoanhNghiepController extends Controller
{
    public function danhSachDoanhNghiep()
    {
        $data = DoanhNghiep::leftJoin('chu_hang', 'doanh_nghiep.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
            ->get();
        $chuHangs = ChuHang::select('ma_chu_hang', 'ten_chu_hang')->get();
        return view('quan-ly-khac.danh-sach-doanh-nghiep',  data: compact('data', 'chuHangs'));
    }

    public function danhSachDoanhNghiepQL($ma_doanh_nghiep)
    {
        $data = DoanhNghiepQL::where('ma_doanh_nghiep_ql', $ma_doanh_nghiep)
            ->get();
        $DoanhNghiep = DoanhNghiep::find($ma_doanh_nghiep);
        $doanhNghieps = DoanhNghiep::all();
        return view('quan-ly-khac.danh-sach-doanh-nghiep-ql', data: compact('data', 'DoanhNghiep', 'doanhNghieps'));
    }
    public function themDoanhNghiep(Request $request)
    {
        if (DoanhNghiep::find($request->ma_doanh_nghiep)) {
            session()->flash('alert-danger', 'Mã doanh nghiệp này đã tồn tại.');
            return redirect()->back();
        } elseif (!TaiKhoan::where('ten_dang_nhap', $request->ma_doanh_nghiep)->get()->isEmpty()) {
            session()->flash('alert-danger', 'Mã doanh nghiệp này trùng tên đăng nhập của một tài khoản.');
            return redirect()->back();
        }
        $taiKhoan = TaiKhoan::create([
            'ten_dang_nhap' => $request->ma_doanh_nghiep,
            'mat_khau' => Hash::make($request->mat_khau),
            'loai_tai_khoan' => "Doanh nghiệp",
        ]);
        DoanhNghiep::create([
            'ma_doanh_nghiep' => $request->ma_doanh_nghiep,
            'ten_doanh_nghiep' => strtoupper($request->ten_doanh_nghiep),
            'dia_chi' => strtoupper($request->dia_chi),
            'ma_chu_hang' => $request->ma_chu_hang,
            'ma_tai_khoan' => $taiKhoan->ma_tai_khoan,
        ]);
        session()->flash('alert-success', 'Thêm doanh nghiệp mới thành công');
        return redirect()->back();
    }
    public function themDoanhNghiepQL(Request $request)
    {
        $doanhNghiepQL = DoanhNghiepQL::where('ma_doanh_nghiep_ql', $request->ma_doanh_nghiep_ql)
            ->where('ma_doanh_nghiep_khac', $request->ma_doanh_nghiep_khac)
            ->exists();
        if ($request->ma_doanh_nghiep_ql == $request->ma_doanh_nghiep_khac) {
            session()->flash('alert-danger', 'Mã doanh nghiệp quản lý không thể trùng với mã doanh nghiệp theo dõi.');
            return redirect()->back();
        } else if ($doanhNghiepQL) {
            session()->flash('alert-danger', 'Mã doanh nghiệp này đã tồn tại.');
            return redirect()->back();
        }
        DoanhNghiepQL::insert([
            'ma_doanh_nghiep_ql' => $request->ma_doanh_nghiep_ql,
            'ma_doanh_nghiep_khac' => $request->ma_doanh_nghiep_khac,
        ]);
        session()->flash('alert-success', 'Thêm theo dõi doanh nghiệp mới thành công');
        return redirect()->back();
    }

    public function xoaDoanhNghiep(Request $request)
    {
        $exists = NhapHang::where('ma_doanh_nghiep', $request->ma_doanh_nghiep)->exists();

        if (DoanhNghiep::find($request->ma_doanh_nghiep)) {
            if ($exists) {
                session()->flash('alert-danger', 'Không thể xóa doanh nghiệp do doanh nghiệp này ở còn xuất hiện trong một số tờ khai nhập đang được xử lý');
            } else {
                DoanhNghiep::find($request->ma_doanh_nghiep)->delete();
                session()->flash('alert-success', 'Xóa doanh nghiệp thành công');
            }
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }

    public function xoaDoanhNghiepQL(Request $request)
    {
        DoanhNghiepQL::where('ma_doanh_nghiep_ql', $request->ma_doanh_nghiep_ql)
            ->where('ma_doanh_nghiep_khac', $request->ma_doanh_nghiep_khac)
            ->first()
            ->delete();
        session()->flash('alert-success', 'Bỏ theo dõi doanh nghiệp thành công');
        return redirect()->back();
    }

    public function updateDoanhNghiep(Request $request)
    {
        if (DoanhNghiep::find($request->ma_doanh_nghiep)) {
            DoanhNghiep::find($request->ma_doanh_nghiep)->update(['ma_chu_hang' => $request->ma_chu_hang]);
            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect('/quan-ly-doanh-nghiep');
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }



}

