@extends('layout.user-layout')

@section('title', 'Thông tin tờ khai nhập hàng')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;
        $ngayThongQuan = Carbon::parse($nhapHang->ngay_thong_quan);
        $ngayDen = Carbon::parse($nhapHang->ngay_thong_quan);

        $daysPassedFromThongQuan = (int) abs(Carbon::now()->floatDiffInDays($ngayThongQuan, false));
        $daysPassedFromNhapHang = (int) abs(Carbon::now()->floatDiffInDays($ngayDen, false));

        $expiryDate = $ngayThongQuan->addDays($nhapHang->so_ngay_gia_han + 60);
        $daysLeft = (int) Carbon::now()->diffInDays($expiryDate, false);

    @endphp

    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif(session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-danger') }}</strong>
                    </div>
                @endif
                <div class="col-4">
                    @if (trim($nhapHang->trang_thai) == '1')
                        <a class="return-link" href="/quan-ly-nhap-hang">
                            <p>
                                < Quay lại quản lý nhập hàng </p>
                        </a>
                    @elseif(trim($nhapHang->trang_thai) == '2' ||
                            trim($nhapHang->trang_thai) == '4' ||
                            trim($nhapHang->trang_thai) == '6' ||
                            trim($nhapHang->trang_thai) == '7')
                        <a class="return-link" href="/to-khai-da-nhap-hang">
                            <p>
                                < Quay lại quản lý tờ khai nhập đã nhập hàng </p>
                        </a>
                    @elseif(trim($nhapHang->trang_thai) == '0')
                        <a class="return-link" href="/to-khai-nhap-da-huy">
                            <p>
                                < Quay lại quản lý tờ khai nhập đã hủy</p>
                        </a>
                    @endif
                </div>
                <div class="col-8">
                    @if (trim($nhapHang->trang_thai) == '2')
                        <a
                            href="{{ route('nhap-hang.vi-tri-hang-hien-tai', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                            <button class="btn btn-primary float-end">Thông tin hàng hiện tại</button>
                        </a>
                    @endif
                    <a
                        href="{{ route('nhap-hang.phieu-xuat-cua-to-khai', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-primary float-end me-1">Phiếu xuất</button>
                    </a>
                    <a
                        href="{{ route('export.theo-doi-hang-hoa-tong', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-success float-end me-1">Theo dõi hàng hóa</button>
                    </a>
                    <a href="{{ route('nhap-hang.lich-su-sua-nhap', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-primary float-end me-1">Các lần sửa đổi</button>
                    </a>
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $nhapHang->doanhNghiep ? $nhapHang->doanhNghiep->ten_doanh_nghiep : '' }} -
                        {{ $nhapHang->chuHang ? $nhapHang->chuHang->ten_chu_hang : '' }}

                    </h2>
                    <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA ({{ $nhapHang->ma_loai_hinh }})</h2>
                    <h2 class="text-center text-dark">Số: {{ $nhapHang->so_to_khai_nhap }} ngày
                        {{ \Carbon\Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y') }} Đăng ký tại:
                        {{ $nhapHang->haiQuan ? $nhapHang->haiQuan->ten_hai_quan : $nhapHang->ma_hai_quan }}
                    </h2>
                    <h2 class="text-center text-dark">Phương tiện vận
                        tải:
                        {{ $nhapHang->ptvt_ban_dau }} - Trọng lượng: {{ $nhapHang->trong_luong }} tấn</h2>
                    <!-- Table for displaying added rows -->
                    <table class="table table-bordered mt-5" id="displayTable">
                        <thead class="align-middle" style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th>STT</th>
                                <th>Tên hàng</th>
                                <th>Loại hàng</th>
                                <th>Xuất xứ</th>
                                <th>Số lượng</th>
                                <th>Đơn vị tính</th>
                                <th>Đơn giá (USD)</th>
                                <th>Trị giá (USD)</th>
                                <th>Số container ban đầu</th>
                                @if ($nhapHang->trang_thai == '1')
                                    <th>Số seal</th>
                                @endif
                                @if (trim($nhapHang->trang_thai) != '1' && trim($nhapHang->trang_thai) != '0')
                                    <th>Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hangHoaRows as $index => $hangHoa)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $hangHoa->ten_hang }}</td>
                                    <td>{{ $hangHoa->loai_hang }}</td>
                                    <td>{{ $hangHoa->xuat_xu }}</td>
                                    <td>{{ number_format($hangHoa->so_luong_khai_bao, 0) }}</td>
                                    <td>{{ $hangHoa->don_vi_tinh }}</td>
                                    <td>{{ number_format($hangHoa->don_gia, 2) }}</td>
                                    <td>{{ number_format($hangHoa->tri_gia, 2) }}</td>
                                    <td>{{ $hangHoa->so_container_khai_bao }}</td>
                                    @if ($nhapHang->trang_thai == '1')
                                        <td>{{ $hangHoa->so_seal }}</td>
                                    @endif
                                    @if (trim($nhapHang->trang_thai) != '1' && trim($nhapHang->trang_thai) != '0')
                                        <td>
                                            <form action="{{ route('export.theo-doi-hang-hoa') }}" method="GET">
                                                <input type="hidden" name="ma_hang" value="{{ $hangHoa->ma_hang }}">
                                                <center>
                                                    <button type="submit" class="btn btn-success mt-2">Theo dõi</button>
                                                </center>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-center"><strong>Tổng cộng</strong></td>
                                <td></td>
                                <td><strong>{{ number_format($soLuongSum, 0) }}</strong></td>
                                <td></td>
                                <td></td>
                                <td><strong>{{ number_format($triGiaSum, 2) }}</strong></td>
                                <td></td>
                                @if ($nhapHang->trang_thai == '1')
                                    <td></td>
                                @endif
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
                            @if (trim($nhapHang->trang_thai) == '1')
                                <h2 class="text-primary">Đang chờ duyệt</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_nhap_hang == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanNhaphangModal"
                                                    class="btn btn-success ">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/approved2.png') }}">
                                                    Xác nhận duyệt</button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy nhập đơn
                                                </button>

                                            </a>
                                        </div>
                                    </div>
                                @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $nhapHang->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('nhap-hang.sua-to-khai-nhap', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                    Sửa nhập đơn
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy nhập đơn
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($nhapHang->trang_thai) == '2')
                                <h2 class="text-success">Đã nhập hàng</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-success">Ngày đến:
                                    {{ \Carbon\Carbon::parse($nhapHang->ngay_thong_quan)->format('d-m-Y') }}</h2>
                                <h2 class="">Đã {{ $daysPassedFromNhapHang }} ngày kể từ ngày thông quan
                                </h2>
                                @if ($nhapHang->loai_hinh == 'G21')
                                    @if ($daysLeft >= 0)
                                        <h2>Còn {{ $daysLeft }} ngày nữa sẽ quá hạn</h2>
                                    @else
                                        <h2>Đã quá hạn {{ abs($daysLeft) }} ngày</h2>
                                    @endif
                                @endif
                                @if ($nhapHang->so_ngay_gia_han)
                                    <h2 class="">Tờ khai được duyệt gia hạn {{ $nhapHang->so_ngay_gia_han }} ngày
                                    </h2>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_nhap_hang == 1)
                                    <div class="row">

                                        {{-- <center>
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('nhap-hang.sua-to-khai-nhap-cong-chuc', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Sửa nhập đơn
                                                    </button>
                                                </a>
                                            </div>
                                        </center> --}}
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $nhapHang->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('nhap-hang.sua-to-khai-nhap', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                    Sửa nhập đơn
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy nhập đơn
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($nhapHang->trang_thai) == '6')
                                <h2 class="text-success">Đã quay về kho ban đầu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-success">Ngày xuất:
                                    {{ \Carbon\Carbon::parse($nhapHang->updated_at)->format('d-m-Y') }}</h2>
                            @elseif(trim($nhapHang->trang_thai) == '4')
                                <h2 class="text-success">Đã xuất hết hàng</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                </h2>
                            @elseif(trim($nhapHang->trang_thai) == '7')
                                <h2 class="text-success">Đã bàn giao hồ sơ</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                </h2>
                            @elseif(trim($nhapHang->trang_thai) == '8')
                                <h2 class="text-warning">Đã duyệt sửa lần 1</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                </h2>
                                <div class="row">
                                    <center>
                                        <div class="col-6">
                                            <a
                                                href="{{ route('nhap-hang.xem-sua-to-khai-nhap', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            @elseif(trim($nhapHang->trang_thai) == '0')
                                <h2 class="text-danger">Tờ khai đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-danger">Ngày hủy:
                                    {{ \Carbon\Carbon::parse($nhapHang->updated_at)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Lý do hủy: {{ $nhapHang->ghi_chu }}</h3>
                            @elseif(trim($nhapHang->trang_thai) == '5')
                                <h2 class="text-danger">Tờ khai đã tiêu hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-danger">Ngày tiêu hủy:
                                    {{ \Carbon\Carbon::parse($nhapHang->updated_at)->format('d-m-Y') }}</h2>
                            @elseif(trim($nhapHang->trang_thai) == '3')
                                <h2 class="text-warning">Doanh nghiệp yêu cầu sửa tờ khai</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                <div class="row">
                                    <center>
                                        <div class="col-6">
                                            <a
                                                href="{{ route('nhap-hang.xem-sua-to-khai-nhap', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if ($tienTrinhs)
                <table class="table table-bordered mt-5" id="displayTable">
                    <thead class="align-middle">
                        <tr>
                            <th>STT</th>
                            <th>Công việc</th>
                            <th>Ngày thực hiện</th>
                            <th>Cán bộ công chức</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tienTrinhs as $index => $tienTrinh)
                            <tr>
                                <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                <td>{{ $tienTrinh->ten_cong_viec }}</td>
                                <td>{{ \Carbon\Carbon::parse($tienTrinh->ngay_thuc_hien)->format('d-m-Y') }}</td>
                                <td>{{ $tienTrinh->ten_cong_chuc ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @if (trim($nhapHang->trang_thai) != '0')
        {{-- Tình trạng: Chờ duyệt --}}
        <div class="modal fade" id="xacNhanNhaphangModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('nhap-hang.duyet-to-khai-nhap') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <h5>Xác nhận duyệt tờ khai nhập này ?
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="hangHoaRows" value="{{ json_encode($hangHoaRows) }}">
                            <input type="hidden" name="so_to_khai_nhap" value="{{ $nhapHang->so_to_khai_nhap }}">
                            <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
        </div>
        {{-- Xác nhận Hủy --}}
        <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('nhap-hang.huy-to-khai-nhap', $nhapHang->so_to_khai_nhap) }}" method="POST">
                        @csrf
                        <div class="modal-body text-danger">
                            <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                            <label for="ghi_chu">Ghi chú:</label>
                            <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop
