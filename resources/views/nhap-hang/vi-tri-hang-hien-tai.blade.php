@extends('layout.user-layout')

@section('title', 'Vị trí hàng hiện tại')

@section('content')
    @php
        use Carbon\Carbon;

        $ngayThongQuan = Carbon::parse($nhapHang->ngay_thong_quan);
        $ngayDen = Carbon::parse($nhapHang->ngay_thong_quan);

        $daysPassedFromThongQuan = (int) abs(Carbon::now()->floatDiffInDays($ngayThongQuan, false)); // Use 'false' for signed difference
        $daysPassedFromNhapHang = (int) abs(Carbon::now()->floatDiffInDays($ngayDen, false));
        $ngayThongQuanPlus365 = $ngayThongQuan->copy()->addDays(365);
        $ngayThongQuanPlus365And180 = $ngayThongQuan->copy()->addDays(365)->addDays(180);

    @endphp

    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/thong-tin-nhap-hang/{{ $nhapHang->so_to_khai_nhap }}">
                        <p>
                            < Quay lại quản lý nhập hàng </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $nhapHang->doanhNghiep ? $nhapHang->doanhNghiep->ten_doanh_nghiep : 'Unknown' }}
                    </h2>
                    <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
                    <h2 class="text-center text-dark">Số: {{ $nhapHang->so_to_khai_nhap }}, ngày
                        {{ \Carbon\Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y') }}, Đăng ký tại:
                        {{ $nhapHang->haiQuan ? $nhapHang->haiQuan->ten_hai_quan : $nhapHang->ma_hai_quan }}
                    </h2>
                    <h2 class="text-center text-dark">Tàu hiện tại: {{ $nhapHang->phuong_tien_vt_nhap }}</h2>
                    <h2 class="text-center mt-5">Thông tin/Vị trí hàng hiện tại</h2>
                    <!-- Table for displaying added rows -->
                    <table class="table table-bordered mt-2" id="displayTable">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Tên hàng</th>
                                <th>Xuất xứ</th>
                                <th>Loại hàng</th>
                                <th>Số lượng trong container</th>
                                <th>Đơn vị tính</th>
                                <th>Số container</th>
                                <th>Số tàu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalSoLuong = 0;
                                $displayIndex = 1;
                            @endphp
                            @foreach ($hangTrongConts as $hang)
                                @if ($hang->is_da_chuyen_cont == 0)
                                    @php
                                        $totalSoLuong += $hang->so_luong;
                                    @endphp
                                    <tr>
                                        <td>{{ $displayIndex }}</td>
                                        <td>{{ $hang->hangHoa->nhapHang->so_to_khai_nhap }}</td>
                                        <td>{{ $hang->hangHoa->ten_hang }}</td>
                                        <td>{{ $hang->hangHoa->xuat_xu }}</td>
                                        <td>{{ $hang->hangHoa->loai_hang }}</td>
                                        <td>{{ $hang->so_luong }}</td>
                                        <td>{{ $hang->hangHoa->don_vi_tinh }}</td>
                                        <td>{{ $hang->so_container }}</td>
                                        <td>{{ $hang->phuong_tien_vt_nhap }}</td>
                                    </tr>
                                    @php
                                        $displayIndex++; 
                                    @endphp
                                @endif
                            @endforeach

                            <tr>
                                <td colspan="5" class="text-center"><strong>Tổng cộng:</strong></td>
                                <td><strong>{{ $totalSoLuong }}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
