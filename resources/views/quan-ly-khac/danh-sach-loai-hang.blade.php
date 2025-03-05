@extends('layout.user-layout')

@section('title', 'Danh sách loại hàng')

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
                            <h4 class="font-weight-bold text-primary">Danh sách loại hàng</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm loại hàng mới</button>
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
                                    Tên loại hàng
                                </th>
                                <th>
                                    Đơn vị tính
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $loaiHang)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $loaiHang->ten_loai_hang }}</td>
                                        <td>{{ $loaiHang->don_vi_tinh }}</td>
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                            data-ma-loai-hang="{{ $loaiHang->ma_loai_hang }}"
                                            data-ten-loai-hang="{{ $loaiHang->ten_loai_hang }}"
                                            data-don-vi-tinh="{{ $loaiHang->don_vi_tinh }}"
                                            >
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
    <!-- Thêm Modal -->
    <div class="modal fade" id="themModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm loại hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-loai-hang') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="" for="ten_loai_hang"><strong>Tên loại hàng</strong></label>
                        <input type="text" class="form-control" id="ten_loai_hang" name="ten_loai_hang"
                            placeholder="Nhập tên loại hàng" required>
                        <label class="" for="don_vi_tinh"><strong>Đơn vị tính</strong></label>
                        <input type="text" class="form-control" id="don_vi_tinh" name="don_vi_tinh"
                            placeholder="Nhập đơn vị tính" required>
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
                    <h4 class="modal-title text-danger">Xác nhận xóa loại hàng này</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-loai-hang') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div>
                            <label><strong>Tên loại hàng:</strong></label>
                            <p class="d-inline" id="modalTenLoaiHang"></p>
                        </div>
                        <div>
                            <label><strong>Đơn vị tính:</strong></label>
                            <p class="d-inline" id="modalDonViTinh"></p>
                        </div>
                        <input type="hidden" name="ma_loai_hang" id="modalInputMaLoaiHang">
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
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenLoaiHang = document.getElementById('modalTenLoaiHang');
            const modalDonViTinh = document.getElementById('modalDonViTinh');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const tenLoaiHang = this.getAttribute('data-ten-loai-hang');
                    const maLoaiHang = this.getAttribute('data-ma-loai-hang');
                    const donViTinh = this.getAttribute('data-don-vi-tinh');

                    // Set data in the modal
                    modalDonViTinh.textContent = donViTinh;
                    modalTenLoaiHang.textContent = tenLoaiHang;
                    modalInputMaLoaiHang.value = maLoaiHang;
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
