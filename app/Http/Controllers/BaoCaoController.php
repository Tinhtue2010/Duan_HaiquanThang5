<?php

namespace App\Http\Controllers;

use App\Exports\BaoCaoCapHai;
use App\Exports\BaoCaoChiTietXNKTheoDN;
use App\Exports\BaoCaoDoanhNghiepXNKTheoDN;
use App\Exports\BaoCaoTheoDoiTruLuiExport;
use App\Exports\BaoCaoTonChuHangExport;
use App\Exports\BaoCaoTonDoanhNghiepExport;
use App\Exports\BaoCaoHangTonTheoToKhaiExport;
use App\Exports\BaoCaoTiepNhanHangNgayExport;
use App\Exports\BaoCaoChiTietXNKTrongNgay;
use App\Exports\BaoCaoDoanhNghiepXNK;
use App\Exports\BaoCaoChuyenCuaKhauXuat;
use App\Exports\BaoCaoHangTonTaiCang;
use App\Exports\BaoCaoContainerLuuTaiCang;
use App\Exports\BaoCaoHangHoaChuaThucXuat;
use App\Exports\BaoCaoPhieuXuatDoanhNghiep;
use App\Exports\BaoCaoSoLuongToKhaiXuat;
use App\Exports\BaoCaoTheoDoiHangHoa;
use App\Exports\BaoCaoPhieuXuatTheoXuong;
use App\Exports\BaoCaoTheoDoiTruLuiCuoiNgayExport;
use App\Exports\BaoCaoTheoDoiHangHoaTong;
use App\Exports\BaoCaoTheoDoiTruLuiTatCaExport;
use App\Exports\BaoCaoTheoDoiTruLuiTheoNgayExport;
use App\Exports\BaoCaoDangKyXuatKhauHangHoa;
use App\Exports\BaoCaoSangContChuyenTau;
use App\Exports\BaoCaoGiamSatXuatKhau;
use App\Models\ChuHang;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\DoanhNghiepQL;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanh;
use App\Models\TheoDoiTruLui;
use App\Models\XuatHang;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoController extends Controller
{
    public function index()
    {
        $ptvtXuatCanhs = PTVTXuatCanh::all();
        $doanhNghieps = DoanhNghiep::with('chuHang')->get();
        $chuHangs = ChuHang::select('ma_chu_hang', 'ten_chu_hang')->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        return view('bao-cao/bao-cao-hang-ton', compact('doanhNghieps', 'chuHangs', 'ptvtXuatCanhs', 'congChucs')); // Pass the data to the view
    }

    public function theoDoiTruLui(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi hàng hóa xuất khẩu.xlsx';
        return Excel::download(new BaoCaoTheoDoiTruLuiExport($request->cong_viec, $request->ma_yeu_cau, $request->so_to_khai_nhap), $fileName);
    }
    public function theoDoiTruLuiCuoiNgay(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi cuối ngày ' . $request->so_to_khai_nhap . '.xlsx';
        if (!NhapHang::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
            return redirect()->back();
        };
        return Excel::download(new BaoCaoTheoDoiTruLuiCuoiNgayExport($request->so_to_khai_nhap), $fileName);
    }
    public function theoDoiTruLuiTatCa(Request $request)
    {
        $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . '.xlsx';
        if (!NhapHang::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
            return redirect()->back();
        };
        return Excel::download(new BaoCaoTheoDoiTruLuiTatCaExport($request->so_to_khai_nhap), $fileName);
    }
    public function theoDoiTruLuiTheoNgay(Request $request)
    {
        $theoDoiTruLui = TheoDoiTruLui::find($request->ma_theo_doi);
        $ngay_name = Carbon::parse($theoDoiTruLui->ngay_them)->format('d-m-Y');
        $fileName = 'Phiếu theo dõi từ lùi của tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $ngay_name . '.xlsx';
        if (!NhapHang::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Không tìm thấy số tờ khai nhập này');
            return redirect()->back();
        };

        if ($theoDoiTruLui->cong_viec == 1) {
            return Excel::download(new BaoCaoTheoDoiTruLuiTheoNgayExport($theoDoiTruLui->so_to_khai_nhap, $theoDoiTruLui->ngay_them), $fileName);
        } else {
            return Excel::download(new BaoCaoTheoDoiTruLuiExport($theoDoiTruLui->cong_viec, $theoDoiTruLui->ma_yeu_cau, $request->so_to_khai_nhap), $fileName);
        }
    }

    public function phieuXuatTheoXuong(Request $request)
    {
        $ten_doanh_nghiep = DoanhNghiep::find($request->ma_doanh_nghiep)->ten_doanh_nghiep;
        $systemDate = $this->formatDateToYMD($request->tu_ngay);
        $fileName = 'Phiếu đăng ký kế hoạch xuất nhập khẩu theo xuồng ' . $ten_doanh_nghiep . '.xlsx';
        return Excel::download(new BaoCaoPhieuXuatTheoXuong($request->ma_doanh_nghiep, $request->so_ptvt_xuat_canh, $systemDate), $fileName);
    }
    public function hangTonDoanhNghiep(Request $request)
    {
        $date = $this->formatDateNow();
        $ma_doanh_nghiep = $request->get('ma_doanh_nghiep');
        $ten_doanh_nghiep = DoanhNghiep::find($ma_doanh_nghiep)->ten_doanh_nghiep;
        $fileName = 'Báo cáo hàng tồn của doanh nghiệp ' . $ten_doanh_nghiep . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTonDoanhNghiepExport($ma_doanh_nghiep, $ten_doanh_nghiep), $fileName);
    }
    public function baoCaoDangKyXuatKhauHangHoa(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $ma_doanh_nghiep = $request->get('ma_doanh_nghiep');
        $ten_doanh_nghiep = DoanhNghiep::find($ma_doanh_nghiep)->ten_doanh_nghiep;
        $fileName = 'Báo cáo tổng hợp đăng ký làm thủ tục xuất khẩu hàng hóa doanh nghiệp ' . $ten_doanh_nghiep . ' ngày ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoDangKyXuatKhauHangHoa($ma_doanh_nghiep, $request->tu_ngay), $fileName);
    }
    public function hangTonChuHang(Request $request)
    {
        $date = $this->formatDateNow();
        $ma_chu_hang = $request->get('ma_chu_hang');
        $ten_chu_hang = $request->get('ten_chu_hang');
        $fileName = 'Báo cáo hàng tồn của đại lý ' . $ten_chu_hang . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTonChuHangExport($ma_chu_hang, $ten_chu_hang), $fileName);
    }
    public function hangTonTheoToKhai(Request $request)
    {
        $date = $this->formatDateNow();
        $so_to_khai_nhap = $request->get('so_to_khai_nhap');
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        if (!$nhapHang) {
            session()->flash('alert-danger', 'Số tờ khai nhập không tồn tại!');
            return redirect()->back();
        }
        $fileName = 'Báo cáo hàng tồn theo tờ khai ' . $so_to_khai_nhap . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoHangTonTheoToKhaiExport($so_to_khai_nhap), $fileName);
    }

    public function theoDoiHangHoa(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo theo dõi hàng hóa xuất nhập khẩu ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTheoDoiHangHoa($request->ma_hang), $fileName);
    }
    public function theoDoiHangHoaTong(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo theo dõi hàng hóa tờ khai ' . $request->so_to_khai_nhap . ' ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoTheoDoiHangHoaTong($request->so_to_khai_nhap), $fileName);
    }

    public function tiepNhanHangNgay(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);

        $fileName = 'Báo cáo tiếp nhận hằng ngày hôm ' . $tu_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoTiepNhanHangNgayExport($tu_ngay), $fileName);
    }
    public function chiTietXNKTrongNgay(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo chi tiết xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoChiTietXNKTrongNgay($tu_ngay, $den_ngay), $fileName);
    }
    public function sangContChuyenTau(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo thống kê hàng hóa sang cont, chuyển tàu, kiểm tra hàng từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSangContChuyenTau($tu_ngay, $den_ngay, $request->ma_cong_chuc), $fileName);
    }
    public function giamSatXuatKhau(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo giám sát hàng hóa xuất khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoGiamSatXuatKhau($tu_ngay, $den_ngay, $request->ma_cong_chuc), $fileName);
    }

    public function hangHoaChuaThucXuat(Request $request)
    {
        $fileName = 'Theo dõi hàng hóa quá 15 ngày chưa thực xuất ' . $this->formatDateNow() . '.xlsx';
        return Excel::download(new BaoCaoHangHoaChuaThucXuat(), $fileName);
    }
    public function soLuongToKhaiXuatHet(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo số lượng tờ khai xuất hết từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoSoLuongToKhaiXuat($tu_ngay, $den_ngay), $fileName);
    }
    public function containerLuuTaiCang(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo số lượng container lưu tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoContainerLuuTaiCang(), $fileName);
    }
    public function doanhNghiepXNK()
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo doanh nghiệp xuất nhập khẩu  ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoDoanhNghiepXNK(), $fileName);
    }

    public function chuyenCuaKhauXuat()
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo hàng chuyển cửa khẩu xuất (Quay về kho) ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoChuyenCuaKhauXuat(), $fileName);
    }
    public function hangTonTaiCang()
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo hàng tồn tại cảng ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoHangTonTaiCang(), $fileName);
    }


    public function baoCaoTheoDoanhNghiep()
    {
        $ma_doanh_nghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
        $maDoanhNghiepKhacs = DoanhNghiepQL::where('ma_doanh_nghiep_ql', $ma_doanh_nghiep)->pluck('ma_doanh_nghiep_khac');
        $maDoanhNghiepKhacs->push($ma_doanh_nghiep);
        $doanhNghieps = DoanhNghiep::whereIn('ma_doanh_nghiep', $maDoanhNghiepKhacs)->get();
        return view('bao-cao/bao-cao-hang-ton-doanh-nghiep', compact('doanhNghieps')); // Pass the data to the view
    }
    public function chiTietXNKTheoDN(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo chi tiết xuất nhập khẩu từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoChiTietXNKTheoDN($tu_ngay, $den_ngay, $request->ma_doanh_nghiep), $fileName);
    }

    public function doanhNghiepXNKTheoDN(Request $request)
    {
        $date = $this->formatDateNow();
        $fileName = 'Báo cáo doanh nghiệp xuất nhập khẩu  ngày ' . $date . '.xlsx';
        return Excel::download(new BaoCaoDoanhNghiepXNKTheoDN($request->ma_doanh_nghiep), $fileName);
    }

    public function phieuXuatTheoDoanhNghiep(Request $request)
    {
        $tu_ngay_name = $this->formatDateToDMY($request->tu_ngay);
        $den_ngay_name = $this->formatDateToDMY($request->den_ngay);
        $tu_ngay = $this->formatDateToYMD($request->tu_ngay);
        $den_ngay = $this->formatDateToYMD($request->den_ngay);
        $fileName = 'Báo cáo phiếu xuất của doanh nghiệp từ ' . $tu_ngay_name . ' đến ' . $den_ngay_name . '.xlsx';
        return Excel::download(new BaoCaoPhieuXuatDoanhNghiep($request->ma_doanh_nghiep, $tu_ngay, $den_ngay), $fileName);
    }
    public function baoCaoCapHai(Request $request)
    {
        $ngay_name = Carbon::createFromFormat('d/m/Y', $request->ngay)->format('d-m-Y');
        $tu_ngay = Carbon::createFromFormat('d/m/Y', $request->ngay)->format('Y-m-d');
        $fileName = 'Báo cáo cấp 2 ngày ' . $ngay_name . '.xlsx';
        return Excel::download(new BaoCaoCapHai($request->ma_doanh_nghiep, $tu_ngay), $fileName);
    }

    private function formatDateToYMD($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
    }
    private function formatDateToDMY($dateString)
    {
        return Carbon::createFromFormat('d/m/Y', $dateString)->format('d-m-Y');
    }
    private function formatDateNow()
    {
        return Carbon::now()->format('d-m-Y');
    }

    public function getHangHoa($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::with('hangHoa')->find($so_to_khai_nhap);
        if (!$nhapHang) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($nhapHang->hangHoa);
    }
    public function getLanTruLui($so_to_khai_nhap)
    {
        $theoDoiTruLuis = TheoDoiTruLui::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->get()
            ->groupBy('cong_viec' == 1 ? 'ngay_them' : 'ma_yeu_cau')
            ->map(function ($group) {
                return $group->first();
            })
            ->values();

        if (!$theoDoiTruLuis) {
            return response()->json(['error' => 'Not found'], 404);
        }
        foreach ($theoDoiTruLuis as $theoDoiTruLui) {
            if ($theoDoiTruLui->cong_viec == 1) {
                $theoDoiTruLui->cong_viec = "Xuất hàng";
            } else if ($theoDoiTruLui->cong_viec == 2) {
                $theoDoiTruLui->cong_viec = "Chuyển container và tàu";
            } else if ($theoDoiTruLui->cong_viec == 3) {
                $theoDoiTruLui->cong_viec = "Chuyển container";
            } else if ($theoDoiTruLui->cong_viec == 4) {
                $theoDoiTruLui->cong_viec = "Chuyển tàu";
            } else if ($theoDoiTruLui->cong_viec == 5) {
                $theoDoiTruLui->cong_viec = "Đưa hàng trở lại kho ban đầu";
            } else if ($theoDoiTruLui->cong_viec == 6) {
                $theoDoiTruLui->cong_viec = "Tiêu hủy hàng";
            } else if ($theoDoiTruLui->cong_viec == 7) {
                $theoDoiTruLui->cong_viec = "Kiểm tra hàng";
            }

            $theoDoiTruLui->ngay_them = Carbon::parse($theoDoiTruLui->ngay_them)->format('d-m-Y');
        }
        return response()->json($theoDoiTruLuis);
    }
}
