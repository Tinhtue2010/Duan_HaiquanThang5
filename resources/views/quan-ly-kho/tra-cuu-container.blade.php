@extends('layout.user-layout')

@section('title', 'Tra cứu container')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-4">
            <div class="row justify-content-center">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-danger') }}</strong>
                    </div>
                @endif
                <div class="col-6 card p-3">
                    <h2 class="text-center">Tra cứu container</h2>
                    {{-- <a
                        href="{{ route('export.bao-cao-tra-cuu-container') }}">
                        <button class="btn btn-success float-end me-1">In báo cáo</button>
                    </a> --}}
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Số container
                                </th>
                                <th>
                                    Tàu
                                </th>
                                <th>
                                    Số seal niêm phong
                                </th>
                                <th>
                                    Số lượng
                                </th>
                            </thead>
                            <tbody class="clickable-row">
                                @foreach ($containers as $key => $container)
                                    @if (!is_null($container->so_container) && $container->so_container != '' && $container->total_so_luong > 0)
                                        <tr
                                            onclick="window.location='{{ route('quan-ly-kho.to-khai-trong-container', ['so_container' => $container->so_container]) }}'">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $container->so_container }}</td>
                                            <td>{{ $container->phuong_tien_vt_nhap ?? '' }}</td>
                                            <td>{{ $container->so_seal ?? '' }}</td>
                                            <td>{{ $container->total_so_luong ?? 0 }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="themContainerModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Thêm container mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.them-container') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <label class="label-text" for="so_luong_khai_bao">Số container</label> <span
                            class="text-danger missing-input-text"></span>
                        <input type="text" class="form-control mt-2 reset-input" id="so_container" name="so_container"
                            placeholder="Nhập số container" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success""> Thêm mới </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            const containerGoc = document.getElementById('container-goc-dropdown-search').value;
            const containerDich = document.getElementById('container-dich-dropdown-search').value;

            if (!containerGoc || !containerDich) {
                alert('Vui lòng chọn cả hai container');
                return false;
            }

            if (containerGoc === containerDich) {
                alert('Vui lòng chọn hai container khác nhau');
                return false;
            }

            return true;
        }

        function validateFormDon() {
            const containerGoc = document.getElementById('don-container-goc-dropdown-search').value;
            const containerDich = document.getElementById('don-container-dich-dropdown-search').value;

            if (!containerGoc || !containerDich) {
                alert('Vui lòng chọn cả hai container');
                return false;
            }

            if (containerGoc === containerDich) {
                alert('Vui lòng chọn hai container khác nhau');
                return false;
            }

            return true;
        }
        $(document).ready(function() {
            $('#container-goc-dropdown-search').select2({
                placeholder: "Chọn container gốc",
                allowClear: true,
                language: "vi", // Set the language (if needed)
                minimumInputLength: 0,
                dropdownAutoWidth: true, // Automatically adjust the dropdown width
                dropdownParent: $(document.body), // Ensure dropdown appears under the input field
                ajax: {
                    dataType: 'json',
                    delay: 250, // Delay for AJAX search
                    processResults: function(data) {
                        return {
                            results: data.items
                        };
                    }
                },
            });
            $('#container-dich-dropdown-search').select2({
                placeholder: "Chọn container đích",
                allowClear: true,
                language: "vi", // Set the language (if needed)
                minimumInputLength: 0,
                dropdownAutoWidth: true, // Automatically adjust the dropdown width
                dropdownParent: $(document.body), // Ensure dropdown appears under the input field
                ajax: {
                    dataType: 'json',
                    delay: 250, // Delay for AJAX search
                    processResults: function(data) {
                        return {
                            results: data.items
                        };
                    }
                },
            });
            $('#don-container-goc-dropdown-search').select2({
                placeholder: "Chọn container gốc",
                allowClear: true,
                language: "vi", // Set the language (if needed)
                minimumInputLength: 0,
                dropdownAutoWidth: true, // Automatically adjust the dropdown width
                dropdownParent: $(document.body), // Ensure dropdown appears under the input field
                ajax: {
                    dataType: 'json',
                    delay: 250, // Delay for AJAX search
                    processResults: function(data) {
                        return {
                            results: data.items
                        };
                    }
                },
            });
            $('#don-container-dich-dropdown-search').select2({
                placeholder: "Chọn container đích",
                allowClear: true,
                language: "vi", // Set the language (if needed)
                minimumInputLength: 0,
                dropdownAutoWidth: true, // Automatically adjust the dropdown width
                dropdownParent: $(document.body), // Ensure dropdown appears under the input field
                ajax: {
                    dataType: 'json',
                    delay: 250, // Delay for AJAX search
                    processResults: function(data) {
                        return {
                            results: data.items
                        };
                    }
                },
            });
        });

        $(document).ready(function() {
            $('#don-container-goc-dropdown-search').select2({
                placeholder: "Chọn container gốc",
                allowClear: true,
            });
            $('#don-container-dich-dropdown-search').select2({
                placeholder: "Chọn container đích",
                allowClear: true,
            });
            $('#container-goc-dropdown-search').select2({
                placeholder: "Chọn container gốc",
                allowClear: true,
            });
            $('#container-dich-dropdown-search').select2({
                placeholder: "Chọn container đích",
                allowClear: true,
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                processing: true,
                stateSave: true,
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
            });
        });
    </script>

@stop
