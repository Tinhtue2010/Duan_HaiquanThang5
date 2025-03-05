@extends('layout.user-layout')

@section('title', 'Danh sách thủ kho')

@section('content')
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
                            <h4 class="font-weight-bold text-primary">Danh sách thủ kho</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm thủ kho mới</button>
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
                                    Mã thủ kho
                                </th>
                                <th>
                                    Tên thủ kho
                                </th>
                                <th>
                                    Tên đăng nhập
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $thuKho)
                                    <tr data-ma-thu-kho="{{ $thuKho->ma_thu_kho }}"
                                        data-ten-thu-kho="{{ $thuKho->ten_thu_kho }}"
                                        data-ten-dang-nhap="{{ $thuKho->ten_dang_nhap }}"
                                        data-ma-tai-khoan="{{ $thuKho->ma_tai_khoan }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $thuKho->ma_thu_kho }}</td>
                                        <td>{{ $thuKho->ten_thu_kho }}</td>
                                        <td>{{ $thuKho->ten_dang_nhap }}</td>
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-thu-kho="{{ $thuKho->ma_thu_kho }}"
                                                data-ten-thu-kho="{{ $thuKho->ten_thu_kho }}">
                                                Xóa
                                            </button></td>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin thủ kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-thu-kho') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="mt-1" for="ma_cong_chuc"><strong>Mã thủ kho</strong></label>
                        <input type="text" class="form-control" id="modalMaCongChuc" name="ma_thu_kho_moi"
                            max="50" placeholder="Nhập mã thủ kho" required>

                        <label class="mt-1" for="ten_cong_chuc"><strong>Tên thủ kho</strong></label>
                        <input type="text" class="form-control" id="modalTenCongChuc" name="ten_thu_kho" max="255"
                            placeholder="Nhập tên thủ kho" required>

                        <p class="mt-2"><strong>Tên đăng nhập:</strong> <span id="modalTenDangNhap"></span></p>

                        <input hidden id="modalMaCongChucInput" name="ma_thu_kho">
                        <hr />
                        <h5>Chọn tài khoản khác cho thủ kho này</h5>
                        <em>(Danh sách chỉ hiện các tài khoản thuộc loại "Thủ kho" chưa được gán cho thủ kho nào)</em>
                        <p><strong>Tên đăng nhập: </strong></p>
                        <select class="form-control" id="tai-khoan-dropdown-search" name="ma_tai_khoan">
                            <option></option>
                            @foreach ($taiKhoans as $taiKhoan)
                                <option value="{{ $taiKhoan->ma_tai_khoan }}">
                                    {{ $taiKhoan->ten_dang_nhap }}
                                </option>
                            @endforeach
                        </select>
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
                    <h5 class="modal-title" id="exampleModalLabel">Thêm thủ kho mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-thu-kho') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="mt-1" for="ma_thu_kho"><strong>Mã thủ kho</strong></label>
                        <input type="text" class="form-control" id="ma_thu_kho" name="ma_thu_kho" max="50"
                            placeholder="Nhập mã thủ kho" required>

                        <label class="mt-1" for="ten_thu_kho"><strong>Tên thủ kho</strong></label>
                        <input type="text" class="form-control" id="ten_thu_kho" name="ten_thu_kho" max="255"
                            placeholder="Nhập tên thủ kho" required>
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
                    <h4 class="modal-title">Xác nhận xóa thủ kho</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-thu-kho') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận xóa thủ kho này?</h6>
                        <div>
                            <label><strong>Mã thủ kho:</strong></label>
                            <p class="d-inline" id="modalMaCongChucXoa"></p>
                        </div>
                        <div>
                            <label><strong>Tên thủ kho:</strong></label>
                            <p class="d-inline" id="modalTenCongChucXoa"></p>
                        </div>
                        <input type="hidden" name="ma_thu_kho" id="modalInputMaCongChucXoa">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
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
                var maCongChuc = $(this).data('ma-thu-kho');
                var tenCongChuc = $(this).data('ten-thu-kho');
                var tenDangNhap = $(this).data('ten-dang-nhap');
                var maTaiKhoan = $(this).data('ma-tai-khoan');                
                console.log(maCongChuc);
                // Set the data in the modal
                document.getElementById('modalMaCongChuc').value = maCongChuc;
                document.getElementById('modalTenCongChuc').value = tenCongChuc;
                $('#modalTenDangNhap').text(tenDangNhap);
                document.getElementById('modalMaCongChucInput').value = maCongChuc;

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
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenCongChuc = document.getElementById('modalTenCongChucXoa');
            const modalMaCongChuc = document.getElementById('modalMaCongChucXoa');
            const modalInputMaCongChuc = document.getElementById('modalInputMaCongChucXoa');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const maCongChuc = this.getAttribute('data-ma-thu-kho');
                    const tenCongChuc = this.getAttribute('data-ten-thu-kho');

                    // Set data in the modal
                    modalTenCongChuc.textContent = tenCongChuc;
                    modalMaCongChuc.textContent = maCongChuc;
                    modalInputMaCongChuc.value = maCongChuc;
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
