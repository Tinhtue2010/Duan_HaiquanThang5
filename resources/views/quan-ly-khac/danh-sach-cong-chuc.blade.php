@extends('layout.user-layout')

@section('title', 'Danh sách cán bộ công chức')

@section('content')
    <style>
        input[type="checkbox"] {
            transform: scale(1.5);
            /* Adjust size */
            margin-right: 10px;
        }
    </style>
    <div id="layoutSidenav_content">
        <div class="container-fluid px-4">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif (session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách cán bộ công chức</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm cán bộ công chức mới</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Mã cán bộ công chức
                                </th>
                                <th>
                                    Tên cán bộ công chức
                                </th>
                                <th>
                                    Tên đăng nhập
                                </th>
                                <th>
                                    Nhập hàng
                                </th>
                                <th>
                                    Xuất hàng
                                </th>
                                <th>
                                    Xuất cảnh
                                </th>
                                <th>
                                    Yêu cầu
                                </th>
                                <th>
                                    Bàn giao
                                </th>
                                <th>
                                    Chỉ xem
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $congChuc)
                                    <tr data-ma-cong-chuc="{{ $congChuc->ma_cong_chuc }}"
                                        data-ten-cong-chuc="{{ $congChuc->ten_cong_chuc }}"
                                        data-ten-dang-nhap="{{ $congChuc->ten_dang_nhap }}"
                                        data-ma-tai-khoan="{{ $congChuc->ma_tai_khoan }}"
                                        data-is-nhap-hang="{{ $congChuc->is_nhap_hang }}"
                                        data-is-xuat-hang="{{ $congChuc->is_xuat_canh }}"
                                        data-is-xuat-canh="{{ $congChuc->is_xuat_canh }}"
                                        data-is-yeu-cau="{{ $congChuc->is_yeu_cau }}"
                                        data-is-ban-giao="{{ $congChuc->is_ban_giao }}"
                                        data-is-chi-xem="{{ $congChuc->is_chi_xem }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $congChuc->ma_cong_chuc }}</td>
                                        <td>{{ $congChuc->ten_cong_chuc }}</td>
                                        <td>{{ $congChuc->ten_dang_nhap }}</td>

                                        <td>
                                            <input type="checkbox" {{ $congChuc->is_nhap_hang ? 'checked' : '' }} disabled>
                                        </td>
                                        <td><input type="checkbox" {{ $congChuc->is_xuat_hang ? 'checked' : '' }} disabled>
                                        </td>
                                        <td><input type="checkbox" {{ $congChuc->is_xuat_canh ? 'checked' : '' }} disabled>
                                        </td>
                                        <td><input type="checkbox" {{ $congChuc->is_yeu_cau ? 'checked' : '' }} disabled>
                                        </td>
                                        <td><input type="checkbox" {{ $congChuc->is_ban_giao ? 'checked' : '' }} disabled>
                                        </td>
                                        <td><input type="checkbox" {{ $congChuc->is_chi_xem ? 'checked' : '' }} disabled>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#baoCaoModal"
                                                data-ma-cong-chuc="{{ $congChuc->ma_cong_chuc }}"
                                                data-ten-cong-chuc="{{ $congChuc->ten_cong_chuc }}">
                                                Báo cáo
                                            </button>
                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-cong-chuc="{{ $congChuc->ma_cong_chuc }}"
                                                data-ten-cong-chuc="{{ $congChuc->ten_cong_chuc }}">
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Thông tin Modal -->
    <div class="modal fade" id="thongTinModal" tabindex="-1" aria-labelledby="thongTinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin cán bộ công chức</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-cong-chuc') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-8">
                                <label class="mt-1" for="ma_cong_chuc"><strong>Mã cán bộ công chức</strong></label>
                                <input type="text" class="form-control" id="modalMaCongChuc" name="ma_cong_chuc_moi"
                                    max="50" placeholder="Nhập mã cán bộ công chức" readonly required>

                                <label class="mt-1" for="ten_cong_chuc"><strong>Tên cán bộ công chức</strong></label>
                                <input type="text" class="form-control" id="modalTenCongChuc" name="ten_cong_chuc"
                                    max="255" placeholder="Nhập tên cán bộ công chức" required>

                                <p class="mt-2"><strong>Tên đăng nhập:</strong> <span id="modalTenDangNhap"></span></p>

                                <input hidden id="modalMaCongChucInput" name="ma_cong_chuc">
                                <hr />
                                <h5>Chọn tài khoản khác cho cán bộ công chức này</h5>
                                <em>(Danh sách chỉ hiện các tài khoản thuộc loại "Cán bộ công chức" chưa được gán cho cán bộ
                                    công
                                    chức nào)</em>
                                <p><strong>Tên đăng nhập: </strong></p>
                                <select class="form-control" id="tai-khoan-dropdown-search" name="ma_tai_khoan">
                                    <option value=''></option>
                                    @foreach ($taiKhoans as $taiKhoan)
                                        <option value="{{ $taiKhoan->ma_tai_khoan }}">
                                            {{ $taiKhoan->ten_dang_nhap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <center>
                                    <h5>Phân quyền</h5>
                                </center>
                                <input type="checkbox" name="is_nhap_hang" id="nhap-hang" value="1">
                                <label for="myCheckbox">Quản lý nhập hàng</label>
                                <br>
                                <input class="mt-2" type="checkbox" name="is_xuat_hang" id="xuat-hang"
                                    value="1">
                                <label for="myCheckbox">Quản lý xuất hàng</label>
                                <br>
                                <input class="mt-2" type="checkbox" name="is_xuat_canh" id="xuat-canh"
                                    value="1">
                                <label for="myCheckbox">Quản lý xuất cảnh</label>
                                <br>
                                <input class="mt-2" type="checkbox" name="is_yeu_cau" id="yeu-cau" value="1">
                                <label for="myCheckbox">Quản lý yêu cầu</label>
                                <br>
                                <input class="mt-2" type="checkbox" name="is_ban_giao" id="ban-giao" value="1">
                                <label for="myCheckbox">Quản lý bàn giao hồ sơ</label>
                                <br>
                                <input class="mt-2" type="checkbox" name="is_chi_xem" id="chi-xem" value="1">
                                <label for="myCheckbox">Chỉ xem</label>

                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Cập nhật</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Thêm -->
    <div class="modal fade" id="themModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Thêm cán bộ công chức mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-cong-chuc') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="mt-1" for="ma_cong_chuc"><strong>Mã cán bộ công chức</strong></label>
                        <input type="text" class="form-control" id="ma_cong_chuc" name="ma_cong_chuc" max="50"
                            placeholder="Nhập mã cán bộ công chức" required>

                        <label class="mt-1" for="ten_cong_chuc"><strong>Tên cán bộ công chức</strong></label>
                        <input type="text" class="form-control" id="ten_cong_chuc" name="ten_cong_chuc"
                            max="255" placeholder="Nhập tên cán bộ công chức" required>
                        <hr />
                        <label class="mt-1" for="ten_dang_nhap"><strong>Tên đăng nhập</strong></label>
                        <input type="text" class="form-control" id="ten_dang_nhap" name="ten_dang_nhap"
                            placeholder="Nhập tên đăng nhập"autocomplete="new-password" required>
                        <label class="mt-1" for="mat_khau"><strong>Mật khẩu</strong></label>
                        <input type="password" class="form-control" id="mat_khau" name="mat_khau"
                            placeholder="Nhập mật khẩu" autocomplete="new-password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Thêm mới</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Xóa Modal -->
    <div class="modal fade" id="xoaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Xác nhận xóa cán bộ công chức</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-cong-chuc') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận xóa cán bộ công chức này?</h6>
                        <div>
                            <label><strong>Mã cán bộ công chức:</strong></label>
                            <p class="d-inline" id="modalMaCongChucXoa"></p>
                        </div>
                        <div>
                            <label><strong>Tên cán bộ công chức:</strong></label>
                            <p class="d-inline" id="modalTenCongChucXoa"></p>
                        </div>
                        <input type="hidden" name="ma_cong_chuc" id="modalInputMaCongChucXoa">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Báo cáo Modal -->
    <div class="modal fade" id="baoCaoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Phân quyền báo cáo công chức</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.phan-quyen-bao-cao') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body fs-5">
                        <div>
                            <label><strong>Mã cán bộ công chức:</strong></label>
                            <p class="d-inline fs-5" id="modalMaCongChucBC"></p>
                        </div>
                        <div>
                            <label><strong>Tên cán bộ công chức:</strong></label>
                            <p class="d-inline fs-5" id="modalTenCongChucBC"></p>
                        </div>
                        <br>
                        <input type="checkbox" name="1" id="1" value="1">
                        <label for="myCheckbox">Báo cáo số lượng container lưu tại cảng</label>
                        <br>
                        <input type="checkbox" name="2" id="2" value="1">
                        <label for="myCheckbox">Báo cáo hàng tồn tại cảng</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="3" id="3" value="1">
                        <label for="3">Báo cáo theo dõi trừ lùi xuất hàng tất cả các ngày</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="4" id="4" value="1">
                        <label for="4">Báo cáo tiếp nhận hằng ngày</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="5" id="5" value="1">
                        <label for="5">Báo cáo theo dõi trừ lùi theo ngày</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="6" id="6" value="1">
                        <label for="6">Báo cáo đăng ký xuất khẩu hàng hóa</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="7" id="7" value="1">
                        <label for="7">Báo cáo hàng tồn theo tờ khai nhập</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="8" id="8" value="1">
                        <label for="8">Báo cáo hàng hóa xuất nhập khẩu</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="9" id="9" value="1">
                        <label for="9">Báo cáo hàng tồn theo Doanh nghiệp</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="10" id="10" value="1">
                        <label for="10">Báo cáo hàng tồn theo đại lý</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="11" id="11" value="1">
                        <label for="myCheckbox">Báo cáo chi tiết hàng hóa xuất nhập khẩu</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="12" id="12" value="1">
                        <label for="myCheckbox">Báo cáo thống kê hàng hóa sang cont, chuyển tàu, kiểm tra hàng</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="13" id="13" value="1">
                        <label for="myCheckbox">Báo cáo doanh nghiệp xuất nhập khẩu hàng hóa</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="14" id="14" value="1">
                        <label for="myCheckbox">Báo cáo doanh nghiệp xuất nhập khẩu hàng hóa theo doanh nghiệp</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="15" id="15" value="1">
                        <label for="myCheckbox">Báo cáo hàng chuyển cửa khẩu xuất (Quay về kho)</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="16" id="16" value="1">
                        <label for="myCheckbox">Theo dõi hàng hóa quá 15 ngày chưa thực xuất</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="17" id="17" value="1">
                        <label for="myCheckbox">Báo cáo số lượng tờ khai xuất hết</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="18" id="18" value="1">
                        <label for="myCheckbox">Báo cáo giám sát hàng hóa xuất khẩu</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="19" id="19" value="1">
                        <label for="myCheckbox">Báo cáo sử dụng seal niêm phong hải quan</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="25" id="25" value="1">
                        <label for="myCheckbox">Báo cáo sử dụng seal niêm phong chi tiết</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="20" id="20" value="1">
                        <label for="myCheckbox">Báo cáo tàu lưu tại cảng</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="21" id="21" value="1">
                        <label for="myCheckbox">Báo cáo phương tiện nhập cảnh</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="22" id="22" value="1">
                        <label for="myCheckbox">Báo cáo phương tiện xuất cảnh</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="27" id="27" value="1">
                        <label for="myCheckbox">Báo cáo phương tiện xuất cảnh sửa, hủy</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="23" id="23" value="1">
                        <label for="myCheckbox">Báo cáo phân công nhiệm vụ</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="24" id="24" value="1">
                        <label for="myCheckbox">Bảng kê công việc</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="26" id="26" value="1">
                        <label for="myCheckbox">Theo dõi phương tiện xuất nhập cảnh tại khu vực đầu tán</label>
                        <br>
                        <input class="mt-2" type="checkbox" name="28" id="28" value="1">
                        <label for="myCheckbox">Báo cáo thời gian tờ khai lưu tại cảng</label>



                        <input type="hidden" name="ma_cong_chuc" id="modalInputMaCongChucBC">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Phân quyền</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#tai-khoan-dropdown-search').select2();
            // Reinitialize Select2 when modal opens
            $('#thongTinModal').on('shown.bs.modal', function() {
                $('#tai-khoan-dropdown-search').select2('destroy');
                $('#tai-khoan-dropdown-search').select2({
                    placeholder: "Chọn tài khoản",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#thongTinModal .modal-body'),
                });
            });
        });
    </script>
    {{-- Script áp dụng cho 3 cột đầu --}}
    <script>
        $(document).ready(function() {
            // Handle row click event
            $('#dataTable tbody').on('click', 'tr', function(event) {
                // Check if the click is on the last column or its children (e.g., a button)
                if ($(event.target).closest('td:last-child').length) {
                    return; // Exit the function to avoid triggering the modal
                }

                // Get data from the clicked row
                var maCongChuc = $(this).data('ma-cong-chuc');
                var tenCongChuc = $(this).data('ten-cong-chuc');
                var tenDangNhap = $(this).data('ten-dang-nhap');
                var maTaiKhoan = $(this).data('ma-tai-khoan');
                document.getElementById('modalMaCongChuc').value = maCongChuc;
                document.getElementById('modalTenCongChuc').value = tenCongChuc;
                $('#modalTenDangNhap').text(tenDangNhap);
                document.getElementById('modalMaCongChucInput').value = maCongChuc;

                $("#nhap-hang").prop("checked", false); // Use jQuery
                $("#xuat-hang").prop("checked", false); // Use jQuery
                $("#xuat-canh").prop("checked", false); // Use jQuery
                $("#ban-giao").prop("checked", false); // Use jQuery
                $("#yeu-cau").prop("checked", false); // Use jQuery


                if ($(this).data('is-nhap-hang') == 1) {
                    $("#nhap-hang").prop("checked", true); // Use jQuery
                }
                if ($(this).data('is-xuat-hang') == 1) {
                    $("#xuat-hang").prop("checked", true); // Use jQuery
                }
                if ($(this).data('is-xuat-canh') == 1) {
                    $("#xuat-canh").prop("checked", true); // Use jQuery
                }
                if ($(this).data('is-ban-giao') == 1) {
                    $("#ban-giao").prop("checked", true); // Use jQuery
                }
                if ($(this).data('is-yeu-cau') == 1) {
                    $("#yeu-cau").prop("checked", true); // Use jQuery
                }
                if ($(this).data('is-chi-xem') == 1) {
                    $("#chi-xem").prop("checked", true); // Use jQuery
                }

                const selectElement = document.getElementById('tai-khoan-dropdown-search');
                const newOption = document.createElement('option');
                newOption.value = maTaiKhoan;
                newOption.text = tenDangNhap;
                selectElement.add(newOption);
                newOption.selected = true;

                // Show the modal
                $('#thongTinModal').modal('show');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baoCaoButtons = document.querySelectorAll('.btn-primary[data-bs-toggle="modal"]');
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenCongChuc = document.getElementById('modalTenCongChucXoa');
            const modalMaCongChuc = document.getElementById('modalMaCongChucXoa');
            const modalInputMaCongChuc = document.getElementById('modalInputMaCongChucXoa');

            const modalTenCongChucBC = document.getElementById('modalTenCongChucBC');
            const modalMaCongChucBC = document.getElementById('modalMaCongChucBC');
            const modalInputMaCongChucBC = document.getElementById('modalInputMaCongChucBC');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const maCongChuc = this.getAttribute('data-ma-cong-chuc');
                    const tenCongChuc = this.getAttribute('data-ten-cong-chuc');

                    // Set data in the modal
                    modalTenCongChuc.textContent = tenCongChuc;
                    modalMaCongChuc.textContent = maCongChuc;
                    modalInputMaCongChuc.value = maCongChuc;
                });
            });
            baoCaoButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const maCongChucBC = this.getAttribute('data-ma-cong-chuc');
                    const tenCongChucBC = this.getAttribute('data-ten-cong-chuc');
                    for (let i = 1; i <= 28; i++) {
                        $(`#${i}`).prop("checked", false);
                    }

                    $.ajax({
                        url: "/get-phan-quyen-bao-cao", // Laravel route
                        type: "GET",
                        contentType: "application/json",
                        data: {
                            ma_cong_chuc: maCongChucBC
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content')
                        },

                        success: function(response) {
                            if (response) {
                                $.each(response, function(index, phanQuyen) {
                                    if (phanQuyen.phan_quyen == 1) {
                                        $(`#${phanQuyen.ma_bao_cao}`).prop(
                                            "checked", true);
                                    }
                                });
                            }

                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });

                    // Set data in the modal
                    modalTenCongChucBC.textContent = tenCongChucBC;
                    modalMaCongChucBC.textContent = maCongChucBC;
                    modalInputMaCongChucBC.value = maCongChucBC;
                });
            });


        });
    </script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                language: {
                    searchPlaceholder: "Tìm kiếm",
                    search: "",
                    "sInfo": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    "sInfoEmpty": "Hiển thị 0 đến 0 của 0 mục",
                    "sInfoFiltered": "Lọc từ _MAX_ mục",
                    "sLengthMenu": "Hiện _MENU_ mục",
                    "sEmptyTable": "Không có dữ liệu",
                },
                stateSave: true,
                dom: '<"clear"><"row"<"col"l><"col"f>>rt<"row"<"col"i><"col"p>><"row"<"col"B>>',
                buttons: [{
                        extend: 'excel',
                        exportOptions: {
                            columns: ':not(:last-child)',
                        },
                        title: ''
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':not(:last-child)',
                        },
                        title: ''
                    }
                ]
            });

            $('.dataTables_filter input[type="search"]').css({
                width: '350px',
                display: 'inline-block',
                height: '40px',
            });
        });
    </script>
@stop
