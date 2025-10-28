@extends('layout.user-layout')

@section('title', 'Thêm yêu cầu chuyển tàu')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-yeu-cau-chuyen-tau">
                <p>
                    < Quay lại quản lý yêu cầu chuyển tàu</p>
            </a>
            <div class="row">
                <div class="col-6">
                    <h2>Thêm yêu cầu chuyển tàu</h2>
                </div>
                <div class="col-6">
                    <button data-bs-toggle="modal" data-bs-target="#chonTheoContainerModal"
                        class="btn btn-success float-end">
                        Chọn theo container
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <div class="form-group">
                                    <span class="mt-n2 mb-1 fs-5">Đoàn tàu số:</span>
                                    <input type="text" class="form-control mb-1" id="ten_doan_tau" name="ten_doan_tau"
                                        placeholder="Nhập tên đoàn tàu" required>
                                    <span class="mt-n2 mb-1 fs-5">Số tờ khai nhập:</span><br>
                                    <span><em class="fs-6"> (Danh sách sẽ không hiện những tờ khai nhập có yêu cầu chuyển
                                            tàu <em class="text-primary">đang chờ duyệt</em>)</em></span>
                                    <select class="form-control " id="so-to-khai-nhap-dropdown-search"
                                        name="so_to_khai_nhap">
                                        <option></option>
                                        @foreach ($toKhaiNhaps as $toKhaiNhap)
                                            <option value="{{ $toKhaiNhap->so_to_khai_nhap }}">
                                                {{ $toKhaiNhap->so_to_khai_nhap }} (Ngày đăng ký:
                                                {{ \Carbon\Carbon::parse($toKhaiNhap->ngay_dang_ky)->format('d-m-Y') }} )
                                            </option>
                                        @endforeach
                                    </select>
                                    <center>
                                        <button id="searchButton" class="btn btn-primary mt-2">Chọn</button>
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
                        <th>Số container</th>
                        <th>Số tàu cũ</th>
                        <th>Số tàu mới</th>
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
                <form action="{{ route('quan-ly-kho.them-yeu-cau-chuyen-tau-submit') }}" method="POST" id="mainForm"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <label class="fs-4">Xác nhận thêm yêu cầu này?</label>
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
                        <input type="hidden" name="ten_doan_tau" id="ten_doan_tau_hidden">
                        <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chonTheoContainerModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chọn theo container</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <label class="label-text mb-2 fw-bold" for="ma_to_khai">Chọn số container:</label>
                        <select class="form-control" id="container-dropdown-search-2" name="so_container_goc">
                            <option></option>
                            @foreach ($soContainers as $soContainer)
                                <option value=""></option>
                                <option value="{{ $soContainer->so_container }}"
                                    data-so-container-moi="{{ $soContainer->so_container }}"
                                    data-total-so-luong="{{ $soContainer->total_so_luong }}">
                                    {{ $soContainer->so_container }}
                                </option>
                            @endforeach
                        </select>
                        <label class="label-text mb-2 fw-bold" for="ma_to_khai">Tàu cũ:</label>
                        <div>
                            <input type="text" class="form-control" id="tau-goc" maxlength="50"
                                placeholder="Nhập tên tàu" required>
                        </div>

                        <label class="label-text mb-2 fw-bold" for="ma_to_khai">Chọn số container mới:</label>
                        <select class="form-control" id="container-dropdown-search-3" name="so_container_moi">
                            <option></option>
                            @foreach ($soContainers as $soContainer)
                                <option value=""></option>
                                <option value="{{ $soContainer->so_container }}"
                                    data-so-container-moi="{{ $soContainer->so_container }}"
                                    data-total-so-luong="{{ $soContainer->total_so_luong }}">
                                    {{ $soContainer->so_container }}
                                </option>
                            @endforeach
                        </select>
                        <label class="label-text mb-2 fw-bold" for="ma_to_khai">Tàu mới:</label>
                        <div>
                            <input type="text" class="form-control" id="tau-dich" maxlength="50"
                                placeholder="Nhập tên tàu" required>
                        </div>

                        <table class="table table-bordered mt-3" id="soToKhaiTable"
                            style="vertical-align: middle; text-align: center;">
                            <thead style="vertical-align: middle; text-align: center;">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="checkAll" checked>
                                    </th>
                                    <th>
                                        Số tờ khai
                                    </th>
                                    <th>
                                        Số lượng hàng
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" id="xacNhanChonNhanhBtn" type="button">
                        Xác nhận
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal chọn  --}}
    <div class="modal fade" id="chonHangTheoToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chuyển hàng sang tàu mới</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 mx-3">
                        <div class="card p-3">
                            <div class="row">
                                <h3 class="text-center mb-2">Thông tin tờ khai nhập</h3>
                                <p class="fs-5"><strong>Số tờ khai:</strong> <span id="modal-so-to-khai"></span></p>
                                <p class="fs-5"><strong>Số container:</strong> <span id="modal-so-container"></span></p>
                                <p class="fs-5 fw-bold">Tàu hiện tại:</p>
                                <div>
                                    <input type="text" class="form-control reset-input" id="modal-tau-cu"
                                        maxlength="255" name="tau_cu" placeholder="Nhập tên tàu" required>
                                </div>

                                <p class="fs-5 fw-bold mt-3">Tên tàu mới:</p>
                                <div>
                                    <input type="text" class="form-control reset-input" id="modal-tau-moi"
                                        maxlength="255" name="tau_moi" placeholder="Nhập tên tàu" required>
                                </div>

                                <hr class="mt-2" />
                                <table class="table table-bordered" id="displayTableHangHoa"
                                    style="vertical-align: middle; text-align: center;">
                                    <thead>
                                        <tr style="vertical-align: middle; text-align: center;">
                                            <th>STT</th>
                                            <th>Số container</th>
                                            <th>Tàu mới</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="doneButton">Chọn</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            $('#searchButton').on('click', function() {
                var soToKhaiNhap = $('#so-to-khai-nhap-dropdown-search')
                    .val();
                const isDuplicate = Array.from(tableBody.querySelectorAll('tr')).some(
                    row => {
                        return row.querySelector('td:nth-child(2)').textContent
                            .trim() === soToKhaiNhap;
                    });

                if (soToKhaiNhap) {
                    $.ajax({
                        url: '/get-to-khai-items2',
                        method: 'GET',
                        data: {
                            so_to_khai_nhap: soToKhaiNhap
                        },
                        success: function(response) {
                            let tableBody = $("#displayTableHangHoa tbody");
                            tableBody.empty(); // Clear existing data
                            $('#modal-so-to-khai').text(response.so_to_khai_nhap);

                            let indexNum = 0;
                            console.log(response.containers);
                            $.each(response.containers, function(index, item) {
                                indexNum++;
                                let row = `
                                    <tr>
                                        <td>${indexNum}</td>
                                        <td>${item}</td>
                                        <td>
                                            <input type="text" class="form-control tau-moi">
                                        </td>                               
                                    </tr>
                                `;
                                tableBody.append(row);

                            });


                            if (response.data) {
                                $('#modal-so-to-khai').text(response.data.so_to_khai_nhap);
                                $('#modal-tau-cu').val(response.data.phuong_tien_vt_nhap);
                                $('#modal-so-container').text(response.data.so_container);

                                $('#chonHangTheoToKhaiModal').modal('show');
                            } else {
                                alert('Không tìm thấy thông tin tờ khai.');
                            }
                        },
                        error: function() {
                            alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        }
                    });
                } else {
                    alert('Vui lòng nhập số tờ khai.');
                }
            });
            $('#container-dropdown-search-2').on('change', function() {
                let selectedValue = $(this).val();
                $.ajax({
                    url: '/get-to-khai-trong-cont-2',
                    method: 'GET',
                    data: {
                        so_container: selectedValue
                    },
                    success: function(response) {
                        document.getElementById("tau-goc").value = response.tauCu;
                        let tableBody = $("#soToKhaiTable tbody");
                        tableBody.empty();
                        $.each(response.nhapHangs, function(index, item) {
                            let row = `
                                <tr>
                                    <td><input type="checkbox" class="check-item" checked></td>
                                    <td>${item.so_to_khai_nhap}</td>
                                    <td>${item.tong_so_luong}</td>
                                </tr>
                            `;
                            tableBody.append(row);
                        });

                    },
                    error: function() {
                        alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                });

            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const tableBody = document.querySelector('#displayTableYeuCau tbody');

            function updateTableIndex() {
                $('#displayTableYeuCau tbody tr').each(function(index) {
                    $(this).find('.row-index').text(index + 1); // Update the index column
                });
            }

            $('#doneButton').on('click', function() {
                var soToKhai = $('#modal-so-to-khai').text();
                var tauCu = $('#modal-tau-cu').val();
                var soContainer = $('#modal-so-container').text();
                var tauMoi = document.getElementById('modal-tau-moi').value;

                // Validate data before adding a row
                if (!soToKhai || !tauCu || !soContainer) {
                    alert('Vui lòng chọn và điền đầy đủ thông tin trước khi thêm!');
                    return;
                }

                var containers = soContainer.split(';').map(item => item.trim()).filter(item => item !==
                    "");
                $("#displayTableHangHoa tbody tr").each(function() {
                    var $cells = $(this).find("td");
                    var soToKhai = $('#modal-so-to-khai').text();

                    var stt = $cells.eq(0).text().trim();
                    var so_container = $cells.eq(1).text().trim();
                    var tau_moi = $cells.eq(2).find(".tau-moi").val().trim();

                    if (tau_moi === "") {
                        return;
                    }
                    var foundMatch = false;
                    $("#displayTableChiTiet tbody tr").each(function() {
                        var $cells2 = $(this).find("td");
                        var soToKhaiNhap2 = $cells2.eq(1).text().trim();
                        var soContainer2 = $cells2.eq(2).text().trim();
                        var tauMoi2 = $cells2.eq(4).text().trim();

                        if (soToKhai === soToKhaiNhap2 && so_container === soContainer2 &&
                            tau_moi === tauMoi2) {
                            foundMatch = true;
                            return false;
                        }

                    });
                    if (foundMatch) {
                        return false;
                    }
                    var newRow = `
                        <tr>
                            <td class="row-index"></td> <!-- Index column -->
                            <td>${soToKhai}</td>
                            <td>${so_container}</td>
                            <td>${tauCu}</td>
                            <td>${tau_moi}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row">Xóa</button>
                            </td>
                        </tr>
                    `;
                    $('#displayTableYeuCau tbody').append(newRow);
                });

                updateTableIndex();
                updateRowsData();

                $('#chonHangTheoToKhaiModal').modal('hide');
            });

            $("#xacNhanChonNhanhBtn").on("click", function() {
                const rows = $('#soToKhaiTable tbody tr')
                    .filter(function() {
                        return $(this).find('.check-item').is(':checked');
                    })
                    .map(function() {
                        const cells = $(this).find('td');
                        return {
                            so_to_khai_nhap: $(cells[1]).text(),
                        };
                    })
                    .get();

                if (rows.length === 0) {
                    alert('Vui lòng chọn ít nhất một tờ khai.');
                    return;
                }

                let so_container_goc = $("#container-dropdown-search-2").val();
                let so_container_dich = $("#container-dropdown-search-3").val();
                let tauGoc = $("#tau-goc").val();
                let tauDich = $("#tau-dich").val();
                if (!tauGoc || !tauDich) {
                    alert('Vui lòng chọn và điền đầy đủ thông tin trước khi thêm!');
                    return;
                }
                if (tauGoc === tauDich) {
                    alert('Tàu hiện tại đang trùng với tàu mới');
                    return;
                }
                let tbody = $("#displayTableYeuCau tbody");
                tbody.empty();
                rows.forEach(function(item, index) {
                    var newRow = `
                        <tr class="text-center">
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${item.so_to_khai_nhap}</td>
                            <td class="text-center">${so_container_goc}</td>
                            <td class="text-center">${tauGoc}</td>
                            <td class="text-center">${tauDich}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row">Xóa</button>
                            </td>
                        </tr>
                    `;
                    $('#displayTableYeuCau tbody').append(newRow);
                });
                updateRowsData();
                getYeuCauTableData();

                $('#chonTheoContainerModal').modal('hide');


            });

            function getYeuCauTableData() {
                let rowsData = $("#rowsDataInput").val();
                $.ajax({
                    url: "/get-to-khai-trong-tau-cont", // Laravel route
                    type: "GET",
                    contentType: "application/json",
                    data: {
                        rows_data: rowsData
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
                tableBody.empty();
                console.log(response);

                data.forEach((item, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.so_to_khai_nhap}</td>
                            <td>${item.so_container_goc}</td>
                            <td>${item.tauGoc}</td>
                            <td>${item.tauDich}</td>

                        </tr>
                    `;
                    tableBody.append(row);
                });
            }
            // Remove a row and update indexes
            $('#displayTableYeuCau').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateTableIndex(); // Recalculate indexes
                updateRowsData();
            });
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                const ten_doan_tau = $('#ten_doan_tau').val();
                if (ten_doan_tau === '') {
                    alert('Vui lòng nhập tên đoàn tàu');
                    return false;
                }
                const rowCount = $('#displayTableYeuCau tbody tr').length;
                if (rowCount === 0) {
                    alert('Vui lòng thêm ít nhất một hàng thông tin');
                    return false;
                }
                document.getElementById('ten_doan_tau_hidden').value = ten_doan_tau;
                $('#xacNhanModal').modal('show');
            })
        });
    </script>
    {{-- Submit table --}}
    <script>
        document.getElementById('modal-tau-moi').addEventListener('input', function() {
            const value = this.value;

            document.querySelectorAll('.tau-moi').forEach(function(input) {
                input.value = value;
            });
        });


        function updateRowsData() {
            const rows = $('#displayTableYeuCau tbody tr').map(function() {
                const cells = $(this).find('td');
                return {
                    so_to_khai_nhap: $(this).find('td').eq(1).text(),
                    so_container: $(this).find('td').eq(2).text(),
                    tau_cu: $(this).find('td').eq(3).text(),
                    tau_moi: $(this).find('td').eq(4).text(),
                };
            }).get();

            $('#rowsDataInput').val(JSON.stringify(rows));
        }
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
    <script>
        $(document).ready(function() {
            $('#chonHangTheoToKhaiModal').on('shown.bs.modal', function() {
                $('#container-dropdown-search').select2('destroy');
                $('#container-dropdown-search').select2({
                    tags: true,
                    placeholder: "Chọn container",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonHangTheoToKhaiModal .modal-body'),
                });
            });
            $('#chonTheoContainerModal').on('shown.bs.modal', function() {
                $('#container-dropdown-search-2').select2('destroy');
                $('#container-dropdown-search-2').select2({
                    tags: true,
                    placeholder: "Chọn container",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonTheoContainerModal .modal-body'),
                });
                $('#container-dropdown-search-3').select2('destroy');
                $('#container-dropdown-search-3').select2({
                    tags: true,
                    placeholder: "Chọn container",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonTheoContainerModal .modal-body'),
                });
            });
        });
    </script>
@stop
