@extends('layout.user-layout')

@section('title', 'Thông tin phiếu xuất cảnh')

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
                    <a class="return-link" href="/quan-ly-xuat-canh">
                        <p>
                            < Quay lại quản lý xuất cảnh </p>
                    </a>
                </div>
                <div class="col-6">
                    @if (trim($xuatCanh->trang_thai) == '2' || trim($xuatCanh->trang_thai) == '3')
                        <form action="{{ route('xuat-canh.export-to-khai-xuat-canh') }}" method="GET">
                            @csrf
                            @method('POST')
                            <input type="hidden" value={{ $xuatCanh->ma_xuat_canh }} name="ma_xuat_canh">
                            <button class="btn btn-success float-end">In tờ khai xuất cảnh</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $xuatCanh->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">
                        Chủ hàng: {{ $xuatCanh->doanhNghiepChon->ten_doanh_nghiep ?? 'Không' }}
                    </h2>

                    <h2 class="text-center text-dark">Tờ khai xuất cảnh số {{ $xuatCanh->ma_xuat_canh }} - Ngày
                        {{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y') }}
                    </h2>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} -
                        Thuyền trưởng: {{ $xuatCanh->ten_thuyen_truong }} </h2>
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
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->chuHang->ten_chu_hang ?? '' }}</td>
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
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($xuatCanh->trang_thai) == '1')
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if ($xuatCanh->ghi_chu)
                                    <h3 class="text-dark">Ghi chú: {{ $xuatCanh->ghi_chu }}</h3>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatCanh->ma_doanh_nghiep)
                                    <center>
                                        <div class="row">
                                            <div class="col-6">
                                                <a
                                                    href="{{ route('xuat-canh.sua-to-khai-xc', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}">
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
                                                $xuatCanh->ma_doanh_nghiep)
                                        <center>
                                            <div class="row">
                                                <div class="col-6">
                                                    <a
                                                        href="{{ route('xuat-canh.sua-to-khai-xc', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}">
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
                    @elseif(trim($xuatCanh->trang_thai) == '2')
                        <h2 class="text-success">Đã duyệt</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                        <h2 class="text-primary">Công chức phụ trách:
                            {{ $xuatCanh->congChuc->ten_cong_chuc ?? '' }}</h2>
                        <h2 class="text-success">Ngày duyệt:
                            {{ \Carbon\Carbon::parse($xuatCanh->ngay_duyet)->format('d-m-Y') }}</h2>



                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $xuatCanh->ma_doanh_nghiep)
                            <center>
                                <div class="row">
                                    <div class="col-6">
                                        <a
                                            href="{{ route('xuat-canh.sua-to-khai-xc', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}">
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
                                <div class="row">
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#duyetThucXuatModal"
                                                class="btn btn-success ">
                                                <img class="side-bar-icon"
                                                    src="{{ asset('images/icons/approved2.png') }}">
                                                Duyệt đã thực xuất</button>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#thayDoiCongChucModal"
                                                class="btn btn-warning ">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                Thay đổi công chức</button>
                                        </a>
                                    </div>
                                </div>
                            </center>
                        @endif
                    @elseif(trim($xuatCanh->trang_thai) == '3')
                        <h2 class="text-success">Đã duyệt thực xuất</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                        <h2 class="text-primary">Công chức phụ trách:
                            {{ $xuatCanh->congChuc->ten_cong_chuc ?? '' }}</h2>
                        <h2 class="text-success">Ngày duyệt:
                            {{ \Carbon\Carbon::parse($xuatCanh->ngay_duyet)->format('d-m-Y') }}</h2>
                    @elseif(trim($xuatCanh->trang_thai) == '4')
                        <h2 class="text-warning">Doanh nghiệp yêu cầu sửa phiếu</h2>
                        <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                        <center>
                            @if (
                                (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                    DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                        $xuatCanh->ma_doanh_nghiep) ||
                                    (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_canh == 1))
                                <a
                                    href="{{ route('xuat-canh.xem-yeu-cau-sua-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}">
                                    <button class="btn btn-warning px-4">
                                        <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                        Xem sửa đổi
                                    </button>
                                </a>
                            @endif
                        </center>
                    @elseif(trim($xuatCanh->trang_thai) == '5')
                        <h2 class="text-danger">Doanh nghiệp xin hủy</h2>
                        <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                        <h3 class="text-dark">Lý do hủy: {{ $xuatCanh->ghi_chu }}</h3>
                        @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $xuatCanh->ma_doanh_nghiep)
                            <form
                                action="{{ route('xuat-canh.thu-hoi-yeu-cau-huy-xc', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}"
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
                                        action="{{ route('xuat-canh.thu-hoi-yeu-cau-huy-xc', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}"
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
                    @elseif(trim($xuatCanh->trang_thai) == '6' || trim($xuatCanh->trang_thai) == '7' || trim($xuatCanh->trang_thai) == '0')
                        <h2 class="text-danger">Tờ khai đã hủy</h2>
                        <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                        <h2 class="text-danger">Ngày hủy:
                            {{ \Carbon\Carbon::parse($xuatCanh->updated_at)->format('d-m-Y') }}</h2>
                        <h3 class="text-dark">Lý do hủy: {{ $xuatCanh->ghi_chu }}</h3>
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
                <form action="{{ route('xuat-canh.duyet-to-khai-xc') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt phiếu xuất này ?</p>
                        <div class="form-group">
                            <label class="label-text mb-1 mt-2" for="">Cán bộ công chức phụ trách</label>
                            <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
                                <option></option>
                                @foreach ($congChucs as $congChuc)
                                    @if ($congChuc->ma_cong_chuc == $maCongChuc)
                                        <option value="{{ $congChuc->ma_cong_chuc }}" selected>
                                            {{ $congChuc->ten_cong_chuc }}
                                        </option>
                                    @else
                                        <option value="{{ $congChuc->ma_cong_chuc }}">
                                            {{ $congChuc->ten_cong_chuc }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $xuatCanh->ma_xuat_canh }}" name="ma_xuat_canh">
                        <button type="submit" class="btn btn-success">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="duyetThucXuatModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-canh.duyet-thuc-xuat-xc') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt thực xuất tờ khai này ?</p>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $xuatCanh->ma_xuat_canh }}" name="ma_xuat_canh">
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
                <form action="{{ route('xuat-canh.huy-to-khai-xc') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_xuat_canh" value="{{ $xuatCanh->ma_xuat_canh }}">
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
                <form action="{{ route('xuat-canh.yeu-cau-huy-to-khai-xc') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận yêu cầu hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_xuat_canh" value="{{ $xuatCanh->ma_xuat_canh }}">
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
                <form action="{{ route('xuat-canh.thay-doi-cong-chuc-xuat-canh') }}" method="POST">
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
                        <input type="hidden" value="{{ $xuatCanh->ma_xuat_canh }}" name="ma_xuat_canh">
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
