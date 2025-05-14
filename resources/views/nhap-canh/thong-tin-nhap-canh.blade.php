@extends('layout.user-layout')

@section('title', 'Thông tin phiếu nhập cảnh')

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
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-danger') }}</strong>
                    </div>
                @endif

                <div class="col-6">
                    <a class="return-link" href="/quan-ly-nhap-canh">
                        <p>
                            < Quay lại quản lý nhập cảnh </p>
                    </a>
                </div>
                <div class="col-6">
                    <form action="{{ route('nhap-canh.export-to-khai-nhap-canh') }}" method="GET">
                        @csrf
                        @method('POST')
                        <input type="hidden" value={{ $nhapCanh->ma_nhap_canh }} name="ma_nhap_canh">
                        <button class="btn btn-success float-end">In tờ khai nhập cảnh</button>
                    </form>
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $nhapCanh->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">Tờ khai nhập cảnh số {{ $nhapCanh->ma_nhap_canh }} - Ngày
                        {{ \Carbon\Carbon::parse($nhapCanh->ngay_dang_ky)->format('d-m-Y') }}
                    </h2>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $nhapCanh->PTVTXuatCanh->ten_phuong_tien_vt ?? '' }}
                        -
                        Thuyền trưởng: {{ $nhapCanh->ten_thuyen_truong }} </h2>

                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $nhapCanh->doanhNghiepChon->ten_doanh_nghiep ?? ''}}
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
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($nhapCanh->trang_thai) == '1')
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if ($nhapCanh->ghi_chu)
                                    <h3 class="text-dark">Ghi chú: {{ $nhapCanh->ghi_chu }}</h3>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $nhapCanh->ma_doanh_nghiep)
                                    <center>
                                        <div class="row">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('nhap-canh.sua-to-khai-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}">
                                                    <button class="btn btn-warning px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/edit.png') }}">
                                                        Sửa tờ khai
                                                    </button>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="#">
                                                    <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Hủy tờ khai
                                                    </button>
                                                </a>
                                            </div>
                                        </div>
                                    </center>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <center>
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
                                                    <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                        class="btn btn-danger px-4">
                                                        <img class="side-bar-icon"
                                                            src="{{ asset('images/icons/cancel.png') }}">
                                                        Hủy tờ khai
                                                    </button>
                                                </a>
                                            </div>
                                    </center>
                                    @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                            DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                                $nhapCanh->ma_doanh_nghiep)
                                        <center>
                                            <div class="row">
                                                <div class="col-6">
                                                    <a
                                                        href="{{ route('xuat-canh.sua-to-khai-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}">
                                                        <button class="btn btn-warning px-4">
                                                            <img class="side-bar-icon"
                                                                src="{{ asset('images/icons/edit.png') }}">
                                                            Sửa tờ khai
                                                        </button>
                                                    </a>
                                                </div>
                                                <div class="col-6">
                                                    <a href="#">
                                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                            class="btn btn-danger px-4">
                                                            <img class="side-bar-icon"
                                                                src="{{ asset('images/icons/cancel.png') }}">
                                                            Hủy tờ khai
                                                        </button>
                                                    </a>
                                                </div>
                                            </div>
                                            <center>
                                    @endif
                                @endif
                        </div>
                    @elseif(trim($nhapCanh->trang_thai) == '2')
                        <h2 class="text-success">Đã duyệt</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                        <h2 class="text-primary">Công chức phụ trách:
                            {{ $nhapCanh->congChuc->ten_cong_chuc ?? '' }}</h2>
                        <h2 class="text-success">Ngày duyệt:
                            {{ \Carbon\Carbon::parse($nhapCanh->ngay_duyet)->format('d-m-Y') }}</h2>


                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $nhapCanh->ma_doanh_nghiep)
                            <center>
                                <div class="row">
                                    <div class="col-6">
                                        <a
                                            href="{{ route('nhap-canh.sua-to-khai-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}">
                                            <button class="btn btn-warning px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                Sửa tờ khai
                                            </button>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#yeuCauHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy tờ khai
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </center>
                        @elseif(Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1)
                            <center>
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#thayDoiCongChucModal"
                                            class="btn btn-warning ">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                            Thay đổi công chức</button>
                                    </a>
                                </div>
                            </center>
                        @endif
                    @elseif(trim($nhapCanh->trang_thai) == '3')
                        <h2 class="text-success">Đã duyệt thực nhập</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                        <h2 class="text-primary">Công chức phụ trách:
                            {{ $nhapCanh->congChuc->ten_cong_chuc ?? '' }}</h2>
                        <h2 class="text-success">Ngày duyệt:
                            {{ \Carbon\Carbon::parse($nhapCanh->ngay_duyet)->format('d-m-Y') }}</h2>
                    @elseif(trim($nhapCanh->trang_thai) == '4')
                        <h2 class="text-warning">Doanh nghiệp yêu cầu sửa tờ khai</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                        <center>
                            @if (
                                (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                    DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                        $nhapCanh->ma_doanh_nghiep) ||
                                    (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1))
                                <a
                                    href="{{ route('nhap-canh.xem-yeu-cau-sua-nhap-canh', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}">
                                    <button class="btn btn-warning px-4">
                                        <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                        Xem sửa đổi
                                    </button>
                                </a>
                            @endif
                        </center>
                    @elseif(trim($nhapCanh->trang_thai) == '5')
                        <h2 class="text-danger">Doanh nghiệp xin hủy</h2>
                        <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                        <h3 class="text-dark">Lý do hủy: {{ $nhapCanh->ghi_chu }}</h3>
                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $nhapCanh->ma_doanh_nghiep)
                            <form
                                action="{{ route('nhap-canh.thu-hoi-yeu-cau-huy-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}"
                                method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary px-4">
                                    <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                    Thu hồi yêu cầu hủy
                                </button>
                            </form>
                        @elseif(Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1)
                            <div class="row">
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                            class="btn btn-danger px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Chấp nhận hủy
                                        </button>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <form
                                        action="{{ route('nhap-canh.thu-hoi-yeu-cau-huy-nc', ['ma_nhap_canh' => $nhapCanh->ma_nhap_canh]) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Từ chối yêu cầu hủy
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @elseif(trim($nhapCanh->trang_thai) == '6' || trim($nhapCanh->trang_thai) == '7' || trim($nhapCanh->trang_thai) == '0')
                        <h2 class="text-danger">Tờ khai đã hủy</h2>
                        <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                        <h2 class="text-danger">Ngày hủy:
                            {{ \Carbon\Carbon::parse($nhapCanh->updated_at)->format('d-m-Y') }}</h2>
                        <h3 class="text-dark">Lý do hủy: {{ $nhapCanh->ghi_chu }}</h3>
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
                <form action="{{ route('nhap-canh.duyet-to-khai-nc') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt tờ khai này ?</p>
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
                        <input type="hidden" value="{{ $nhapCanh->ma_nhap_canh }}" name="ma_nhap_canh">
                        <button type="submit" class="btn btn-success">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- <div class="modal fade" id="duyetThucXuatModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nhap-canh.duyet-thuc-nhap-nc') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt thực nhập tờ khai này ?</p>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $nhapCanh->ma_nhap_canh }}" name="ma_nhap_canh">
                        <button type="submit" class="btn btn-success">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    {{-- Xác nhận Hủy --}}
    <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nhap-canh.huy-to-khai-nc') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_nhap_canh" value="{{ $nhapCanh->ma_nhap_canh }}">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
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
                <form action="{{ route('nhap-canh.yeu-cau-huy-to-khai-nc') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận yêu cầu hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_nhap_canh" value="{{ $nhapCanh->ma_nhap_canh }}">
                        <button type="submit" class="btn btn-danger">Xác nhận yêu cầu hủy</button>
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
                <form action="{{ route('nhap-canh.thay-doi-cong-chuc-nhap-canh') }}" method="POST">
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
                        <input type="hidden" value="{{ $nhapCanh->ma_nhap_canh }}" name="ma_nhap_canh">
                        <button type="submit" class="btn btn-success">
                            Xác nhận
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
