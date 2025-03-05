@extends('layout.user-layout')

@section('title', 'Quản lý nhập hàng')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @endif
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai xếp hàng lên phương tiện vận tải</h4>
                        </div>
                        <div class="col-6">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')                            
                                <a href="/them-to-khai-ptvt"><button class="btn btn-success float-end">Nhập tờ khai</button></a>
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
                                    Doanh nghiệp
                                </th>
                                <th>
                                    Tên phương tiện
                                </th>
                                <th>
                                    Ngày đăng ký
                                </th>
                                <th>
                                    Trạng thái
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $phuong_tien_vt)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('phuong-tien-vt.thong-tin-to-khai-ptvt', $phuong_tien_vt->so_to_khai_ptvt) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $phuong_tien_vt->so_to_khai_ptvt }}</td>
                                        <td>{{ $phuong_tien_vt->doanhNghiep->ten_doanh_nghiep }}</td>
                                        <td>{{ $phuong_tien_vt->ten_phuong_tien_vt }}</td>
                                        <td>{{ \Carbon\Carbon::parse($phuong_tien_vt->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        @if (trim($phuong_tien_vt->trang_thai) == 'Đang chờ duyệt' || trim($phuong_tien_vt->trang_thai) == 'Đang chờ duyệt (Từ chối hủy)')
                                            <td class="text-primary">{{ $phuong_tien_vt->trang_thai }}</td>
                                        @elseif (trim($phuong_tien_vt->trang_thai) == 'Đã duyệt')
                                            <td class="text-success">{{ $phuong_tien_vt->trang_thai }}</td>
                                        @elseif (trim($phuong_tien_vt->trang_thai) == 'Đã hủy')
                                            <td class="text-danger">{{ $phuong_tien_vt->trang_thai }}</td>
                                        @elseif (trim($phuong_tien_vt->trang_thai) == 'Xin hủy tờ khai')
                                            <td class="text-warning">{{ $phuong_tien_vt->trang_thai }}</td>
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
