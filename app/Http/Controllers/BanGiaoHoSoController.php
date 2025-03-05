<?php

namespace App\Http\Controllers;

use App\Exports\BienBanBanGiaoHoSo;
use App\Models\BanGiaoHoSo;
use App\Models\BanGiaoHoSoChiTiet;
use App\Models\CongChuc;
use App\Models\NhapHang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\XuatCanhService;

class BanGiaoHoSoController extends Controller
{
    protected $xuatCanhService;

    public function __construct(XuatCanhService $xuatCanhService)
    {
        $this->xuatCanhService = $xuatCanhService;
    }

    public function danhSachBanGiaoHoSo()
    {
        $data = BanGiaoHoSo::orderBy('ma_ban_giao', 'desc')->get();
        return view('ban-giao-ho-so.danh-sach-ban-giao-ho-so', data: compact('data'));
    }
    public function themBanGiaoHoSo(Request $request)
    {
        $congChuc = $this->getCongChucHienTai();
        return view('ban-giao-ho-so.them-ban-giao-ho-so', data: compact('congChuc'));
    }
    public function themBanGiaoHoSoSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $congChuc = $this->getCongChucHienTai();
                $nhapHangs = $this->getNhapHangDaXuatHet($request);
                $banGiao = BanGiaoHoSo::create([
                    'tu_ngay' => $this->formatDateToYMD($request->tu_ngay),
                    'den_ngay' => $this->formatDateToYMD($request->den_ngay),
                    'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    'ngay_tao' => now(),
                ]);
                foreach ($nhapHangs as $nhapHang) {
                    BanGiaoHoSoChiTiet::insert([
                        'ma_ban_giao' => $banGiao->ma_ban_giao,
                        'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap
                    ]);
                    $nhapHang->update([
                        'trang_thai' => 'Đã bàn giao hồ sơ',
                    ]);
                }

                return redirect()
                    ->route('ban-giao.thong-tin-ban-giao-ho-so', ['ma_ban_giao' => $banGiao->ma_ban_giao])
                    ->with('alert-success', 'Thêm bàn giao hồ sơ thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in themBanGiaoHoSoSubmit: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinBanGiaoHoSo($ma_ban_giao)
    {
        $bienBan = BanGiaoHoSo::find($ma_ban_giao);
        $chiTiets =  BanGiaoHoSoChiTiet::where('ma_ban_giao', $ma_ban_giao)->get();
        return view('ban-giao-ho-so.thong-tin-ban-giao-ho-so', data: compact('bienBan', 'chiTiets'));
    }


    public function getToKhaiDaXuatHet(Request $request)
    {
        $nhapHangs = $this->getNhapHangDaXuatHet($request);

        return response()->json(['nhapHangs' => $nhapHangs]);
    }
    public function getNhapHangDaXuatHet(Request $request)
    {
        $congChuc = $this->getCongChucHienTai();
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $nhapHangs = NhapHang::join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('hang_hoa', function ($join) {
                $join->on('nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->whereRaw('hang_hoa.so_luong_khai_bao = (SELECT MAX(so_luong_khai_bao) FROM hang_hoa WHERE hang_hoa.so_to_khai_nhap = nhap_hang.so_to_khai_nhap)');
            })
            ->whereBetween('ngay_xuat_het', [$tu_ngay, $den_ngay])
            ->where('ma_cong_chuc_ban_giao', $congChuc->ma_cong_chuc)
            ->get();

        return $nhapHangs;
    }


    private function formatDateToYMD($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
    }


    public function exportBienBanBanGiao($ma_ban_giao)
    {
        $fileName = 'Biên bản bàn giao hồ sơ.xlsx';
        return Excel::download(new BienBanBanGiaoHoSo($ma_ban_giao), $fileName);
    }

    public function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }
}
