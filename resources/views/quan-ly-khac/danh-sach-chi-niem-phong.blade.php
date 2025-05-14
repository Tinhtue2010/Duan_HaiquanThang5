@extends('layout.user-layout')

@section('title', 'Danh sách doanh nghiệp')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-4">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif (session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Danh sách seal niêm phong</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm seal niêm phong</button>
                            <a href="{{ route('quan-ly-khac.xoa-nhanh-chi-niem-phong') }}"><button
                                    class="btn btn-danger float-end mx-1">Xóa nhanh</button></a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Số seal
                                </th>
                                <th>
                                    Loại seal
                                </th>
                                <th>
                                    Công chức phụ trách
                                </th>
                                <th>
                                    Ngày cấp
                                </th>
                                <th>
                                    Ngày sử dụng
                                </th>
                                <th>
                                    Số container
                                </th>
                                <th>

                                </th>
                                <th>
                                    Thao tác
                                </th>
                            </thead>
                            <tbody>
                                {{-- @foreach ($seals as $index => $seal)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $seal->so_seal }}</td>
                                        @if ($seal->loai_seal == 1)
                                            <td>Seal dây cáp đồng</td>
                                        @elseif($seal->loai_seal == 2)
                                            <td>Seal dây cáp thép</td>
                                        @elseif($seal->loai_seal == 3)
                                            <td>Seal container</td>
                                        @elseif($seal->loai_seal == 4)
                                            <td>Seal dây nhựa dẹt</td>
                                        @elseif($seal->loai_seal == 5)
                                            <td>Seal định vị điện tử</td>
                                        @endif
                                        <td>{{ $seal->congChuc->ten_cong_chuc ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($seal->ngay_cap)->format('d-m-Y') }}</td>
                                        @if ($seal->trang_thai == 0)
                                            <td></td>
                                        @else
                                            <td>{{ \Carbon\Carbon::parse($seal->ngay_su_dung)->format('d-m-Y') }}</td>
                                        @endif
                                        <td>{{ $seal->so_container }}</td>
                                        @if ($seal->trang_thai == 0)
                                            <td>Chưa sử dụng</td>
                                        @elseif($seal->trang_thai == 1)
                                            <td>Đã sử dụng</td>
                                        @elseif($seal->trang_thai == 2)
                                            <td>Seal hỏng</td>
                                        @endif
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                                data-so-seal="{{ $seal->so_seal }}" data-loai-seal="{{ $seal->loai_seal }}">
                                                Xóa
                                            </button></td>
                                    </tr>
                                @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Xóa Modal -->
    <div class="modal fade" id="xoaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-danger">Xác nhận xóa seal này</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.xoa-seal') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div>
                            <label><strong>Số seal:</strong></label>
                            <p class="d-inline" id="modalSoSeal"></p>

                        </div>
                        <div>
                            <label><strong>Loại seal:</strong></label>
                            <p class="d-inline" id="modalLoaiSeal"></p>

                        </div>
                        <input type="hidden" name="so_seal" id="modalInputSoSeal">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Thêm Modal -->
    <div class="modal fade" id="themModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm seal niêm phong</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-khac.them-chi-niem-phong') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="row">
                            <div>
                                <label for=""><strong>Số ký hiệu </strong></label>
                                <input type="text" class="form-control" id="tiep_ngu" name="tiep_ngu" max="50"
                                    placeholder="Nhập số ký hiệu" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label for=""><strong>Số bắt đầu </strong></label>
                                <input type="number" class="form-control" id="moc_dau" name="moc_dau"
                                    placeholder="Nhập số bắt đầu" step="1" min="0" required>
                            </div>
                            <div class="col-6">
                                <label for=""><strong>Mốc cuối </strong></label>
                                <input type="number" class="form-control" id="moc_cuoi" name="moc_cuoi"
                                    placeholder="Nhập mốc cuối" step="1" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label for="loai_seal"><strong>Loại seal</strong></label>
                                <select class="form-control" name="loai_seal" placeholder="Chọn loại seal" required>
                                    <option></option>
                                    <option value="1">Seal dây cáp đồng</option>
                                    <option value="2">Seal dây cáp thép</option>
                                    <option value="3">Seal container</option>
                                    <option value="4">Seal dây nhựa dẹt</option>
                                </select>
                                <label class="label-text mt-3" for=""><strong>Cán bộ công chức phụ
                                        trách</strong></label>
                                <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
                                    <option></option>
                                    @foreach ($congChucs as $congChuc)
                                        <option value="{{ $congChuc->ma_cong_chuc }}">
                                            {{ $congChuc->ten_cong_chuc }} ({{ $congChuc->taiKhoan->ten_dang_nhap ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Thêm mới</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#cong-chuc-dropdown-search').select2();
            // Reinitialize Select2 when modal opens
            $('#themModal').on('shown.bs.modal', function() {
                $('#cong-chuc-dropdown-search').select2('destroy');
                $('#cong-chuc-dropdown-search').select2({
                    placeholder: "Chọn cán bộ công chức",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#themModal .modal-body'),
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('quan-ly-khac.getChiNiemPhong') }}",

                language: {
                    searchPlaceholder: "Tìm kiếm",
                    search: "",
                    sInfo: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    sInfoEmpty: "Hiển thị 0 đến 0 của 0 mục",
                    sInfoFiltered: "Lọc từ _MAX_ mục",
                    sLengthMenu: "Hiện _MENU_ mục",
                    sEmptyTable: "Không có dữ liệu",
                },
                dom: '<"clear"><"row"<"col"l><"col"f>>rt<"row"<"col"i><"col"p>><"row"<"col"B>>',
                buttons: [{
                        extend: 'excel',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        title: ''
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        title: ''
                    }
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'so_seal',
                        name: 'so_seal'
                    },
                    {
                        data: 'loai_seal',
                        name: 'loai_seal'
                    },
                    {
                        data: 'ten_cong_chuc',
                        name: 'ten_cong_chuc'
                    },
                    {
                        data: 'ngay_cap',
                        name: 'ngay_cap',
                    },
                    {
                        data: 'ngay_su_dung',
                        name: 'ngay_su_dung',
                    },
                    {
                        data: 'so_container',
                        name: 'so_container',
                    },
                    {
                        data: 'trang_thai',
                        name: 'trang_thai',
                        orderable: false
                    },
                    {
                        data: 'thao_tac',
                        name: 'thao_tac',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return data; // ✅ Ensure raw HTML is returned
                        }
                    }
                ],
                columnDefs: [{
                        width: "150px",
                        targets: -1
                    },
                    {
                        width: "230px",
                        targets: 3
                    }
                ],
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
                        '<option class="text-success" value="0">CHƯA SỬ DỤNG</option>');
                    select.append(
                        '<option class="text-success" value="1">ĐÃ SỬ DỤNG</option>');
                    select.append(
                        '<option class="text-success" value="2">SEAL HỎNG</option>'
                    );
                    var header = $(column.header());
                    header.append(select);
                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val().trim());
                        localStorage.setItem('qlSeal', val);
                        column.search(val ? val : '', false, true).draw();
                    });

                    var savedFilter = localStorage.getItem('qlSeal');
                    if (savedFilter) {
                        select.val(savedFilter);
                        column.search(savedFilter ? savedFilter : '', false, true).draw();
                    }

                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('dataTable'); // Use a static parent

            table.addEventListener('click', function(event) {
                if (event.target.classList.contains('btn-danger')) {
                    const button = event.target;
                    const soSeal = button.getAttribute('data-so-seal');
                    const loaiSeal = button.getAttribute('data-loai-seal');

                    // Ensure these elements exist
                    const modalSoSeal = document.getElementById('modalSoSeal');
                    const modalLoaiSeal = document.getElementById('modalLoaiSeal');
                    const modalInputSoSeal = document.getElementById('modalInputSoSeal');

                    if (modalSoSeal && modalLoaiSeal && modalInputSoSeal) {
                        modalSoSeal.textContent = soSeal;
                        modalLoaiSeal.textContent = loaiSeal;
                        modalInputSoSeal.value = soSeal;
                    }
                }
            });
        });
    </script>
@stop
