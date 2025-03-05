@extends('layout.user-layout')

@section('title', 'Thêm tờ khai xuất cảnh')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/quan-ly-xuat-canh">
                <p>
                    < Quay lại quản lý tờ khai xuất cảnh</p>
            </a>
            <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center">TỜ KHAI XUẤT CẢNH</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <h3 class="text-center">Thông tin tờ khai</h3>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label for="ptvtxc" class="mb-1 fw-bold">Phương tiện vận tải xuất cảnh</label>
                                    <select class="form-control" id="ptvt-xc-dropdown-search" name="ptvtxc">
                                        <option></option>
                                        @foreach ($PTVTXuatCanhs as $PTVTXuatCanh)
                                            <option value="{{ $PTVTXuatCanh->so_ptvt_xuat_canh }}">
                                                {{ $PTVTXuatCanh->ten_phuong_tien_vt }} Số:
                                                {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn thuyền trưởng (Hoặc nhập
                                        tên khác trong ô tìm kiếm)</label>
                                    <select class="form-control" id="thuyen-truong-dropdown-search" name="ten_thuyen_truong"
                                        required>
                                        <option></option>
                                        @foreach ($thuyenTruongs as $thuyenTruong)
                                            <option value="{{ $thuyenTruong }}"> {{ $thuyenTruong }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn doanh nghiệp/ Chủ
                                        hàng</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        <option></option>

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row card p-2">
                <h3 class="text-center">Danh sách các phiếu xuất</h3>
                <div class="table-responsive">
                    <table class="table table-bordered" id="toKhaiXuatTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th>STT</th>
                                <th>Số</th>
                                <th>Công ty</th>
                                <th>Loại hình</th>
                                <th>Số lượng</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <center>

                <button id="xacNhanBtn" class="btn btn-success mt-5">Nhập tờ khai xuất cảnh</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm phiếu xuất hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm tờ khai xuất cảnh này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-canh.them-to-khai-xuat-canh-submit') }}" method="POST" id="mainForm"
                        name='xuatCanhForm'>
                        @csrf
                        <input type="hidden" name="ma_doanh_nghiep_chon" id="ma_doanh_nghiep_chon_hidden">
                        <input type="hidden" name="ten_thuyen_truong" id="ten_thuyen_truong_hidden">
                        <input type="hidden" name="so_ptvt_xuat_canh" id="so_ptvt_xuat_canh_hidden">
                        <button id="submitData" type="submit" class="btn btn-success">Nhập tờ khai xuất cảnh</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {

                let dropdownValue = document.getElementById('ptvt-xc-dropdown-search').value;
                if (!dropdownValue) {
                    alert('Vui lòng chọn phương tiện vận tải xuất cảnh');
                    return false;
                }

                let tenThuyenTruong = document.getElementById('thuyen-truong-dropdown-search').value.trim();
                let maDoanhNghiepChon = document.getElementById('doanh-nghiep-dropdown-search').value
                    .trim();
                document.getElementById('ten_thuyen_truong_hidden').value = tenThuyenTruong;
                document.getElementById('ma_doanh_nghiep_chon_hidden').value = maDoanhNghiepChon;

                if (!tenThuyenTruong) {
                    alert('Vui lòng chọn tên thuyền trưởng');
                    return false;
                }
                if (!maDoanhNghiepChon) {
                    alert('Vui lòng chọn doanh nghiệp');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });

            function convertDateFormat(dateStr) {
                return dateStr.split("-").reverse().join("-");
            }

            function updateSealDropdown() {
                let so_ptvt_xuat_canh = $('#ptvt-xc-dropdown-search').val();
                $.ajax({
                    url: "{{ route('xuat-canh.getPhieuXuats') }}", // Adjust with your route
                    type: "GET",
                    data: {
                        so_ptvt_xuat_canh: so_ptvt_xuat_canh,
                    },

                    success: function(response) {
                        let tbody = $("#toKhaiXuatTable tbody");
                        tbody.empty(); // Clear previous data
                        let totalTongSoLuongXuat = 0; // Initialize total sum

                        if (response.xuatHangs && response.xuatHangs.length > 0) {
                            $.each(response.xuatHangs, function(index, item) {
                                totalTongSoLuongXuat += parseFloat(item.tong_so_luong_xuat) ||
                                    0; // Sum up values
                                tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.so_to_khai_xuat}</td>
                                    <td>${item.ten_doanh_nghiep}</td>
                                    <td>${item.ma_loai_hinh}</td>
                                    <td>${item.tong_so_luong_xuat}</td>
                                    <td>${convertDateFormat(item.ngay_dang_ky)}</td>
                                </tr>
                            `);
                            });
                            tbody.append(`
                                <tr style="font-weight: bold; background-color: #f8f9fa;">
                                    <td colspan="4" style="text-center: right;">Tổng cộng:</td>
                                    <td>${totalTongSoLuongXuat}</td>
                                    <td></td>
                                </tr>
                            `);
                        } else {
                            tbody.append('<tr><td colspan="8">Không có dữ liệu</td></tr>');
                        }
                    }
                });

            }

            function updateDoanhNghiepsDropdown() {
                let so_ptvt_xuat_canh = $('#ptvt-xc-dropdown-search').val();
                let doanhNghiepDropdown = $("#doanh-nghiep-dropdown-search"); // Use jQuery for easier manipulation
                $.ajax({
                    url: "{{ route('xuat-canh.getDoanhNghiepsTrongCacPhieu') }}", // Adjust with your route
                    type: "GET",
                    data: {
                        so_ptvt_xuat_canh: so_ptvt_xuat_canh,
                    },

                    success: function(response) {
                        console.log(response);
                        doanhNghiepDropdown.empty().append(
                            '<option value="">Chọn doanh nghiệp</option>'); // Clear and add default
                        if (response.doanhNghieps && response.doanhNghieps.length > 0) {
                            $.each(response.doanhNghieps, function(index, doanhNghiep) {
                                doanhNghiepDropdown.append(
                                    `<option value="${doanhNghiep.ma_doanh_nghiep}">${doanhNghiep.ten_doanh_nghiep}</option>`
                                );
                            });
                        }
                    }
                });

            }

            $('#ptvt-xc-dropdown-search').on('change', function() {
                document.getElementById('so_ptvt_xuat_canh_hidden').value = document.getElementById(
                    'ptvt-xc-dropdown-search').value.trim();
                updateDoanhNghiepsDropdown();
                $('#toKhaiXuatTable tr').each(function() {
                    updateSealDropdown($(this));


                });
            });
        });
    </script>
    <script>
        //Kiểm tra trước khi tìm kiếm
        function validateAndSearch(event) {
            event.preventDefault();
            const soToKhaiNhap = document.getElementById('so-to-khai-nhap-dropdown-search').value.trim();

            if (!soToKhaiNhap) {
                alert('Vui lòng nhập số tờ khai trước khi tìm kiếm');
                return;
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all dropdowns with the select2-dropdown class
            $('.select2-dropdown').select2({
                placeholder: "",
                allowClear: true,
                width: '100%' // You can adjust this as needed
            });

            // If your rows are dynamically shown/hidden
            // Re-initialize Select2 when rows become visible
            $('.container-row').on('show', function() {
                $(this).find('.select2-dropdown').select2({
                    placeholder: "",
                    allowClear: true,
                    width: '100%'
                });
            });
        });
    </script>

@stop
