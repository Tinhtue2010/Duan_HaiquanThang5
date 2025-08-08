@extends('layout.user-layout')

@section('title', 'Danh sách hải quan')

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
                            <h4 class="font-weight-bold text-primary">Danh sách đơn vị hải quan</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm hải quan mới</button>
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
                                    Mã hải quan
                                </th>
                                <th>
                                    Tên hải quan
                                </th>
                                {{-- <th>
                                    Thao tác
                                </th> --}}
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $haiQuan)
                                    <tr data-ma-hai-quan="{{ $haiQuan->ma_hai_quan }}"
                                        data-ten-hai-quan="{{ $haiQuan->ten_hai_quan }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $haiQuan->ma_hai_quan }}</td>
                                        <td>{{ $haiQuan->ten_hai_quan }}</td>
                                        {{-- <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-ma-hai-quan="{{ $haiQuan->ma_hai_quan }}"
                                                data-ten-hai-quan="{{ $haiQuan->ten_hai_quan }}">
                                                Xóa
                                            </button></td> --}}
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
                    <h5 class="modal-title">Thêm hải quan mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-hai-quan') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label for="ma_hai_quan">Mã hải quan</label>
                        <input type="text" class="form-control" id="ma_hai_quan" name="ma_hai_quan"
                            placeholder="Nhập mã hải quan" required>

                        <label class="mt-3" for="ten_hai_quan">Tên hải quan</label>
                        <input type="text" class="form-control" id="ten_hai_quan" name="ten_hai_quan"
                            placeholder="Nhập tên hải quan" required>
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
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin hải quan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-hai-quan') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <p><strong>Mã hải quan:</strong> <span id="modalMaHaiQuan"></span></p>
                        <label for="ten_hai_quan"><strong>Tên hải quan</strong></label>
                        <textarea type="text" class="form-control mb-3" id="modalTenHaiQuanInput" name="ten_hai_quan"
                            placeholder="Nhập tên hải quan" cols="3" required></textarea>
                        <input type="hidden" name="ma_hai_quan" id="modalMaHaiQuanInput">
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
    {{-- <div class="modal fade" id="xoaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Xác nhận xóa hải quan</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-hai-quan') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <h6 class="text-danger">Xác nhận xóa hải quan này?</h6>
                        <div>
                            <label><strong>Mã hải quan:</strong></label>
                            <p class="d-inline" id="modalMaHaiQuan"></p>
                        </div>
                        <div>
                            <label><strong>Tên hải quan:</strong></label>
                            <p class="d-inline" id="modalTenHaiQuan"></p>
                        </div>
                        <input type="hidden" name="ma_hai_quan" id="modalInputMaHaiQuan">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalTenHaiQuan = document.getElementById('modalTenHaiQuan');
            const modalMaHaiQuan = document.getElementById('modalMaHaiQuan');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Get data from the clicked button
                    const tenHaiQuan = this.getAttribute('data-ten-hai-quan');
                    const maHaiQuan = this.getAttribute('data-ma-hai-quan');

                    // Set data in the modal
                    modalTenHaiQuan.textContent = tenHaiQuan;
                    modalMaHaiQuan.textContent = maHaiQuan;
                    modalInputMaHaiQuan.value = maHaiQuan;
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#dataTable tbody').on('click', 'tr', function(event) {
                var maHaiQuan = $(this).data('ma-hai-quan');
                var tenHaiQuan = $(this).data('ten-hai-quan');

                $('#modalMaHaiQuan').text(maHaiQuan);
                $('#modalTenHaiQuanInput').val(tenHaiQuan);
                $('#modalMaHaiQuanInput').val(maHaiQuan);

                $('#thongTinModal').modal('show');
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
