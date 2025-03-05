@extends('layout.user-layout')

@section('title', 'Thêm yêu cầu chuyển container và tàu')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-yeu-cau-tau-cont">
                <p>
                    < Quay lại quản lý yêu cầu chuyển container và tàu</p>
            </a>
            <h2>Thêm yêu cầu chuyển container và tàu</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="col-5">
                                <div class="form-group">
                                    <span class="mt-n2 mb-1 fs-5">Đoàn tàu số:</span>
                                    <input type="text" class="form-control mb-1" id="ten_doan_tau" name="ten_doan_tau"
                                        placeholder="Nhập tên đoàn tàu" required>
                                    <span class="mt-n2 mb-1 mt-1 fs-5">Số tờ khai nhập:</span></br>
                                    <span><em class="fs-6"> (Danh sách sẽ không hiện các tờ khai nhập có yêu cầu chuyển
                                            <em class="text-primary">đang chờ duyệt</em>)</em></span>
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
            <h2 class="text-center">Chi tiết hàng hóa chuyển</h2>
            <table class="table table-bordered" id="displayTableChiTiet"
                style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr style="vertical-align: middle; text-align: center;">
                        <th hidden>Mã Hàng cont</th>
                        <th>Số tờ khai</th>
                        <th>Tên hàng</th>
                        <th>Số lượng</th>
                        <th>Container cũ</th>
                        <th>Container mới</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <center>
                <h2>Thông tin yêu cầu</h2>
            </center>
            <table class="table table-bordered" id="displayTableYeuCau" style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr style="vertical-align: middle; text-align: center;">
                        <th>STT</th>
                        <th>Số tờ khai</th>
                        <th>Số container cũ</th>
                        <th>Số lượng chuyển (kiện)</th>
                        <th>Số container mới</th>
                        <th>Số tờ khai tại container mới</th>
                        <th>Số lượng tồn trong container (kiện)</th>
                        <th>Tổng hàng hóa sau khi chuyển (kiện)</th>
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
                <form action="{{ route('quan-ly-kho.them-yeu-cau-tau-cont-submit') }}" method="POST" id="mainForm"
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

    {{-- Modal chọn container --}}
    <div class="modal fade" id="chonHangTheoToKhaiModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-2 modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chuyển hàng sang container mới</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 mx-3">
                        <div class="card p-3">
                            <div class="row">
                                <h3 class="text-center mb-2">Thông tin tờ khai nhập</h3>
                                <p class="fs-5"><strong>Số tờ khai:</strong> <span id="modal-so-to-khai"></span></p>
                                <p class="fs-5"><strong>Số tàu hiện tại:</strong> <span id="modal-tau-cu"></span></p>
                                <p class="fs-5 mt-3"><strong>Tên tàu mới: </strong></p>
                                <div>
                                    <input type="text" class="form-control reset-input" id="modal-tau-moi"
                                        maxlength="50" name="tau_moi" placeholder="Nhập tên tàu" required>
                                </div>

                                <p class="fs-5"><strong>Số container mới (Người dùng có thể nhập số container mới trong ô
                                        "Nhập để tìm kiếm"): </strong></p>



                                <select class="form-control" id="container-dropdown-search" name="so_container_moi">
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
                                <hr class="mt-2" />
                                <table class="table table-bordered" id="displayTableHangHoa"
                                    style="vertical-align: middle; text-align: center;">
                                    <thead>
                                        <tr style="vertical-align: middle; text-align: center;">
                                            <th>STT</th>
                                            <th>Tên hàng hóa</th>
                                            <th>Số container</th>
                                            <th>Số lượng</th>
                                            <th>Số container mới</th>
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
            const soContainers = @json($soContainers);
            $('#searchButton').on('click', function() {
                var soToKhaiNhap = $('#so-to-khai-nhap-dropdown-search')
                    .val(); // Replace with the actual input field for so_to_khai_nhap

                if (soToKhaiNhap) {
                    $.ajax({
                        url: '/get-to-khai-items', // The route to your controller method
                        method: 'GET',
                        data: {
                            so_to_khai_nhap: soToKhaiNhap
                        },
                        success: function(response) {
                            let tableBody = $("#displayTableHangHoa tbody");
                            tableBody.empty();

                            $('#modal-so-to-khai').text(response.so_to_khai_nhap);
                            $('#modal-tau-cu').text(response.phuong_tien_vt_nhap);
                            $('#modal-so-to-khai').text(response.so_to_khai_nhap);
                            let options =
                                '<option value=""></option>';
                            soContainers.forEach(container => {
                                options +=
                                    `<option value="${container.so_container}" data-soluong="${container.total_so_luong}">${container.so_container}</option>`;
                            });

                            let indexNum = 0;
                            $.each(response.hangHoas, function(index, item) {
                                let remainingSoLuong = parseInt(item.so_luong) || 0;
                                let itemMaHangCont = String(item.ma_hang_cont).trim();
                                $('#displayTableChiTiet tbody tr').each(function() {
                                    let existingMaHangCont = $(this).find(
                                        'td:eq(0)').text().trim();
                                    let existingSoLuongText = $(this).find(
                                        'td:eq(3)').text().trim();
                                    let existingSoLuong = parseInt(
                                        existingSoLuongText) || 0;
                                    if (existingMaHangCont === itemMaHangCont) {
                                        remainingSoLuong -= existingSoLuong;
                                    }
                                });

                                remainingSoLuong = Math.max(0, remainingSoLuong);
                                if (remainingSoLuong != 0 || item.is_da_chuyen_cont ==
                                    0) {
                                    indexNum++;
                                    let row = `
                                    <tr>
                                        <td>${indexNum}</td>
                                        <td hidden>${item.ma_hang_cont}</td>
                                        <td>${item.ten_hang}</td>
                                        <td class="original-container">${item.so_container}</td>
                                        <td>
                                            <input type="number" class="form-control so-luong-input" 
                                                value="${remainingSoLuong}" min="0" max="${remainingSoLuong}" 
                                                data-max="${remainingSoLuong}">
                                        </td>                               
                                        <td>
                                            <select class="select2-dropdown" name="so-container">
                                                ${options}
                                            </select>
                                        </td>
                                    </tr>
                                `;
                                    tableBody.append(row);
                                }


                            });


                            $('.select2-dropdown').select2({
                                tags: true,
                                placeholder: "",
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $(
                                    '#chonHangTheoToKhaiModal .modal-body'),
                            });
                            $('#container-dropdown-search').on('change', function() {
                                let selectedValue = $(this).val();

                                $('.select2-dropdown').each(function() {
                                    let select = $(this);

                                    // If the selected value is not in the current options, add it
                                    if (select.find(
                                            `option[value="${selectedValue}"]`)
                                        .length === 0) {
                                        let newOption = new Option(
                                            selectedValue, selectedValue,
                                            true, true);
                                        select.append(newOption);
                                    }

                                    // Set the new value and trigger Select2 change
                                    select.val(selectedValue).trigger('change');
                                });
                            });

                            $('#chonHangTheoToKhaiModal').modal('show');

                        },
                        error: function() {
                            alert('Có lỗi xảy ra. Vui lòng thử lại.');
                        }
                    });
                } else {
                    alert('Vui lòng nhập số tờ khai.');
                }
            });
            $(document).on('input', '.so-luong-input', function() {
                let maxVal = parseInt($(this).attr('data-max'));
                let currentVal = parseInt($(this).val());

                if (currentVal > maxVal) {
                    $(this).val(maxVal);
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const tableHangHoaBody = document.querySelector('#displayTableHangHoa tbody');
            let soToKhaiNhaps = '';

            function updateTableIndex() {
                $('#displayTableYeuCau tbody tr').each(function(index) {
                    $(this).find('.row-index').text(index + 1);
                });
            }
            $("#doneButton").on("click", function() {
                $("#displayTableHangHoa tbody tr").each(function() {
                    var $cells = $(this).find("td");
                    var soToKhai = $('#modal-so-to-khai').text();

                    var stt = $cells.eq(0).text().trim();
                    var maHangCont = $cells.eq(1).text().trim();
                    var tenHangHoa = $cells.eq(2).text().trim();
                    var soContainer = $cells.eq(3).text().trim();
                    var soLuong = $cells.eq(4).find(".so-luong-input").val().trim();
                    var soContainerMoi = $cells.eq(5).find("select").val();

                    if (soLuong === 0 || soContainerMoi === "" || soContainerMoi ===
                        soContainer) {
                        return;
                    }

                    var foundMatch = false;
                    $("#displayTableChiTiet tbody tr").each(function() {
                        var $cells2 = $(this).find("td");
                        var maHangCont2 = $cells2.eq(0).text().trim();
                        var soContainer2 = $cells2.eq(4).text().trim();
                        var soContainerMoi2 = $cells2.eq(5).text().trim();
                        if (maHangCont === maHangCont2 && soContainer ===
                            soContainer2 &&
                            soContainerMoi === soContainerMoi2) {
                            foundMatch = true;
                            return false;
                        }

                    });
                    if (foundMatch) {
                        return false;
                    }
                    var tauGoc = $('#modal-tau-cu').text();
                    var tauDich = document.getElementById('modal-tau-moi').value;
                    var newRow = `
                        <tr class="text-center">
                            <td class="text-center" hidden>${maHangCont}</td>
                            <td class="text-center">${soToKhai}</td>
                            <td class="text-center">${tenHangHoa}</td>
                            <td class="text-center">${soLuong}</td>
                            <td class="text-center">${soContainer}</td>
                            <td class="text-center">${soContainerMoi}</td>
                            <td class="text-center" hidden>${tauGoc}</td>
                            <td class="text-center" hidden>${tauDich}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row">Xóa</button>
                            </td>
                        </tr>
                    `;
                    $('#displayTableChiTiet tbody').append(newRow);
                });

                updateRowsData();
                getYeuCauTableData();
                $('#chonHangTheoToKhaiModal').modal('hide');
            });

            $('#displayTableChiTiet').on('click', '.remove-row', function() {
                $(this).closest('tr').remove(); // Remove the closest <tr> (row)
                updateRowsData();
                getYeuCauTableData();
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

                data.forEach((item, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.so_to_khai_nhap}</td>
                            <td>${item.so_container_goc}</td>
                            <td>${item.total_so_luong_chuyen}</td>
                            <td>${item.so_container_dich}</td>
                            <td>${item.so_to_khai_cont_moi}</td>
                            <td>${item.so_luong_ton_cont_moi}</td>
                            <td>${item.so_luong_sau_chuyen}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            }



            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton
                .addEventListener('click', function() {
                    const ten_doan_tau = $('#ten_doan_tau').val();
                    if (ten_doan_tau === '') {
                        alert('Vui lòng nhập tên đoàn tàu');
                        return false;
                    }
                    const rowCount = $('#displayTableChiTiet tbody tr').length;
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
        function updateRowsData() {
            const rows = $('#displayTableChiTiet tbody tr').map(function() {
                const cells = $(this).find('td');
                return {
                    ma_hang_cont: $(this).find('td').eq(0).text(),
                    so_to_khai_nhap: $(this).find('td').eq(1).text(),
                    ten_hang: $(this).find('td').eq(2).text(),
                    so_luong_chuyen: $(this).find('td').eq(3).text(),
                    so_container_goc: $(this).find('td').eq(4).text(),
                    so_container_dich: $(this).find('td').eq(5).text(),
                    tau_goc: $(this).find('td').eq(6).text(),
                    tau_dich: $(this).find('td').eq(7).text(),
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
