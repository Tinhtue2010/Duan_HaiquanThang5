@extends('layout.user-layout')

@section('title', 'Quản lý xuất nhập cảnh')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    @if (session('alert-success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                            <strong>{{ session('alert-success') }}</strong>
                        </div>
                    @elseif(session('alert-danger'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                            <strong>{{ session('alert-danger') }}</strong>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-6">
                            <h4 class="font-weight-bold text-primary">Danh sách theo dõi xuất nhập cảnh</h4>
                        </div>
                        <div class="col-6">
                            <a href="/them-xnc">
                                <button class="btn btn-success float-end">Thêm xuất nhập cảnh</button>
                            </a>
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
                                    Số thẻ
                                </th>
                                <th>
                                    Tên phương tiện
                                </th>
                                <th>
                                    Loại hàng
                                </th>
                                <th>
                                    Ngày
                                </th>
                                <th>
                                    Giờ nhập cảnh
                                </th>
                                <th>
                                    Giờ xuất cảnh
                                </th>
                                <th>
                                    Tên công chức
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
    <!-- Custom search box -->
    <div class="mb-3">
        <div class="col-4">
            <input type="text" id="customSearchBox" class="form-control mb-3" placeholder="Tìm kiếm theo Số thẻ...">
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Move the custom search box above the table
            $('#customSearchBox').insertBefore('#dataTable');

            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('xuat-nhap-canh.getXNCs') }}",

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
                        data: 'so_the',
                        name: 'so_the'
                    },
                    {
                        data: 'ten_phuong_tien_vt',
                        name: 'ten_phuong_tien_vt'
                    },
                    {
                        data: 'loai_hang',
                        name: 'loai_hang'
                    },
                    {
                        data: 'ngay_them',
                        name: 'ngay_them'
                    },
                    {
                        data: 'thoi_gian_nhap_canh',
                        name: 'thoi_gian_nhap_canh'
                    },
                    {
                        data: 'thoi_gian_xuat_canh',
                        name: 'thoi_gian_xuat_canh',
                    },
                    {
                        data: 'ten_cong_chuc',
                        name: 'ten_cong_chuc',
                    },
                    {
                        data: 'trang_thai',
                        name: 'trang_thai',
                    }

                ],
                initComplete: function() {
                    $('.dataTables_filter input[type="search"]').css({
                        width: '350px',
                        display: 'inline-block',
                        height: '40px'
                    });
                    var column = this.api().column(8); // Status column index
                    var select = $(
                        '<select class="form-control"><option value="">TẤT CẢ</option></select>'
                    )

                    select.append('<option class="text-success" value="1">ĐÃ DUYỆT</option>');
                    select.append(
                        '<option class="text-warning" value="3">YÊU CẦU SỬA</option>');
                    select.append(
                        '<option class="text-danger" value="4">YÊU CẦU HỦY</option>');

                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('xnk', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('xnk');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                },
                createdRow: function(row, data, dataIndex) {
                    $(row).addClass('clickable-row').attr('onclick',
                        `window.location='{{ url('/thong-tin-xnc') }}/${data.ma_xnc}'`
                    );
                },
            });

            // Custom search box event: search only in the 2nd column (Số thẻ)
            $('#customSearchBox').on('keyup', function() {
                table
                    .columns(1) // 0-based index, so 1 is the 2nd column
                    .search(this.value)
                    .draw();
            });
        });
    </script>
@stop
