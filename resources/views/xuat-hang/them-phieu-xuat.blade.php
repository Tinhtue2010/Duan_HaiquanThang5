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
            <a class="return-link" href="/quan-ly-xuat-hang">
                <p>
                    < Quay lại quản lý phiếu xuất hàng</p>
            </a>
            <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center">PHIẾU XUẤT HÀNG</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="so_to_khai_nhap" class="mb-1">Số tờ khai nhập:</label> <i>(Sau khi xác
                                        nhận sẽ không thể thay đổi)</i>
                                    <select class="form-control" id="so-to-khai-nhap-dropdown-search" name="so_to_khai_nhap" id="so_to_khai_nhap">
                                        <option></option>
                                        @foreach ($soToKhaiNhaps as $soToKhaiNhap)
                                            <option value="{{ $soToKhaiNhap }}">
                                                {{ $soToKhaiNhap }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <center>
                                        <button class="btn btn-primary mt-2" id="searchButton"
                                            onclick="validateAndSearch(event)">Xác nhận/Tìm kiếm</button>
                                    </center>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="" class="mb-1">Loại hình:</label>
                                    <select class="form-control" id="loai-hinh-dropdown-search" name="ma_loai_hinh">
                                        <option></option>
                                        @foreach ($loaiHinhs as $loaiHinh)
                                            <option value="{{ $loaiHinh->ma_loai_hinh }}">
                                                {{ $loaiHinh->ma_loai_hinh }} ({{ $loaiHinh->ten_loai_hinh }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" id="displayTableXuatHang">
                <thead>
                    <tr style="vertical-align: middle; text-align: center;">
                        <th>STT</th>
                        <th style="display: none;">Mã hàng</th>
                        <th>TÊN HÀNG</th>
                        <th>XUẤT XỨ</th>
                        <th>SỐ LƯỢNG</th>
                        <th>ĐƠN VỊ TÍNH</th>
                        <th>ĐƠN GIÁ (USD)</th>
                        <th>TRỊ GIÁ (USD)</th>
                        <th>SỐ CONTAINER</th>
                        <th>THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <center>
                <button data-bs-toggle="modal" data-bs-target="#xacNhanModal" class="btn btn-success">Nhập phiếu xuất hàng</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm phiếu xuất hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm phiếu xuất hàng này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-hang.them-to-khai-xuat-submit') }}" method="POST" id="mainForm"
                        name='xuatHangForm'>
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <button type="submit" class="btn btn-success">Nhập phiếu xuất hàng</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Chọn hàng theo tờ khai --}}
    <div class="modal fade" id="chonHangTheoToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chọn hàng theo tờ khai</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row mb-3 mx-3">
                        <div class="card p-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="so_luong_xuat_input">Số lượng xuất:</label>
                                        <input type="number" class="form-control" id="so_luong_xuat_input" min="1"
                                            placeholder="Nhập số lượng xuất">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="tri_gia">Trị giá (USD)</label>
                                        <input type="number" class="form-control" id="tri_gia" min="0"
                                            placeholder="Nhập trị giá">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center"><i>(Hàng đã được chọn để xuất sẽ không xuất hiện trong danh sách này)</i></p>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="hangTrongContTable">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th style="display: none;">Mã Hàng cont</th>
                                    <th>Số tờ khai nhập</th>
                                    <th>Tên hàng</th>
                                    <th>Loại hàng</th>
                                    <th>Xuất xứ</th>
                                    <th>Đơn vị tính</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng tồn</th>
                                    <th>Số lượng chờ xuất</th>
                                    <th>Số container</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($containers as $index => $container)
                                    <tr class="container-row selectable-row" style="display: none;"
                                        data-so-to-khai="{{ $container->so_to_khai_nhap }}"
                                        data-item="{{ json_encode($container) }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $container->so_to_khai_nhap }}</td>
                                        <td style="display: none;">{{ $container->ma_hang_cont }}</td>
                                        <td>{{ $container->ten_hang }}</td>
                                        <td>{{ $container->loai_hang }}</td>
                                        <td>{{ $container->xuat_xu }}</td>
                                        <td>{{ $container->don_vi_tinh }}</td>
                                        <td>{{ $container->don_gia }}</td>
                                        <td>{{ $container->so_luong }}</td>
                                        <td>{{ $container->so_luong_cho_xuat }}</td>
                                        <td>{{ $container->so_container }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="doneButton">Chọn</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .selectable-row {
            cursor: pointer;
        }

        .selected-row {
            background-color: #e2f0ff !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            let selectedRow = null;

            function updateSelectableRows() {
                // Get all ma_hang_cont values from display table
                const usedMaHangConts = $('#displayTableXuatHang tbody tr').map(function() {
                    return $(this).find('td:eq(1)').text();
                }).get();

                // Update selectable rows in the modal
                $('.container-row').each(function() {
                    const maHangCont = $(this).find('td:eq(2)')
                        .text(); // Index 2 for hidden ma_hang_cont column
                    if (usedMaHangConts.includes(maHangCont)) {
                        $(this).hide();
                        $(this).removeClass('selectable-row selected-row');
                    } else {
                        $(this).addClass('selectable-row');
                    }
                });
            }
            //Xử lý tìm kiếm
            $('#searchButton').click(function() {
                const soToKhaiNhap = document.getElementById('so-to-khai-nhap-dropdown-search').value.trim();
                // Hide all rows first
                $('.container-row').hide();

                // Show only rows matching the search criteria
                if (soToKhaiNhap) {
                    $(`.container-row[data-so-to-khai="${soToKhaiNhap}"]`).each(function() {
                        const maHangCont = $(this).find('td:eq(2)').text();
                        const usedMaHangConts = $('#displayTableXuatHang tbody tr').map(function() {
                            return $(this).find('td:eq(1)').text();
                        }).get();

                        if (!usedMaHangConts.includes(maHangCont)) {
                            $(this).show();
                        }
                    });
                }
            });

            // Xử lý chọn hàng trog modal
            $(document).on('click', '.selectable-row', function() {
                if (selectedRow) {
                    selectedRow.removeClass('selected-row');
                }
                $(this).addClass('selected-row');
                selectedRow = $(this);

                // Get the current value from so_luong_xuat_input
                const currentValue = $('#so_luong_xuat_input').val();

                // Only calculate if there's a value
                if (currentValue) {
                    const item = selectedRow.data('item');
                    const maxQuantity = parseFloat(item.so_luong - item.so_luong_cho_xuat);
                    let value = parseFloat(currentValue);
                    const triGiaInput = document.getElementById("tri_gia");
                    const triGia = value * item.don_gia;

                    if (value > maxQuantity) {
                        alert('Số lượng xuất không thể lớn hơn số lượng tồn, lớn nhất là ' + maxQuantity);
                        $('#so_luong_xuat_input').val(maxQuantity);
                        const triGia = maxQuantity * item.don_gia;
                        triGiaInput.value = triGia;
                    } else {
                        triGiaInput.value = triGia;
                    }
                    if (value < 0) {
                        $('#so_luong_xuat_input').val(0);
                    }
                }
            });

            // Xử lý nút thêm hàng
            $('#doneButton').click(function() {
                if (!selectedRow) {
                    alert('Vui lòng chọn một hàng');
                    return;
                }

                const soLuongXuat = $('#so_luong_xuat_input').val();
                const triGia = $('#tri_gia').val();
                const phieuXuatKho = $('#phieu_xuat_kho').val();
                const item = selectedRow.data('item');

                // Validate quantity
                if (!soLuongXuat || soLuongXuat <= 0) {
                    alert('Vui lòng nhập số lượng xuất hợp lệ');
                    return;
                }

                if (parseFloat(soLuongXuat) > parseFloat(item.so_luong)) {
                    alert('Số lượng xuất không thể lớn hơn số lượng tồn');
                    return;
                }

                const displayTable = $('#displayTableXuatHang tbody');
                const rowCount = displayTable.children().length + 1;
                const newRow = `
            <tr>
                <td>${rowCount}</td>
                <td style="display: none;">${item.ma_hang_cont}</td>
                <td>${item.ten_hang}</td>
                <td>${item.xuat_xu}</td>
                <td>${soLuongXuat}</td>
                <td>${item.don_vi_tinh}</td>
                <td>${item.don_gia}</td>
                <td>${triGia}</td>
                <td>${item.so_container}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-row">Xóa</button>
                </td>
            </tr>
        `;

                displayTable.append(newRow);

                // Reset modal state
                selectedRow.removeClass('selected-row');
                selectedRow = null;

                $('#so_luong_xuat_input').val('');
                $('#tri_gia').val('');
                $('#phieu_xuat_kho').val('');

                // Close modal
                $('#chonHangTheoToKhaiModal').modal('hide');

                updateRowsData();
                updateSelectableRows();
            });

            $(document).on('click', '.delete-row', function() {
                const maHangCont = $(this).closest('tr').find('td:eq(1)').text();
                $(this).closest('tr').remove();
                updateRowsData();

                // Show the corresponding row in the modal again
                $(`.container-row`).each(function() {
                    if ($(this).find('td:eq(2)').text() === maHangCont) {
                        $(this).addClass('selectable-row');
                    }
                });
            });

            // Modal shown event
            $('#chonHangTheoToKhaiModal').on('show.bs.modal', function() {
                updateSelectableRows();
            });

            // Kiểm tra so_luong_xuat trước khi thêm
            $('#so_luong_xuat_input').on('input', function() {
                if (selectedRow) {
                    const item = selectedRow.data('item');
                    const maxQuantity = parseFloat(item.so_luong - item.so_luong_cho_xuat);
                    let value = parseFloat($(this).val());
                    const triGiaInput = document.getElementById("tri_gia");
                    const triGia = value * item.don_gia;


                    if (value > maxQuantity) {
                        alert('Số lượng xuất không thể lớn hơn số lượng tồn, lớn nhất là ' + maxQuantity);
                        $(this).val(maxQuantity);
                        const triGia = maxQuantity * item.don_gia;
                        triGiaInput.value = triGia;
                    } else {
                        triGiaInput.value = triGia;
                    }
                    if (value < 0) {
                        $(this).val(0);
                    }
                }
            });
        });
        //Thêm dòng mới vào bảng chính
        function updateRowsData() {
            const rows = $('#displayTableXuatHang tbody tr').map(function() {
                const cells = $(this).find('td');
                return {
                    ma_hang_cont: $(cells[1]).text(),
                    ten_hang: $(cells[2]).text(),
                    xuat_xu: $(cells[3]).text(),
                    so_luong_xuat: $(cells[4]).text(),
                    don_vi_tinh: $(cells[5]).text(),
                    don_gia: $(cells[6]).text(),
                    tri_gia: $(cells[7]).text(),
                    so_container: $(cells[8]).text()
                };
            }).get();

            $('#rowsDataInput').val(JSON.stringify(rows));
        }

        // Submit tờ khai nhập
        $('#mainForm').on('submit', function(e) {
            e.preventDefault();
            const so_to_khai_nhap = document.getElementById('so-to-khai-nhap-dropdown-search').value.trim();
            const ma_loai_hinh = document.getElementById('loai-hinh-dropdown-search').value.trim();

            const formData = new FormData(this);
            formData.append('so_to_khai_nhap', so_to_khai_nhap);
            formData.append('ma_loai_hinh', ma_loai_hinh);

            const rowCount = $('#displayTableXuatHang tbody tr').length;
            if (rowCount === 0) {
                alert('Vui lòng chọn ít nhất một hàng để xuất');
                return false;
            }
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Có lỗi xảy ra');
                    }
                },
                error: function(xhr) {
                    alert('Có lỗi xảy ra: ' + xhr.responseText);
                }
            });
        });
    </script>
    <script>
        //Kiểm tra trước khi tìm kiếm
        function validateAndSearch(event) {
            event.preventDefault();
            const soToKhaiNhap = document.getElementById('so-to-khai-nhap-dropdown-search').value.trim();

            if (!soToKhaiNhap) {
                alert('Vui lòng nhập số tờ khai trước khi tìm kiếm');
                return;
            }

            // If validation passes, open the modal
            new bootstrap.Modal(document.getElementById('chonHangTheoToKhaiModal')).show();
        }

        document.getElementById('searchButton').addEventListener('click', function() {
            const input = document.getElementById('so-to-khai-nhap-dropdown-search');
            if (!input.hasAttribute('readonly')) {
                input.setAttribute('readonly', true);
            }
        });
    </script>
        <script>
            document.getElementById('searchButton').addEventListener('click', function () {
                document.getElementById('so-to-khai-nhap-dropdown-search').disabled = true;
            });
        </script>
@stop