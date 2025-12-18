@extends('layout.user-layout')

@section('title', 'Sửa tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif(session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <a class="return-link" href="/quan-ly-nhap-hang">
                <p>
                    < Quay lại quản lý nhập hàng</p>
            </a>
            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                <center>
                    <h3>Giao diện này chỉ có thể sửa: Trọng lượng, trị giá, đơn giá</h3>
                </center>
            @endif
            <h2 class="text-center text-dark">{{ $nhapHang->doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
            <!-- Input fields for each column -->
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <h3 class="text-center text-dark">Thông tin tờ khai</h3>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_hai_quan">Chi cục Hải quan</label>
                                    <select class="form-control" id="hai-quan-dropdown-search" name="ma_hai_quan">
                                        @foreach ($haiQuans as $haiQuan)
                                            <option></option>
                                            @if ($haiQuan->ma_hai_quan == $nhapHang->ma_hai_quan)
                                                <option value="{{ $haiQuan->ma_hai_quan }}" selected>
                                                    {{ $haiQuan->ten_hai_quan }}
                                                    ({{ $haiQuan->ma_hai_quan }})
                                                </option>
                                            @else
                                                <option value="{{ $haiQuan->ma_hai_quan }}">
                                                    {{ $haiQuan->ten_hai_quan }}
                                                    ({{ $haiQuan->ma_hai_quan }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="">Số tờ khai nhập</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="so_to_khai_nhap" maxlength="255"
                                    name="so_to_khai_nhap" placeholder="Nhập số tờ khai nhập"
                                    value="{{ $nhapHang->so_to_khai_nhap }}" readonly required>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_loai_hinh">Loại hình tờ khai</label>
                                    <select class="form-control" id="loai-hinh-dropdown-search" name="ma_loai_hinh">
                                        @foreach ($loaiHinhs as $loaiHinh)
                                            <option></option>
                                            @if ($nhapHang->ma_loai_hinh == $loaiHinh->ma_loai_hinh)
                                                <option value="{{ $loaiHinh->ma_loai_hinh }}" selected>
                                                    {{ $loaiHinh->ten_loai_hinh }}
                                                    ({{ $loaiHinh->ma_loai_hinh }})
                                                </option>
                                            @else
                                                <option value="{{ $loaiHinh->ma_loai_hinh }}">
                                                    {{ $loaiHinh->ten_loai_hinh }}
                                                    ({{ $loaiHinh->ma_loai_hinh }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ngay_thong_quan">Ngày thông quan</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" id="datepicker" class="form-control" placeholder="dd/mm/yyyy"
                                        value="{{ \Carbon\Carbon::parse($nhapHang->ngay_thong_quan)->format('d/m/Y') }}"
                                        name="ngay_thong_quan" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label class="label-text" for="">Đại lý</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="chu-hang-dropdown-search" name="ma_chu_hang">
                                    @foreach ($chuHangs as $chuHang)
                                        <option></option>
                                        @if ($nhapHang->ma_chu_hang == $chuHang->ma_chu_hang)
                                            <option value="{{ $chuHang->ma_chu_hang }}" selected>
                                                {{ $chuHang->ten_chu_hang }}
                                                ({{ $chuHang->ma_chu_hang }})
                                            </option>
                                        @else
                                            <option value="{{ $chuHang->ma_chu_hang }}">
                                                {{ $chuHang->ten_chu_hang }}
                                                ({{ $chuHang->ma_chu_hang }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="label-text" for="phuong_tien_vt_nhap">Phương tiện vận
                                    tải</label> <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="phuong_tien_vt_nhap"
                                    name="phuong_tien_vt_nhap" placeholder="Nhập phương tiện vận tải" maxlength="50"
                                    required value={{ $nhapHang->phuong_tien_vt_nhap }}>
                            </div>
                            <div class="col">
                                <label class="label-text" for="trong_luong">Trọng lượng
                                    (Tấn)</label> <span class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2" id="trong_luong" name="trong_luong"
                                    placeholder="Nhập tổng trọng lượng (Tấn)" value={{ $nhapHang->trong_luong }} required>
                            </div>
                            <div class="col">
                                <label class="label-text mb-2" for="xuat_xu">Xuất xứ</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="xuat-xu-dropdown-search" name="xuat_xu">
                                    <option value="">Nhập xuất xứ của sản phẩm</option>
                                    @foreach ($xuatXus as $xuatXu)
                                        @if ($xuatXu == $hangHoaRows->first()->xuat_xu)
                                            <option value="{{ $xuatXu }}" selected>
                                                {{ $xuatXu }}
                                            </option>
                                        @else
                                            <option value="{{ $xuatXu }}">
                                                {{ $xuatXu }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="label-text" for="ten_doan_tau">Đoàn tàu</label>
                                <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="ten_doan_tau" name="ten_doan_tau"
                                    value="{{ $nhapHang->ten_doan_tau ?? '' }}" placeholder="Nhập đoàn tàu" required>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
            <table class="table table-bordered" id="displayTableNhapHang"
                style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th hidden>Mã hàng</th>
                        <th>Tên hàng</th>
                        <th>Loại hàng</th>
                        <th>Xuất xứ</th>
                        <th>Số lượng</th>
                        <th>Đơn vị tính</th>
                        <th>Đơn giá (USD)</th>
                        <th>Trị giá (USD)</th>
                        <th>Số container ban đầu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hangHoaRows as $hangHoaRow)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td hidden>
                                <center>
                                    <input type="text" class="form-control" name="ma_hang"
                                        value="{{ $hangHoaRow->ma_hang }}" style="width: 70px;" />
                                </center>
                            </td>
                            <td style="width: 300px;">
                                <textarea class="form-control" maxlength="255" name="ten_hang" rows="3">{{ $hangHoaRow->ten_hang }}</textarea>
                            </td>
                            <td>
                                <select class="form-control select2-dropdown" name="loai_hang" style="width: 100px;">
                                    <option></option>
                                    @foreach ($loaiHangs as $loaiHang)
                                        <option value="{{ $loaiHang->ten_loai_hang }}"
                                            {{ $loaiHang->ten_loai_hang == $hangHoaRow->loai_hang ? 'selected' : '' }}>
                                            {{ $loaiHang->ten_loai_hang }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control select2-dropdown" name="xuat_xu">
                                    <option></option>
                                    @foreach ($xuatXus as $xuatXu)
                                        <option value="{{ $xuatXu }}"
                                            {{ $xuatXu == $hangHoaRow->xuat_xu ? 'selected' : '' }}>
                                            {{ $xuatXu }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <center>
                                    <input type="text" class="form-control number" name="so_luong_khai_bao"
                                        value="{{ $hangHoaRow->so_luong_khai_bao }}" style="width: 70px;" />
                                </center>
                            </td>
                            <td>
                                <select class="form-control select2-dropdown" name="don_vi_tinh">
                                    <option></option>
                                    @foreach ($donViTinhs as $donViTinh)
                                        <option value="{{ $donViTinh }}"
                                            {{ $donViTinh == $hangHoaRow->don_vi_tinh ? 'selected' : '' }}>
                                            {{ $donViTinh }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <center>
                                    <input type="text" class="form-control number" name="don_gia"
                                        value="{{ number_format($hangHoaRow->don_gia, 2) }}" style="width: 100px;" />
                                </center>
                            </td>
                            <td>
                                <center>
                                    <input type="text" class="form-control number" name="tri_gia"
                                        value="{{ number_format($hangHoaRow->tri_gia, 2) }}" style="width: 100px;" />
                                </center>
                            </td>
                            <td>
                                <center>
                                    <input type="text" class="form-control" name="so_container_khai_bao"
                                        value="{{ $hangHoaRow->so_container_khai_bao }}" style="width: 150px;" />
                                </center>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
            <center>
                <button type="button" id="xacNhanBtn" class="btn btn-success mb-5">Sửa tờ khai</button>
            </center>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận sửa tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận sửa tờ khai này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('nhap-hang.submit-sua-to-khai-nhap') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ma_chu_hang" id="ma_chu_hang_hidden">
                        <input type="hidden" name="ma_hai_quan" id="ma_hai_quan">
                        <input type="hidden" name="so_container" id="so_container_hidden">
                        <input type="hidden" name="ma_loai_hinh" id="ma_loai_hinh">
                        <input type="hidden" name="phuong_tien_vt_nhap" id="phuong_tien_vt_nhap_hidden">
                        <input type="hidden" name="trong_luong" id="trong_luong_hidden">
                        <input type="hidden" name="so_to_khai_nhap" id="so_to_khai_nhap_hidden">
                        <input type="hidden" name="ten_doan_tau" id="ten_doan_tau_hidden">
                        <input type="hidden" name="xuat_xu" id="xuat_xu_hidden">
                        <input type="hidden" name="so_to_khai_nhap_goc" value="{{ $nhapHang->so_to_khai_nhap }}">
                        <input type="hidden" name="ngay_thong_quan" id="ngay_thong_quan_hidden">
                        <button type="submit" class="btn btn-success">Sửa tờ khai</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Update hidden fields when dropdowns change
        document.getElementById('hai-quan-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_hai_quan').value = this.value;
        });
        document.getElementById('loai-hinh-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_loai_hinh').value = this.value;
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            let rowsData = [];
            // Form submission handler
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                // Get values from dropdowns
                const maHaiQuan = document.getElementById('hai-quan-dropdown-search').value;
                const maLoaiHinh = document.getElementById('loai-hinh-dropdown-search').value;
                const maChuHang = document.getElementById('chu-hang-dropdown-search').value;
                const xuatXu = document.getElementById('xuat-xu-dropdown-search').value;
                const ngayThongQuan = $('#datepicker').val();
                const soToKhaiNhap = $("#so_to_khai_nhap").val();
                const phuongTienVanTaiNhap = $("#phuong_tien_vt_nhap").val();
                const trongLuong = $("#trong_luong").val();
                const tenDoanTau = $("#ten_doan_tau").val();

                if (!maHaiQuan) {
                    alert('Vui lòng chọn hải quan');
                    return;
                } else if (!ngayThongQuan) {
                    alert('Vui lòng chọn ngày thông quan');
                    return;
                } else if (!soToKhaiNhap) {
                    alert('Vui lòng điền số tờ khai nhập');
                    return;
                } else if (!phuongTienVanTaiNhap) {
                    alert('Vui lòng điền phương tiện vận tải');
                    return;
                } else if (!maLoaiHinh) {
                    alert('Vui lòng chọn loại hình');
                    return;
                } else if (!maChuHang) {
                    alert('Vui lòng chọn đại lý');
                    return;
                } else if (!trongLuong) {
                    alert('Vui lòng điền trọng lượng');
                    return;
                }

                // Get all rows from the table
                const tableRows = document.querySelectorAll('#displayTableNhapHang tbody tr');

                // Check if there are any rows
                if (tableRows.length === 0) {
                    alert('Vui lòng thêm ít nhất một hàng hóa');
                    return;
                }

                // Map the table data to an array of objects
                const rowsData = Array.from(tableRows).map(row => ({
                    ma_hang: row.querySelector('input[name="ma_hang"]')?.value || '',
                    ten_hang: row.querySelector('textarea[name="ten_hang"]')?.value.trim() ||
                        '',
                    loai_hang: row.querySelector('select[name="loai_hang"]')?.value || '',
                    xuat_xu: row.querySelector('select[name="xuat_xu"]')?.value || '',
                    so_luong: row.querySelector('input[name="so_luong_khai_bao"]')?.value || '',
                    don_vi_tinh: row.querySelector('select[name="don_vi_tinh"]')?.value || '',
                    don_gia: row.querySelector('input[name="don_gia"]')?.value || '',
                    tri_gia: row.querySelector('input[name="tri_gia"]')?.value || '',
                    so_container: row.querySelector('input[name="so_container_khai_bao"]')
                        ?.value || '',
                }));

                // Set values for hidden inputs
                document.getElementById('rowsDataInput').value = JSON.stringify(rowsData);
                document.getElementById('ma_hai_quan').value = maHaiQuan;
                document.getElementById('ma_loai_hinh').value = maLoaiHinh;
                document.getElementById('phuong_tien_vt_nhap_hidden').value = phuongTienVanTaiNhap;
                document.getElementById('trong_luong_hidden').value = trongLuong;
                document.getElementById('so_to_khai_nhap_hidden').value = soToKhaiNhap;
                document.getElementById('ngay_thong_quan_hidden').value = ngayThongQuan;
                document.getElementById('ma_chu_hang_hidden').value = maChuHang;
                document.getElementById('ten_doan_tau_hidden').value = tenDoanTau;
                document.getElementById('xuat_xu_hidden').value = xuatXu;

                // Submit the form
                $('#xacNhanModal').modal('show');
            });

            // Additional listeners for calculations
            const soLuongInput = document.getElementById("so_luong_khai_bao");
            const donGiaInput = document.getElementById("don_gia");
            const triGiaInput = document.getElementById("tri_gia");

            function calculateTriGia() {
                const soLuong = parseFloat(soLuongInput.value) || 0;
                const donGia = parseFloat(donGiaInput.value) || 0;
                const triGia = soLuong * donGia;

                triGiaInput.value = triGia;
            }

            soLuongInput.addEventListener("input", calculateTriGia);
            donGiaInput.addEventListener("input", calculateTriGia);

        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi', // Set language to Vietnamese
                endDate: '0d',
                keyboardNavigation: true, // Allow keyboard navigation
                forceParse: true // Ensure manually typed dates are parsed
            }).on('changeDate', function(e) {
                // When a date is selected via the datepicker UI
                handleDateChange(e.date);
            });

            // Handle manually typed date
            $('#datepicker').on('blur', function() {
                const typedDate = $(this).val();
                const parsedDate = moment(typedDate, "DD/MM/YYYY", true);

                if (parsedDate.isValid()) {
                    // Update the datepicker with the manually entered date
                    $('#datepicker').datepicker('setDate', parsedDate.toDate());
                    handleDateChange(parsedDate.toDate());
                } else {
                    alert("Invalid date format. Please enter in DD/MM/YYYY format.");
                }
            });

            function handleDateChange(selectedDate) {
                const currentDate = new Date();
                const diffTime = Math.abs(currentDate - selectedDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                console.log("Selected Date:", selectedDate);
                console.log("Days Difference:", diffDays);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2-dropdown').select2({
                placeholder: "Chọn loại hàng",
                allowClear: true,
                width: 'resolve' // makes sure it inherits width from Bootstrap
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input.number').forEach(function(input) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            });
        });
    </script>
@stop
