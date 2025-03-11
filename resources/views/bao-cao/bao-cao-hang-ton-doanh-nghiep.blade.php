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
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="so_container"
                                            placeholder="Nhập số container" required>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">

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
                            <h4>Báo cáo đăng ký xuất khẩu hàng hóa</h4>
                            <div class="form-group">
                                <form action="{{ route('export.dang-ky-xuat-khau-hang-hoa') }}" method="GET">
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
                                    <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                    <input type="text" id="datepicker2" class="form-control" placeholder="dd/mm/yyyy"
                                        name="tu_ngay" readonly>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button></center>
                                </form>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo theo dõi trừ lùi xuất hàng cuối ngày</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                <form action="{{ route('export.theo-doi-tru-lui-cuoi-ngay') }}" method="GET">
                                    <input type="text" class="form-control" id="so_to_khai_nhap" name="so_to_khai_nhap"
                                        placeholder="Nhập số tờ khai" required>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div>
                        <div class="card p-3 me-3 col-5">

                        </div>
                    </div> --}}
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
    @stop
