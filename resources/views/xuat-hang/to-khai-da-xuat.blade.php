@extends('layout.user-layout')

@section('title', 'Quản lý xuất hàng')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="font-weight-bold text-primary">Danh sách phiếu xuất</h4>
                        </div>
                        <div class="col-6">
                            @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                <a href="/duyet-nhanh-thuc-xuat"><button class="btn btn-success float-end">Duyệt
                                        thực xuất</button></a>
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
                                    Loại hình
                                </th>
                                <th>
                                    Công ty
                                </th>
                                <th>
                                    Ngày đăng ký
                                </th>
                                <th>
                                    Số lượng
                                </th>
                                <th>
                                    Tên xuồng
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
                ajax: "{{ route('xuat-hang.getXuatHangDaDuyets') }}",

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
                        data: 'so_to_khai_xuat',
                        name: 'so_to_khai_xuat'
                    },
                    {
                        data: 'ma_loai_hinh',
                        name: 'ma_loai_hinh'
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
                        data: 'tong_so_luong',
                        name: 'tong_so_luong'
                    },
                    {
                        data: 'ten_phuong_tien_vt',
                        name: 'ten_phuong_tien_vt'
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
                        `window.location='{{ url('/thong-tin-xuat-hang') }}/${data.so_to_khai_xuat}'`
                    );
                },
                initComplete: function() {
                    $('.dataTables_filter input[type="search"]').css({
                        width: '350px',
                        display: 'inline-block',
                        height: '40px'
                    });
                    var column = this.api().column(7); // Status column index
                    var select = $(
                        '<select class="form-control"><option value="">TẤT CẢ</option></select>'
                    )

                    select.append('<option class="text-success" value="ĐÃ DUYỆT">ĐÃ DUYỆT</option>');
                    select.append(
                        '<option class="text-success" value="ĐÃ CHỌN PHƯƠNG TIỆN XUẤT CẢNH">ĐÃ CHỌN PHƯƠNG TIỆN XUẤT CẢNH</option>'
                    );
                    select.append(
                        '<option class="text-success" value="ĐÃ DUYỆT XUẤT HÀNG">ĐÃ DUYỆT XUẤT HÀNG</option>'
                    );
                    select.append(
                        '<option class="text-success" value="ĐÃ THỰC XUẤT HÀNG">ĐÃ THỰC XUẤT HÀNG</option>'
                    );
                    $(column.header()).empty().append(select);

                    // Handle filtering
                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('xuatHang2', val);
                        column.search(val ? val : '', false, true).draw();
                    });
                    var savedFilter = localStorage.getItem('xuatHang2');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                },
            });
        });
    </script>
@stop
