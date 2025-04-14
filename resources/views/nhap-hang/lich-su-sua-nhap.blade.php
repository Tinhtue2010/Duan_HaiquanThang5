@extends('layout.user-layout')

@section('title', 'Danh sách các lần sửa đổi')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        @if (session('alert-success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                                <strong>{{ session('alert-success') }}</strong>
                            </div>
                        @elseif(session('alert-danger'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                                <strong>{{ session('alert-danger') }}</strong>
                            </div>
                        @endif
                        <div class="col-7">
                            <h4 class="font-weight-bold text-primary">Danh sách các lần sửa đổi</h4>
                        </div>
                        <div class="col-5">
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
                                    Ngày sửa
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($nhapHangs->skip(1) as $index => $nhapHang)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('nhap-hang.xem-sua-nhap-theo-lan', $nhapHang->ma_nhap_sua) }}'">
                                        <td>{{ $index }}</td> {{-- +2 because we're skipping the first item --}}
                                        <td>{{ $nhapHang->so_to_khai_nhap }}</td>
                                        <td>{{ $nhapHang->doanhNghiep ? $nhapHang->doanhNghiep->ten_doanh_nghiep : 'Unknown' }}
                                        </td>
                                        <td>{{ $nhapHang->chuHang ? $nhapHang->chuHang->ten_chu_hang : 'Unknown' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($nhapHang->created_at)->format('d-m-Y H:i') }}</td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
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

                    select.append('<option class="text-primary" value="1">ĐANG CHỜ DUYỆT</option>');
                    select.append(
                        '<option class="text-warning" value="3">DOANH NGHIỆP YÊU CẦU SỬA</option>');

                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('nhapHang1', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('nhapHang1');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                },
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
