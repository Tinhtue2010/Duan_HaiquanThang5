@extends('layout.user-layout')

@section('title', 'Thêm yêu cầu gỡ seal')

@section('content')

    <style>
        .toggle-container {
            display: flex;
            align-items: center;
            gap: 10px;
            /* space between toggle and text */
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }
    </style>
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
            <div class="row">
                <div class="col-9">
                    <h2>Thêm yêu cầu gỡ seal</h2>
                </div>
                <div class="col-3 ">
                    {{-- <button type="button" id="addContainer" class="btn btn-primary mt-2 float-end">Chọn nhanh</button> --}}
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    {{-- <span class="mb fs-5 fst-italic">*Nếu cần niêm phong sau khi gỡ seal thì doanh nghiệp vào yêu cầu niêm phong container</span> --}}
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
                                        </div>
                                        <center>
                                            <div class="row">
                                                <div class="toggle-container">
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" id="toggleControl">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span>Niêm phong sau khi gỡ seal</span>
                                                </div>
                                            </div>
                                        </center>
                                        <center>
                                            <button type="button" id="addRowButton" class="btn btn-primary mt-2">Thêm
                                                dòng</button>
                                        </center>
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
                        <th>Số tàu</th>
                        <th>Số seal</th>
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
                    <form action="{{ route('quan-ly-kho.them-yeu-cau-go-seal-submit') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="is_niem_phong" id="is_niem_phong_hidden" value="0">
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
            const rowsDataInput = document.getElementById('rowsDataInput'); // Ensure this exists in your HTML form

            let rowIndex = 1;

            // Add a new row
            addRowButton.addEventListener('click', function() {
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
                    });

                    rowIndex--;
                }
            });
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                const toggle = document.getElementById('toggleControl');
                document.getElementById('is_niem_phong_hidden').value = toggle.checked ? 1 : 0;

                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const rowsData = rows.map(row => {
                    return {
                        stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                        so_container: row.querySelector('td:nth-child(2)').textContent.trim(),
                        phuong_tien_vt_nhap: row.querySelector('td:nth-child(3)').textContent
                            .trim(),
                        so_seal: row.querySelector('td:nth-child(4)').textContent.trim()
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
