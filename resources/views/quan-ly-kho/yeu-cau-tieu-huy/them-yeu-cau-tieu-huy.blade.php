@extends('layout.user-layout')

@section('title', 'Thêm yêu cầu tiêu hủy hàng')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-yeu-cau-tieu-huy">
                <p>
                    < Quay lại quản lý yêu cầu tiêu hủy hàng</p>
            </a>
            <h2>Thêm yêu cầu tiêu hủy hàng</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <div class="form-group">
                                    <span class="mt-n2 mb-1 fs-5">Số tờ khai nhập:</span></br>
                                    <span><em class="fs-6"> (Danh sách sẽ không hiện những tờ khai nhập có yêu cầu tiêu
                                            hủy <em class="text-primary">đang chờ duyệt</em> )</em></span>
                                    <select class="form-control " id="so-to-khai-nhap-dropdown-search"
                                        name="so_to_khai_nhap">
                                        <option></option>
                                        @foreach ($toKhaiNhaps as $toKhaiNhap)
                                            <option value="{{ $toKhaiNhap->so_to_khai_nhap }}">
                                                {{ $toKhaiNhap->so_to_khai_nhap }} (Ngày đăng ký:
                                                {{ \Carbon\Carbon::parse($toKhaiNhap->ngay_dang_ky)->format('d-m-Y') }})</option>
                                            </option>
                                        @endforeach
                                    </select>
                                    <center>
                                        <button id="addRowButton" class="btn btn-primary mt-2">Chọn</button>
                                    </center>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" id="displayTableYeuCau" style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr style="vertical-align: middle; text-align: center;">
                        <th>STT</th>
                        <th>Số tờ khai</th>
                        <th>Tên hàng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <center>
                <button id="xacNhanBtn" class="btn btn-success">Nhập yêu cầu</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận thêm yêu cầu</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.them-yeu-cau-tieu-huy-submit') }}" method="POST" id="mainForm"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        Xác nhận thêm yêu cầu này?
                        <br>
                        <label class="mb-1"><strong>Chọn file đính kèm:</strong></label>
                        <br>
                        <div class="file-upload">
                            <input type="file" name="file" class="file-upload-input" id="fileInput">
                            <button type="button" class="file-upload-btn">
                                <svg class="file-upload-icon" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                Chọn File
                            </button>
                            <span class="file-name" id="fileName"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const toKhaiNhaps = @json($toKhaiNhaps);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowButton = document.getElementById('addRowButton');
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            const rowsDataInput = document.getElementById('rowsDataInput'); // Ensure this exists in your HTML form
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');

            let rowIndex = 0;

            // Add a new row
            addRowButton.addEventListener('click', function() {
                const dropdown = document.getElementById('so-to-khai-nhap-dropdown-search');
                const soToKhaiNhap = dropdown.value;

                if (soToKhaiNhap === '') {
                    alert('Vui lòng chọn số tờ khai nhập');
                    return;
                }

                // Check for duplicate entry in the table
                const isDuplicate = Array.from(tableBody.querySelectorAll('tr')).some(row => {
                    return row.querySelector('td:nth-child(2)').textContent.trim() === soToKhaiNhap;
                });

                if (isDuplicate) {
                    alert('Số tờ khai nhập đã tồn tại trong bảng!');
                    return;
                }

                rowIndex++;

                // Find the related `hangHoa` items
                const selectedToKhaiNhap = toKhaiNhaps.find(
                    item => item.so_to_khai_nhap.toString().trim() === soToKhaiNhap.toString().trim()
                );
                const hangHoas = selectedToKhaiNhap ? selectedToKhaiNhap.hang_hoa.map(h => h.ten_hang).join(
                    '<br>') : 'Không có hàng hóa';

                // Create a new table row
                const newRow = `
                <tr data-index="${rowIndex}">
                    <td class="text-center">${rowIndex}</td>
                    <td class="text-center">${soToKhaiNhap}</td>
                    <td>${hangHoas}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                    </td>
                </tr>
            `;
                tableBody.insertAdjacentHTML('beforeend', newRow);

                // Clear the dropdown selection
                dropdown.value = '';
            });

            // Delete a row
            tableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('deleteRowButton')) {
                    const row = e.target.closest('tr');
                    row.remove();

                    // Reorder the STT column after deletion
                    Array.from(tableBody.querySelectorAll('tr')).forEach((tr, index) => {
                        tr.querySelector('td:first-child').textContent = index + 1;
                    });

                    rowIndex--;
                }
            });

            nhapYeuCauButton.addEventListener('click', function() {
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const rowsData = rows.map(row => {
                    return {
                        stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                        so_to_khai_nhap: row.querySelector('td:nth-child(2)').textContent.trim()
                    };
                });
                const rowCount = $('#displayTableYeuCau tbody tr').length;
                if (rowCount === 0) {
                    alert('Vui lòng thêm ít nhất một hàng thông tin');
                    return false;
                }
                rowsDataInput.value = JSON.stringify(rowsData);
                $('#xacNhanModal').modal('show');

            });
        });
    </script>
    {{-- Submit table --}}
    <script>
        function updateRowsData() {
            const rows = $('#displayTableYeuCau tbody tr').map(function() {
                const cells = $(this).find('td');
                return {
                    so_to_khai_nhap: $(this).find('td').eq(1).text(),
                    ten_hang_hoa: $(this).find('td').eq(3).text(),
                };
            }).get();

            $('#rowsDataInput').val(JSON.stringify(rows));
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#chonHangTheoToKhaiModal').on('shown.bs.modal', function() {
                $('#container-dropdown-search').select2('destroy');
                $('#container-dropdown-search').select2({
                    placeholder: "Chọn container",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonHangTheoToKhaiModal .modal-body'),
                });
            });
        });
    </script>
    <script>
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const fileUpload = document.querySelector('.file-upload');
        document.getElementById("fileInput").addEventListener("change", function() {
            let file = this.files[0]; // Get the selected file

            if (file && file.size > 5 * 1024 * 1024) { // 5MB = 5 * 1024 * 1024 bytes
                alert("File quá lớn! Vui lòng chọn tệp dưới 5MB.");
                this.value = ""; // Clear the file input
            } else {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    fileUpload.classList.add('file-selected');
                } else {
                    fileName.textContent = '';
                    fileUpload.classList.remove('file-selected');
                }
            }
        });
    </script>
@stop
