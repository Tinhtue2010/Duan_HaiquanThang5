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
                            <h4 class="font-weight-bold text-primary">Danh sách theo dõi xuất nhập cảnh yêu cầu sửa</h4>
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
                                @foreach ($XNCs as $item)
                                    <tr onclick="window.location='{{ url('/thong-tin-xnc/' . $item->ma_xnc) }}'"
                                        style="cursor: pointer;">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->so_the }}</td>
                                        <td>{{ $item->ten_phuong_tien_vt }}</td>
                                        <td>
                                            @if ($item->is_hang_lanh == 1)
                                                Hàng lạnh
                                            @else
                                                Hàng nóng
                                            @endif
                                        </td>
                                        <td>{{ $item->ngay_them }}</td>
                                        <td>{{ $item->thoi_gian_nhap_canh }}</td>
                                        <td>{{ $item->thoi_gian_xuat_canh }}</td>
                                        <td>{{ $item->ten_cong_chuc }}</td>
                                        @if ($item->trang_thai == 1)
                                            <td class="text-success">Đã duyệt</td>
                                        @elseif($item->trang_thai == 3)
                                            <td class="text-warning">Yêu cầu sửa</td>
                                        @elseif($item->trang_thai == 4)
                                            <td class="text-danger">Yêu cầu hủy</td>
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
