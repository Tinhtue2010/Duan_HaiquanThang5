@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu chuyển container và tàu')

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
                    <a class="return-link" href="/thong-tin-yeu-cau-tau-cont/{{ $yeuCau->ma_yeu_cau }}">
                        <p>
                            < Quay lại danh sách yêu cầu chuyển container và tàu </p>
                    </a>

                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">

                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
                    <h2 class="text-center">YÊU CẦU CHUYỂN HÀNG SANG CONTAINER VÀ TÀU MỚI</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</h2>
                    <hr>
                    <h1 class="text-center">Yêu cầu ban đầu</h1>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} - Ngày
                        {{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y') }}</h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $xuatCanh->ten_thuyen_truong }}</h2>
                    <hr />
                    <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
                    <table class="table table-bordered mt-2 fs-6" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Lần xuất</th>
                                <th>Số tờ khai xuất</th>
                                <th>Công ty</th>
                                <th>Loại hình</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTiet->xuatHang->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->xuatHang->lan_xuat_canh }}</td>
                                    <td>{{ $chiTiet->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTiet->xuatHang->nhapHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTiet->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('xuat-hang.export-to-khai-xuat', ['so_to_khai_nhap' => $chiTiet->xuatHang->so_to_khai_nhap, 'lan_xuat_canh' => $chiTiet->xuatHang->lan_xuat_canh]) }}">
                                            <button class="btn btn-success">In phiếu</button>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h1 class="text-center">Yêu cầu sau khi sửa</h1>
                    <h2 class="text-center text-dark"> Phương tiện: {{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} -
                        Ngày
                        {{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y') }}</h2>
                    <h2 class="text-center text-dark"> Thuyền trưởng: {{ $xuatCanh->ten_thuyen_truong }}</h2>
                    <hr />
                    <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
                    <table class="table table-bordered mt-2 fs-6" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Lần xuất</th>
                                <th>Số tờ khai xuất</th>
                                <th>Công ty</th>
                                <th>Loại hình</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTiet->xuatHang->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->xuatHang->lan_xuat_canh }}</td>
                                    <td>{{ $chiTiet->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTiet->xuatHang->nhapHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTiet->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('xuat-hang.export-to-khai-xuat', ['so_to_khai_nhap' => $chiTiet->xuatHang->so_to_khai_nhap, 'lan_xuat_canh' => $chiTiet->xuatHang->lan_xuat_canh]) }}">
                                            <button class="btn btn-success">In phiếu</button>
                                        </a>
                                    </td>
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
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
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
                                    $yeuCau->ma_doanh_nghiep)
                            <div class="row">
                                <center>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy yêu cầu
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
                <form action="{{ route('xuat-canh.duyet-sua-xuat-canh') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu sửa này ?</h5>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_yeu_cau" value="{{ $xuatCanhSua->ma_yeu_cau }}">
                        <input type="hidden" name="ma_xuat_canh" value="{{ $xuatCanh->ma_xuat_canh }}">
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
                <form action="{{ route('xuat-canh.huy-sua-xuat-canh') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="ma_yeu_cau" value="{{ $xuatCanhSua->ma_yeu_cau }}">
                        <input type="hidden" name="ma_xuat_canh" value="{{ $xuatCanh->ma_xuat_canh }}">
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
