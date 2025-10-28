@extends('layout.user-layout')

@section('title', 'Danh sách đoàn tàu')

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
                            <h4 class="font-weight-bold text-primary">Danh sách đoàn tàu</h4>
                        </div>
                        <div class="col-3">
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
                                    Tên tàu
                                </th>
                                <th>
                                    Tên đoàn tàu
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $item)
                                    <tr  data-ten-doan-tau="{{ $item->ten_doan_tau }}"
                                        data-phuong-tien-vt-nhap="{{ $item->phuong_tien_vt_nhap }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->phuong_tien_vt_nhap }}</td>
                                        <td>{{ $item->ten_doan_tau }}</td>
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
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin đoàn tàu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.update-doan-tau') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <p><strong>Tên tàu:</strong> <span id="modalTenTau"></span></p>
                        <label class="mt-1" for="ten_doan_tau"><strong>Tên đoàn tàu</strong></label>
                        <input type="text" class="form-control" name="ten_doan_tau" id="modalTenDoanTau">
                        <input hidden id="modalPhuongTienVTNhap" name="phuong_tien_vt_nhap">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Cập nhật</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Script áp dụng cho 3 cột đầu --}}
    <script>
        $(document).ready(function() {
            $('#dataTable tbody').on('click', 'tr', function(event) {
                var tenDoanTau = $(this).data('ten-doan-tau');
                var phuongTienVTNhap = $(this).data('phuong-tien-vt-nhap');

                $('#modalTenTau').text(phuongTienVTNhap);
                $('#modalTenDoanTau').val(tenDoanTau);
                $('#modalPhuongTienVTNhap').val(phuongTienVTNhap);

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
