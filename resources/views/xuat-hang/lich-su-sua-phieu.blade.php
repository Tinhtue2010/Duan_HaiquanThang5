@extends('layout.user-layout')

@section('title', 'Lịch sử sửa phiếu của tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="font-weight-bold text-primary">Danh sách lịch sử sửa phiếu xuất của tờ khai: {{ $nhapHang->so_to_khai_nhap }}</h4>
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
                                    Số tờ khai xuất
                                </th>
                                <th>
                                    Ngày tạo
                                </th>
                                <th>
                                    Trạng thái phiếu
                                </th>
                                <th>
                                    Trạng thái yêu cầu
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($suaToKhais as $index => $suaToKhai)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('xuat-hang.xem-yeu-cau-sua', [$suaToKhai->so_to_khai_xuat, $suaToKhai->ma_yeu_cau]) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $suaToKhai->so_to_khai_xuat }}</td>
                                        <td>{{ \Carbon\Carbon::parse($suaToKhai->ngay_tao)->format('d-m-Y') }}</td>
                                        @if ($suaToKhai->trang_thai_phieu_xuat == 'Đang chờ duyệt')
                                            <td class="text-primary">{{ $suaToKhai->trang_thai_phieu_xuat }}</td>
                                        @elseif($suaToKhai->trang_thai_phieu_xuat == 'Đã duyệt')
                                            <td class="text-success">{{ $suaToKhai->trang_thai_phieu_xuat }}</td>
                                        @else
                                            <td class="text-success">{{ $suaToKhai->trang_thai_phieu_xuat }}</td>
                                        @endif                                        
                                      @if ($suaToKhai->trang_thai == 'Đang chờ duyệt')
                                            <td class="text-primary">{{ $suaToKhai->trang_thai }}</td>
                                        @elseif($suaToKhai->trang_thai == 'Đã duyệt')
                                            <td class="text-success">{{ $suaToKhai->trang_thai }}</td>
                                        @else
                                            <td class="text-success">{{ $suaToKhai->trang_thai }}</td>
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
