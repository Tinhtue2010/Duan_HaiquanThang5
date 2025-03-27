@extends('layout.user-layout')

@section('title', 'Quản lý yêu cầu gia hạn')

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
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách yêu cầu gia hạn tờ khai G21</h4>
                        </div>
                        <div class="col-3">
                            @if (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <a href="{{ route('quan-ly-kho.them-yeu-cau-gia-han') }}"><button
                                        class="btn btn-success float-end">Nhập yêu cầu</button></a>
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
                                    Số tờ khai
                                </th>
                                <th>
                                    Doanh nghiệp
                                </th>
                                <th>
                                    Ngày yêu cầu
                                </th>
                                <th>
                                    Trạng thái
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($data as $index => $yeuCau)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('quan-ly-kho.thong-tin-yeu-cau-gia-han', $yeuCau->ma_yeu_cau) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $yeuCau->ma_yeu_cau }}</td>
                                        <td>{{ $yeuCau->so_to_khai_nhap_list }}</td>
                                        <td>{{ $yeuCau->ten_doanh_nghiep }}</td>
                                        <td>{{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</td>
                                        @if ($yeuCau->trang_thai == 1)
                                            <td class="text-primary">Đang chờ duyệt</td>
                                        @elseif ($yeuCau->trang_thai == 2)
                                            <td class="text-success">Đã duyệt</td>
                                        @elseif ($yeuCau->trang_thai == 3)
                                            <td class="text-warning">Doanh nghiệp đề nghị sửa yêu cầu</td>
                                        @elseif ($yeuCau->trang_thai == 4)
                                            <td class="text-danger">Doanh nghiệp đề nghị hủy yêu cầu</td>
                                        @elseif ($yeuCau->trang_thai == 0)
                                        <td class="text-danger">Đã hủy</td>
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
                        width: "360px",
                        targets: 2
                    },
                    {
                        orderable: false,
                        targets: -1
                    }
                ],
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
                    select.append('<option class="text-danger" value="ĐÃ HỦY">ĐÃ HỦY</option>');

                    $(column.header()).empty().append(select);

                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('giaHan', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('giaHan');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    } else {
                        select.val("");
                    }

                },
            });
        });
    </script>
@stop
