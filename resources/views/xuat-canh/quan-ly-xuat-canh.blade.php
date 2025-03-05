@extends('layout.user-layout')

@section('title', 'Quản lý xuất cảnh')

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
                            <h4 class="font-weight-bold text-primary">Danh sách tờ khai xuất cảnh</h4>
                        </div>
                        <div class="col-6">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <a href="/them-to-khai-xuat-canh"><button class="btn btn-success float-end">Nhập tờ khai
                                        xuất cảnh</button></a>
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
                                    Tên phương tiện xuất cảnh
                                </th>
                                <th>
                                    Công ty
                                </th>
                                <th>
                                    Ngày đăng ký
                                </th>
                                <th>
                                    Trạng thái
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($xuatCanhs as $index => $xuatCanh)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('xuat-canh.thong-tin-xuat-canh', $xuatCanh->ma_xuat_canh) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $xuatCanh->ma_xuat_canh }}</td>
                                        <td>{{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }}</td>
                                        <td>{{ $xuatCanh->doanhNghiep->ten_doanh_nghiep ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d-m-Y') }}</td>
                                        @if (trim($xuatCanh->trang_thai) == 'Đang chờ duyệt')
                                            <td class="text-primary">{{ $xuatCanh->trang_thai }}</td>
                                        @elseif (trim($xuatCanh->trang_thai) == 'Đã duyệt'||trim($xuatCanh->trang_thai) == 'Đã duyệt thực xuất')
                                            <td class="text-success">{{ $xuatCanh->trang_thai }}</td>
                                        @elseif (trim($xuatCanh->trang_thai) == 'Doanh nghiệp xin hủy (Chờ duyệt)' ||
                                                trim($xuatCanh->trang_thai) == 'Doanh nghiệp xin hủy (Đã duyệt)')
                                            <td class="text-warning">{{ $xuatCanh->trang_thai }}</td>
                                        @elseif (trim($xuatCanh->trang_thai) == 'Chấp nhận hủy' ||
                                                trim($xuatCanh->trang_thai) == 'Từ chối hủy' ||
                                                trim($xuatCanh->trang_thai) == 'Đã hủy')
                                            <td class="text-danger">{{ $xuatCanh->trang_thai }}</td>
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
                columnDefs: [{
                    orderable: false,
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

                    select.append(
                        '<option class="text-primary" value="ĐANG CHỜ DUYỆT">ĐANG CHỜ DUYỆT</option>'
                    );
                    select.append('<option class="text-success" value="ĐÃ DUYỆT">ĐÃ DUYỆT</option>');
                    select.append('<option class="text-success" value="ĐÃ DUYỆT THỰC XUẤT">ĐÃ DUYỆT THỰC XUẤT</option>');
                    select.append(
                        '<option class="text-warning" value="DOANH NGHIỆP XIN HỦY">DOANH NGHIỆP XIN HỦY</option>'
                    );
                    select.append('<option class="text-danger" value="ĐÃ HỦY">ĐÃ HỦY</option>');
                    select.append(
                        '<option class="text-danger" value="CHẤP NHẬN HỦY">CHẤP NHẬN HỦY</option>');
                    select.append(
                        '<option class="text-danger" value="TỪ CHỐI HỦY">TỪ CHỐI HỦY</option>');

                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('xuatCanh1', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('xuatCanh1');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }else{
                        select.val("");
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
