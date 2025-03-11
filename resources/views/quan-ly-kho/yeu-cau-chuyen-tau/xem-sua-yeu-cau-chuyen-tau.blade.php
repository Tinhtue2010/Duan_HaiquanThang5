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
                    <a class="return-link" href="/thong-tin-yeu-cau-chuyen-tau/{{ $yeuCau->ma_yeu_cau }}">
                        <p>
                            < Quay lại danh sách yêu cầu chuyển tàu </p>
                    </a>

                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">

                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
                    <h2 class="text-center">YÊU CẦU CHUYỂN HÀNG SANG TÀU MỚI</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</h2>
                    <hr>
                    <h1 class="text-center">Yêu cầu ban đầu</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $yeuCau->ten_doan_tau }}</h2>
                    <div class="float-end mb-2 d-flex align-items-center justify-content-center">

                        @if ($yeuCau->file_name)
                            {{ $yeuCau->file_name }}
                            <a href="{{ route('quan-ly-kho.download-yeu-cau-chuyen-tau', [$yeuCau->ma_yeu_cau]) }}">
                                <button class="btn btn-success float-end mx-1">Xem file đính kèm</button>

                            </a>
                        @else
                            <button class="btn btn-secondary float-end mx-1" disabled>Không có file đính kèm</button>
                        @endif

                    </div>
                    <table class="table table-bordered mt-2" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr style="vertical-align: middle; text-align: center;">
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Số container</th>
                                <th>Tàu hiện tại</th>
                                <th>Tàu mới</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTietYeuCaus as $index => $chiTiet)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->so_container }}</td>
                                    <td>{{ $chiTiet->tau_goc }}</td>
                                    <td>{{ $chiTiet->tau_dich }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h1 class="text-center">Yêu cầu sau khi sửa</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $suaYeuCau->ten_doan_tau }}</h2>
                    <div class="float-end mb-2 d-flex align-items-center justify-content-center">
                        @if ($suaYeuCau->file_name)
                            {{ $suaYeuCau->file_name }}
                            <a href="{{ route('quan-ly-kho.download-yeu-cau-chuyen-tau', [$suaYeuCau->ma_yeu_cau,true]) }}">
                                <button class="btn btn-success float-end mx-1">Xem file đính kèm</button>

                            </a>
                        @else
                            <button class="btn btn-secondary float-end mx-1" disabled>Không thay đổi file đính kèm</button>
                        @endif
                    </div>
                    <table class="table table-bordered mt-2" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr style="vertical-align: middle; text-align: center;">
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Số container</th>
                                <th>Tàu hiện tại</th>
                                <th>Tàu mới</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTietSuaYeuCaus as $index => $chiTietSuaYeuCau)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTietSuaYeuCau->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTietSuaYeuCau->so_container }}</td>
                                    <td>{{ $chiTietSuaYeuCau->tau_goc }}</td>
                                    <td>{{ $chiTietSuaYeuCau->tau_dich }}</td>
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
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
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
                                                Hủy yêu cầu sửa
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
                <form action="{{ route('quan-ly-kho.duyet-sua-yeu-cau-chuyen-tau') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu sửa này ?</h5>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_sua_yeu_cau" value="{{ $suaYeuCau->ma_sua_yeu_cau }}">
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
                <form action="{{ route('quan-ly-kho.huy-sua-yeu-cau-chuyen-tau') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <input type="hidden" name="ma_sua_yeu_cau" value="{{ $suaYeuCau->ma_sua_yeu_cau }}">
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
        document.addEventListener('DOMContentLoaded', function() {
            var suaSealModal = document.getElementById('suaSealModal')
            suaSealModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget
                var containerNumber = button.getAttribute('data-container')
                var containerInput = suaSealModal.querySelector('#so_container_hidden')
                containerInput.value = containerNumber
            })
        })
    </script>
@stop
