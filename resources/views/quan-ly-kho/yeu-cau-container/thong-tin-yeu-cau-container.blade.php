@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu chuyển container')

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
                    <a class="return-link" href="/danh-sach-yeu-cau-container">
                        <p>
                            < Quay lại danh sách yêu cầu chuyển container </p>
                    </a>

                </div>
                <div class="col-6">
                    @if ($yeuCau->file_name)
                        <a href="{{ route('quan-ly-kho.download-yeu-cau-container', [$yeuCau->ma_yeu_cau]) }}">
                            <button class="btn btn-success float-end mx-1">Xem file đính kèm</button>
                        </a>
                    @else
                        <button class="btn btn-secondary float-end mx-1" disabled>Không có file đính kèm</button>
                    @endif
                    @if (trim($yeuCau->trang_thai) == 'Đã duyệt')
                        <a
                            href="{{ route('quan-ly-kho.in-phieu-chuyen-container', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                            <button class="btn btn-success float-end"> In phiếu yêu cầu</button>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">

                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center">YÊU CẦU CHUYỂN HÀNG SANG CONTAINER MỚI</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }} - Đoàn tàu số:
                        {{ $yeuCau->ten_doan_tau }}</h2>
                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr style="vertical-align: middle; text-align: center;">
                                <th>STT</th>
                                <th>Số tờ khai</th>
                                <th>Số container cũ / Tàu cũ</th>
                                <th>Số lượng chuyển (kiện)</th>
                                <th>Số container mới</th>
                                <th>Số tờ khai tại container mới</th>
                                <th>Số lượng tồn trong container (kiện)</th>
                                <th>Tổng hàng hóa sau khi chuyển (kiện)</th>
                                @if ($yeuCau->trang_thai == 'Đã duyệt')
                                    <th>Thao tác</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr class="text-center" data-chitiet='@json($chiTiet)'>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->so_container_goc }}
                                        {{-- ({{ $chiTiet->nhapHang->phuong_tien_vt_nhap ?? ' ' }}) --}}
                                    </td>
                                    <td>{{ $chiTiet->so_luong_chuyen }}</td>
                                    <td>{{ $chiTiet->so_container_dich }}
                                    <td>
                                        {!! $chiTiet->so_to_khai_cont_moi !!}
                                    </td>
                                    <td>{{ $chiTiet->so_luong_ton_cont_moi }}</td>
                                    <td>{{ $chiTiet->so_luong_chuyen + $chiTiet->so_luong_ton_cont_moi }}</td>
                                    @if ($yeuCau->trang_thai == 'Đã duyệt')
                                        <td>
                                            <a
                                                href="{{ route('export.theo-doi-tru-lui', ['cong_viec' => 3, 'ma_yeu_cau' => $yeuCau->ma_yeu_cau, 'so_to_khai_nhap' => $chiTiet->so_to_khai_nhap]) }}">
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
                            @if (trim($yeuCau->trang_thai) == 'Đang chờ duyệt')
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
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
                                                    Hủy nhập đơn
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
                                                href="{{ route('quan-ly-kho.sua-yeu-cau-container', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
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
                            @elseif(trim($yeuCau->trang_thai) == 'Đã duyệt')
                                <h2 class="text-success">Đã duyệt</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Cán bộ công chức phụ trách: {{ $yeuCau->ten_cong_chuc }}</h2>
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $yeuCau->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('quan-ly-kho.sua-yeu-cau-container', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
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
                                @elseif(Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <center>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#duyetDaHoanThanhModal"
                                                    class="btn btn-success ">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/approved2.png') }}">
                                                    Đã hoàn thành yêu cầu</button>
                                            </a>
                                        </div>
                                    </center>
                                @endif
                            @elseif(trim($yeuCau->trang_thai) == 'Đã hoàn thành')
                                <h2 class="text-success">Đã hoàn thành yêu cầu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-primary">Công chức phụ trách:
                                    {{ $yeuCau->congChuc->ten_cong_chuc ?? '' }}</h2>
                                <h2 class="text-success">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($yeuCau->ngay_duyet)->format('d-m-Y') }}</h2>
                            @elseif(trim($yeuCau->trang_thai) == 'Doanh nghiệp đề nghị sửa yêu cầu')
                                <h2 class="text-warning">Doanh nghiệp đề nghị sửa yêu cầu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                <h2 class="text-primary">Cán bộ công chức phụ trách: {{ $yeuCau->ten_cong_chuc }}</h2>
                                <div class="row">
                                    <center>
                                        <div class="col-6">
                                            <a
                                                href="{{ route('quan-ly-kho.xem-sua-yeu-cau-container', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </div>
                                    </center>
                                </div>
                            @elseif(trim($yeuCau->trang_thai) == 'Đã hủy')
                                <h2 class="text-danger">Yêu cầu đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h3 class="text-dark">Lý do hủy: {{ $yeuCau->ghi_chu }}</h3>
                            @elseif(trim($yeuCau->trang_thai) == 'Doanh nghiệp đề nghị hủy yêu cầu')
                                <h2 class="text-danger">Doanh nghiệp đề nghị hủy yêu cầu</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h3 class="text-dark">{{ $yeuCau->ghi_chu }}</h3>
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
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
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.duyet-yeu-cau-container') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu chuyển container?</h5>
                        <div class="form-group">
                            <label class="label-text mb-1 mt-2" for=""><strong>Cán bộ công chức phụ
                                    trách</strong></label>
                            <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc">
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
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="duyetDaHoanThanhModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.duyet-hoan-thanh-container') }}" method="POST">
                    <div class="modal-body">
                        <p class="fw-bold">Xác nhận duyệt đã hoàn thành yêu cầu này ?</p>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <input type="hidden" value="{{ $yeuCau->ma_yeu_cau }}" name="ma_yeu_cau">
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
                <form action="{{ route('quan-ly-kho.huy-yeu-cau-container') }}" method="POST">
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
                <form action="{{ route('quan-ly-kho.huy-huy-yeu-cau-container') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
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
