@extends('layout.user-layout')

@section('title', 'Xem yêu cầu sửa phiếu xuất')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;
    @endphp
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="">
                        <strong>{!! nl2br(session('alert-danger')) !!}</strong>
                    </div>
                @endif

                <div class="col-6">
                    <a class="return-link"
                        href={{ route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $suaXuatHang->so_to_khai_xuat]) }}>
                        <p>
                            < Quay lại thông tin phiếu xuất </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $xuatHang->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">Số: {{ $xuatHang->so_to_khai_xuat }},ngày
                        {{ \Carbon\Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                        <hr />
                        {{-- <h2 class="text-center text-dark">Tờ khai phương tiện vận tải xuất cảnh: {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}</h2> --}}
                        <div class="row mt-4">
                            <h1 class="text-center">Phiếu xuất ban đầu</h1>
                            <h2 class="text-center text-dark">Phiếu
                                {{ $xuatHang->loaiHinh ? $xuatHang->loaiHinh->ten_loai_hinh : '' }}
                                ({{ $xuatHang->loaiHinh->ma_loai_hinh }})
                            </h2>
                        </div>
                        <hr />
                        <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
                        <table class="table table-bordered mt-2 fs-6" id="displayTable"
                            style="vertical-align: middle; text-align: center;">
                            <thead class="align-middle">
                                <tr style="vertical-align: middle; text-align: center;">
                                    <th>STT</th>
                                    <th>SỐ TỜ KHAI NHẬP</th>
                                    <th>TÊN HÀNG</th>
                                    <th>XUẤT XỨ</th>
                                    <th>SỐ LƯỢNG XUẤT</th>
                                    <th>ĐƠN VỊ TÍNH</th>
                                    <th>ĐƠN GIÁ (USD)</th>
                                    <th>TRỊ GIÁ (USD)</th>
                                    <th>SỐ CONTAINER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hangHoaRows as $index => $hangHoa)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $hangHoa->so_to_khai_nhap }}</td>
                                        <td>{{ $hangHoa->ten_hang }}</td>
                                        <td>{{ $hangHoa->xuat_xu }}</td>
                                        <td>{{ $hangHoa->so_luong_xuat }}</td>
                                        <td>{{ $hangHoa->don_vi_tinh }}</td>
                                        <td>{{ number_format($hangHoa->don_gia, 2) }}</td>
                                        <td>{{ number_format($hangHoa->tri_gia, 2) }}</td>
                                        <td>{{ $hangHoa->so_container }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"><strong>Tổng cộng</strong></td>
                                    <td><strong>{{ $soLuongSum }}</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong>{{ number_format($triGiaSum, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <hr />
                        <center>
                            <div class="col-6">
                                <h3 class="text-center text-dark">Thông tin phương tiện vận tải</h3>
                                <table class="table table-bordered mt-2 fs-6" id="displayTable"
                                    style="vertical-align: middle; text-align: center;">
                                    <thead class="align-middle">
                                        <tr style="vertical-align: middle; text-align: center;">
                                            <th>STT</th>
                                            <th>Tên phương tiện vận tải</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ptvts as $index => $ptvt)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $ptvt->PTVTXuatCanh->ten_phuong_tien_vt ?? 'N/A' }} (Số:
                                                    {{ $ptvt->PTVTXuatCanh->so_ptvt_xuat_canh }})</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </center>


                        <center>
                            <div class="custom-line mb-2"></div>
                        </center>
                        <div class="row mt-4">
                            <h1 class="text-center">Phiếu xuất sau khi sửa</h1>
                            <h2 class="text-center text-dark">Phiếu
                                {{ $suaXuatHang->loaiHinh ? $suaXuatHang->loaiHinh->ten_loai_hinh : '' }}
                                ({{ $suaXuatHang->loaiHinh->ma_loai_hinh }})
                            </h2>
                        </div>
                        <hr />
                        <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
                        <table class="table table-bordered mt-2 fs-6" id="displayTable"
                            style="vertical-align: middle; text-align: center;">
                            <thead class="align-middle">
                                <tr style="vertical-align: middle; text-align: center;">
                                    <th>STT</th>
                                    <th>SỐ TỜ KHAI NHẬP</th>
                                    <th>TÊN HÀNG</th>
                                    <th>XUẤT XỨ</th>
                                    <th>SỐ LƯỢNG XUẤT</th>
                                    <th>ĐƠN VỊ TÍNH</th>
                                    <th>ĐƠN GIÁ (USD)</th>
                                    <th>TRỊ GIÁ (USD)</th>
                                    <th>SỐ CONTAINER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suaHangHoaRows as $index => $hangHoa)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $hangHoa->so_to_khai_nhap }}</td>
                                        <td>{{ $hangHoa->ten_hang }}</td>
                                        <td>{{ $hangHoa->xuat_xu }}</td>
                                        <td>{{ $hangHoa->so_luong_xuat }}</td>
                                        <td>{{ $hangHoa->don_vi_tinh }}</td>
                                        <td>{{ number_format($hangHoa->don_gia, 2) }}</td>
                                        <td>{{ number_format($hangHoa->tri_gia, 2) }}</td>
                                        <td>{{ $hangHoa->so_container }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"><strong>Tổng cộng</strong></td>
                                    <td><strong>{{ $suaSoLuongSum }}</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong>{{ number_format($suaTriGiaSum, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <hr />
                        <center>
                            <div class="col-6">
                                <h3 class="text-center text-dark">Thông tin phương tiện vận tải</h3>
                                <table class="table table-bordered mt-2 fs-6" id="displayTable"
                                    style="vertical-align: middle; text-align: center;">
                                    <thead class="align-middle">
                                        <tr style="vertical-align: middle; text-align: center;">
                                            <th>STT</th>
                                            <th>Tên phương tiện vận tải</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($suaPTVTs as $index => $suaPTVT)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $suaPTVT->PTVTXuatCanh->ten_phuong_tien_vt ?? 'N/A' }} (Số:
                                                    {{ $suaPTVT->PTVTXuatCanh->so_ptvt_xuat_canh }})</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </center>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if ($suaXuatHang->trang_thai == '1')
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                    DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                        $xuatHang->ma_doanh_nghiep)
                                <div class="row">
                                    <center>
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy yêu cầu sửa
                                            </button>
                                        </a>
                                    </center>
                                </div>
                            @endif
                            @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_xuat_hang == 1)
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#duyetToKhaiModal"
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
                            @endif
                        @elseif($suaXuatHang->trang_thai == '2')
                            <div class="text-center">
                                <h2 class="text-dark">Công chức duyệt phê duyệt:</h2>
                                <h2 class="text-primary">{{ $suaXuatHang->congChuc->ten_cong_chuc ?? '' }}</h2>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Xác nhận duyệt --}}
    {{-- Tình trạng: Chờ thông quan --}}
    <div class="modal fade" id="duyetToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt yêu cầu sửa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.duyet-yeu-cau-sua', ['ma_yeu_cau' => $suaXuatHang->ma_yeu_cau]) }}"
                    method="POST">
                    <div class="modal-body">
                        Xác nhận duyệt yêu cầu sửa này ?
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="modal-footer">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-success"">
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
                <form action="{{ route('xuat-hang.huy-yeu-cau-sua', ['ma_yeu_cau' => $suaXuatHang->ma_yeu_cau]) }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa phiếu này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập lý do hủy" name="ghi_chu" maxlength="200"></textarea>
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
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                startDate: new Date(),
                endDate: new Date(new Date().setDate(new Date().getDate() + 60)),
                autoclose: true,
                todayHighlight: true,
                language: 'vi' // Set language to Vietnamese
            });
        });
    </script>
@stop
