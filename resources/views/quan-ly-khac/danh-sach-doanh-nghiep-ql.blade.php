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
            <a class="return-link" href="/quan-ly-doanh-nghiep">
                <p>
                    < Quay lại quản lý doanh nghiệp </p>
            </a>
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách doanh nghiệp mà {{ $DoanhNghiep->ten_doanh_nghiep }} có thể theo dõi</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Theo dõi doanh nghiệp mới</button>
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
                                    Thao tác
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $doanhNghiep)
                                    <tr data-ma-doanh-nghiep="{{ $doanhNghiep->ma_doanh_nghiep_khac }}"
                                        data-ten-doanh-nghiep="{{ $doanhNghiep->doanhNghiepKhac->ten_doanh_nghiep }}"
                                        data-dia-chi="{{ $doanhNghiep->doanhNghiepKhac->dia_chi }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $doanhNghiep->ma_doanh_nghiep_khac }}</td>
                                        <td>{{ $doanhNghiep->DoanhNghiepKhac->ten_doanh_nghiep }}</td>
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-doanh-nghiep="{{ $doanhNghiep->ma_doanh_nghiep_khac }}"
                                                data-ten-doanh-nghiep="{{ $doanhNghiep->doanhNghiepKhac->ten_doanh_nghiep }}"
                                                data-dia-chi="{{ $doanhNghiep->doanhNghiepKhac->dia_chi }}">
                                                Bỏ theo dõi
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
    <!-- Thêm Modal -->
    <div class="modal fade" id="themModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm doanh nghiệp mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-doanh-nghiep-ql') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <lable>Thêm doanh nghiệp để theo dõi</lable>
                        <input type="hidden" value="{{ $DoanhNghiep->ma_doanh_nghiep }}" name="ma_doanh_nghiep_ql" >
                        <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep_khac">
                            <option></option>
                            @foreach ($doanhNghieps as $doanhNghiep)
                                <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                    {{ $doanhNghiep->ten_doanh_nghiep }}
                                    ({{ $doanhNghiep->ma_doanh_nghiep }})
                                </option>
                            @endforeach
                        </select>
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
                    <h4 class="modal-title">Xác nhận bỏ theo dõi doanh nghiệp</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-doanh-nghiep-ql') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận bỏ theo dõi doanh nghiệp này?</h6>
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
                        <input type="hidden" name="ma_doanh_nghiep_khac" id="modalInputMaDoanhNghiepXoa">
                        <input type="hidden" name="ma_doanh_nghiep_ql" value="{{ $DoanhNghiep->ma_doanh_nghiep }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận bỏ theo dõi</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
