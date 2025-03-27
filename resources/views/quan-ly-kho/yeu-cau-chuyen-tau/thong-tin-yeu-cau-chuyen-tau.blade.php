@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu chuyển tàu')

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
                    <a class="return-link" href="/danh-sach-yeu-cau-chuyen-tau">
                        <p>
                            < Quay lại danh sách yêu cầu chuyển tàu </p>
                    </a>

                </div>
                <div class="col-6">
                    @if ($yeuCau->file_name)
                        <a href="{{ route('quan-ly-kho.download-yeu-cau-chuyen-tau', [$yeuCau->ma_yeu_cau]) }}">
                            <button class="btn btn-success float-end mx-1">Xem file đính kèm</button>
                        </a>
                    @else
                        <button class="btn btn-secondary float-end mx-1" disabled>Không có file đính kèm</button>
                    @endif

                    @if (trim($yeuCau->trang_thai) != '0')
                        <a href="{{ route('quan-ly-kho.in-phieu-chuyen-tau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                            <button class="btn btn-success float-end"> In phiếu yêu cầu</button>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}

                    </h2>
                    <h2 class="text-center">YÊU CẦU CHUYỂN HÀNG SANG TÀU MỚI</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }} - Đoàn tàu số:
                        {{ $yeuCau->ten_doan_tau }}</h2>
                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Số container</th>
                                <th>Tàu hiện tại</th>
                                <th>Tàu mới</th>
                                @if ($yeuCau->trang_thai == '2')
                                    <th>Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->so_container }}</td>
                                    <td>{{ $chiTiet->tau_goc }}</td>
                                    <td>{{ $chiTiet->tau_dich }}</td>
                                    @if ($yeuCau->trang_thai == '2')
                                        <td>
                                            <a
                                                href="{{ route('export.theo-doi-tru-lui', ['cong_viec' => 4, 'ma_yeu_cau' => $yeuCau->ma_yeu_cau, 'so_to_khai_nhap' => $chiTiet->so_to_khai_nhap]) }}">
                                                <center>
                                                    <button class="btn btn-primary">Theo dõi trừ lùi</button>
                                                </center>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($yeuCau->trang_thai) == '1')
                                <h2 class="text-primary">Đang chờ duyệt</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanModal"
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
                                                    Hủy yêu cầu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $yeuCau->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('quan-ly-kho.sua-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                    Sửa yêu cầu
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy yêu cầu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($yeuCau->trang_thai) == '2')
                                <h2 class="text-primary">Đã duyệt</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-success">Cán bộ công chức phụ trách: {{ $yeuCau->ten_cong_chuc ?? ''}}</h2>
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $yeuCau->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('quan-ly-kho.sua-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                                    Sửa yêu cầu
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy yêu cầu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($yeuCau->trang_thai) == '3')
                                <h2 class="text-warning">Doanh nghiệp đề nghị sửa yêu cầu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                <h2 class="text-primary">Cán bộ công chức phụ trách: {{ $yeuCau->ten_cong_chuc ?? '' }}</h2>
                                <div class="row">
                                    <center>
                                        <div class="col-6">
                                            <a
                                                href="{{ route('quan-ly-kho.xem-sua-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            @elseif(trim($yeuCau->trang_thai) == '0')
                                <h2 class="text-danger">Yêu cầu đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h3 class="text-dark">Lý do hủy: {{ $yeuCau->ghi_chu }}</h3>
                            @elseif(trim($yeuCau->trang_thai) == '4')
                                <h2 class="text-danger">Doanh nghiệp đề nghị hủy yêu cầu</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h3 class="text-dark">{{ $yeuCau->ghi_chu }}</h3>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Duyệt đề nghị
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanTuChoiHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Từ chối đề nghị
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @elseif(Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $yeuCau->ma_doanh_nghiep)
                                    <center>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanTuChoiHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy đề nghị
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                @endif
                            @endif
                        </div>
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
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.duyet-yeu-cau-chuyen-tau') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu và chuyển tàu?</h5>
                        <div class="form-group">
                            <label class="label-text mb-1" for="">Cán bộ công chức phụ trách</label>
                            <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
                                <option></option>
                                @foreach ($congChucs as $congChuc)
                                    <option value="{{ $congChuc->ma_cong_chuc }}">
                                        {{ $congChuc->ten_cong_chuc ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
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
                <form action="{{ route('quan-ly-kho.huy-yeu-cau-chuyen-tau') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy yêu cầu này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
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
                <form action="{{ route('quan-ly-kho.huy-huy-yeu-cau-chuyen-tau') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                            <p class="text-danger">Xác nhận từ chối đề nghị xin hủy của yêu cầu này?</p>
                        @else
                            <p class="text-danger">Xác nhận hủy đề nghị xin hủy của yêu cầu này?</p>
                        @endif
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
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
