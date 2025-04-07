@extends('layout.user-layout')

@section('title', 'Quản lý xuất cảnh')

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
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai xuất cảnh</h4>
                        </div>
                        <div class="col-6">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <a href="/them-to-khai-xuat-canh"><button class="btn btn-success float-end">Nhập tờ khai
                                        xuất cảnh</button></a>
                            @endif
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
                                    Số
                                </th>
                                <th>
                                    Tên phương tiện xuất cảnh
                                </th>
                                <th>
                                    Công ty
                                </th>
                                <th>
                                    Ngày đăng ký
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
                ajax: "{{ route('xuat-canh.getXuatCanhs') }}",

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
                        data: 'ma_xuat_canh',
                        name: 'ma_xuat_canh'
                    },
                    {
                        data: 'ten_phuong_tien_vt',
                        name: 'ten_phuong_tien_vt'
                    },
                    {
                        data: 'ten_doanh_nghiep',
                        name: 'ten_doanh_nghiep'
                    },
                    {
                        data: 'ngay_dang_ky',
                        name: 'ngay_dang_ky'
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
                        `window.location='{{ url('/thong-tin-xuat-canh') }}/${data.ma_xuat_canh}'`
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
                        '<option class="text-success" value="3">ĐÃ DUYỆT THỰC XUẤT</option>'
                    );
                    select.append(
                        '<option class="text-warning" value="4">DOANH NGHIỆP XIN SỬA</option>'
                    );
                    select.append(
                        '<option class="text-danger" value="5">DOANH NGHIỆP XIN HỦY</option>'
                    );
                    select.append('<option class="text-danger" value="0">ĐÃ HỦY</option>');
                    select.append(
                        '<option class="text-danger" value="6">CHẤP NHẬN HỦY</option>');
                        
                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('xuatCanh1', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('xuatCanh1');
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
