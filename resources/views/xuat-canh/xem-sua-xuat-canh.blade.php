@extends('layout.user-layout')

@section('title', 'Xem yêu cầu sửa xuất cảnh')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;
    @endphp
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="">
                        <strong>{!! nl2br(session('alert-danger')) !!}</strong>
                    </div>
                @endif

                <div class="col-6">
                    <a class="return-link"
                        href={{ route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}>
                        <p>
                            < Quay lại thông tin phiếu xuất </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $xuatCanh->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">Tờ khai xuất cảnh số {{ $xuatCanh->ma_xuat_canh }}
                    </h2>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} -
                        Ngày
                        {{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y') }}</h2>
                    <hr/>
                    <h2 class="text-center mt-3">Tờ khai xuất cảnh ban đầu</h2>
                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $xuatCanh->doanhNghiepChon->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $xuatCanh->ten_thuyen_truong }}</h2>
                    <hr />
                    <h3 class="text-center text-dark">Thông tin phiếu xuất</h3>
                    <table class="table table-bordered mt-2 fs-6" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai xuất</th>
                                <th>Công ty</th>
                                <th>Đại lý</th>
                                <th>Loại hình</th>
                                <th>Số lượng</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalSoLuong = 0; @endphp
                            @foreach ($chiTiets as $index => $chiTiet)
                                @php $totalSoLuong += $chiTiet->tong_so_luong_xuat; @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTiet->xuatHang->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->chuHang->ten_chu_hang }}</td>
                                    <td>{{ $chiTiet->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ $chiTiet->tong_so_luong_xuat }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="5"><strong>Tổng cộng:</strong></td>
                                <td><strong>{{ $totalSoLuong }}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>

                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h2 class="text-center">Tờ khai xuất cảnh sau khi sửa</h2>
                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $xuatCanh->doanhNghiepChon->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $xuatCanh->ten_thuyen_truong }}</h2>
                    <hr />
                    <h3 class="text-center text-dark">Thông tin phiếu xuất</h3>
                    <table class="table table-bordered mt-2 fs-6" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai xuất</th>
                                <th>Công ty</th>
                                <th>Đại lý</th>
                                <th>Loại hình</th>
                                <th>Số lượng</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalSoLuongSua = 0; @endphp
                            @foreach ($chiTietSuas as $index => $chiTietSua)
                                @php $totalSoLuongSua += $chiTietSua->tong_so_luong_xuat; @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTietSua->xuatHang->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTietSua->xuatHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTietSua->xuatHang->doanhNghiep->chuHang->ten_chu_hang }}</td>
                                    <td>{{ $chiTietSua->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ $chiTietSua->tong_so_luong_xuat }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTietSua->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="5"><strong>Tổng cộng:</strong></td>
                                <td><strong>{{ $totalSoLuongSua }}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $xuatCanh->ma_doanh_nghiep)
                            <div class="row">
                                <center>
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                            class="btn btn-danger px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Hủy yêu cầu sửa
                                        </button>
                                    </a>
                                </center>
                            </div>
                        @endif
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1)
                            <div class="row mt-3">
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#duyetToKhaiModal"
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
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Xác nhận duyệt --}}
    {{-- Tình trạng: Chờ thông quan --}}
    <div class="modal fade" id="duyetToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt yêu cầu sửa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form
                    action="{{ route('xuat-canh.duyet-yeu-cau-sua-xuat-canh', ['ma_yeu_cau' => $xuatCanhSua->ma_yeu_cau]) }}"
                    method="POST">
                    <div class="modal-body">
                        Xác nhận duyệt yêu cầu sửa này ?
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-success"">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
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
                <form
                    action="{{ route('xuat-canh.huy-yeu-cau-sua-xuat-canh', ['ma_yeu_cau' => $xuatCanhSua->ma_yeu_cau]) }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa này?</p>
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
        });
    </script>
@stop
