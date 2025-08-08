<?php

namespace App\Http\Controllers;

use App\Models\HaiQuan;
use Illuminate\Http\Request;
use App\Models\NhapHang;

class HaiQuanController extends Controller
{
    public function danhSachHaiQuan()
    {
        $data = HaiQuan::all();
        return view('quan-ly-khac.danh-sach-hai-quan', data: compact(var_name: 'data'));
    }
    public function themHaiQuan(Request $request)
    {
        if (HaiQuan::find($request->ma_hai_quan)) {
            session()->flash('alert-danger', 'Mã hải quan này đã tồn tại.');
            return redirect('/quan-ly-hai-quan');
        }

        HaiQuan::create([
            'ma_hai_quan' => $request->ma_hai_quan,
            'ten_hai_quan' => $request->ten_hai_quan,
        ]);
        session()->flash('alert-success', 'Thêm hải quan mới thành công');
        return redirect()->back();
    }
    public function xoaHaiQuan(Request $request)
    {
        $exists = NhapHang::where('ma_hai_quan', $request->ma_hai_quan)->exists();

        if (HaiQuan::find($request->ma_hai_quan)) {
            if ($exists) {
                session()->flash('alert-danger', 'Không thể xóa hải quan do hải quan này ở còn xuất hiện trong một số tờ khai nhập đang được xử lý');
            } else {
                HaiQuan::find($request->ma_hai_quan)->delete();
                session()->flash('alert-success', 'Xóa hải quan thành công');
            }
            return redirect()->back();
        }

        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }

    public function updateHaiQuan(Request $request)
    {
        $haiQuan = HaiQuan::find($request->ma_hai_quan);
        if ($haiQuan) {
            $haiQuan->ten_hai_quan = $request->ten_hai_quan;
            $haiQuan->save();
            session()->flash('alert-success', 'Cập nhật thông tin hải quan thành công');
        } else {
            session()->flash('alert-danger', 'Hải quan không tồn tại');
        }
        return redirect()->back();
    }


}
