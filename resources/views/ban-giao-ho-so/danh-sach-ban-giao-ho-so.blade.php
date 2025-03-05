@extends('layout.user-layout')

@section('title', 'Quản lý bàn giao hồ sơ')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách biên bản bàn giao hồ sơ</h4>
                        </div>
                        <div class="col-3">
                            @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức')
                                <a href="{{ route('ban-giao.them-ban-giao-ho-so')}}"><button class="btn btn-success float-end">Thêm biên bản</button></a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="container-fluid card-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" >
                            <thead>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Ngày tạo
                                </th>
                                <th>
                                    Tên công chức
                                </th>
                                <th>
                                    Từ ngày
                                </th>
                                <th>
                                    Đến ngày
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $bienBan)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('ban-giao.thong-tin-ban-giao-ho-so', $bienBan->ma_ban_giao) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bienBan->ngay_tao)->format('d-m-Y') }}</td>
                                        <td>{{ $bienBan->congChuc->ten_cong_chuc }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bienBan->tu_ngay)->format('d-m-Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bienBan->den_ngay)->format('d-m-Y') }}</td>
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
            });
        
            $('.dataTables_filter input[type="search"]').css({
                width: '350px',
                display: 'inline-block',
                height: '40px',
            });
        });
    </script>
@stop
