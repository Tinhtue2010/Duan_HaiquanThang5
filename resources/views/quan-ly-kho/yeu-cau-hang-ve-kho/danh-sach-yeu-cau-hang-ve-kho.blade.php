@extends('layout.user-layout')

@section('title', 'Quản lý yêu cầu đưa hàng trở lại kho ban đầu')

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
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách yêu cầu đưa hàng trở lại kho ban đầu</h4>
                        </div>
                        <div class="col-3">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <a href="{{ route('quan-ly-kho.them-yeu-cau-hang-ve-kho') }}"><button
                                        class="btn btn-success float-end">Nhập yêu cầu</button></a>
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
                                    Số tờ khai
                                </th>
                                <th>
                                    Doanh nghiệp
                                </th>
                                <th>
                                    Ngày yêu cầu
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
                ajax: "{{ route('quan-ly-kho.getYeuCauHangVeKho') }}",

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
                        data: 'ma_yeu_cau',
                        name: 'ma_yeu_cau'
                    },
                    {
                        data: 'so_to_khai_nhap_list',
                        name: 'so_to_khai_nhap_list'
                    },
                    {
                        data: 'ten_doanh_nghiep',
                        name: 'ten_doanh_nghiep'
                    },
                    {
                        data: 'ngay_yeu_cau',
                        name: 'ngay_yeu_cau'
                    },
                    {
                        data: 'trang_thai',
                        name: 'trang_thai',
                        orderable: false
                    }

                ],
                columnDefs: [{
                        orderable: false,
                        width: "350px",
                        targets: 2
                    },
                    {
                        orderable: false,
                        targets: -1
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    $(row).addClass('clickable-row').attr('onclick',
                        `window.location='{{ url('/thong-tin-yeu-cau-hang-ve-kho') }}/${data.ma_yeu_cau}'`
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
                        '<option class="text-primary" value="ĐANG CHỜ DUYỆT">ĐANG CHỜ DUYỆT</option>'
                    );
                    select.append('<option class="text-success" value="ĐÃ DUYỆT">ĐÃ DUYỆT</option>');

                    select.append(
                        '<option class="text-warning" value="DOANH NGHIỆP ĐỀ NGHỊ SỬA YÊU CẦU">DOANH NGHIỆP ĐỀ NGHỊ SỬA YÊU CẦU</option>'
                    );
                    select.append(
                        '<option class="text-danger" value="DOANH NGHIỆP ĐỀ NGHỊ HỦY YÊU CẦU">DOANH NGHIỆP ĐỀ NGHỊ HỦY YÊU CẦU</option>'
                    );
                    select.append('<option class="text-danger" value="ĐÃ HỦY">ĐÃ HỦY</option>');

                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('hangVeKho', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('hangVeKho');
                    if (!savedFilter) {
                        savedFilter = '';
                        localStorage.setItem('hangVeKho', savedFilter);
                    }

                    select.val(savedFilter);
                    column.search(savedFilter, false, true).draw();
                },
            });
        });
    </script>
@stop
