@extends('layout.user-layout')

@section('title', 'Kết xuất báo cáo')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
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
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Kết xuất báo cáo</h4>
                        </div>
                        <div class="col-3">
                        </div>
                    </div>
                </div>
                <div class="container-fluid card-body">
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo số lượng container lưu tại cảng</h4>
                            <div class="form-group">
                                <form action="{{ route('export.so-luong-container-theo-cont') }}" method="GET">
                                    <label class="label-text mb-2" for="ma_to_khai">Số container</label>
                                    <select class="form-control" id="container-dropdown-search" name="so_container">
                                        <option></option>
                                        @foreach ($containers as $container)
                                            <option value="{{ $container->so_container }}">
                                                {{ $container->so_container }} ({{ $container->phuong_tien_vt_nhap }} - {{ $container->so_seal }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo số lượng tờ khai xuất hết</h4>
                            <div class="form-group">
                                <form action="{{ route('export.to-khai-xuat-het-doanh-nghiep') }}" method="GET">
                                    <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                            <input type="text" id="datepicker8" class="form-control"
                                                placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                            <input type="text" id="datepicker9" class="form-control"
                                                placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo theo dõi trừ lùi theo ngày</h4>
                            <div class="form-group">
                                <form action="{{ route('export.theo-doi-tru-lui-theo-ngay') }}" method="GET">
                                    <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="idNhap" name="so_to_khai_nhap"
                                            placeholder="Nhập số tờ khai" required>
                                        <button type="button" id="searchLanTruLui" class="btn btn-secondary">Tìm</button>
                                    </div>

                                    <label class="label-text mb-1 mt-2" for="">Chọn ngày</label>
                                    <select class="form-control" id="lan-xuat-canh-dropdown-search" name="ma_theo_doi"
                                        required>
                                        <option></option>
                                    </select>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Tất cả theo dõi trừ lùi theo ngày của doanh nghiệp
                            </h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Hoạt động</label>
                                <select class="form-control" id="list-cong-viec" name="cong_viec" required>
                                    <option value="0" selected>Tất cả</option>
                                    <option value="10">Tất cả trong cùng 1 phiếu</option>
                                    <option value="1">Xuất hàng</option>
                                    <option value="2">Chuyển tàu cont</option>
                                    <option value="3">Chuyển container</option>
                                    <option value="4">Chuyển tàu</option>
                                    <option value="7">Kiểm tra hàng</option>
                                    <option value="5">Hàng về kho ban đầu</option>
                                    <option value="6">Tiêu hủy hàng</option>
                                </select>
                                <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                <input type="text" id="ngay-tru-lui" class="form-control" placeholder="dd/mm/yyyy"
                                    name="tu_ngay" readonly>
                                <center>
                                    <a href="#">
                                        <button class="btn btn-success mt-2" id="btnInTruLui">
                                            Tải xuống báo cáo
                                        </button>
                                    </a>
                                </center>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo theo dõi trừ lùi xuất hàng tất cả các ngày</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                <form action="{{ route('export.theo-doi-tru-lui-tat-ca') }}" method="GET">
                                    <input type="text" class="form-control" id="so_to_khai_nhap"
                                        name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo theo dõi trừ lùi cuối ngày</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                <form action="{{ route('export.theo-doi-tru-lui-cuoi-ngay') }}" method="GET">
                                    <input type="text" class="form-control" id="so_to_khai_nhap"
                                        name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                    <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                    <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                        name="tu_ngay" readonly>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo đăng ký thủ tục xuất khẩu hàng hóa</h4>
                            <div class="form-group">
                                <form action="{{ route('export.dang-ky-xuat-khau-hang-hoa') }}" method="GET">
                                    <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công
                                        ty</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                    <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                        name="tu_ngay" readonly>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo đăng ký thủ tục xuất khẩu hàng hóa (Sang cont + Cẩu Cont + Kiểm tra hàng)</h4>
                            <div class="form-group">
                                <form action="{{ route('export.dang-ky-xuat-khau-hang-hoa-2') }}" method="GET">
                                    <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công
                                        ty</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                    <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                        name="tu_ngay" readonly>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <div class="form-group">
                                <h4>Báo cáo số lượng container lưu trên tàu</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.so-luong-tau-tai-cang') }}" method="GET">
                                        <label class="label-text mb-1 mt-2" for="">Tên tàu</label>
                                        <select class="form-control" id="tau-dropdown-search" name="phuong_tien_vt_nhap">
                                            <option></option>
                                            @foreach ($phuongTienVTNhaps as $phuongTienVTNhap)
                                                <option value="{{ $phuongTienVTNhap }}">
                                                    {{ $phuongTienVTNhap }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo hàng tồn theo Doanh nghiệp</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                <form action="{{ route('export.hang-ton-doanh-nghiep') }}" method="GET">
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <!-- Hidden input to send the ten_doanh_nghiep -->
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo chi tiết hàng hóa xuất nhập khẩu</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                <form action="{{ route('export.chi-tiet-xnk-theo-dn') }}" method="GET">
                                    <select class="form-control" id="doanh-nghiep-dropdown-search-3"
                                        name="ma_doanh_nghiep" required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                            <input type="text" id="datepicker3" class="form-control"
                                                placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                            <input type="text" id="datepicker4" class="form-control"
                                                placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo doanh nghiệp xuất nhập khẩu hàng hóa</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                <form action="{{ route('export.doanh-nghiep-xnk-theo-dn') }}" method="GET">
                                    <select class="form-control" id="doanh-nghiep-dropdown-search-2"
                                        name="ma_doanh_nghiep" required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                            <input type="text" class="form-control datepicker"
                                                placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                            <input type="text" class="form-control datepicker"
                                                placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                        </div>
                                    </div>
                                    <center>
                                        <button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>

                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo phiếu xuất của doanh nghiệp</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                <form action="{{ route('export.phieu-xuat-theo-doanh-nghiep') }}" method="GET">
                                    <select class="form-control" id="doanh-nghiep-dropdown-search-4"
                                        name="ma_doanh_nghiep" required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                            <input type="text" id="datepicker5" class="form-control"
                                                placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                            <input type="text" id="datepicker6" class="form-control"
                                                placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo cấp 2</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                <form action="{{ route('export.bao-cao-cap-hai') }}" method="GET">
                                    <select class="form-control" id="doanh-nghiep-dropdown-search-5"
                                        name="ma_doanh_nghiep" required>
                                        <option value="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                ({{ $doanhNghiep->ma_doanh_nghiep }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                            <input type="text" id="datepicker7" class="form-control"
                                                placeholder="dd/mm/yyyy" name="ngay" readonly>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">

                        </div>
                        <div class="card p-3 me-3 col-5">

                        </div>
                    </div>
                    {{-- <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>In phiếu xuất hàng</h4>
                            <form action="{{ route('xuat-hang.export-to-khai-xuat') }}" method="GET">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="so_to_khai_nhap"
                                                name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" id="lan_xuat_canh"
                                                name="lan_xuat_canh" placeholder="Nhập lần xuất" required>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-1">Tải xuống</button>
                                    </center>
                                </div>
                            </form>
                        </div>
                        <div class="card p-3 me-3 col-5">

                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="inTruLuiModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">In phiếu trừ lùi</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('export.theo-doi-tru-lui-theo-ngay-zip-sheet') }}" method="GET">
                    @csrf
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="soToKhaiTable"
                                style="vertical-align: middle; text-align: center;">
                                <thead style="vertical-align: middle; text-align: center;">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="checkAll" checked>
                                        </th>
                                        <th>
                                            Số tờ khai
                                        </th>
                                        <th>
                                            Hoạt động
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ngay_tru_lui" id="ngay_tru_lui_hidden">
                        <button class="btn btn-success xacNhanBtn" name="loai_in" value="sheet">Tải file tổng
                            hợp</button>
                        <button class="btn btn-success xacNhanBtn" name="loai_in" value="zip">Tải file
                            zip</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </form>
            </div>
        </div>
    </div>
    </div>




    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker2').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker3').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker4').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker5').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker6').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker7').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#ngay-tru-lui').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize all datepickers
            $('[id^=datepicker]').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });

            // Attach submit event to all forms with date fields
            $('form').on('submit', function(event) {
                const formHasDates = $(this).find('[name="tu_ngay"], [name="den_ngay"]').length > 0;
                if (formHasDates && !validateDateFields(this)) {
                    event.preventDefault(); // Prevent submission if validation fails
                }
            });
        });

        function convertDateFormat(dateStr) {
            return dateStr.split("-").reverse().join("-");
        }
        document.getElementById('checkAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
        let rowIndex = 0;

        document.querySelectorAll('.xacNhanBtn').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default form submission

                const rows = $('#soToKhaiTable tbody tr')
                    .filter(function() {
                        return $(this).find('.row-checkbox').is(':checked');
                    })
                    .map(function() {
                        const cells = $(this).find('td');
                        return {
                            so_to_khai_nhap: $(cells[1]).text(),
                            cong_viec: $(cells[3]).text().trim(),
                        };
                    })
                    .get();

                if (rows.length === 0) {
                    alert('Vui lòng chọn ít nhất một tờ khai.');
                    return;
                }

                $('#rowsDataInput').val(JSON.stringify(rows));
                const form = button.closest('form');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = button.name;
                hiddenInput.value = button.value;
                form.appendChild(hiddenInput);

                form.submit();
            });
        });

        $('#btnInTruLui').on('click', function() {
            const congViec = document.getElementById('list-cong-viec').value.trim();
            const ngayTruLui = document.getElementById('ngay-tru-lui').value;
            $('#ngay_tru_lui_hidden').val(ngayTruLui);

            $.ajax({
                url: `/get-so-to-khai-tru-lui`,
                data: {
                    cong_viec: congViec,
                    ngay_tru_lui: ngayTruLui,
                },
                method: 'GET',
                success: function(response) {
                    let tbody = $("#soToKhaiTable tbody");
                    tbody.empty();
                    if (response) {
                        $.each(response, function(index, item) {
                            tbody.append(`
                                <tr>
                                    <td><input type="checkbox" class="row-checkbox" checked></td>
                                    <td>${item.so_to_khai_nhap}</td>
                                    <td>
                                        ${item.ten_cong_viec}
                                    </td>
                                    <td hidden>
                                        ${item.cong_viec}
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="3">Không có dữ liệu</td></tr>');
                    }
                },
                error: function() {}
            });
            $('#inTruLuiModal').modal('show');

        });
        $('#searchLanTruLui').on('click', function() {
            const so_to_khai_nhap = $('#idNhap').val();
            $.ajax({
                url: `/get-lan-tru-lui/${so_to_khai_nhap}`,
                method: 'GET',
                success: function(response) {
                    const dropdown = $('#lan-xuat-canh-dropdown-search');
                    dropdown.empty();
                    console.log(response);
                    response.forEach(theoDoiTruLuis => {
                        dropdown.append(
                            `<option value="${theoDoiTruLuis.ma_theo_doi}">${theoDoiTruLuis.cong_viec} - Ngày ${theoDoiTruLuis.ngay_them}</option>`
                        );
                    });
                },
                error: function() {
                    alert('Không tìm thấy tờ khai nhập');
                }
            });
        });
    </script>
    <script>
        // Listen for input changes and copy value to second form
        document.getElementById('idNhap').addEventListener('input', function() {
            document.getElementById('idNhapCopy').value = this.value;
        });
    </script>
@stop
