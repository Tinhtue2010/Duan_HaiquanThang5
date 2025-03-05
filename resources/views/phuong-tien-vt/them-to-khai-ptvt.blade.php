@extends('layout.user-layout')

@section('title', 'Thêm tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-to-khai-ptvt">
                <p>
                    < Quay lại quản lý tờ khai xếp hàng lên phương tiện vận tải</p>
            </a>
            <h2>TỜ KHAI XẾP HÀNG LÊN PHƯƠNG TIỆN VẬN TẢI</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="so_to_khai_xuat" class="mb-1">Thêm phiếu xuất:</label>
                                    <select class="form-control" id="so-to-khai-nhap-dropdown-search" name="so_to_khai_xuat"
                                        id="so_to_khai_xuat">
                                        <option></option>
                                        @foreach ($toKhaiXuats as $toKhaiXuat)
                                            <option value="{{ $toKhaiXuat->so_to_khai_xuat }}"
                                                data-ten-loai-hinh="{{ $toKhaiXuat->ten_loai_hinh }}"
                                                data-ma-loai-hinh="{{ $toKhaiXuat->ma_loai_hinh }}"
                                                data-ngay-dang-ky="{{ $toKhaiXuat->ngay_dang_ky }}"
                                                data-lan-xuat="{{ $toKhaiXuat->lan_xuat_canh }}"
                                                data-so-to-khai-nhap="{{ $toKhaiXuat->so_to_khai_nhap }}"
                                                data-so-to-khai-xuat="{{ $toKhaiXuat->so_to_khai_xuat }}"
                                                data-tong-so-luong="{{ $toKhaiXuat->tong_so_luong }}"
                                                data-ten-doanh-nghiep="{{ $toKhaiXuat->nhapHang->doanhNghiep->ten_doanh_nghiep ?? '' }}">
                                                Số: {{ $toKhaiXuat->so_to_khai_xuat }}
                                                - Tờ khai nhập {{ $toKhaiXuat->so_to_khai_nhap }}
                                                - Lần xuất {{ $toKhaiXuat->lan_xuat_canh }}
                                                - Ngày {{ $toKhaiXuat->ngay_dang_ky }}
                                                - Doanh nghiệp:
                                                {{ $toKhaiXuat->nhapHang->doanhNghiep->ten_doanh_nghiep ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <center>
                                        <button class="btn btn-primary mt-2" type="button" id="selectButton">Thêm</button>
                                    </center>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="so_ptvt_xuat_canh" class="mb-1">Tên phương tiện vận tải</label>
                                    <select class="form-control" id="ptvt-xc-dropdown-search" name="so_ptvt_xuat_canh"
                                        id="so_ptvt_xuat_canh">
                                        <option></option>
                                        @foreach ($ptvtXuatCanhs as $ptvtXuatCanh)
                                            <option value="{{ $ptvtXuatCanh->so_ptvt_xuat_canh }}">
                                                {{ $ptvtXuatCanh->ten_phuong_tien_vt }} Số:
                                                {{ $ptvtXuatCanh->so_ptvt_xuat_canh }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <table id="dataTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Số Phiếu Xuất</th>
                                        <th>Số Tờ Khai Nhập</th>
                                        <th>Lần Xuất</th>
                                        <th>Tên Loại Hình</th>
                                        <th>Mã Loại Hình</th>
                                        <th>Ngày Đăng Ký</th>
                                        <th>Số lượng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- New rows will be appended here -->
                                </tbody>
                            </table>

                        </div>
                        <center>
                            <button class="btn btn-success mb-3" type="button" id="setButton">Thêm tờ khai</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm tờ khai này ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm tờ khai này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('phuong-tien-vt.them-to-khai-ptvt-submit') }}" method="POST">
                        @csrf
                        @method('POST')
                        <div id="hiddenInputs"></div>
                        <div id="hiddenInputs2"></div>
                        <input type="hidden" id="so_ptvt_xuat_canh" name="so_ptvt_xuat_canh">
                        <button type="submit" class="btn btn-success">Nhập tờ khai</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('selectButton').addEventListener('click', function() {
            const dropdown = document.getElementById('so-to-khai-nhap-dropdown-search');
            const selectedOption = dropdown.options[dropdown.selectedIndex];

            // Get data attributes from the selected option
            const soToKhaiXuat = selectedOption.value;
            const tenLoaiHinh = selectedOption.getAttribute('data-ten-loai-hinh');
            const maLoaiHinh = selectedOption.getAttribute('data-ma-loai-hinh');
            const ngayDangKy = selectedOption.getAttribute('data-ngay-dang-ky');
            const lanXuat = selectedOption.getAttribute('data-lan-xuat');
            const soToKhaiNhap = selectedOption.getAttribute('data-so-to-khai-nhap');
            const so_luong = selectedOption.getAttribute('data-tong-so-luong');
            const ten_doanh_nghiep = selectedOption.getAttribute('data-ten-doanh-nghiep');

            // Check if a valid option is selected
            if (!soToKhaiXuat) {
                alert('Please select a valid option.');
                return;
            }

            // Create a new row
            const table = document.getElementById('dataTable').querySelector('tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>${soToKhaiXuat}</td>
                <td>${soToKhaiNhap}</td>
                <td>${lanXuat}</td>
                <td>${tenLoaiHinh}</td>
                <td>${maLoaiHinh}</td>
                <td>${ngayDangKy}</td>
                <td>${so_luong}</td>
                <td>
                    <button class="btn btn-danger btn-sm delete-row">Delete</button>
                </td>
            `;

            // Append the new row to the table
            table.appendChild(newRow);

            // Add hidden input for this `soToKhaiNhap`
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'so_to_khai_xuat[]';
            hiddenInput.value = soToKhaiXuat;

            const hiddenInput2 = document.createElement('input');
            hiddenInput2.type = 'hidden';
            hiddenInput2.name = 'so_to_khai_nhap[]';
            hiddenInput2.value = soToKhaiNhap;
            // Append the hidden input to the form
            document.getElementById('hiddenInputs').appendChild(hiddenInput);
            document.getElementById('hiddenInputs2').appendChild(hiddenInput2);

            // Remove the selected option from the dropdown
            dropdown.removeChild(selectedOption);

            // Add delete row functionality
            newRow.querySelector('.delete-row').addEventListener('click', function() {
                // Remove the hidden input for this deleted row
                const hiddenInputs = document.querySelectorAll('input[name="so_to_khai_xuat[]"]');
                hiddenInputs.forEach(function(input) {
                    if (input.value === soToKhaiXuat) {
                        input.remove();
                    }
                });
                const hiddenInputs2 = document.querySelectorAll('input[name="so_to_khai_nhap[]"]');
                hiddenInputs2.forEach(function(input) {
                    if (input.value === soToKhaiNhap) {
                        input.remove();
                    }
                });

                // Add the option back to the dropdown
                const newOption = document.createElement('option');
                newOption.value = soToKhaiXuat;
                newOption.setAttribute('data-ten-loai-hinh', tenLoaiHinh);
                newOption.setAttribute('data-ma-loai-hinh', maLoaiHinh);
                newOption.setAttribute('data-ngay-dang-ky', ngayDangKy);
                newOption.setAttribute('data-lan-xuat', lanXuat);
                newOption.setAttribute('data-so-to-khai-nhap', soToKhaiNhap);
                newOption.setAttribute('data-tong-so-luong', so_luong);
                newOption.setAttribute('data-ten-doanh-nghiep', ten_doanh_nghiep);
                newOption.textContent =
                    `Số: ${soToKhaiXuat} - Tờ khai nhập ${soToKhaiNhap} - Lần xuất ${lanXuat} - Ngày ${ngayDangKy} - Doanh nghiệp: ${ten_doanh_nghiep} `;
                dropdown.appendChild(newOption);

                // Remove the row from the table
                table.removeChild(newRow);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.getElementById('ptvt-xc-dropdown-search');
            const inputField = document.getElementById('so_ptvt_xuat_canh');
            const setButton = document.getElementById('setButton');
            const modalElement = document.getElementById('xacNhanModal');
            const modal = new bootstrap.Modal(modalElement);
            setButton.addEventListener('click', function(event) {
                const selectedValue = dropdown.value;
                const table = document.getElementById('dataTable').querySelector('tbody');
                const tbody = table.querySelector('tbody');
                const rows = tbody ? tbody.rows : table.rows;

                if (!selectedValue) {
                    alert("Xin hãy chọn phương tiện vận tải xuất cảnh");
                }
                else if(rows.length === 0) {
                    alert("Xin hãy thêm ít nhất 1 dòng phiếu xuất");
                }
                else {
                    inputField.value = selectedValue;
                    modal.show();
                }
            });
        });
    </script>
@stop
