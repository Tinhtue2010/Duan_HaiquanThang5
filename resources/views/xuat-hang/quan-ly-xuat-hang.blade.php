@extends('layout.user-layout')

@section('title', 'Quản lý xuất hàng')

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
                            <h4 class="font-weight-bold text-primary">Danh sách phiếu xuất</h4>
                        </div>
                        <div class="col-6">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <a href="/them-to-khai-xuat"><button class="btn btn-success float-end">Nhập phiếu
                                        xuất</button></a>
                            @elseif(Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_chi_xem == 0)
                                <a href="/duyet-nhanh-phieu-xuat"><button class="btn btn-success float-end">Duyệt
                                        nhanh</button></a>
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
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($xuatHangs as $index => $xuatHang)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('xuat-hang.thong-tin-xuat-hang', $xuatHang->so_to_khai_xuat) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $xuatHang->so_to_khai_xuat }}</td>
                                        <td>{{ $xuatHang->ma_loai_hinh }}</td>
                                        <td>{{ $xuatHang->ten_doanh_nghiep ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        <td>{{ $xuatHang->tong_so_luong }}</td>
                                        <td>{{ $xuatHang->ten_phuong_tien_vt }}</td>
                                        @if ($xuatHang->trang_thai == 1)
                                            <td class="text-primary">Đang chờ duyệt</td>
                                        @elseif($xuatHang->trang_thai == 3)
                                            <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã thực xuất hàng</td>
                                        @elseif($xuatHang->trang_thai == 4)
                                            <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã duyệt</td>
                                        @elseif($xuatHang->trang_thai == 5)
                                            <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC</td>
                                        @elseif($xuatHang->trang_thai == 6)
                                            <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng</td>
                                        @elseif($xuatHang->trang_thai == 7)
                                            <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã thực xuất hàng</td>
                                        @elseif($xuatHang->trang_thai == 8)
                                            <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã duyệt</td>
                                        @elseif($xuatHang->trang_thai == 9)
                                            <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC</td>
                                        @elseif($xuatHang->trang_thai == 10)
                                            <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng</td>
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
                    select.append(
                        '<option class="text-primary" value="ĐANG CHỜ DUYỆT">ĐANG CHỜ DUYỆT</option>'
                    );
                    select.append(
                        '<option class="text-warning" value="DOANH NGHIỆP YÊU CẦU SỬA PHIẾU">DOANH NGHIỆP YÊU CẦU SỬA PHIẾU</option>'
                    );
                    select.append(
                        '<option class="text-danger" value="DOANH NGHIỆP YÊU CẦU HỦY PHIẾU">DOANH NGHIỆP YÊU CẦU HỦY PHIẾU</option>'
                    );

                    var header = $(column.header());
                    header.append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('xuatHang1', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('xuatHang1');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                },
                dom: '<"clear"><"row"<"col"l><"col"f>>rt<"row"<"col"i><"col"p>><"row"<"col"B>>',
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
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
