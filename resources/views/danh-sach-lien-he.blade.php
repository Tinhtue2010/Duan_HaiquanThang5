@extends('layout.user-layout')

@section('title', 'Danh sách liên hệ')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách liên hệ</h4>
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
                                    Tên cá nhân/Tổ chức
                                </th>
                                <th>
                                    Email
                                </th>
                                <th>
                                    Ngày gửi
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $lienHe)
                                    <tr data-ten-ca-nhan="{{ $lienHe->ten_ca_nhan }}"
                                        data-ngay-tao="{{ $lienHe->ngay_tao }}"
                                        data-ghi-chu="{{ $lienHe->loi_nhan }}"
                                        >
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $lienHe->ten_ca_nhan }}</td>
                                        <td>{{ $lienHe->email }}</td>
                                        <td>{{ $lienHe->ngay_tao }}</td>
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
                    <h5 class="modal-title" id="thongTinModalLabel">Thông tin liên hệ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Tên cá nhân:</strong> <span id="modalTenCaNhan"></span></p>
                    <p><strong>Lời nhắn:</strong> <span id="modalGhiChu"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#dataTable tbody').on('click', 'tr', function(event) {
                if ($(event.target).closest('td:last-child').length) {
                    return;
                }

                var ghiChu = $(this).data('ghi-chu');
                var tenCaNhan = $(this).data('ten-ca-nhan');

                $('#modalTenCaNhan').text(tenCaNhan);
                $('#modalGhiChu').text(ghiChu);

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
