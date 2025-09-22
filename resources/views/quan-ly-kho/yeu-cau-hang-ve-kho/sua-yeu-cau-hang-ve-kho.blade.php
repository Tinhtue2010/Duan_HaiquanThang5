@extends('layout.user-layout')

@section('title', 'Sửa yêu cầu đưa hàng trở lại kho ban đầu')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-yeu-cau-hang-ve-kho">
                <p>
                    < Quay lại quản lý yêu cầu đưa hàng trở lại kho ban đầu</p>
            </a>
            <h2>Sửa yêu cầu đưa hàng trở lại kho ban đầu</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="fs-5 fw-bold fs-6">Tên phương tiện vận tải:</label>
                                        <input class="form-control mt-2" id="ten_phuong_tien_vt" maxlength="50"
                                            name="ten_phuong_tien_vt" placeholder="Nhập tên phương tiện vận tải" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="mt-2 fw-bold fs-6">Số tờ khai nhập:</label>
                                        <select class="form-control mt-2" id="so-to-khai-nhap-dropdown-search"
                                            name="so_to_khai_nhap">
                                            <option></option>
                                            @foreach ($toKhaiNhaps as $toKhaiNhap)
                                                <option value="{{ $toKhaiNhap->so_to_khai_nhap }}">
                                                    {{ $toKhaiNhap->so_to_khai_nhap }} (Ngày đăng ký:
                                                    {{ \Carbon\Carbon::parse($toKhaiNhap->ngay_dang_ky)->format('d-m-Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="label-text mb-1 fw-bold fs-6" for="ma_hai_quan">Hải quan cửa khẩu nơi
                                            hàng hóa
                                            chuyển đến (Quay về kho)</label>
                                        <select class="form-control" id="hai-quan-dropdown-search" name="ma_hai_quan">
                                            @foreach ($haiQuans as $haiQuan)
                                                <option></option>
                                                <option value="{{ $haiQuan->ma_hai_quan }}">
                                                    {{ $haiQuan->ten_hai_quan }}
                                                    ({{ $haiQuan->ma_hai_quan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-text fw-bold fs-6" for="">Số tờ khai mới</label> <span
                                            class="text-danger missing-input-text"></span>
                                        <input type="text" class="form-control mt-4" id="so_to_khai_moi" maxlength="15"
                                            name="so_to_khai_moi" placeholder="Nhập số tờ khai mới" required>
                                    </div>
                                </div>
                                <center>
                                    <button id="addRowButton" class="btn btn-primary mt-2">Chọn</button>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" id="displayTableYeuCau" style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Số tờ khai</th>
                        <th>Số container</th>
                        <th>Tên hàng</th>
                        <th>Phương tiện vận tải</th>
                        <th style="display: none;">Mã PTVT</th>
                        <th>Hải quan chuyển đến</th>
                        <th style="display: none;">Mã HQ</th>
                        <th>Số tờ khai mới</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <center>
                <button id="xacNhanBtn" class="btn btn-success">Sửa yêu cầu</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận sửa yêu cầu</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.sua-yeu-cau-hang-ve-kho-submit') }}" method="POST" id="mainForm"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <label class="fs-5">Xác nhận sửa yêu cầu này?</label>
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
                            <span class="file-name" id="fileName">{{ $yeuCau->file_name ? $yeuCau->file_name : '' }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <button type="submit" class="btn btn-success">Sửa yêu cầu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const toKhaiNhaps = @json($toKhaiNhaps);
        const chiTiets = @json($chiTiets);
    </script>
    <script>
        const addRowButton = document.getElementById('addRowButton');
        const tableBody = document.querySelector('#displayTableYeuCau tbody');
        const rowsDataInput = document.getElementById('rowsDataInput'); // Ensure this exists in your HTML form
        const nhapYeuCauButton = document.getElementById('xacNhanBtn');
        let rowIndex = 0;

        document.addEventListener('DOMContentLoaded', function() {

            chiTiets.forEach(chiTiet => {
                rowIndex++;

                const newRow = `
                    <tr data-index="${rowIndex}">
                        <td class="text-center">${rowIndex}</td>
                        <td class="text-center">${chiTiet.so_to_khai_nhap}</td>
                        <td>${chiTiet.so_container}</td>
                        <td>${chiTiet.ten_hang}</td>
                        <td class="text-center">${chiTiet.ten_phuong_tien_vt}</td>
                        <td class="text-center">${chiTiet.ten_hai_quan}</td>
                        <td class="text-center" hidden>${chiTiet.ma_hai_quan}</td>
                        <td class="text-center">${chiTiet.so_to_khai_moi}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `;

                tableBody.insertAdjacentHTML('beforeend', newRow);
            });

        });
        // Add a new row
        addRowButton.addEventListener('click', function() {
            const soToKhaiDropdown = document.getElementById('so-to-khai-nhap-dropdown-search');
            const haiQuanDropdown = document.getElementById('hai-quan-dropdown-search');
            const ptvt_xc = document.getElementById('ten_phuong_tien_vt');
            const soToKhaiMoi = document.getElementById('so_to_khai_moi').value;
            const maHaiQuan = document.getElementById('hai-quan-dropdown-search').value;
            const selectedOption = haiQuanDropdown.options[haiQuanDropdown.selectedIndex];
            const tenHaiQuan = selectedOption ? selectedOption.textContent.trim() : '';

            const soToKhaiNhap = soToKhaiDropdown.value;
            const PTVTXC_Value = ptvt_xc.value;

            if (soToKhaiNhap === '') {
                alert('Vui lòng chọn số tờ khai nhập');
                return;
            } else if (!PTVTXC_Value) {
                alert("Xin hãy chọn phương tiện vận tải xuất cảnh");
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
            getYeuCauTableData();

            function getYeuCauTableData() {
                let rowsData = $("#rowsDataInput").val();
                $.ajax({
                    url: "/get-hang-trong-to-khai", // Laravel route
                    type: "GET",
                    contentType: "application/json",
                    data: {
                        so_to_khai_nhap: soToKhaiNhap
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // For Laravel CSRF protection
                    },
                    success: function(response) {
                        updateTable(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                    }
                });
            }

            function updateTable(data) {
                let tableBody = $("#displayTableYeuCau tbody");
                data.forEach((item, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${soToKhaiNhap}</td>
                            <td>${item.so_container}</td>
                            <td>${item.hang_hoa}</td>
                            <td>${PTVTXC_Value}</td>
                            <td>${tenHaiQuan}</td>
                            <td hidden>${maHaiQuan}</td>
                            <td>${soToKhaiMoi}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            }
        });

        nhapYeuCauButton.addEventListener('click', function() {
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            const rowsData = rows.map(row => {
                return {
                    stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                    so_to_khai_nhap: row.querySelector('td:nth-child(2)').textContent.trim(),
                    so_container: row.querySelector('td:nth-child(3)').textContent.trim(),
                    ten_phuong_tien_vt: row.querySelector('td:nth-child(5)').textContent.trim(),
                    ma_hai_quan: row.querySelector('td:nth-child(7)').textContent.trim(),
                    so_to_khai_moi: row.querySelector('td:nth-child(8)').textContent.trim()

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
        tableBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('deleteRowButton')) {
                const row = e.target.closest('tr');
                if (!row) return;
                row.remove();

                // Get the text from the second column of the clicked row
                // const soToKhaiNhapValue = row.children[1].textContent.trim();

                // Remove all rows with the same second column value
                // Array.from(tableBody.querySelectorAll('tr')).forEach((tr) => {
                //     if (tr.children[1].textContent.trim() === soToKhaiNhapValue) {
                //         tr.remove();
                //     }
                // });

                // Reorder the STT column after deletion
                Array.from(tableBody.querySelectorAll('tr')).forEach((tr, index) => {
                    tr.querySelector('td:first-child').textContent = index + 1;
                });
            }
        });
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
