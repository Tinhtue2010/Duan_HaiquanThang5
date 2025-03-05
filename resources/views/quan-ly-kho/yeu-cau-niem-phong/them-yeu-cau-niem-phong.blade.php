@extends('layout.user-layout')

@section('title', 'Thêm yêu cầu niêm phong')

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
            <a class="return-link" href="/danh-sach-yeu-cau-niem-phong">
                <p>
                    < Quay lại quản lý yêu cầu niêm phong</p>
            </a>
            <h2>Thêm yêu cầu niêm phong</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <div class="form-group">
                                    <span class="mt-n2 mb-1 fs-5">Số container:</span>
                                    <select class="form-control" id="container-dropdown-search">
                                        <option></option>
                                        @foreach ($soContainers as $soContainer)
                                            <option value=""></option>
                                            <option value="{{ $soContainer->so_container }}">
                                                {{ $soContainer->so_container }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <center>
                                        <button type="button" id="addRowButton" class="btn btn-primary mt-2">Thêm
                                            dòng</button>
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
                        <th>Số container</th>
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
                <div class="modal-body">
                    Xác nhận thêm yêu cầu này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('quan-ly-kho.them-yeu-cau-niem-phong-submit') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowButton = document.getElementById('addRowButton');
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            const soContainerInput = document.getElementById('container-dropdown-search');
            const rowsDataInput = document.getElementById('rowsDataInput'); // Ensure this exists in your HTML form

            let rowIndex = 0;

            // Add a new row
            addRowButton.addEventListener('click', function() {
                const soContainer = soContainerInput.value.trim();

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

                rowIndex++;

                // Create a new table row
                const newRow = `
                    <tr data-index="${rowIndex}">
                        <td class="text-center">${rowIndex}</td>
                        <td class="text-center">${soContainer}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', newRow);

                // Clear the input field
                soContainerInput.value = '';
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
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const rowsData = rows.map(row => {
                    return {
                        stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                        so_container: row.querySelector('td:nth-child(2)').textContent.trim()
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
