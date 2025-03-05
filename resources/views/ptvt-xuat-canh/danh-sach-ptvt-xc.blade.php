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
                            @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                <a href="/them-to-khai-ptvt-xc"><button class="btn btn-success float-end">Thêm phương tiện
                                        vận tải</button></a>
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
                                    Tên phương tiện
                                </th>
                                <th>
                                    Tên thuyền trưởng
                                </th>
                                <th>
                                    Quốc tịch tàu
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $phuong_tien_vt)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('phuong-tien-vt.thong-tin-ptvt-xc', $phuong_tien_vt->so_ptvt_xuat_canh) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $phuong_tien_vt->ten_phuong_tien_vt }}</td>
                                        <td>{{ $phuong_tien_vt->ten_thuyen_truong }}</td>
                                        <td>{{ $phuong_tien_vt->quoc_tich_tau }}</td>
                                        </td>
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
