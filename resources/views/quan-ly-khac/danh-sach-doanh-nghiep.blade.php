@extends('layout.user-layout')

@section('title', 'Danh sách doanh nghiệp')

@section('content')
    <style>
        <style>

        /* Ensure modal content has proper stacking order */
        .modal-content {
            position: relative;
            z-index: 1050;
            /* Make sure it's above the rest of the page but below the dropdown */
        }

        /* Ensure select2 dropdown appears above modal */
        .select2-container--open {
            z-index: 1060 !important;
            /* Ensure the dropdown is above the modal */
        }

        /* Ensure the dropdown itself is not clipped or hidden by other elements */
        .select2-container {
            z-index: 1060 !important;
            /* Ensure select2 itself has a higher z-index */
        }
    </style>
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
                            <h4 class="font-weight-bold text-primary">Danh sách doanh nghiệp</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm doanh nghiệp mới</button>
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
                                    Mã doanh nghiệp
                                </th>
                                <th>
                                    Tên doanh nghiệp
                                </th>
                                <th>
                                    Đại lý
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $doanhNghiep)
                                    <tr data-ma-doanh-nghiep="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                        data-ten-doanh-nghiep="{{ $doanhNghiep->ten_doanh_nghiep }}"
                                        data-dia-chi="{{ $doanhNghiep->dia_chi }}"
                                        data-ma-chu-hang="{{ $doanhNghiep->ma_chu_hang }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $doanhNghiep->ma_doanh_nghiep }}</td>
                                        <td>{{ $doanhNghiep->ten_doanh_nghiep }}</td>
                                        <td>{{ $doanhNghiep->ten_chu_hang }}</td>

                                        <td>
                                            {{-- <button class="btn btn-primary mx-1"
                                                onclick="window.location.href='{{ route('quan-ly-khac.danh-sach-doanh-nghiep-ql', ['ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep]) }}'">
                                                Theo dõi
                                            </button> --}}

                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-doanh-nghiep="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                data-ten-doanh-nghiep="{{ $doanhNghiep->ten_doanh_nghiep }}"
                                                data-dia-chi="{{ $doanhNghiep->dia_chi }}">
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
    <!-- Thêm Modal -->
    <div class="modal fade" id="themModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm doanh nghiệp mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-doanh-nghiep') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label for="ma_doanh_nghiep">Mã doanh nghiệp / Tên đăng nhập</label>
                        <input type="text" class="form-control" id="ma_doanh_nghiep" name="ma_doanh_nghiep"
                            placeholder="Nhập mã doanh nghiệp" required>

                        <label class="mt-3" for="ten_doanh_nghiep">Tên doanh nghiệp</label>
                        <input type="text" class="form-control" id="ten_doanh_nghiep" name="ten_doanh_nghiep"
                            placeholder="Nhập tên doanh nghiệp" required>

                        <label class="mt-3" for="dia_chi">Địa chỉ</label>
                        <textarea type="text" class="form-control" id="dia_chi" name="dia_chi" placeholder="Nhập địa chỉ doanh nghiệp"
                            cols="3" required></textarea>

                        <label class="mt-3" for="ma_chu_hang">Đại lý</label>
                        <select class="form-control" id="chu-hang-dropdown-search1" name="ma_chu_hang">
                            <option></option>
                            @foreach ($chuHangs as $chuHang)
                                <option value="{{ $chuHang->ma_chu_hang }}">
                                    {{ $chuHang->ten_chu_hang }}
                                    ({{ $chuHang->ma_chu_hang }})
                                </option>
                            @endforeach
                        </select>
                        <hr />
                        <label class="mt-1" for="mat_khau"><strong>Mật khẩu</strong></label>
                        <input type="password" class="form-control" id="mat_khau" name="mat_khau"
                            autocomplete="new-password" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Thêm mới</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Thông tin Modal -->
    <div class="modal fade" id="thongTinModal" tabindex="-1" aria-labelledby="thongTinModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin doanh nghiệp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-doanh-nghiep') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <p><strong>Mã doanh nghiệp:</strong> <span id="modalMaDoanhNghiep"></span></p>
                        <p><strong>Tên doanh nghiệp:</strong> <span id="modalTenDoanhNghiep"></span></p>
                        <p><strong>Địa chỉ:</strong> <span id="modalDiaChi"></span></p>
                        <input hidden id="modalMaDoanhNghiepInput" name="ma_doanh_nghiep">
                        <p><strong>Đại lý:</strong>
                            <select class="form-control" id="chu-hang-dropdown-search2" name="ma_chu_hang">
                                <option></option>
                                @foreach ($chuHangs as $chuHang)
                                    <option value="{{ $chuHang->ma_chu_hang }}">
                                        {{ $chuHang->ten_chu_hang }}
                                        ({{ $chuHang->ma_chu_hang }})
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
    <!-- Xóa Modal -->
    <div class="modal fade" id="xoaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Xác nhận xóa doanh nghiệp</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-doanh-nghiep') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận xóa doanh nghiệp này?</h6>
                        <div>
                            <label><strong>Mã doanh nghiệp:</strong></label>
                            <p class="d-inline" id="modalMaDoanhNghiepXoa"></p>
                        </div>
                        <div>
                            <label><strong>Tên doanh nghiệp:</strong></label>
                            <p class="d-inline" id="modalTenDoanhNghiepXoa"></p>
                        </div>
                        <div>
                            <label><strong>Địa chỉ:</strong></label>
                            <p class="d-inline" id="modalDiaChiXoa"></p>
                        </div>
                        <input type="hidden" name="ma_doanh_nghiep" id="modalInputMaDoanhNghiepXoa">
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
            $('#chu-hang-dropdown-search1').select2();
            // Reinitialize Select2 when modal opens
            $('#themModal').on('shown.bs.modal', function() {
                $('#chu-hang-dropdown-search1').select2('destroy');
                $('#chu-hang-dropdown-search1').select2({
                    placeholder: "Chọn đại lý",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#themModal .modal-body'),
                });
            });

            $('#chu-hang-dropdown-search2').select2();
            // Reinitialize Select2 when modal opens
            $('#themModal').on('shown.bs.modal', function() {
                $('#chu-hang-dropdown-search2').select2('destroy');
                $('#chu-hang-dropdown-search2').select2({
                    placeholder: "Chọn đại lý",
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
                var maDoanhNghiep = $(this).data('ma-doanh-nghiep');
                var tenDoanhNghiep = $(this).data('ten-doanh-nghiep');
                var maChuHang = $(this).data('ma-chu-hang');
                var diaChi = $(this).data('dia-chi');

                // Set the data in the modal
                $('#modalMaDoanhNghiep').text(maDoanhNghiep);
                $('#modalTenDoanhNghiep').text(tenDoanhNghiep);
                $('#modalMaChuHang').text(maChuHang);
                $('#modalDiaChi').text(diaChi);
                document.getElementById('modalMaDoanhNghiepInput').value = maDoanhNghiep;

                $('#chu-hang-dropdown-search2').val(maChuHang).trigger('change');

                // Show the modal
                $('#thongTinModal').modal('show');
            });
        });
    </script>
    {{-- Script áp dụng cho cột thao tác --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenDoanhNghiep = document.getElementById('modalTenDoanhNghiepXoa');
            const modalMaDoanhNghiep = document.getElementById('modalMaDoanhNghiepXoa');
            const modalDiaChi = document.getElementById('modalDiaChiXoa');
            const modalInputMaDoanhNghiep = document.getElementById('modalInputMaDoanhNghiepXoa');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const tenDoanhNghiep = this.getAttribute('data-ten-doanh-nghiep');
                    const maDoanhNghiep = this.getAttribute('data-ma-doanh-nghiep');
                    const diaChi = this.getAttribute('data-dia-chi');

                    // Set data in the modal
                    modalTenDoanhNghiep.textContent = tenDoanhNghiep;
                    modalMaDoanhNghiep.textContent = maDoanhNghiep;
                    modalDiaChi.textContent = diaChi;
                    modalInputMaDoanhNghiep.value = maDoanhNghiep;
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
