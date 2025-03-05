@extends('layout.user-layout')

@section('title', 'Thông tin phiếu xuất hàng')

@section('content')
    @php
        use Carbon\Carbon;
        use App\Models\DoanhNghiep;

        $ngayThongQuan = Carbon::parse($xuatHang->ngay_thong_quan);
        $ngayDen = Carbon::parse($xuatHang->ngay_thong_quan);

        $daysPassedFromThongQuan = (int) abs(Carbon::now()->floatDiffInDays($ngayThongQuan, false)); // Use 'false' for signed difference
        $daysPassedFromXuatHang = (int) abs(Carbon::now()->floatDiffInDays($ngayDen, false));
        $ngayThongQuanPlus365 = $ngayThongQuan->copy()->addDays(365);
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
                    @if (trim($xuatHang->trang_thai) == 'Đang chờ duyệt')
                        <a class="return-link" href="/quan-ly-xuat-hang">
                            <p>
                                < Quay lại quản lý xuất hàng </p>
                        </a>
                    @elseif(trim($xuatHang->trang_thai) == 'Đã duyệt')
                        <a class="return-link" href="/to-khai-da-xuat-hang">
                            <p>
                                < Quay lại quản lý phiếu đã duyệt </p>
                        </a>
                    @elseif(trim($xuatHang->trang_thai) == 'Đã hủy')
                        <a class="return-link" href="/to-khai-xuat-da-huy">
                            <p>
                                < Quay lại quản lý phiếu xuất đã hủy </p>
                        </a>
                    @endif
                </div>
                <div class="col-6">

                    {{-- <a href="{{ route('xuat-hang.export-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                        <button class="btn btn-success float-end mx-1">Xuất Excel </button>
                    </a> --}}
                    @if (trim($xuatHang->trang_thai) == 'Đã duyệt')
                        <button onclick="printToKhai('divPrint')" class="btn btn-success float-end">In phiếu xuất</button>
                    @endif
                    <a href="{{ route('nhap-hang.show', ['so_to_khai_nhap' => $xuatHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-primary float-end mx-1">Tờ khai nhập</button>
                    </a>
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center text-dark pt-4">
                        {{ $xuatHang->nhapHang->doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center text-dark">Phiếu
                        {{ $xuatHang->loaiHinh ? $xuatHang->loaiHinh->ten_loai_hinh : '' }}
                        ({{ $xuatHang->loaiHinh->ma_loai_hinh }})
                    </h2>
                    <h2 class="text-center text-dark">Số tờ khai nhập: {{ $xuatHang->so_to_khai_nhap }}, lần xuất
                        {{ $xuatHang->lan_xuat_canh }}, ngày
                        {{ \Carbon\Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                        <hr />
                        {{-- <h2 class="text-center text-dark">Tờ khai phương tiện vận tải xuất cảnh: {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}</h2> --}}
                        <div class="row mt-4">
                            <div class="col-1"></div>
                            <div class="col-5">
                                <p class="fs-5"><strong value="">Số tờ khai phương tiện vận tải xuất cảnh :</strong>
                                    {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}</p>
                                <p class="fs-5"><strong>Tên doanh nghiệp :</strong>
                                    {{ $PTVTXuatCanh->doanhNghiep->ten_doanh_nghiep }}</p>
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
                        <table class="table table-bordered mt-5 fs-6" id="displayTable">
                            <thead class="align-middle">
                                <tr style="vertical-align: middle; text-align: center;">
                                    <th>STT</th>
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong>{{ $soLuongSum }}</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong>{{ number_format($triGiaSum, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="card p-3">
                        <div class="text-center">
                            @if (trim($xuatHang->trang_thai) == 'Đang chờ duyệt')
                                <h2 class="text-primary">Đang chờ duyệt </h2>
                                <img class="status-icon mb-3" src="{{ asset('images/icons/pending.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <div class="row">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('xuat-hang.sua-to-khai-xuat', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
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
                                                    Hủy phiếu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
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
                                                    Hủy phiếu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == 'Đã duyệt')
                                <h2 class="text-warning">Đã duyệt</h2>
                                <img class="status-icon" src="{{ asset('images/icons/waiting-for-goods.png') }}">
                                <h2 class="">Ngày duyệt:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_thong_quan)->format('d-m-Y') }}</h2>
                                <h2 class="">Đã {{ $daysPassedFromThongQuan }} ngày kể từ ngày duyệt</h2>
                                @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                        DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                            $xuatHang->ma_doanh_nghiep)
                                    <div class="row">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy phiếu
                                            </button>
                                        </a>
                                    </div>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == 'Đã duyệt')
                                <h2 class="text-success">Đã duyệt</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/success.png') }}">
                                <h2 class="text-success">Ngày xuất hàng:
                                    {{ \Carbon\Carbon::parse($xuatHang->ngay_xuat_canh)->format('d-m-Y') }}</h2>
                            @elseif(trim($xuatHang->trang_thai) == 'Doanh nghiệp yêu cầu sửa phiếu chờ duyệt' || trim($xuatHang->trang_thai) == 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt')
                                <h2 class="text-warning">Doanh nghiệp yêu cầu sửa phiếu</h2>
                                <img class="status-icon mb-2" src="{{ asset('images/icons/edit.png') }}">
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                    <hr />
                                    <h2 class="text-dark">Cập nhật trạng thái</h2>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <a
                                                href="{{ route('xuat-hang.xem-yeu-cau-sua', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]) }}">
                                                <button class="btn btn-warning px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/edit.png') }}">
                                                    Xem sửa đổi
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#">
                                                <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                    class="btn btn-danger px-4">
                                                    <img class="side-bar-icon"
                                                        src="{{ asset('images/icons/cancel.png') }}">
                                                    Hủy phiếu
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @elseif(trim($xuatHang->trang_thai) == 'Đã hủy')
                                <h2 class="text-danger">Tờ khai đã hủy</h2>
                                <img class="status-icon" src="{{ asset('images/icons/cancel2.png') }}">
                                <h2 class="text-danger">Ngày hủy:
                                    {{ \Carbon\Carbon::parse($xuatHang->updated_at)->format('d-m-Y') }}</h2>
                                <h3 class="text-dark">Lý do hủy: {{ $xuatHang->ghi_chu }}</h3>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Xác nhận duyệt --}}
    {{-- Tình trạng: Chờ thông quan --}}
    <div class="modal fade" id="duyetToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.updateDuyetToKhai') }}" method="POST">
                    <div class="modal-body">
                        Xác nhận duyệt tờ khai này và chuyển sang trạng thái chờ xuất hàng ?
                        <div class="form-group">
                            <label class="label-text mb-1" for="">Cán bộ công chức phụ trách</label>
                            <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
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
                        <input type="hidden" value="{{ $xuatHang->so_to_khai_xuat }}" name="so_to_khai_xuat">
                        <button type="submit" class="btn btn-success"">
                            Xác nhận duyệt
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Tình trạng: Chờ thông quan G21 --}}
    <div class="modal fade" id="duyetToKhaiG21Modal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('xuat-hang.updateDuyetToKhai') }}" method="POST" id="G21">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <input type="hidden" value="{{ $xuatHang->so_to_khai_xuat }}" name="so_to_khai_xuat">
                        <label>Xác nhận thông quan tờ khai này và chuyển sang trạng thái chờ xuất hàng ?</label>
                        <label for="date">Chọn ngày hết hạn (Không quá 60 ngày thông quan):</label>
                        <input type="text" id="datepicker" class="form-control" placeholder="dd/mm/yyyy"
                            name="ngay_het_han" readonly>
                        <input type="hidden" value="G21" name="ma_loai_hinh">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitTQG21Btn" class="btn btn-success"">
                            Xác nhận thông quan
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Tình trạng: Đã thông quan - Chờ xuất hàng --}}
    <div class="modal fade" id="xacNhanXuatHangModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận xuất hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận doanh nghiệp đã xuất hàng theo tờ khai
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-hang.update-da-xuat-hang') }}" method="POST">
                        @csrf
                        <input type="hidden" name="so_to_khai_xuat" value="{{ $xuatHang->so_to_khai_xuat }}">
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
                <form action="{{ route('xuat-hang.huy-to-khai') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger">Xác nhận hủy tờ khai này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>

                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="so_to_khai_xuat" value="{{ $xuatHang->so_to_khai_xuat }}">
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
