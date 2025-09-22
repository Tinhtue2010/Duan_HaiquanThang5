@extends('layout.user-layout')

@section('title', 'Quản lý tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai nhập đã quá 14 ngày, từ ngày 15/8/2025</h4>
                        </div>
                        <div class="col-3">
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
                                    Ngày tạo
                                </th>
                                <th>
                                    Trạng thái
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($nhapHangs as $index => $nhapHang)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('nhap-hang.show', $nhapHang->so_to_khai_nhap) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $nhapHang->so_to_khai_nhap }}</td>
                                        <td>{{ $nhapHang->doanhNghiep ? $nhapHang->doanhNghiep->ten_doanh_nghiep : 'Unknown' }}
                                        </td>
                                        <td>{{ $nhapHang->chuHang ? $nhapHang->chuHang->ten_chu_hang : 'Unknown' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($nhapHang->created_at)->format('d-m-Y') }}</td>
                                        @if (trim($nhapHang->trang_thai) == '0')
                                            <td class="text-danger">Đã hủy</td>
                                        @elseif (trim($nhapHang->trang_thai) == '2')
                                            <td class="text-success">Đã duyệt</td>
                                        @endif
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
