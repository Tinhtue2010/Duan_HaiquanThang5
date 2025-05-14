@extends('layout.user-layout')

@section('title', 'Xem yêu cầu sửa nhập cảnh')

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
                        href={{ route('nhap-canh.thong-tin-nhap-canh', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}>
                        <p>
                            < Quay lại thông tin phiếu nhập </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $nhapCanh->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">Tờ khai nhập cảnh số {{ $nhapCanh->ma_nhap_canh }}
                    </h2>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $nhapCanh->PTVTXuatCanh->ten_phuong_tien_vt }} -
                        Ngày
                        {{ \Carbon\Carbon::parse($nhapCanh->ngay_dang_ky)->format('d-m-Y') }}</h2>
                    <hr />
                    <h2 class="text-center mt-3">Tờ khai nhập cảnh ban đầu</h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $nhapCanh->ten_thuyen_truong }}</h2>
                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $nhapCanh->ten_chu_hang }}
                    </h2>
                    <h2 class="text-center text-dark">
                        Địa chỉ: {{ $nhapCanh->dia_chi_chu_hang }}
                    </h2>
                    <hr />

                    @if ($nhapCanh->is_khong_hang == 0)
                        <div class="row mt-4 justify-content-center">
                            <div class="col-1"></div>
                            <div class="col-5">
                                <p class="fs-5"><strong>Loại hàng :</strong>
                                    {{ $nhapCanh->loai_hang ?? '' }}</p>
                                <p class="fs-5"><strong>Đơn vị tính :</strong> {{ $nhapCanh->don_vi_tinh ?? '' }}
                                </p>
                            </div>
                            <div class="col-1"></div>
                            <div class="col-5">
                                <p class="fs-5"><strong>Số lượng :</strong> {{ $nhapCanh->so_luong ?? '' }}
                                </p>
                                <p class="fs-5"><strong>Trọng lượng :</strong> {{ $nhapCanh->trong_luong ?? '' }}
                                </p>
                            </div>
                            <div class="col-1"></div>
                            <div class="col-11">
                                <p class="fs-5"><strong>Thông tin hàng hóa :</strong> {{ $nhapCanh->ten_hang_hoa ?? '' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <center>
                            <h2 class="text-primary">Tờ khai không có hàng hóa</h2>
                        </center>
                    @endif
                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h2 class="text-center">Tờ khai nhập cảnh sau khi sửa</h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $nhapCanhSua->ten_thuyen_truong }}</h2>
                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $nhapCanhSua->ten_chu_hang }}
                    </h2>
                    <h2 class="text-center text-dark">
                        Địa chỉ: {{ $nhapCanhSua->dia_chi_chu_hang }}
                    </h2>
                    <hr />

                    @if ($nhapCanhSua->is_khong_hang == 0)
                        <div class="row mt-4 justify-content-center">
                            <div class="col-1"></div>
                            <div class="col-5">
                                <p class="fs-5"><strong>Loại hàng :</strong>
                                    {{ $nhapCanhSua->loai_hang ?? '' }}</p>
                                <p class="fs-5"><strong>Đơn vị tính :</strong> {{ $nhapCanhSua->don_vi_tinh ?? '' }}
                                </p>
                            </div>
                            <div class="col-1"></div>
                            <div class="col-5">
                                <p class="fs-5"><strong>Số lượng :</strong> {{ $nhapCanhSua->so_luong ?? '' }}
                                </p>
                                <p class="fs-5"><strong>Trọng lượng :</strong> {{ $nhapCanhSua->trong_luong ?? '' }}
                                </p>
                            </div>
                            <div class="col-1"></div>
                            <div class="col-11">
                                <p class="fs-5"><strong>Thông tin hàng hóa :</strong> {{ $nhapCanhSua->ten_hang_hoa ?? '' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <center>
                            <h2 class="text-primary">Tờ khai không có hàng hóa</h2>
                        </center>
                    @endif
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $nhapCanh->ma_doanh_nghiep)
                            <div class="row">
                                <div class="col-6">
                                    <a
                                        href="{{ route('nhap-canh.sua-to-khai-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}">
                                        <button class="btn btn-warning px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                            Tiếp tục sửa
                                        </button>
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
                <form action="{{ route('nhap-canh.duyet-yeu-cau-sua-nc', ['ma_yeu_cau' => $nhapCanhSua->ma_yeu_cau]) }}"
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
                <form action="{{ route('nhap-canh.huy-yeu-cau-sua-nc', ['ma_yeu_cau' => $nhapCanhSua->ma_yeu_cau]) }}"
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
