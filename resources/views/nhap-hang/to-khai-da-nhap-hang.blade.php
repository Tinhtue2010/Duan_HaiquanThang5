@extends('layout.user-layout')

@section('title', 'Quản lý tờ khai đã nhập hàng')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai nhập đã nhập hàng</h4>
                        </div>
                        <div class="col-6">
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
                                    Số tờ khai nhập
                                </th>
                                <th>
                                    Công ty
                                </th>
                                <th>
                                    Đại lý
                                </th>
                                <th>
                                    Ngày đăng ký
                                </th>
                                <th>
                                    
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
                ajax: "{{ route('nhap-hang.getNhapHangDaDuyets') }}",

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
                        name: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'so_to_khai_nhap',
                        name: 'so_to_khai_nhap'
                    },
                    {
                        data: 'ten_doanh_nghiep',
                        name: 'ten_doanh_nghiep'
                    },
                    {
                        data: 'ten_chu_hang',
                        name: 'ten_chu_hang'
                    },
                    {
                        data: 'ngay_dang_ky',
                        name: 'ngay_dang_ky',
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
                        targets: 3
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    $(row).addClass('clickable-row').attr('onclick',
                        `window.location='{{ url('/thong-tin-nhap-hang') }}/${data.so_to_khai_nhap}'`
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
                        '<option class="text-success" value="2">ĐÃ NHẬP HÀNG</option>');
                    select.append(
                        '<option class="text-success" value="4">ĐÃ XUẤT HẾT</option>');
                    select.append(
                        '<option class="text-success" value="7">ĐÃ BÀN GIAO HỒ SƠ</option>'
                    );
                    select.append(
                        '<option class="text-success" value="6">QUAY VỀ KHO BAN ĐẦU</option>'
                    );
                    select.append(
                        '<option class="text-danger" value="5">ĐÃ TIÊU HỦY</option>');

                    var header = $(column.header());
                    header.append(select);
                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('nhapHang2', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('nhapHang2');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                }
            });
        });
    </script>
@stop
