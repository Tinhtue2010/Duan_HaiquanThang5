<?php

namespace App\Http\Controllers;

use App\Exports\ToKhaiNhapCanh;
use App\Exports\ToKhaiXuatCanh;
use App\Models\XuatCanhChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\LoaiHang;
use App\Models\PTVTXuatCanh;
use App\Models\ThuyenTruong;
use App\Models\NhapCanh;
use App\Models\NhapCanhSua;
use App\Models\XuatCanhChiTietSua;
use App\Models\XuatCanhSua;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Services\XuatCanhService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class NhapCanhController extends Controller
{
    public function danhSachToKhai()
    {
        $nhapCanhs = NhapCanh::orderBy('ma_nhap_canh', 'desc')->get();
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $nhapCanhs = NhapCanh::where('ma_doanh_nghiep', $this->getDoanhNghiepHienTai()->ma_doanh_nghiep)
                ->orderBy('ma_nhap_canh', 'desc')
                ->get();
        }
        return view('nhap-canh.quan-ly-nhap-canh', ['nhapCanhs' => $nhapCanhs]);
    }

    public function themToKhai()
    {
        if (Auth::user()->loai_tai_khoan !== "Doanh nghiệp") {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
        $thuyenTruongs = ThuyenTruong::all()->pluck("ten_thuyen_truong");
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();


        return view('nhap-canh.them-to-khai-nhap-canh', [
            'PTVTXuatCanhs' => PTVTXuatCanh::where('trang_thai', '2')->get(),
            'doanhNghiep' => $doanhNghiep,
            'maDoanhNghiep' => preg_replace('/\D/', '', $doanhNghiep->ma_doanh_nghiep),
            'thuyenTruongs' => $thuyenTruongs,
            'donViTinhs' => $this->getDonViTinh(),
            'loaiHangs' => LoaiHang::all(),
            'doanhNghieps' => DoanhNghiep::whereRaw("ma_doanh_nghiep REGEXP '^[0-9]+$'")->get(),
        ]);
    }

    public function themNhapCanhSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $date = Carbon::createFromFormat('d/m/Y', $request->ngay_dang_ky)->format('Y-m-d');
                $doanh_nghiep = $this->getDoanhNghiepHienTai();
                $nhapCanh = NhapCanh::create([
                    'ma_doanh_nghiep' => $doanh_nghiep->ma_doanh_nghiep,
                    'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
                    'ten_thuyen_truong' => $request->ten_thuyen_truong,
                    'so_luong' => $request->so_luong,
                    'loai_hang' => $request->loai_hang,
                    'don_vi_tinh' => $request->don_vi_tinh,
                    'trong_luong' => $request->trong_luong,
                    'ten_hang_hoa' => $request->ten_hang_hoa,
                    'ma_doanh_nghiep_chon' => $request->ma_doanh_nghiep,
                    'is_khong_hang' => $request->is_khong_hang,
                    'ngay_dang_ky' => $date,
                    'trang_thai' => "1",
                ]);

                $thuyenTruongs = ThuyenTruong::pluck("ten_thuyen_truong")->toArray();
                if (!in_array($request->ten_thuyen_truong, $thuyenTruongs)) {
                    ThuyenTruong::insert([
                        'ten_thuyen_truong' => $request->ten_thuyen_truong,
                    ]);
                }
                return redirect()
                    ->route('nhap-canh.thong-tin-nhap-canh', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh])
                    ->with('alert-success', 'Thêm tờ khai mới thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in themNhapCanhSubmit: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra trong hệ thống');
            return redirect()->back();
        }
    }

    public function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    public function thongTinNhapCanh($ma_nhap_canh)
    {
        if (NhapCanh::find($ma_nhap_canh)) {
            $nhapCanh = NhapCanh::find($ma_nhap_canh);
        }
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        $maCongChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_cong_chuc ?? '';
        return view('nhap-canh.thong-tin-nhap-canh', compact('nhapCanh', 'congChucs','maCongChuc')); // Pass data to the view
    }

    public function duyetNhapCanh(Request $request)
    {
        try {
            DB::beginTransaction();
            $nhapCanh = NhapCanh::find($request->ma_nhap_canh);
            $nhapCanh->trang_thai = "2";
            $nhapCanh->ma_cong_chuc = $request->ma_cong_chuc;
            $nhapCanh->ngay_duyet = now();
            $nhapCanh->save();

            DB::commit();
            session()->flash('alert-success', 'Duyệt tờ khai thành công!');
            return redirect()->route('nhap-canh.quan-ly-nhap-canh');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetNhapCanh: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function duyetThucNhap(Request $request)
    {
        try {
            DB::beginTransaction();
            $nhapCanh = NhapCanh::find($request->ma_nhap_canh);
            $nhapCanh->trang_thai = "3";
            $nhapCanh->save();

            DB::commit();
            session()->flash('alert-success', 'Duyệt thực xuất tờ khai thành công!');
            return redirect()->route('nhap-canh.quan-ly-nhap-canh');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetThucNhap: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function yeuCauHuyNhapCanh(Request $request)
    {
        $nhapCanh = NhapCanh::find($request->ma_nhap_canh);
        if ($nhapCanh) {

            if ($nhapCanh->trang_thai == '1') {
                $nhapCanh->trang_thai = '0';
            } elseif ($nhapCanh->trang_thai == '2') {
                $nhapCanh->trang_thai = '5';
            }

            $nhapCanh->ghi_chu = $request->ghi_chu;
            $nhapCanh->save();
            session()->flash('alert-success', 'Yêu cầu hủy nhập cảnh thành công!');
        }
        return redirect()->back();
    }

    public function thuHoiYeuCauHuyNhapCanh(Request $request)
    {
        $nhapCanh = NhapCanh::find($request->ma_nhap_canh);

        if ($nhapCanh) {
            if ($nhapCanh->trang_thai == '4') {
                $nhapCanh->trang_thai = '1';
            } elseif ($nhapCanh->trang_thai == '5') {
                $nhapCanh->trang_thai = '2';
            }

            $nhapCanh->ghi_chu = $request->ghi_chu;
            $nhapCanh->save();
            session()->flash('alert-success', 'Thu hồi yêu cầu hủy thành công!');
        }
        return redirect()->back();
    }

    public function huyNhapCanh(Request $request)
    {
        NhapCanh::find($request->ma_nhap_canh)->update([
            'trang_thai' => '0',
            'ghi_chu' => $request->ghi_chu
        ]);
        session()->flash('alert-success', 'Hủy tờ khai xuất cảnh thành công!');
        return redirect()->back();
    }
    public function suaNhapCanh($ma_nhap_canh)
    {
        $nhapCanh = NhapCanh::find($ma_nhap_canh);
        $thuyenTruongs = ThuyenTruong::all()->pluck("ten_thuyen_truong");
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

        return view('nhap-canh.sua-to-khai-nhap-canh', [
            'PTVTXuatCanhs' => PTVTXuatCanh::where('trang_thai', '2')->get(),
            'doanhNghiep' => $doanhNghiep,
            'thuyenTruongs' => $thuyenTruongs,
            'donViTinhs' => $this->getDonViTinh(),
            'loaiHangs' => LoaiHang::all(),
            'nhapCanh' => $nhapCanh,
            'doanhNghieps' => DoanhNghiep::whereRaw("ma_doanh_nghiep REGEXP '^[0-9]+$'")->get(),
        ]);
    }
    public function suaNhapCanhSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            $nhapCanh = NhapCanh::find($request->ma_nhap_canh);
            $trang_thai = $nhapCanh->trang_thai;
            $nhapCanhSua = $this->themNhapCanhSua($request, $nhapCanh);

            DB::commit();
            if ($trang_thai == '1') {
                $this->duyetSuaNhapCanh($nhapCanhSua->ma_yeu_cau);
            } else {
                $nhapCanh->trang_thai = '4';
                $nhapCanh->save();
            }
            session()->flash('alert-success', 'Thêm sửa tờ khai nhập cảnh thành công!');
            return redirect()->route('nhap-canh.thong-tin-nhap-canh', ['ma_nhap_canh' => $request->ma_nhap_canh]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in suaNhapCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function duyetSuaNhapCanh($ma_yeu_cau)
    {
        try {
            DB::beginTransaction();
            $nhapCanhSua = NhapCanhSua::find($ma_yeu_cau);
            $nhapCanh = NhapCanh::find($nhapCanhSua->ma_nhap_canh);
            if ($nhapCanh->trang_thai == '1') {
                $trang_thai = 1;
            } else {
                $trang_thai = 2;
            }
            NhapCanh::find($nhapCanhSua->ma_nhap_canh)->update([
                'trang_thai' => $trang_thai,
                'so_ptvt_xuat_canh' => $nhapCanhSua->so_ptvt_xuat_canh,
                'loai_hang' => $nhapCanhSua->loai_hang,
                'so_luong' => $nhapCanhSua->so_luong,
                'don_vi_tinh' => $nhapCanhSua->don_vi_tinh,
                'trong_luong' => $nhapCanhSua->trong_luong,
                'ten_hang_hoa' => $nhapCanhSua->ten_hang_hoa,
                'ma_doanh_nghiep_chon' => $nhapCanhSua->ma_doanh_nghiep,
                'ngay_dang_ky' => $nhapCanhSua->ngay_dang_ky,
                'ngay_duyet' => $nhapCanhSua->ngay_duyet,
                'ghi_chu' => $nhapCanhSua->ghi_chu,
                'ten_thuyen_truong' => $nhapCanhSua->ten_thuyen_truong,
                'is_khong_hang' => $nhapCanhSua->is_khong_hang,
            ]);
            $nhapCanhSua->delete();
            DB::commit();
            session()->flash('alert-success', 'Sửa tờ khai nhập cảnh thành công!');
            return redirect()->route('nhap-canh.thong-tin-nhap-canh', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaNhapCanhSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function themNhapCanhSua($request, $nhapCanh)
    {
        $doanh_nghiep = $this->getDoanhNghiepHienTai();
        $date = Carbon::createFromFormat('d/m/Y', $request->ngay_dang_ky)->format('Y-m-d');
        if ($nhapCanh->trang_thai == '4') {
            $nhapCanhSua =  NhapCanhSua::where('ma_nhap_canh', $nhapCanh->ma_nhap_canh)->orderBy('ma_yeu_cau', 'desc')->first();
            $nhapCanhSua->update([
                'ma_nhap_canh'  => $nhapCanh->ma_nhap_canh,
                'ma_doanh_nghiep' => $nhapCanh->ma_doanh_nghiep,
                'so_ptvt_xuat_canh' => $nhapCanh->so_ptvt_xuat_canh,
                'ten_thuyen_truong' => $request->ten_thuyen_truong,
                'so_luong' => $request->so_luong,
                'loai_hang' => $request->loai_hang,
                'don_vi_tinh' => $request->don_vi_tinh,
                'trong_luong' => $request->trong_luong,
                'ten_hang_hoa' => $request->ten_hang_hoa,
                'ma_doanh_nghiep_chon' => $request->ma_doanh_nghiep,
                'is_khong_hang' => $request->is_khong_hang,
                'ngay_dang_ky' => $date,
                'trang_thai' => "1",
            ]);
        } else {
            $nhapCanhSua = NhapCanhSua::create([
                'ma_nhap_canh'  => $nhapCanh->ma_nhap_canh,
                'ma_doanh_nghiep' => $nhapCanh->ma_doanh_nghiep,
                'so_ptvt_xuat_canh' => $nhapCanh->so_ptvt_xuat_canh,
                'ten_thuyen_truong' => $request->ten_thuyen_truong,
                'so_luong' => $request->so_luong,
                'loai_hang' => $request->loai_hang,
                'don_vi_tinh' => $request->don_vi_tinh,
                'trong_luong' => $request->trong_luong,
                'ten_hang_hoa' => $request->ten_hang_hoa,
                'ma_doanh_nghiep_chon' => $request->ma_doanh_nghiep,
                'is_khong_hang' => $request->is_khong_hang,
                'ngay_dang_ky' => $date,
                'trang_thai' => "1",
            ]);
        }
        return $nhapCanhSua;
    }

    public function xemYeuCauSuaNhapCanh($ma_nhap_canh)
    {
        $nhapCanhSua = NhapCanhSua::where('ma_nhap_canh', $ma_nhap_canh)->first();
        $nhapCanh = NhapCanh::find($ma_nhap_canh);

        return view('nhap-canh.xem-sua-nhap-canh', compact('nhapCanh', 'nhapCanhSua'));
    }

    public function huyYeuCauSuaNhapCanh(Request $request, $ma_yeu_cau)
    {
        try {
            DB::beginTransaction();
            $nhapCanhSua = NhapCanhSua::find($ma_yeu_cau);
            $nhapCanh = NhapCanh::find($nhapCanhSua->ma_nhap_canh);

            $nhapCanh->trang_thai = 2;
            $nhapCanh->ghi_chu = $request->ghi_chu;
            $nhapCanh->save();

            $nhapCanhSua->delete();
            session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
            DB::commit();
            return redirect()->route('nhap-canh.thong-tin-nhap-canh', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauSua: ' . $e->getMessage());
            return redirect()->back();
        }
    }




    public function exportToKhaiNhapCanh(Request $request)
    {
        $fileName = 'Tờ khai nhập cảnh.xlsx';
        return Excel::download(new ToKhaiNhapCanh($request->ma_nhap_canh), $fileName);
    }


    public function thayDoiCongChucNhapCanh(Request $request)
    {
        NhapCanh::find($request->ma_nhap_canh)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }

    public function getDonViTinh()
    {
        $units = [
            "Kiện",
            "Hộp",
            "Bao",
            "PP",
            "Pallet",
            "Kiện/Hộp/Bao",
            "Thùng",
            "Đôi",
            "Tá",
            "Chục",
            "Cuộn",
            "Sợi",
            "Tờ",
            "Quyển",
            "Viên",
            "Vỉ",
            "Cặp",
            "Thẻ",
            "Lon",
            "Chai",
            "Ống",
            "Tuýp",
            "Bịch",
            "Miếng",
            "Tấm",
            "Cây",
            "Khối"
        ];
        return $units;
    }

    public function getNhapCanhs(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = NhapCanh::query()
                ->select([
                    'nhap_canh.*',
                    'doanh_nghiep.ten_doanh_nghiep',
                    'ptvt_xuat_canh.ten_phuong_tien_vt'
                ])
                ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_canh.ma_doanh_nghiep')
                ->join('ptvt_xuat_canh', 'ptvt_xuat_canh.so_ptvt_xuat_canh', 'nhap_canh.so_ptvt_xuat_canh');
            if ($user->loai_tai_khoan === "Doanh nghiệp") {
                $query->where('nhap_canh.ma_doanh_nghiep', function ($subquery) use ($user) {
                    $subquery->select('ma_doanh_nghiep')
                        ->from('doanh_nghiep')
                        ->where('ma_tai_khoan', $user->ma_tai_khoan)
                        ->limit(1);
                });
            }
            $query->orderBy('nhap_canh.ma_nhap_canh', 'desc');

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('nhap_canh.ma_nhap_canh', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(nhap_canh.ngay_dang_ky, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('doanh_nghiep.ten_doanh_nghiep', 'LIKE', "%{$search}%")
                                ->orWhere('ptvt_xuat_canh.ten_phuong_tien_vt', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($nhapCanh) {
                    return '';
                })
                ->editColumn('ngay_dang_ky', function ($nhapCanh) {
                    return Carbon::parse($nhapCanh->ngay_dang_ky)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($nhapCanh) {
                    return $nhapCanh->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('ten_phuong_tien_vt', function ($nhapCanh) {
                    return $nhapCanh->ten_phuong_tien_vt ?? 'N/A';
                })
                ->addColumn('action', function ($nhapCanh) {
                    return '<a href="' . route('nhap-canh.thong-tin-nhap-canh', $nhapCanh->ma_nhap_canh) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->editColumn('trang_thai', function ($nhapCanh) {
                    $status = trim($nhapCanh->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đang chờ duyệt', 'class' => 'text-primary'],
                        '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Đã duyệt thực xuất', 'class' => 'text-success'],
                        '4' => ['text' => 'Doanh nghiệp xin sửa', 'class' => 'text-warning'],
                        '5' => ['text' => 'Doanh nghiệp xin hủy', 'class' => 'text-danger'],
                        '0' => ['text' => 'Đã hủy', 'class' => 'text-danger'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->rawColumns(['trang_thai', 'action'])
                ->make(true);
        }
    }
}
