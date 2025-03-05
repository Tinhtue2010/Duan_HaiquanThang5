@extends('layout.user-layout')

@section('title', 'Thông tin tờ khai phương tiện vận tải')

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
                @endif
                <div class="col-6">
                    <a class="return-link" href="/danh-sach-to-khai-ptvt">
                        <p>
                            < Quay lại quản lý tờ khai </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">TỜ KHAI XẾP HÀNG LÊN PHƯƠNG TIỆN VẬN TẢI</h2>
                    <h2 class="text-center">Số: {{ $phuongTienVT->so_to_khai_ptvt }} ngày
                        {{ \Carbon\Carbon::parse($phuongTienVT->ngay_dang_ky)->format('d-m-Y') }}
                    </h2>
                    <hr />
                    {{-- <h2 class="text-center text-dark">Tờ khai phương tiện vận tải xuất cảnh: {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}</h2> --}}
                    <div class="row mt-4">
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong value="">Số tờ khai phương tiện vận tải xuất cảnh :</strong>
                                {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}</p>
                            <p class="fs-5"><strong>Tên doanh nghiệp :</strong> {{ $PTVTXuatCanh->doanhNghiep->ten_doanh_nghiep }}</p>
                            <p class="fs-5"><strong>Tên thuyền trưởng :</strong>
                                {{ $PTVTXuatCanh->ten_thuyen_truong }}</p>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-5">
                            <p class="fs-5"><strong>Tên phương tiện vận tải :</strong>
                                {{ $PTVTXuatCanh->ten_phuong_tien_vt }}</p>
                            <p class="fs-5"><strong>Cảng đến :</strong> {{ $PTVTXuatCanh->cang_den }}</p>
                            <p class="fs-5"><strong>Số giấy chứng nhận :</strong>
                                {{ $PTVTXuatCanh->so_giay_chung_nhan }}</p>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <h2 class="text-center text-dark">Thông tin phiếu xuất</h2>

                        <table id="dataTable" class="table table-bordered fs-6">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Số Tờ Khai Nhập</th>
                                    <th>Lần Xuất</th>
                                    <th>Tên Loại Hình</th>
                                    <th>Mã Loại Hình</th>
                                    <th>Ngày Đăng Ký</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($toKhaiXuats as $index => $toKhaiXuat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                        <td>{{ $toKhaiXuat->so_to_khai_nhap }}</td>
                                        <td>{{ $toKhaiXuat->lan_xuat_canh }}</td>
                                        <td>{{ $toKhaiXuat->loaiHinh->ten_loai_hinh }}</td>
                                        <td>{{ $toKhaiXuat->ma_loai_hinh }}</td>
                                        <td>{{ \Carbon\Carbon::parse($toKhaiXuat->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        <td>{{ $toKhaiXuat->tong_so_luong }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($phuongTienVT->trang_thai) == 'Đang chờ duyệt')
                                <h2 class="text-primary">{{ $phuongTienVT->trang_thai }}</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $phuongTienVT->ma_doanh_nghiep)
                                    )
                                    <div class="row mt-3">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xinHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Xin hủy tờ khai
                                            </button>

                                        </a>
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#hoanThanhModal"
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
                                    </div>
                                @endif
                            @elseif (trim($phuongTienVT->trang_thai) == 'Đang chờ duyệt (Từ chối hủy)')
                                <h2 class="text-primary">{{ $phuongTienVT->trang_thai }}</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                <h3 class="text-dark">Lý do từ chối: {{ $phuongTienVT->ghi_chu }}</h3>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#hoanThanhModal"
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
                                    </div>
                                @endif
                            @elseif(trim($phuongTienVT->trang_thai) == 'Đã duyệt')
                                <h2 class="text-primary">Đã duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($phuongTienVT->ngay_thong_quan)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Ghi chú: {{ $phuongTienVT->ghi_chu }}</h3>
                            @elseif(trim($phuongTienVT->trang_thai) == 'Đã hủy')
                                <h2 class="text-danger">Tờ khai đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-danger">Ngày hủy:
                                    {{ \Carbon\Carbon::parse($phuongTienVT->updated_at)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Lý do hủy: {{ $phuongTienVT->ghi_chu }}</h3>
                            @elseif(trim($phuongTienVT->trang_thai) == 'Xin hủy tờ khai')
                                <h2 class="text-warning">Doanh nghiệp xin hủy tờ khai</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-warning">Ngày:
                                    {{ \Carbon\Carbon::parse($phuongTienVT->updated_at)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Lý do hủy: {{ $phuongTienVT->ghi_chu }}</h3>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#chapNhanHuyModal"
                                                    class="btn btn-primary ">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/approved2.png') }}">
                                                    Chấp nhận hủy</button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#tuChoiHuyModal"
                                                    class="btn btn-primary px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Từ chối yêu cầu hủy
                                                </button>

                                            </a>
                                        </div>
                                    </div>
                                @endif

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Xác nhận duyệt --}}
    {{-- Tình trạng:  --}}
    <div class="modal fade" id="hoanThanhModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('phuong-tien-vt.hoan-thanh-to-khai-ptvt') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <p>Xác nhận duyệt tờ khai này ?</P>
                        <label for="ghi_chu">Ghi chú:</label>
                        <input name="so_to_khai_ptvt" type="hidden" value="{{ $phuongTienVT->so_to_khai_ptvt }}" />
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </form>
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
                <form action="{{ route('phuong-tien-vt.huy-to-khai-ptvt') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input name="so_to_khai_ptvt" type="hidden" value="{{ $phuongTienVT->so_to_khai_ptvt }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Xác nhận Xin Hủy --}}
    <div class="modal fade" id="xinHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('phuong-tien-vt.xin-huy-to-khai-ptvt') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu"  maxlength="200"></textarea>
                        <input name="so_to_khai_ptvt" type="hidden" value="{{ $phuongTienVT->so_to_khai_ptvt }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Xác nhận Chấp nhận hủy --}}
    <div class="modal fade" id="chapNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="exampleModalLabel">Chấp nhận yêu cầu hủy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('phuong-tien-vt.huy-to-khai-ptvt') }}" method="POST">
                    @csrf
                    <div class="modal-body text-primary">
                        <p class="text-primary">Chấp nhận yêu cầu hủy</p>
                        <textarea class="form-control" type="hidden" name="ghi_chu" value="{{ $phuongTienVT->ghi_chu }}"></textarea>
                        <input name="so_to_khai_ptvt" type="hidden" value="{{ $phuongTienVT->so_to_khai_ptvt }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Xác nhận từ chối hủy --}}
    <div class="modal fade" id="tuChoiHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="exampleModalLabel">Xác nhận từ chối yêu cầu hủy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('phuong-tien-vt.tu-choi-huy-to-khai-ptvt') }}" method="POST">
                    @csrf
                    <div class="modal-body text-primary">
                        <p class="text-primary">Xác nhận từ chối yêu cầu hủy</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập lý do từ chối" name="ghi_chu" maxlength="200"></textarea>
                        <input name="so_to_khai_ptvt" type="hidden" value="{{ $phuongTienVT->so_to_khai_ptvt }}" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
