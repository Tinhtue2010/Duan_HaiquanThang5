@extends('layout.user-layout')

@section('title', 'Xem yêu cầu sửa xuất nhập cảnh')

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
                        href={{ route('xuat-nhap-canh.thong-tin-xnc', ['ma_xnc' => $xuatNhapCanhSua->ma_xnc]) }}>
                        <p>
                            < Quay lại thông tin phiếu xuất </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark">Tờ khai nhập cảnh số {{ $xuatNhapCanh->ma_xnc }} - Ngày
                        {{ \Carbon\Carbon::parse($xuatNhapCanh->ngay_them)->format('d-m-Y') }}
                    </h2>

                    <h1 class="text-center">Thông tin ban đầu</h1>
                    <h2 class="text-center text-dark"> Phương tiện:
                        {{ $xuatNhapCanh->PTVTXuatCanh->ten_phuong_tien_vt ?? '' }}</h2>
                    <h2 class="text-center text-dark">
                        Số thẻ: {{ $xuatNhapCanh->so_the ?? '' }}
                    </h2>
                    @if ($xuatNhapCanh->is_hang_nong == 1)
                        <h2 class="text-center text-dark">
                            Loại hàng: Hàng nóng
                        </h2>
                    @elseif($xuatNhapCanh->is_hang_lanh == 1)
                        <h2 class="text-center text-dark">
                            Loại hàng: Hàng lạnh
                        </h2>
                    @else
                        <h2 class="text-center text-dark">
                            Chưa chọn loại hàng
                        </h2>
                    @endif
                    <hr />
                    <div class="row mt-4 justify-content-center">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong>Giờ nhập cảnh :</strong>
                                {{ $xuatNhapCanh->thoi_gian_nhap_canh ?? '' }}</p>
                            <p class="fs-5"><strong>Giờ xuất cảnh :</strong>
                                {{ $xuatNhapCanh->thoi_gian_xuat_canh ?? '' }}
                            </p>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong>Số lượng máy :</strong> {{ $xuatNhapCanh->so_luong_may ?? '' }}
                            </p>
                            <p class="fs-5"><strong>Tổng trọng tải (Tấn) :</strong>
                                {{ $xuatNhapCanh->tong_trong_tai ?? '' }}
                            </p>
                            <p class="fs-5"><strong>Ghi chú :</strong> {{ $xuatNhapCanh->ghi_chu ?? '' }}
                            </p>
                        </div>
                    </div>

                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>


                    <h1 class="text-center">Thông tin sau khi sửa</h1>
                    <h2
                        class="text-center {{ optional($xuatNhapCanhSua->PTVTXuatCanh)->ten_phuong_tien_vt !== optional($xuatNhapCanh->PTVTXuatCanh)->ten_phuong_tien_vt ? 'text-warning' : 'text-dark' }}">
                        Phương tiện: {{ $xuatNhapCanhSua->PTVTXuatCanh->ten_phuong_tien_vt ?? '' }}
                    </h2>

                    <h2
                        class="text-center {{ $xuatNhapCanhSua->so_the !== $xuatNhapCanh->so_the ? 'text-warning' : 'text-dark' }}">
                        Số thẻ: {{ $xuatNhapCanhSua->so_the ?? '' }}
                    </h2>

                    @php
                        $loaiHangTextSua =
                            $xuatNhapCanhSua->is_hang_nong == 1
                                ? 'Hàng nóng'
                                : ($xuatNhapCanhSua->is_hang_lanh == 1
                                    ? 'Hàng lạnh'
                                    : 'Chưa chọn loại hàng');
                        $loaiHangTextCu =
                            $xuatNhapCanh->is_hang_nong == 1
                                ? 'Hàng nóng'
                                : ($xuatNhapCanh->is_hang_lanh == 1
                                    ? 'Hàng lạnh'
                                    : 'Chưa chọn loại hàng');
                    @endphp
                    <h2 class="text-center {{ $loaiHangTextSua !== $loaiHangTextCu ? 'text-warning' : 'text-dark' }}">
                        Loại hàng: {{ $loaiHangTextSua }}
                    </h2>

                    <hr />
                    <div class="row mt-4 justify-content-center">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p
                                class="fs-5 {{ $xuatNhapCanhSua->thoi_gian_nhap_canh !== $xuatNhapCanh->thoi_gian_nhap_canh ? 'text-warning' : '' }}">
                                <strong>Giờ nhập cảnh :</strong> {{ $xuatNhapCanhSua->thoi_gian_nhap_canh ?? '' }}
                            </p>
                            <p
                                class="fs-5 {{ $xuatNhapCanhSua->thoi_gian_xuat_canh !== $xuatNhapCanh->thoi_gian_xuat_canh ? 'text-warning' : '' }}">
                                <strong>Giờ xuất cảnh :</strong> {{ $xuatNhapCanhSua->thoi_gian_xuat_canh ?? '' }}
                            </p>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p
                                class="fs-5 {{ $xuatNhapCanhSua->so_luong_may !== $xuatNhapCanh->so_luong_may ? 'text-warning' : '' }}">
                                <strong>Số lượng máy :</strong> {{ $xuatNhapCanhSua->so_luong_may ?? '' }}
                            </p>
                            <p
                                class="fs-5 {{ $xuatNhapCanhSua->tong_trong_tai !== $xuatNhapCanh->tong_trong_tai ? 'text-warning' : '' }}">
                                <strong>Tổng trọng tải (Tấn) :</strong> {{ $xuatNhapCanhSua->tong_trong_tai ?? '' }}
                            </p>
                            <p
                                class="fs-5 {{ $xuatNhapCanhSua->ghi_chu !== $xuatNhapCanh->ghi_chu ? 'text-warning' : '' }}">
                                <strong>Ghi chú :</strong> {{ $xuatNhapCanhSua->ghi_chu ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('xuat-nhap-canh.sua-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc]) }}">
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
                        @if (Auth::user()->loai_tai_khoan == 'Admin')
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
                        action="{{ route('xuat-nhap-canh.duyet-yeu-cau-sua-xnc', ['ma_yeu_cau' => $xuatNhapCanhSua->ma_yeu_cau]) }}"
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
        <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form
                        action="{{ route('xuat-nhap-canh.huy-yeu-cau-sua-xnc', ['ma_yeu_cau' => $xuatNhapCanhSua->ma_yeu_cau]) }}"
                        method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-danger">Xác nhận hủy yêu cầu sửa phiếu này?</p>
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
