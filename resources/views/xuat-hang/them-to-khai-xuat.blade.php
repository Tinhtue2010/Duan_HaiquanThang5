@extends('layout.user-layout')

@section('title', 'Thêm phiếu xuất')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/quan-ly-xuat-hang">
                        <p>
                            < Quay lại quản lý phiếu xuất hàng</p>
                    </a>
                </div>
                <div class="col-6">
                    <a class="float-end" href="#">
                        <button data-bs-toggle="modal" data-bs-target="#chonFileModal" class="btn btn-success ">
                            Nhập từ file</button>
                    </a>
                </div>
            </div>
            <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center">PHIẾU XUẤT HÀNG</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <h3 class="text-center">Thông tin tờ khai</h3>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label for="so_to_khai_nhap" class="mb-1">Số tờ khai nhập:</label>
                                    <select class="form-control" id="so-to-khai-nhap-dropdown-search" name="so_to_khai_nhap"
                                        id="so_to_khai_nhap">
                                        <option></option>
                                        @foreach ($nhapHangs as $nhapHang)
                                            <option value="{{ $nhapHang->so_to_khai_nhap }}">
                                                {{ $nhapHang->so_to_khai_nhap }} (Ngày thông quan:
                                                {{ \Carbon\Carbon::parse($nhapHang->ngay_thong_quan)->format('d-m-Y') }} )
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label for="" class="mb-1">Đoàn tàu số:</label>
                                    <input type="text" class="form-control mb-1" id="ten_doan_tau" name="ten_doan_tau"
                                        placeholder="Nhập tên đoàn tàu" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mt-3">
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
            <div class="row card p-2">
                <h3 class="text-center">Nhập số lượng hàng hóa xuất</h3>
                <div class="table-responsive">
                    <table class="table table-bordered" id="hangTrongContTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th style="display: none;">STT</th>
                                <th style="display: none;">Mã Hàng cont</th>
                                <th>Số tờ khai nhập</th>
                                <th>Tên hàng</th>
                                <th>Xuất xứ</th>
                                <th>Đơn vị tính</th>
                                <th>Đơn giá</th>
                                <th>Số container</th>
                                <th>Số lượng tồn</th>
                                <th>Số lượng xuất</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Load các dòng hàng ngay từ đầu --}}
                            @foreach ($containers as $index => $container)
                                @if ($container->so_luong != 0)
                                    <tr class="container-row" style="display: none;"
                                        data-so-to-khai="{{ $container->so_to_khai_nhap }}"
                                        data-item="{{ json_encode($container) }}">
                                        <td style="display: none;">{{ $index + 1 }}</td>
                                        <td>{{ $container->so_to_khai_nhap }}</td>
                                        <td style="display: none;">{{ $container->ma_hang_cont }}</td>
                                        <td>{{ $container->ten_hang }}</td>
                                        <td>{{ $container->xuat_xu }}</td>
                                        <td>{{ $container->don_vi_tinh }}</td>
                                        <td>{{ $container->don_gia }}</td>
                                        <td>{{ $container->so_container }}</td>
                                        <td class="remaining-quantity">{{ $container->so_luong }}</td>
                                        <td>
                                            <center>
                                                <input type="number" class="form-control so_luong_xuat_input"
                                                    id="so_luong_xuat_input" min="0" placeholder="0"
                                                    style="width: 80px;" oninput="this.value = Math.abs(this.value)">
                                            </center>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>

                    </table>
                    <center>
                        <button type="button" class="btn btn-primary mt-2" id="chonXuatBtn">Chọn xuất</button>
                    </center>
                </div>
            </div>

            <div class="row card p-2">
                <h3 class="text-center">Thông tin hàng hóa chọn xuất (Có thể chọn nhiều tờ khai)</h3>
                <div class="table-responsive">
                    <table class="table table-bordered" id="xuatHangCont"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th style="display: none;">Mã Hàng cont</th>
                                <th>Số tờ khai nhập</th>
                                <th>Tên hàng</th>
                                <th>Xuất xứ</th>
                                <th>Đơn vị tính</th>
                                <th>Đơn giá</th>
                                <th>Số container</th>
                                <th>Số lượng tồn</th>
                                <th>Số lượng xuất</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7"><strong>Tổng:</strong></td>
                                <td id="totalQty"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <center>
                <div class="row card p-2 col-6">
                    <h3 class="text-center">Chọn phương tiện vận tải</h3>
                    <div class="form-group">
                        <select class="form-control" name="so_ptvt_xuat_canh" id="ptvt-dropdown-search">
                            <option value=""></option>
                            @foreach ($ptvtXuatCanhs as $ptvtXuatCanh)
                                <option value="{{ $ptvtXuatCanh->so_ptvt_xuat_canh }}">
                                    {{ $ptvtXuatCanh->ten_phuong_tien_vt }} (Số:
                                    {{ $ptvtXuatCanh->so_ptvt_xuat_canh }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <center>
                        <button type="button" id="addRowButton" class="btn btn-primary mt-2">Chọn</button>
                    </center>

                    <table class="table table-bordered mt-2" id="displayTableYeuCau"
                        style="vertical-align: middle; text-align: center;">
                        <thead>
                            <tr style="vertical-align: middle; text-align: center;">
                                <th>STT</th>
                                <th>Tên phương tiện vận tải</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </center>
            <center>
                <button id="xacNhanBtn" class="btn btn-success mt-5">Nhập phiếu xuất hàng</button>
            </center>
            </form>

        </div>
    </div>
    <div class="modal fade" id="chonFileModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chọn file để nhập dữ liệu</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold mb-0 fs-5">Hãy đảm bảo file có đủ các cột sau:</p>
                    <p class="fw-bold">Số tờ khai, Tên hàng, Số lượng đăng ký xuất</p>
                    <div class="file-upload">
                        <input type="file" id="hys_file" class="file-upload-input">
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
                    <button type="submit" id="uploadHys" class="btn btn-success">Xác nhận</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
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
                        <input type="hidden" name="ptvt_rows_data" id="ptvtRowsDataInput">
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="so_to_khai_nhap" id="so_to_khai_nhap_hidden">
                        <input type="hidden" name="ma_loai_hinh" id="ma_loai_hinh_hidden">
                        <input type="hidden" name="ten_doan_tau" id="ten_doan_tau_hidden">
                        <button id="submitData" type="submit" class="btn btn-success">Nhập phiếu xuất hàng</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            //Xử lý tìm kiếm
            $('#so-to-khai-nhap-dropdown-search').change(function() {
                const soToKhaiNhap = document.getElementById('so-to-khai-nhap-dropdown-search').value
                    .trim();
                // Hide all rows first

                $('.so_luong_xuat_input').val('');
                $('.container-row').hide();

                // Show only rows matching the search criteria
                if (soToKhaiNhap) {
                    $.ajax({
                        url: '/kiem-tra-qua-han', // The route to your controller method
                        method: 'GET',
                        data: {
                            so_to_khai_nhap: soToKhaiNhap
                        },
                        success: function(response) {
                            if (response.data === true) {
                                alert('Tờ khai đã quá hạn 60 ngày, không cho nhập liệu');
                            } else if (response.data === false) {
                                document.getElementById('so-to-khai-nhap-dropdown-search')
                                    .disabled = false;
                                $(`.container-row[data-so-to-khai="${soToKhaiNhap}"]`).each(
                                    function() {
                                        const maHangCont = $(this).find('td:eq(2)').text();
                                        const usedMaHangConts = $(
                                            '#displayTableXuatHang tbody tr').map(
                                            function() {
                                                return $(this).find('td:eq(1)').text();
                                            }).get();

                                        if (!usedMaHangConts.includes(maHangCont)) {
                                            $(this).show();
                                        }
                                    });
                            } else {
                                alert('Có lỗi xảy ra');
                            }
                        },
                        error: function() {
                            alert('Có lỗi xảy ra');
                        }
                    });

                }


            });
            // Xử lý chọn số lượng xuất
            const inputs = document.querySelectorAll("input#so_luong_xuat_input");

            inputs.forEach(input => {
                input.addEventListener("input", function() {
                    const row = input.closest("tr");
                    const remainingQuantityCell = row.querySelector(".remaining-quantity");
                    const remainingQuantity = parseFloat(remainingQuantityCell
                        .textContent);

                    const inputValue = parseFloat(input.value);
                    if (inputValue > remainingQuantity) {
                        alert('Số lượng xuất không thể lớn hơn số lượng tồn, lớn nhất là ' +
                            remainingQuantity);
                        input.value = remainingQuantity;
                    }
                });
            });

            //#displayTableYeuCau
            const addRowButton = document.getElementById('addRowButton');
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            const ptvtInput = document.getElementById('ptvt-dropdown-search');
            const ptvtRowsDataInput = document.getElementById(
                'ptvtRowsDataInput'); // Ensure this exists in your HTML form

            let rowIndex = 0;

            // Add a new row
            addRowButton.addEventListener('click', function() {
                const so_ptvt_xuat_canh = ptvtInput.value.trim();
                let ten_phuong_tien_vt = document.getElementById("ptvt-dropdown-search").options[document
                    .getElementById("ptvt-dropdown-search").selectedIndex].text;
                if (so_ptvt_xuat_canh === '') {
                    alert('Vui lòng chọn phương tiện');
                    return;
                }

                // Check for duplicate entry in the table
                const isDuplicate = Array.from(tableBody.querySelectorAll('tr')).some(row => {
                    return row.querySelector('td:nth-child(2)').textContent.trim() ===
                        ten_phuong_tien_vt;
                });

                if (isDuplicate) {
                    alert('Phương tiện đã tồn tại!');
                    return;
                }

                rowIndex++;

                // Create a new table row
                const newRow = `
                    <tr data-index="${rowIndex}">
                        <td class="text-center">${rowIndex}</td>
                        <td class="text-center">${ten_phuong_tien_vt}</td>
                        <td class="text-center" hidden>${so_ptvt_xuat_canh}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', newRow);

                // Clear the input field
                ptvtInput.value = '';
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

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#xuatHangCont tbody tr").forEach(row => {
                    let quantityCell = row.cells[8]; // 9th column (zero-based index)
                    let quantity = parseFloat(quantityCell.textContent || quantityCell.querySelector(
                        "input")?.value || 0);
                    total += isNaN(quantity) ? 0 : quantity;
                });

                // Update the total in <tfoot>
                document.getElementById("totalQty").textContent = total;
            }

            // Modified xacNhanBtn click handler with date validation
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                // Table 2 validation (existing code)
                let invalidEntry = false;
                const rows2 = Array.from(tableBody.querySelectorAll('tr'));
                const rowsData2 = rows2.map(row => {
                    return {
                        stt: row.querySelector('td:nth-child(1)').textContent.trim(),
                        so_ptvt_xuat_canh: row.querySelector('td:nth-child(3)').textContent.trim()
                    };
                });
                const rowCount2 = $('#displayTableYeuCau tbody tr').length;
                if (rowCount2 === 0) {
                    alert('Vui lòng chọn ít nhất 1 phương tiện xuất cảnh');
                    return false;
                }
                ptvtRowsDataInput.value = JSON.stringify(rowsData2);

                // Table 1 validation and date checking
                const rows = $('#xuatHangCont tbody tr')
                    .map(function() {
                        const cells = $(this).find('td');
                        const so_luong_xuat = parseFloat($(cells[8]).text()) || 0;
                        const don_gia = parseFloat($(cells[5]).text()) || 0;
                        const tri_gia = don_gia * so_luong_xuat;
                        return {
                            ma_hang_cont: $(cells[0]).text(),
                            so_to_khai_nhap: $(cells[1]).text(),
                            so_container: $(cells[6]).text(),
                            so_luong_xuat: $(cells[8]).text(),
                            tri_gia: tri_gia,
                        };
                    })
                    .get();

                if (rows.length === 0) {
                    alert('Vui lòng chọn ít nhất một hàng hóa để xuất.');
                    return false;
                }

                // NEW: Check if any row has ngay_thong_quan older than 15 days
                let hasExpiredRows = false;
                const currentDate = new Date();
                const expiredRows = [];
                const processedToKhai = new Set(); // Track processed declarations to avoid duplicates

                $('#xuatHangCont tbody tr').each(function() {
                    const soToKhaiNhap = $(this).find('td:eq(1)').text().trim();

                    // Skip if we've already processed this declaration
                    if (processedToKhai.has(soToKhaiNhap)) {
                        return;
                    }

                    // Find the corresponding container data to get ngay_thong_quan
                    const containerData = @json($containers).find(container =>
                        container.so_to_khai_nhap === soToKhaiNhap
                    );

                    if (containerData && containerData.ngay_tiep_nhan) {
                        const ngayThongQuan = new Date(containerData.ngay_tiep_nhan);
                        const daysDifference = Math.floor((currentDate - ngayThongQuan) / (1000 *
                            60 * 60 * 24));
                        console.log(daysDifference)
                        const referenceDate = new Date(2025, 7,
                        15); // JS months are 0-based → 7 = August

                        if (ngayThongQuan > referenceDate) {
                            if (soToKhaiNhap.startsWith('5') && daysDifference > 15) {
                                hasExpiredRows = true;
                                expiredRows.push({
                                    soToKhai: soToKhaiNhap,
                                    ngayThongQuan: ngayThongQuan.toLocaleDateString(
                                        'vi-VN'),
                                    daysDifference: daysDifference
                                });
                            }
                        }

                        // Mark this declaration as processed
                        processedToKhai.add(soToKhaiNhap);
                    }
                });

                // If there are expired rows, show warning and prevent submission
                if (hasExpiredRows) {
                    let warningMessage =
                        'Các tờ khai sau đây đã đến 15 ngày trở lên kể từ ngày tiếp nhận:\n\n';
                    expiredRows.forEach(row => {
                        warningMessage +=
                            `- Tờ khai: ${row.soToKhai}, Ngày tiếp nhận: ${row.ngayThongQuan}\n`;
                    });
                    // warningMessage += '\nKhông thể xuất hàng cho các tờ khai từ 15 ngày trở lên.';

                    alert(warningMessage);
                    // return false;
                }

                // Continue with existing validation
                document.getElementById('so_to_khai_nhap_hidden').value = document.getElementById(
                    'so-to-khai-nhap-dropdown-search').value.trim();
                document.getElementById(
                    'ma_loai_hinh_hidden').value = document.getElementById(
                    'loai-hinh-dropdown-search').value.trim();
                document.getElementById(
                    'ten_doan_tau_hidden').value = document.getElementById(
                    'ten_doan_tau').value.trim();

                $('#rowsDataInput').val(JSON.stringify(rows));

                let dropdownValue = document.getElementById('loai-hinh-dropdown-search').value;
                if (!dropdownValue) {
                    alert('Vui lòng chọn loại hình trước khi nhập phiếu xuất hàng.');
                    return false;
                }

                let tenDoanTau = document.getElementById('ten_doan_tau').value;
                if (!tenDoanTau) {
                    alert('Vui lòng nhập tên đoàn tàu');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });

            $("#chonXuatBtn").click(function() {
                $(".container-row").each(function() {
                    let inputField = $(this).find(".so_luong_xuat_input");
                    let inputValue = parseFloat(inputField.val()) || 0;

                    if (inputValue > 0) {
                        let maHangCont = $(this).find("td:eq(2)").text()
                            .trim();
                        let soToKhai = $(this).data("so-to-khai");
                        let tenHang = $(this).find("td:eq(3)").text();
                        let xuatXu = $(this).find("td:eq(4)").text();
                        let donViTinh = $(this).find("td:eq(5)").text();
                        let donGia = $(this).find("td:eq(6)").text();
                        let soContainer = $(this).find("td:eq(7)").text();
                        let soLuongTon = $(this).find("td:eq(8)").text();

                        // Find if the row already exists in xuatHangCont
                        let existingRow = $("#xuatHangCont tbody").find(
                            `tr[data-ma-hang-cont='${maHangCont}']`);

                        if (existingRow.length > 0) {
                            existingRow.find(".so_luong_xuat").text(inputValue);
                        } else {
                            // Row does not exist, append a new row
                            let newRow = `
                                    <tr data-ma-hang-cont="${maHangCont}">
                                        <td style="display: none;">${maHangCont}</td>
                                        <td>${soToKhai}</td>
                                        <td>${tenHang}</td>
                                        <td>${xuatXu}</td>
                                        <td>${donViTinh}</td>
                                        <td>${donGia}</td>
                                        <td>${soContainer}</td>
                                        <td>${soLuongTon}</td>
                                        <td class="so_luong_xuat">${inputValue}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                        </td>
                                    </tr>
                                `;
                            $("#xuatHangCont tbody").append(newRow);
                        }
                        calculateTotal();
                        inputField.val("");
                    }
                });
            });

            $(document).on("click", ".deleteRowButton", function() {
                $(this).closest("tr").remove(); // Remove the closest row when clicking "Xóa"
                calculateTotal();
            });
            $("#uploadHys").on("click", function() {
                var file = $("#hys_file")[0].files[0];
                if (!file) {
                    alert("Xin hãy chọn 1 file!");
                    return;
                }
                var formData = new FormData();
                formData.append("hys_file", file);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('xuat-hang.upload-file-xuat') }}", // Define the route in Laravel
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (!response.data) {
                            alert(response);
                        } else {
                            var tbody = $("#xuatHangCont tbody");
                            tbody.empty();

                            $.each(response.data, function(index, row) {
                                var tr = `<tr>
                                        <td hidden>${row.ma_hang_cont}</td>
                                        <td>${row.so_to_khai_nhap}</td>
                                        <td>${row.ten_hang}</td>
                                        <td>${row.xuat_xu}</td>
                                        <td>${row.don_vi_tinh}</td>
                                        <td>${row.don_gia}</td>
                                        <td>${row.so_container}</td>
                                        <td>${row.so_luong_ton}</td>
                                        <td>${row.so_luong_xuat}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                        </td>                                    
                                    </tr>`;
                                tbody.append(tr);
                            });
                            calculateTotal();
                            alert("Nhập file thành công");
                            $('#chonFileModal').modal('hide');
                        }

                    },
                    error: function(xhr) {
                        alert(xhr.responseText);
                    }
                });
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
        }
    </script>
    <script>
        const fileInput = document.getElementById('hys_file');
        const fileName = document.getElementById('fileName');
        const fileUpload = document.querySelector('.file-upload');
        document.getElementById("hys_file").addEventListener("change", function() {
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
    <script>
        $(document).ready(function() {
            $('.select2-dropdown').select2({
                placeholder: "",
                allowClear: true,
                width: '100%'
            });

            $('.container-row').on('show', function() {
                $(this).find('.select2-dropdown').select2({
                    placeholder: "",
                    allowClear: true,
                    width: '100%'
                });
            });
        });
    </script>

@stop
