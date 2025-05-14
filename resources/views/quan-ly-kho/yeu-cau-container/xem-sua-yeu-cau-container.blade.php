@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu chuyển container ')

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
                    <a class="return-link" href="/thong-tin-yeu-cau/{{ $yeuCau->ma_yeu_cau }}">
                        <p>
                            < Quay lại danh sách yêu cầu chuyển container </p>
                    </a>

                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">

                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
                    <h2 class="text-center">YÊU CẦU CHUYỂN HÀNG SANG CONTAINER</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</h2>
                    <hr>
                    <h1 class="text-center">Yêu cầu ban đầu</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $yeuCau->ten_doan_tau }}</h2>
                    <div class="float-end mb-2 d-flex align-items-center justify-content-center">

                        @if ($yeuCau->file_name)
                            {{ $yeuCau->file_name }}
                            <a href="{{ route('quan-ly-kho.download-yeu-cau-container', [$yeuCau->ma_yeu_cau]) }}">
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
                                <th>Số tờ khai</th>
                                <th>Số container cũ / Tàu cũ</th>
                                <th>Số lượng chuyển (kiện)</th>
                                <th>Số container mới / Tàu mới</th>
                                <th>Số tờ khai tại container mới</th>
                                <th>Số lượng tồn trong container (kiện)</th>
                                <th>Tổng hàng hóa sau khi chuyển (kiện)</th>
                                @if ($yeuCau->trang_thai == '2')
                                    <th>Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($chiTietYeuCaus as $index => $chiTiet)
                                <tr class="text-center" data-chitiet='@json($chiTiet)'>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->so_container_goc }}</br>
                                    <td>{{ $chiTiet->so_luong_chuyen }}</td>
                                    <td>{{ $chiTiet->so_container_dich }}</br>
                                    <td>
                                        {!! $chiTiet->so_to_khai_cont_moi !!}
                                    </td>
                                    <td>{{ $chiTiet->so_luong_ton_cont_moi }}</td>
                                    <td>{{ $chiTiet->so_luong_chuyen + $chiTiet->so_luong_ton_cont_moi }}</td>
                                    @if ($yeuCau->trang_thai == '2')
                                        <td>
                                            <a
                                                href="{{ route('export.theo-doi-tru-lui', ['so_to_khai_nhap' => $chiTiet->so_to_khai_nhap]) }}">
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
                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h1 class="text-center">Yêu cầu sau khi sửa</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $suaYeuCau->ten_doan_tau }}</h2>
                    <div class="float-end mb-2 d-flex align-items-center justify-content-center">
                        @if ($suaYeuCau->file_name)
                            {{ $suaYeuCau->file_name }}
                            <a
                                href="{{ route('quan-ly-kho.download-yeu-cau-chuyen-tau', [$suaYeuCau->ma_yeu_cau, true]) }}">
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
                                <th>Số tờ khai</th>
                                <th>Số container cũ / Tàu cũ</th>
                                <th>Số lượng chuyển (kiện)</th>
                                <th>Số container mới / Tàu mới</th>
                                <th>Số tờ khai tại container mới</th>
                                <th>Số lượng tồn trong container (kiện)</th>
                                <th>Tổng hàng hóa sau khi chuyển (kiện)</th>
                            </tr>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($chiTietSuaYeuCaus as $index => $chiTietSuaYeuCau)
                                <tr class="text-center" data-chitiet='@json($chiTietSuaYeuCau)'>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTietSuaYeuCau->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTietSuaYeuCau->so_container_goc }}
                                    </td>
                                    <td>{{ $chiTietSuaYeuCau->so_luong_chuyen }}</td>
                                    <td>{{ $chiTietSuaYeuCau->so_container_dich }}
                                    </td>
                                    <td>
                                        {!! $chiTietSuaYeuCau->so_to_khai_cont_moi !!}
                                    </td>
                                    <td>{{ $chiTietSuaYeuCau->so_luong_ton_cont_moi }}</td>
                                    <td>{{ $chiTietSuaYeuCau->so_luong_chuyen + $chiTietSuaYeuCau->so_luong_ton_cont_moi }}
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

    {{-- Modal chọn container --}}
    <div class="modal fade" id="thongTinModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chuyển hàng sang container mới</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 mx-3">
                        <div class="card p-3">
                            <div class="row">
                                <h3 class="text-center mb-2">Thông tin dòng yêu cầu</h3>
                                <p class="fs-5"><strong>Số tờ khai:</strong> <span id="modalSoToKhaiNhap"></span></p>
                                <hr />
                                <table class="table table-bordered" id="displayTableHangHoa"
                                    style="vertical-align: middle; text-align: center;">
                                    <thead>
                                        <tr style="vertical-align: middle; text-align: center;">
                                            <th>STT</th>
                                            <th>Tên hàng hóa</th>
                                            <th>Số container</th>
                                            <th>Số lượng</th>
                                            <th>Số container mới</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="doneButton">Chọn</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
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
                <form action="{{ route('quan-ly-kho.duyet-sua-yeu-cau-container') }}" method="POST">
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
                <form action="{{ route('quan-ly-kho.huy-sua-yeu-cau-container') }}" method="POST">
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
        $(document).on('click', '.xemChiTiet', function() {
            let chiTiet = $(this).data('chitiet');
            $('#modalSoToKhaiNhap').text(chiTiet.so_to_khai_nhap);

            let tableBody = $('#displayTableHangHoa tbody');
            tableBody.empty();

            chiTiet.yeu_cau_container_hang_hoa.forEach((hangHoa, index) => {
                let row = `<tr>
                       <td>${index + 1}</td>
                       <td>${hangHoa.ten_hang}</td>
                       <td>${hangHoa.so_container_cu}</td>
                       <td>${hangHoa.so_luong}</td>
                       <td>${hangHoa.so_container_moi}</td>
                   </tr>`;
                tableBody.append(row);
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            // Handle row click event
            $('#displayTable tbody').on('click', 'tr', function(event) {
                // Check if the click is on the last column or its children (e.g., a button)
                if ($(event.target).closest('td:last-child').length) {
                    return; // Exit the function to avoid triggering the modal
                }

                let chiTiet = $(this).data('chitiet');
                $('#modalSoToKhaiNhap').text(chiTiet.so_to_khai_nhap);

                let tableBody = $('#displayTableHangHoa tbody');
                tableBody.empty();

                chiTiet.yeu_cau_container_hang_hoa.forEach((hangHoa, index) => {
                    let row = `<tr>
               <td>${index + 1}</td>
               <td>${hangHoa.ten_hang}</td>
               <td>${hangHoa.so_container_cu}</td>
               <td>${hangHoa.so_luong}</td>
               <td>${hangHoa.so_container_moi}</td>
           </tr>`;
                    tableBody.append(row);
                });

                // Show the modal
                $('#thongTinModal').modal('show');
            });
        });
    </script>

@stop
