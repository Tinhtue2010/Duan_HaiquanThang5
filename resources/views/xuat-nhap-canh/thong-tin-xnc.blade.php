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
                    <a class="return-link" href="/danh-sach-xnc">
                        <p>
                            < Quay lại quản lý nhập cảnh </p>
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
                            <p class="fs-5"><strong>Tổng trọng tải (Tấn) :</strong> {{ $xuatNhapCanh->tong_trong_tai ?? '' }}
                            </p>
                            <p class="fs-5"><strong>Ghi chú :</strong> {{ $xuatNhapCanh->ghi_chu ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        <center>
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('xuat-nhap-canh.sua-xnc', ['ma_xnc' => $xuatNhapCanh->ma_xnc]) }}">
                                        <button class="btn btn-warning px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/edit.png') }}">
                                            Sửa theo dõi
                                        </button>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                            class="btn btn-danger px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Hủy theo dõi
                                        </button>
                                    </a>
                                </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Xác nhận Hủy --}}
    <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy theo dõi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-nhap-canh.huy-xnc') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy theo dõi này?</p>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_xnc" value="{{ $xuatNhapCanh->ma_xnc }}">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="thayDoiCongChucModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-nhap-canh.thay-doi-cong-chuc-xnc') }}" method="POST">
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
                        <input type="hidden" value="{{ $xuatNhapCanh->ma_xnc }}" name="ma_xnc">
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
