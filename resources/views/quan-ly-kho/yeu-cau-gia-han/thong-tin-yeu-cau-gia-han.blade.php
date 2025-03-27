@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu gia hạn')

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
                    <a class="return-link" href="/danh-sach-yeu-cau-gia-han">
                        <p>
                            < Quay lại danh sách yêu cầu gia hạn </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}
                        @if (Auth::user()->doanhNghiep && Auth::user()->doanhNghiep->chuHang)
                            - {{ Auth::user()->doanhNghiep->chuHang->ten_chu_hang }}
                        @endif

                    </h2>
                    <h2 class="text-center">YÊU CẦU GIA HẠN TỜ KHAI G21</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</h2>
                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai</th>
                                <th>Số tàu</th>
                                <th>Số container</th>
                                <th>Ngày tờ khai</th>
                                <th>Tên hàng</th>
                                <th>Số ngày gia hạn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($yeuCau->trang_thai == '2' || $yeuCau->trang_thai == '0')
                                @foreach ($chiTiets as $index => $chiTiet)
                                    <tr>
                                        <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                        <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                        <td>{{ $chiTiet->so_tau }}</td>
                                        <td>{{ $chiTiet->so_container }}</td>
                                        <td>{{ \Carbon\Carbon::parse($chiTiet->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        <td>{!! $chiTiet->ten_hang !!}</td>
                                        <td>{!! $chiTiet->so_ngay_gia_han !!}</td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach ($chiTiets as $index => $chiTiet)
                                    <tr>
                                        <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                        <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                        <td>{{ $chiTiet->so_tau }}</td>
                                        <td>{{ $chiTiet->so_container }}</td>
                                        <td>{{ \Carbon\Carbon::parse($chiTiet->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        <td>{!! $chiTiet->ten_hang !!}</td>
                                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                                            <td>
                                                <center>
                                                    <input type="number" class="form-control" id="so_ngay_gia_han_input"
                                                        min="0" placeholder="0" style="width: 100px;">
                                                </center>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
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
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a href="#">
                                                <button id="xacNhanBtn" class="btn btn-success ">
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
                                                href="{{ route('quan-ly-kho.sua-yeu-cau-gia-han', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]) }}">
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
                                <h2 class="text-success">Đã duyệt</h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/success.png') }}">
                            @elseif(trim($yeuCau->trang_thai) == '0')
                                <h2 class="text-danger">Yêu cầu đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h3 class="text-dark">Lý do hủy: {{ $yeuCau->ghi_chu }}</h3>
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
                <form action="{{ route('quan-ly-kho.duyet-yeu-cau-gia-han') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu gia hạn?</h5>
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <input type="hidden" name="rows_data" id="rowsDataInput">
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
                <form action="{{ route('quan-ly-kho.huy-yeu-cau-gia-han') }}" method="POST">
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
    <script>
        $(document).ready(function() {
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');

            nhapYeuCauButton.addEventListener('click', function() {
                const rows = $('#displayTable tbody tr')
                    .map(function() {
                        const cells = $(this).find('td'); // Initialize cells here
                        const so_ngay_gia_han = $(cells[6]).find("input").val() || 0;

                        return {
                            so_to_khai_nhap: $(cells[1]).text(),
                            so_ngay_gia_han: so_ngay_gia_han,
                        };
                    })
                    .get();

                $('#rowsDataInput').val(JSON.stringify(rows));

                // Show the modal
                $('#xacNhanModal').modal('show');
            });
        });
    </script>
@stop
