@extends('layout.user-layout')

@section('title', 'Danh sách tờ khai quá hạn')

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
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai quá hạn</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm tờ khai quá hạn</button>
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
                                    Số tờ khai nhập
                                </th>
                                <th>
                                    Doanh nghiệp
                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody>
                                @foreach (($data instanceof \Illuminate\Database\Eloquent\Builder ? $data->get() : $data) as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->so_to_khai_nhap }}</td>
                                        <td>{{ $item->ten_doanh_nghiep }}</td>
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                            data-so-to-khai-nhap="{{ $item->so_to_khai_nhap }}"
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
                    <h5 class="modal-title">Thêm tờ khai quá hạn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-tk-qua-han') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="" for="so_to_khai_nhap"><strong>Số tờ khai nhập</strong></label>
                        <input type="text" class="form-control" id="so_to_khai_nhap" name="so_to_khai_nhap"
                            placeholder="Nhập số tờ khai nhập" required>
                                                
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
                    <h4 class="modal-title text-danger">Xác nhận xóa tờ khai quá hạn</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-tk-qua-han') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div>
                            <label><strong>Số tờ khai nhập:</strong></label>
                            <p class="d-inline" id="modalSoToKhaiNhap"></p>
                        </div>
                        <input type="hidden" name="so_to_khai_nhap" id="modalInputSoToKhaiNhap">
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
            const modalSoToKhaiNhap = document.getElementById('modalSoToKhaiNhap');
            const modalInputSoToKhaiNhap = document.getElementById('modalInputSoToKhaiNhap');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const soToKhaiNhap = this.getAttribute('data-so-to-khai-nhap');

                    modalSoToKhaiNhap.textContent = soToKhaiNhap;
                    modalInputSoToKhaiNhap.value = soToKhaiNhap;
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
