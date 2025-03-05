@extends('layout.user-layout')

@section('title', 'Quản lý xuất hàng')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="font-weight-bold text-primary">Danh sách phiếu xuất của tờ khai nhập {{ $so_to_khai_nhap }}</h4>
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
                                    Số phiếu xuất
                                </th>
                                <th>
                                    Lần xuất
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
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($xuatHangs as $index => $xuatHang)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('tra-cuu.thong-tin-to-khai-xuat', $xuatHang->so_to_khai_xuat) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $xuatHang->so_to_khai_nhap }}</td>
                                        <td>{{ $xuatHang->so_to_khai_xuat }}</td>
                                        <td>{{ $xuatHang->lan_xuat_canh }}</td>
                                        <td>{{ $xuatHang->ma_loai_hinh }}</td>
                                        <td>{{ $xuatHang->ten_doanh_nghiep}}</td>
                                        <td>{{ $xuatHang->ngay_dang_ky }}</td>
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
                buttons: [
                    {
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
