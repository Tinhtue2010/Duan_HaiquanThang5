@extends('layout.user-layout')

@section('title', 'Thông tin sửa tờ khai nhập')

@section('content')
    @php
        use App\Models\DoanhNghiep;
    @endphp
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
                <div class="col-6">
                    <a class="return-link" href="/thong-tin-nhap-hang/{{ $nhapHang->so_to_khai_nhap }}">
                        <p>
                            < Quay lại danh sách tờ khai nhập </p>
                    </a>

                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">

                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }} - 
                        {{ $nhapHang->chuHang ? $nhapHang->chuHang->ten_chu_hang : '' }}
                    </h2>
                    

                    <h2 class="text-center">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
                    <h2 class="text-center">Số {{ $nhapHang->so_to_khai_nhap }}</h2>
                    <hr>
                    <h1 class="text-center">Tờ khai ban đầu</h1>
                    <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA ({{ $nhapHang->ma_loai_hinh }})</h2>
                    <h2 class="text-center text-dark">Số: {{ $nhapHang->so_to_khai_nhap }} ngày
                        {{ \Carbon\Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y') }} Đăng ký tại:
                        {{ $nhapHang->haiQuan ? $nhapHang->haiQuan->ten_hai_quan : $nhapHang->ma_hai_quan }}
                    </h2>
                    <h2 class="text-center text-dark">Số container: {{ $nhapHang->container_ban_dau }} - Phương tiện vận
                        tải:
                        {{ $nhapHang->ptvt_ban_dau }} - Trọng lượng: {{ $nhapHang->trong_luong }} tấn</h2>
                    <div class="float-end mb-2 d-flex align-items-center justify-content-center">
                    </div>
                    <table class="table table-bordered mt-2" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Tên hàng</th>
                                <th>Loại hàng</th>
                                <th>Xuất xứ</th>
                                <th>Số lượng</th>
                                <th>Đơn vị tính</th>
                                <th>Đơn giá (USD)</th>
                                <th>Trị giá (USD)</th>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h1 class="text-center">Tờ khai sau khi sửa</h1>
                    <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA ({{ $nhapHangSua->ma_loai_hinh }})</h2>
                    <h2 class="text-center text-dark">Số: {{ $nhapHangSua->so_to_khai_nhap }} ngày
                        {{ \Carbon\Carbon::parse($nhapHangSua->ngay_dang_ky)->format('d-m-Y') }} Đăng ký tại:
                        {{ $nhapHangSua->haiQuan ? $nhapHangSua->haiQuan->ten_hai_quan : $nhapHangSua->ma_hai_quan }}
                    </h2>
                    <h2 class="text-center text-dark">Số container: {{ $nhapHangSua->container_ban_dau }} - Phương tiện vận
                        tải:
                        {{ $nhapHangSua->ptvt_ban_dau }} - Trọng lượng: {{ $nhapHangSua->trong_luong }} tấn</h2>
                    <table class="table table-bordered mt-2" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Tên hàng</th>
                                <th>Loại hàng</th>
                                <th>Xuất xứ</th>
                                <th>Số lượng</th>
                                <th>Đơn vị tính</th>
                                <th>Đơn giá (USD)</th>
                                <th>Trị giá (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hangHoaSuaRows as $index => $hangHoa)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $hangHoa->ten_hang }}</td>
                                    <td>{{ $hangHoa->loai_hang }}</td>
                                    <td>{{ $hangHoa->xuat_xu }}</td>
                                    <td>{{ number_format($hangHoa->so_luong_khai_bao, 0) }}</td>
                                    <td>{{ $hangHoa->don_vi_tinh }}</td>
                                    <td>{{ number_format($hangHoa->don_gia, 2) }}</td>
                                    <td>{{ number_format($hangHoa->tri_gia, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_nhap_hang == 1)
                            <hr />
                            <div class="row mt-3">
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanModal"
                                            class="btn btn-success ">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/approved2.png') }}">
                                            Duyệt yêu cầu sửa</button>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                            class="btn btn-danger px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Hủy yêu cầu sửa
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $nhapHang->ma_doanh_nghiep)
                            <div class="row">
                                <center>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy yêu cầu sửa
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
    </div>

    {{-- Tình trạng: Chờ duyệt --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận duyệt yêu cầu sửa</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nhap-hang.duyet-sua-to-khai-nhap') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu sửa này ?</h5>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="so_to_khai_nhap" value="{{ $nhapHang->so_to_khai_nhap }}">
                        <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
    </div>

    {{-- Xác nhận Hủy --}}
    <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nhap-hang.huy-sua-to-khai-nhap') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="so_to_khai_nhap" value="{{ $nhapHang->so_to_khai_nhap }}">
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
        document.addEventListener('DOMContentLoaded', function() {
            var suaSealModal = document.getElementById('suaSealModal')
            suaSealModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget
                var containerNumber = button.getAttribute('data-container')
                var containerInput = suaSealModal.querySelector('#so_container_hidden')
                containerInput.value = containerNumber
            })
        })
    </script>
@stop
