@extends('layout.user-layout')

@section('title', 'Thông tin phương tiện vận tải xuất cảnh')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;
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
                <div class="col-6">
                    <a class="return-link" href="/danh-sach-ptvt-xc">
                        <p>
                            < Quay lại quản lý phương tiện vận tải </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $phuong_tien_vt->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">THÔNG TIN PHƯƠNG TIỆN VẬN TẢI XUẤT CẢNH</h2>
                    <h2 class="text-center text-dark">Số: {{ $phuong_tien_vt->so_ptvt_xuat_canh }} ngày
                        {{ \Carbon\Carbon::parse($phuong_tien_vt->ngay_dang_ky)->format('d-m-Y') }}
                    </h2>
                    <hr />
                    <div class="row mt-4 justify-content-center">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong>Tên phương tiện vận tải :</strong>
                                {{ $phuong_tien_vt->ten_phuong_tien_vt }}</p>
                            <p class="fs-5"><strong>Tên thuyền trưởng :</strong> {{ $phuong_tien_vt->ten_thuyen_truong }}
                            </p>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong>Cảng đến :</strong> {{ $phuong_tien_vt->cang_den }}</p>
                            <p class="fs-5"><strong>Số giấy chứng nhận
                                    :</strong>{{ $phuong_tien_vt->so_giay_chung_nhan }}</p>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <center>
                            <h4>Thông số xuồng đến</h4>
                        </center>
                        <div class="col-2">
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>Draft :</strong> {{ $phuong_tien_vt->draft_den }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>DWT :</strong> {{ $phuong_tien_vt->dwt_den }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>LOA :</strong> {{ $phuong_tien_vt->loa_den }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>Breadth :</strong> {{ $phuong_tien_vt->breadth_den }}</p>
                        </div>
                    </div>
                    <hr />

                    <div class="row">
                        <center>
                            <h4>Thông số xuồng rời</h4>
                        </center>
                        <div class="col-2">
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>Draft :</strong> {{ $phuong_tien_vt->draft_roi }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>DWT :</strong> {{ $phuong_tien_vt->dwt_roi }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>LOA :</strong> {{ $phuong_tien_vt->loa_roi }}</p>
                        </div>
                        <div class="col-2">
                            <p class="fs-5"><strong>Breadth :</strong> {{ $phuong_tien_vt->breadth_roi }}</p>
                        </div>
                    </div>

                    @if ($phuong_tien_vt->trang_thai == 1)
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                            <div class="row">
                                <hr />
                                <div class="col-3">
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="row">
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
                                                    <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Hủy thông tin
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $phuong_tien_vt->ma_doanh_nghiep)
                            <center>
                                <div class="row">
                                    <div class="col-6">
                                        <a
                                            href="{{ route('phuong-tien-vt.sua-to-khai-ptvt-xc', ['so_ptvt_xuat_canh' => $phuong_tien_vt->so_ptvt_xuat_canh]) }}">
                                            <button class="btn btn-warning px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                Sửa thông tin
                                            </button>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy thông tin
                                            </button>
                                        </a>
                                    </div>
                                </div>
                                <center>
                        @endif
                    @elseif($phuong_tien_vt->trang_thai == 2)
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                            <div class="row">
                                <hr />
                                <div class="col-3">
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <div class="row">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('phuong-tien-vt.sua-to-khai-ptvt-xc', ['so_ptvt_xuat_canh' => $phuong_tien_vt->so_ptvt_xuat_canh]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Sửa thông tin
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Hủy thông tin
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- Đang chờ duyệt --}}
    <div class="modal fade" id="duyetToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('phuong-tien-vt.duyet-to-khai-ptvt') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt phương tiện này ?</p>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $phuong_tien_vt->so_ptvt_xuat_canh }}" name="so_ptvt_xuat_canh">
                        <button type="submit" class="btn btn-success">
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
                <form action="{{ route('phuong-tien-vt.huy-to-khai-ptvt-xc') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <input name="so_ptvt_xuat_canh" type="hidden"
                            value="{{ $phuong_tien_vt->so_ptvt_xuat_canh }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
