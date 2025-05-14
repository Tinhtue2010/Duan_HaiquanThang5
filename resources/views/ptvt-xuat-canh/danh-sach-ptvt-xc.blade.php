@extends('layout.user-layout')

@section('title', 'Quản lý phương tiện vận tải xuất cảnh')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif(session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách phương tiện vận tải</h4>
                        </div>
                        <div class="col-3">
                            <a href="/them-to-khai-ptvt-xc"><button class="btn btn-success float-end">Thêm phương tiện
                                    vận tải</button></a>
                        </div>
                    </div>
                </div>
                <div class="container-fluid card-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Tên phương tiện
                                </th>
                                <th>
                                    Tên thuyền trưởng
                                </th>
                                <th>
                                    Quốc tịch tàu
                                </th>
                                <th>
                                    Doanh nghiệp
                                </th>
                                <th>
                                    Trạng thái
                                </th>
                            </thead>
                            <tbody class="clickable-row">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('phuong-tien-vt.getPTVTs') }}",
                language: {
                    searchPlaceholder: "Tìm kiếm",
                    search: "",
                    sInfo: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    sInfoEmpty: "Hiển thị 0 đến 0 của 0 mục",
                    sInfoFiltered: "Lọc từ _MAX_ mục",
                    sLengthMenu: "Hiện _MENU_ mục",
                    sEmptyTable: "Không có dữ liệu",
                },
                dom: '<"clear"><"row"<"col"l><"col"f>>rt<"row"<"col"i><"col"p>><"row"<"col"B>>',
                buttons: [{
                        extend: 'excel',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        title: ''
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        title: ''
                    }
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'ten_phuong_tien_vt',
                        name: 'ten_phuong_tien_vt'
                    },
                    {
                        data: 'ten_thuyen_truong',
                        name: 'ten_thuyen_truong'
                    },
                    {
                        data: 'quoc_tich_tau',
                        name: 'quoc_tich_tau'
                    },
                    {
                        data: 'ten_doanh_nghiep',
                        name: 'ten_doanh_nghiep'
                    },
                    {
                        data: 'trang_thai',
                        name: 'trang_thai',
                        orderable: false
                    }

                ],
                columnDefs: [{
                        width: "150px",
                        targets: -1
                    },
                    {
                        width: "230px",
                        targets: 4
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    $(row).addClass('clickable-row').attr('onclick',
                        `window.location='{{ url('/thong-tin-ptvt-xc') }}/${data.so_ptvt_xuat_canh}'`
                    );
                },
                initComplete: function() {
                    $('.dataTables_filter input[type="search"]').css({
                        width: '350px',
                        display: 'inline-block',
                        height: '40px'
                    });
                    var column = this.api().column(5); // Status column index
                    var select = $(
                        '<select class="form-control"><option value="">TẤT CẢ</option></select>'
                    )

                    select.append(
                        '<option class="text-primary" value="1">ĐANG CHỜ DUYỆT</option>'
                    );
                    select.append('<option class="text-success" value="2">ĐÃ DUYỆT</option>');
                    select.append(
                        '<option class="text-warning" value="4">DOANH NGHIỆP XIN SỬA</option>'
                    );
                    select.append(
                        '<option class="text-danger" value="5">DOANH NGHIỆP XIN HỦY</option>'
                    );
                    select.append('<option class="text-danger" value="0">ĐÃ HỦY</option>');
                        
                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('PTVT', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('PTVT');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    } else {
                        select.val("");
                    }

                },
            });
        });
    </script>
@stop
