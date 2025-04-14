@extends('layout.user-layout')

@section('title', 'Thông tin phiếu xuất hàng')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;

        $ngayThongQuan = Carbon::parse($xuatHang->ngay_thong_quan);
        $ngayDen = Carbon::parse($xuatHang->ngay_thong_quan);

        $daysPassedFromThongQuan = (int) abs(Carbon::now()->floatDiffInDays($ngayThongQuan, false)); // Use 'false' for signed difference
        $daysPassedFromXuatHang = (int) abs(Carbon::now()->floatDiffInDays($ngayDen, false));
        $ngayThongQuanPlus365 = $ngayThongQuan->copy()->addDays(365);
    @endphp
    <style>
        a {
            text-decoration: none;
            /* Removes underline */
        }
    </style>

    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-danger') }}</strong>
                    </div>
                @endif

                <div class="col-4">
                    @if (trim($xuatHang->trang_thai) == '1' ||
                            trim($xuatHang->trang_thai) == '3' ||
                            trim($xuatHang->trang_thai) == '4' ||
                            trim($xuatHang->trang_thai) == '5' ||
                            trim($xuatHang->trang_thai) == '6' ||
                            trim($xuatHang->trang_thai) == '7' ||
                            trim($xuatHang->trang_thai) == '8' ||
                            trim($xuatHang->trang_thai) == '9' ||
                            trim($xuatHang->trang_thai) == '10')
                        <a class="return-link" href="/quan-ly-xuat-hang">
                            <p>
                                < Quay lại quản lý xuất hàng </p>
                        </a>
                    @elseif(trim($xuatHang->trang_thai) == '2' ||
                            trim($xuatHang->trang_thai) == '4' ||
                            trim($xuatHang->trang_thai) == '11' ||
                            trim($xuatHang->trang_thai) == '12' ||
                            trim($xuatHang->trang_thai) == '13')
                        <a class="return-link" href="/to-khai-da-xuat-hang">
                            <p>
                                < Quay lại quản lý phiếu đã duyệt </p>
                        </a>
                    @elseif(trim($xuatHang->trang_thai) == '0')
                        <a class="return-link" href="/to-khai-xuat-da-huy">
                            <p>
                                < Quay lại quản lý phiếu xuất đã hủy </p>
                        </a>
                    @endif
                </div>
                <div class="col-8">
                    @if (trim($xuatHang->trang_thai) == '2' ||
                            trim($xuatHang->trang_thai) == '1' ||
                            trim($xuatHang->trang_thai) == '4' ||
                            trim($xuatHang->trang_thai) == '11' ||
                            trim($xuatHang->trang_thai) == '12' ||
                            trim($xuatHang->trang_thai) == '13')
                        </a>
                        <a
                            href="{{ route('xuat-hang.export-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                            <button class="btn btn-success float-end me-1">In phiếu xuất</button>
                        </a>
                        <a href="{{ route('xuat-hang.lich-su-sua-xuat-hang', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                            <button class="btn btn-primary float-end me-1">Các lần sửa đổi</button>
                        </a>
                        {{-- <a
                            href="{{ route('xuat-hang.lich-su-sua-phieu', ['so_to_khai_nhap' => $xuatHang->so_to_khai_nhap]) }}">
                            <button class="btn btn-primary float-end me-1">Lịch sử sửa phiếu</button>
                        </a> --}}
                    @endif
                    {{-- <a href="{{ route('nhap-hang.show', ['so_to_khai_nhap' => $xuatHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-primary float-end mx-1">Tờ khai nhập</button>
                    </a> --}}

                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $xuatHang->doanhNghiep->ten_doanh_nghiep }} -
                        {{ $xuatHang->doanhNghiep->chuHang ? $xuatHang->doanhNghiep->chuHang->ten_chu_hang : '' }}

                    </h2>
                    <h2 class="text-center text-dark">Phiếu
                        {{ $xuatHang->loaiHinh ? $xuatHang->loaiHinh->ten_loai_hinh : '' }}
                        ({{ $xuatHang->loaiHinh->ma_loai_hinh }})
                    </h2>
                    <h2 class="text-center text-dark">Số: {{ $xuatHang->so_to_khai_xuat }}, ngày
                        {{ \Carbon\Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                    </h2>
                    <h2 class="text-center text-dark">Phương tiện xuất cảnh:
                        {{ $ptvts }}</h2>
                    <hr />
                    <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
                    <table class="table table-bordered mt-2 fs-6" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr style="vertical-align: middle; text-align: center;">
                                <th>STT</th>
                                <th>SỐ TỜ KHAI NHẬP</th>
                                <th>TÊN HÀNG</th>
                                <th>XUẤT XỨ</th>
                                <th>SỐ LƯỢNG</th>
                                <th>ĐƠN VỊ TÍNH</th>
                                <th>ĐƠN GIÁ (USD)</th>
                                <th>TRỊ GIÁ (USD)</th>
                                <th>SỐ CONTAINER</th>
                                @if ($xuatHang->trang_thai != 0 && $xuatHang->trang_thai != 1)
                                    <th>THAO TÁC</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hangHoaRows as $index => $hangHoa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="/thong-tin-nhap-hang/{{ $hangHoa->so_to_khai_nhap }}">
                                            {{ $hangHoa->so_to_khai_nhap }}
                                        </a>
                                    </td>
                                    <td>{{ $hangHoa->ten_hang }}</td>
                                    <td>{{ $hangHoa->xuat_xu }}</td>
                                    <td>{{ number_format($hangHoa->so_luong_xuat, 0) }}</td>
                                    <td>{{ $hangHoa->don_vi_tinh }}</td>
                                    <td>{{ number_format($hangHoa->don_gia, 2) }}</td>
                                    <td>{{ number_format($hangHoa->tri_gia, 2) }}</td>
                                    <td>{{ $hangHoa->so_container }}</td>
                                    @if ($xuatHang->trang_thai != 0 && $xuatHang->trang_thai != 1)
                                        <td>
                                            <a
                                                href="{{ route('export.theo-doi-tru-lui-theo-ngay', ['so_to_khai_nhap' => $hangHoa->so_to_khai_nhap, 'ngay_dang_ky' => $xuatHang->ngay_dang_ky, 'xuat_hang' => true]) }}">
                                                <button class="btn btn-success float-end me-1">Theo dõi trừ lùi</button>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><strong>Tổng cộng</strong></td>
                                <td><strong>{{ number_format($soLuongSum, 0) }}</strong></td>
                                <td></td>
                                <td></td>
                                <td><strong>{{ number_format($triGiaSum, 2) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($xuatHang->trang_thai) == '1')
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if ($xuatHang->ghi_chu)
                                    <h3 class="text-dark">Ghi chú: {{ $xuatHang->ghi_chu }}</h3>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('xuat-hang.sua-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                    Sửa phiếu
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy phiếu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#duyetToKhaiModal"
                                                    class="btn btn-success ">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/approved2.png') }}">
                                                    Xác nhận duyệt</button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy phiếu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == '2')
                                <h2 class="text-success">Đã duyệt</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Công chức phụ trách:
                                    {{ $xuatHang->congChuc->ten_cong_chuc ?? '' }}</h2>
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_xuat_canh)->format('d-m-Y') }}</h2>

                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <center>
                                        <div class="row mt-5">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('xuat-hang.sua-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Yêu cầu sửa phiếu
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Yêu cầu hủy phiếu
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </center>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <div class="row mt-5">
                                        <center>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#thayDoiCongChucModal"
                                                        class="btn btn-warning ">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Thay đổi công chức</button>
                                                </a>
                                            </div>
                                        </center>
                                    </div>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == '11')
                                <h2 class="text-success">Đã chọn phương tiện xuất cảnh</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Công chức phụ trách:
                                    {{ $xuatHang->congChuc->ten_cong_chuc ?? '' }}</h2>
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_xuat_canh)->format('d-m-Y') }}</h2>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <div class="row mt-5">
                                        <center>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#thayDoiCongChucModal"
                                                        class="btn btn-warning ">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Thay đổi công chức</button>
                                                </a>
                                            </div>
                                        </center>
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <center>
                                        <div class="row">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('xuat-hang.sua-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Yêu cầu sửa phiếu
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Yêu cầu hủy phiếu
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </center>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == '12')
                                <h2 class="text-success">Đã duyệt xuất hàng</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Công chức phụ trách:
                                    {{ $xuatHang->congChuc->ten_cong_chuc ?? '' }}</h2>
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_xuat_canh)->format('d-m-Y') }}</h2>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <div class="row mt-5">
                                        <center>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#thayDoiCongChucModal"
                                                        class="btn btn-warning ">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Thay đổi công chức</button>
                                                </a>
                                            </div>
                                        </center>
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <center>
                                        <div class="row">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('xuat-hang.sua-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Yêu cầu sửa phiếu
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Yêu cầu hủy phiếu
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </center>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == '13')
                                <h2 class="text-success">Đã thực xuất hàng</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Công chức phụ trách:
                                    {{ $xuatHang->congChuc->ten_cong_chuc ?? '' }}</h2>
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_xuat_canh)->format('d-m-Y') }}</h2>
                            @elseif(
                                $xuatHang->trang_thai == '3' ||
                                    $xuatHang->trang_thai == '4' ||
                                    $xuatHang->trang_thai == '5' ||
                                    $xuatHang->trang_thai == '6')
                                <h2 class="text-warning">Doanh nghiệp yêu cầu sửa phiếu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                @if ($xuatHang->ghi_chu)
                                    <h3 class="text-dark">Ghi chú: {{ $xuatHang->ghi_chu }}</h3>
                                @endif
                                <center>
                                    @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                            DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                                $xuatHang->ma_doanh_nghiep)
                                        <a
                                            href="{{ route('xuat-hang.xem-yeu-cau-sua', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat, 0]) }}">
                                            <button class="btn btn-warning px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                Xem sửa đổi
                                            </button>
                                        </a>
                                    @endif
                                </center>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <center>
                                            <a
                                                href="{{ route('xuat-hang.xem-yeu-cau-sua', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat, 0]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </center>
                                    </div>
                                @endif
                            @elseif(
                                $xuatHang->trang_thai == '7' ||
                                    $xuatHang->trang_thai == '8' ||
                                    $xuatHang->trang_thai == '9' ||
                                    $xuatHang->trang_thai == '10')
                                <h2 class="text-danger">Doanh nghiệp yêu cầu hủy phiếu</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                @if ($xuatHang->ghi_chu)
                                    <h3 class="text-dark">Ghi chú: {{ $xuatHang->ghi_chu }}</h3>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <center>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanTuChoiHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Thu hồi yêu cầu hủy
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                @elseif(Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                    <center>

                                        <div class="row">
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Chấp nhận hủy
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#xacNhanTuChoiHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Từ chối yêu cầu hủy
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </center>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == '0')
                                <h2 class="text-danger">Tờ khai đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-danger">Ngày hủy:
                                    {{ \Carbon\Carbon::parse($xuatHang->updated_at)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Lý do hủy: {{ $xuatHang->ghi_chu }}</h3>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tình trạng: Chờ thông quan --}}
    <div class="modal fade" id="duyetToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.updateDuyetToKhai') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt phiếu xuất này ?</p>
                        <div class="form-group">
                            <label class="label-text mb-1 mt-2" for="">Cán bộ công chức phụ trách</label>
                            <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
                                <option></option>
                                @foreach ($congChucs as $congChuc)
                                    <option value="{{ $congChuc->ma_cong_chuc }}">
                                        {{ $congChuc->ten_cong_chuc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $xuatHang->so_to_khai_xuat }}" name="so_to_khai_xuat">
                        <button type="submit" class="btn btn-success">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="thayDoiCongChucModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.thay-doi-cong-chuc-xuat') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Thay đổi công chức phụ trách</p>
                        <div class="form-group">
                            <label class="label-text mb-1" for="">Cán bộ công chức phụ trách</label>
                            <select class="form-control" id="cong-chuc-dropdown-search-2" name="ma_cong_chuc" required>
                                <option></option>
                                @foreach ($congChucs as $congChuc)
                                    <option value="{{ $congChuc->ma_cong_chuc }}">
                                        {{ $congChuc->ten_cong_chuc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $xuatHang->so_to_khai_xuat }}" name="so_to_khai_xuat">
                        <button type="submit" class="btn btn-success">
                            Xác nhận
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Yêu cầu Hủy --}}
    <div class="modal fade" id="yeuCauHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.yeu-cau-huy-to-khai') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận yêu cầu hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="so_to_khai_xuat" value="{{ $xuatHang->so_to_khai_xuat }}">
                        <button type="submit" class="btn btn-danger">Xác nhận yêu cầu hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Từ chối Hủy --}}
    <div class="modal fade" id="xacNhanTuChoiHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.thu-hoi-yeu-cau-huy') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                            <p class="text-danger">Xác nhận từ chối yêu cầu xin hủy của phiếu xuất này?</p>
                        @else
                            <p class="text-danger">Xác nhận thu hồi yêu cầu xin hủy của phiếu xuất này?</p>
                        @endif
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="so_to_khai_xuat" value="{{ $xuatHang->so_to_khai_xuat }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                startDate: new Date(),
                endDate: new Date(new Date().setDate(new Date().getDate() + 60)),
                autoclose: true,
                todayHighlight: true,
                language: 'vi' // Set language to Vietnamese
            });

            // Validate date selection before submission
            $('#submitTQG21Btn').on('click', function(event) {
                const selectedDate = $('#datepicker').val();
                if (!selectedDate) {
                    alert('Vui lòng chọn ngày trước khi gửi.');
                    event.preventDefault(); // Prevent form submission
                } else {
                    alert('Ngày đã chọn: ' + selectedDate);
                    // You can proceed with form submission here
                }
            });
        });
    </script>
@stop
