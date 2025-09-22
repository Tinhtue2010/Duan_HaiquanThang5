@extends('layout.user-layout')

@section('title', 'Danh sách đại lý')

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
                            <h4 class="font-weight-bold text-primary">Đại lý làm thủ tục</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm đại lý mới</button>
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
                                    Mã đại lý
                                </th>
                                <th>
                                    Tên đại lý
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $chuHang)
                                    <tr data-ma-chu-hang="{{ $chuHang->ma_chu_hang }}"
                                        data-ten-chu-hang="{{ $chuHang->ten_chu_hang }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $chuHang->ma_chu_hang }}</td>
                                        <td>{{ $chuHang->ten_chu_hang }}</td>
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-chu-hang="{{ $chuHang->ma_chu_hang }}"
                                                data-ten-chu-hang="{{ $chuHang->ten_chu_hang }}">
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
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin đại lý</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-chu-hang') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <p><strong>Mã đại lý:</strong> <span id="modalMaChuHang"></span></p>
                        <label class="mt-1" for="ten_dang_nhap"><strong>Tên đại lý</strong></label>
                        <input type="text" class="form-control" name="ten_chu_hang" id="modalTenChuHang">
                        <input hidden id="modalMaChuHangInput" name="ma_chu_hang">
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
                    <h5 class="modal-title" id="exampleModalLabel">Thêm đại lý mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-chu-hang') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label for="ma_chu_hang">Mã đại lý</label>
                        <input type="text" class="form-control" id="ma_chu_hang" name="ma_chu_hang" max="50"
                            placeholder="Nhập mã đại lý" required>

                        <label class="mt-3" for="ten_chu_hang">Tên đại lý</label>
                        <input type="text" class="form-control" id="ten_chu_hang" name="ten_chu_hang" max="255"
                            placeholder="Nhập tên đại lý" required>
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
                    <h4 class="modal-title">Xác nhận xóa đại lý</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-chu-hang') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận xóa đại lý này?</h6>
                        <div>
                            <label><strong>Mã đại lý:</strong></label>
                            <p class="d-inline" id="modalMaChuHangXoa"></p>
                        </div>
                        <div>
                            <label><strong>Tên đại lý:</strong></label>
                            <p class="d-inline" id="modalTenChuHangXoa"></p>
                        </div>
                        <input type="hidden" name="ma_chu_hang" id="modalInputMaChuHangXoa">
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
                var maChuHang = $(this).data('ma-chu-hang');
                var tenChuHang = $(this).data('ten-chu-hang');
                var maTaiKhoan = $(this).data('ma-tai-khoan');
                var tenDangNhap = $(this).data('ten-dang-nhap');

                // Set the data in the modal
                $('#modalMaChuHang').text(maChuHang);
                document.getElementById('modalTenChuHang').value = tenChuHang;
                document.getElementById('modalMaChuHangInput').value = maChuHang;


                // Show the modal
                $('#thongTinModal').modal('show');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenChuHang = document.getElementById('modalTenChuHangXoa');
            const modalMaChuHang = document.getElementById('modalMaChuHangXoa');


            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const maChuHang = this.getAttribute('data-ma-chu-hang');
                    const tenChuHang = this.getAttribute('data-ten-chu-hang');

                    // Set data in the modal
                    modalTenChuHang.textContent = tenChuHang;
                    modalMaChuHang.textContent = maChuHang;
                    document.getElementById('modalInputMaChuHangXoa').value = maChuHang;
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
