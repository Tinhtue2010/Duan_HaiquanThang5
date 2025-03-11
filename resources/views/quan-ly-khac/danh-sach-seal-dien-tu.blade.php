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
                            <h4 class="font-weight-bold text-primary">Danh sách seal điện tử</h4>
                        </div>
                        <div class="col-3">
                            <button data-bs-toggle="modal" data-bs-target="#themModal"
                                class="btn btn-success float-end">Thêm seal điện tử</button>
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
                                    Thao tác
                                </th>
                            </thead>
                            <tbody>
                                @foreach ($seals as $index => $seal)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $seal->so_seal }}</td>
                                        @if ($seal->loai_seal == 5)
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
                                        <td><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#xoaModal"
                                            data-so-seal="{{ $seal->so_seal }}" data-loai-seal="{{ $seal->loai_seal }}">
                                            Xóa
                                        </button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Thêm Modal -->
    <div class="modal fade" id="themModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm seal điện tử</h5>
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
                                    <option value="5">Seal định vị điện tử</option>
                                </select>
                                <label class="label-text mt-3" for=""><strong>Cán bộ công chức phụ
                                        trách</strong></label>
                                <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc" required>
                                    <option></option>
                                    @foreach ($congChucs as $congChuc)
                                        <option value="{{ $congChuc->ma_cong_chuc }}">
                                            {{ $congChuc->ten_cong_chuc }}
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-danger[data-bs-toggle="modal"]');
            const modalSoSeal = document.getElementById('modalSoSeal');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const soSeal = this.getAttribute('data-so-seal');
                    const loaiSeal = this.getAttribute('data-loai-seal');
                    modalSoSeal.textContent = soSeal;
                    modalLoaiSeal.textContent = loaiSeal;
                    modalInputSoSeal.value = soSeal;
                });
            });
        });
    </script>
@stop
