@extends('layout.user-layout')

@section('title', 'Sửa yêu cầu gỡ seal')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif (Session::has('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-danger') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-yeu-cau-go-seal">
                <p>
                    < Quay lại quản lý yêu cầu gỡ seal</p>
            </a>
            <h2>Sửa yêu cầu gỡ seal</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="form-group">
                                <center>
                                    <div class="col-10">
                                        <div class="row">
                                            <div class="col-4">
                                                <span class="mt-n2 mb-1 fs-5">Số container:</span>
                                                <select class="form-control" id="container-dropdown-search">
                                                    <option></option>
                                                    @foreach ($soContainers as $soContainer)
                                                        <option value=""></option>
                                                        <option value="{{ $soContainer['so_container'] }}">
                                                            {{ $soContainer['so_container'] }}
                                                            ({{ $soContainer['phuong_tien_vt_nhap'] ?? '' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <span class="mt-n2 mb-1 fs-5">Số tàu:</span>
                                                <input type="text" class="form-control" id="phuong-tien-vt-nhap"
                                                    placeholder="Nhập số tàu">
                                            </div>
                                            <div class="col-4">
                                                <span class="mt-n2 mb-1 fs-5">Số seal điện tử:</span>
                                                <input type="text" class="form-control" id="so-seal"
                                                    placeholder="Nhập số seal điện tử">
                                            </div>
                                            <center>
                                                <button type="button" id="addRowButton" class="btn btn-primary mt-2">Thêm
                                                    dòng</button>
                                            </center>
                                        </div>

                                    </div>
                                </center>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" id="displayTableYeuCau" style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr style="vertical-align: middle; text-align: center;">
                        <th>STT</th>
                        <th>Số container</th>
                        <th>Tàu</th>
                        <th>Số seal</th>
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
                <div class="modal-body">
                    Xác nhận sửa yêu cầu này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('quan-ly-kho.sua-yeu-cau-go-seal-submit') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" value="{{ $yeuCau->ma_yeu_cau }}" name="ma_yeu_cau">
                        <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const chiTiets = @json($chiTiets);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowButton = document.getElementById('addRowButton');
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            const soContainerInput = document.getElementById('container-dropdown-search');
            const rowsDataInput = document.getElementById('rowsDataInput');

            let rowIndex = 0;

            function addRow(soContainer, phuongTien, soSeal) {
                rowIndex++;

                const newRow = `
                    <tr data-index="${rowIndex}">
                        <td class="text-center">${rowIndex}</td>
                        <td class="text-center">${soContainer}</td>
                        <td class="text-center">${phuongTien}</td>
                        <td class="text-center">${soSeal}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', newRow);
            }

            if (chiTiets && Array.isArray(chiTiets)) {
                chiTiets.forEach((chiTiet, index) => {
                    addRow(chiTiet.so_container, chiTiet.phuong_tien_vt_nhap, chiTiet.so_seal_cu);
                });
            }

            addRowButton.addEventListener('click', function() {
                const values = soContainerInput.value;
                const soContainer = document.getElementById('container-dropdown-search').value;
                const phuongTien = document.getElementById('phuong-tien-vt-nhap').value;
                const soSeal = document.getElementById('so-seal').value;

                if (soContainer === '') {
                    alert('Vui lòng nhập số container!');
                    return;
                }

                // Check for duplicate entry in the table
                const isDuplicate = Array.from(tableBody.querySelectorAll('tr')).some(row => {
                    return row.querySelector('td:nth-child(2)').textContent.trim() === soContainer;
                });

                if (isDuplicate) {
                    alert('Số container đã tồn tại!');
                    return;
                }

                // Insert row HTML
                tableBody.insertAdjacentHTML('beforeend', `
                    <tr data-index="${rowIndex}">
                        <td class="text-center">${rowIndex}</td>
                        <td class="text-center">${soContainer}</td>
                        <td class="text-center">${phuongTien}</td>
                        <td class="text-center">${soSeal}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `);

                rowIndex++;
            });

            // Delete a row
            tableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('deleteRowButton')) {
                    const row = e.target.closest('tr');
                    row.remove();

                    // Reorder the STT column after deletion
                    Array.from(tableBody.querySelectorAll('tr')).forEach((tr, index) => {
                        tr.querySelector('td:first-child').textContent = index + 1;
                        tr.setAttribute('data-index', index + 1);
                    });

                    rowIndex = tableBody.querySelectorAll('tr').length;
                }
            });

            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const rowsData = rows.map(row => {
                    return {
                        stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                        so_container: row.querySelector('td:nth-child(2)').textContent.trim(),
                        phuong_tien_vt_nhap: row.querySelector('td:nth-child(3)').textContent.trim(),
                        so_seal: row.querySelector('td:nth-child(4)').textContent.trim(),
                    };
                });
                const rowCount = $('#displayTableYeuCau tbody tr').length;
                if (rowCount === 0) {
                    alert('Vui lòng thêm ít nhất một hàng thông tin');
                    return false;
                }
                // Update the hidden input with JSON data
                rowsDataInput.value = JSON.stringify(rowsData);
                $('#xacNhanModal').modal('show');
            });
        });
    </script>


@stop
