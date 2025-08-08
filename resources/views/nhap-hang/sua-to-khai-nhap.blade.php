@extends('layout.user-layout')

@section('title', 'Sửa tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif(session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/quan-ly-nhap-hang">
                        <p>
                            < Quay lại quản lý nhập hàng</p>
                    </a>
                </div>
                <div class="col-6">
                    <a class="float-end" href="#">
                        <button data-bs-toggle="modal" data-bs-target="#chonFileModal" class="btn btn-success ">
                            Nhập từ file</button>
                    </a>
                </div>
            </div>
            <h2 class="text-center text-dark">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
            <!-- Input fields for each column -->
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <h3 class="text-center text-dark">Thông tin tờ khai</h3>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_hai_quan">Chi cục Hải quan</label>
                                    <select class="form-control" id="hai-quan-dropdown-search" name="ma_hai_quan">
                                        @foreach ($haiQuans as $haiQuan)
                                            <option></option>
                                            @if ($haiQuan->ma_hai_quan == $nhapHang->ma_hai_quan)
                                                <option value="{{ $haiQuan->ma_hai_quan }}" selected>
                                                    {{ $haiQuan->ten_hai_quan }}
                                                    ({{ $haiQuan->ma_hai_quan }})
                                                </option>
                                            @else
                                                <option value="{{ $haiQuan->ma_hai_quan }}">
                                                    {{ $haiQuan->ten_hai_quan }}
                                                    ({{ $haiQuan->ma_hai_quan }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="">Số tờ khai nhập</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="so_to_khai_nhap" maxlength="255"
                                    name="so_to_khai_nhap" placeholder="Nhập số tờ khai nhập"
                                    value="{{ $nhapHang->so_to_khai_nhap }}" required>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_loai_hinh">Loại hình tờ khai</label>
                                    <select class="form-control" id="loai-hinh-dropdown-search" name="ma_loai_hinh">
                                        @foreach ($loaiHinhs as $loaiHinh)
                                            <option></option>
                                            @if ($nhapHang->ma_loai_hinh == $loaiHinh->ma_loai_hinh)
                                                <option value="{{ $loaiHinh->ma_loai_hinh }}" selected>
                                                    {{ $loaiHinh->ten_loai_hinh }}
                                                    ({{ $loaiHinh->ma_loai_hinh }})
                                                </option>
                                            @else
                                                <option value="{{ $loaiHinh->ma_loai_hinh }}">
                                                    {{ $loaiHinh->ten_loai_hinh }}
                                                    ({{ $loaiHinh->ma_loai_hinh }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ngay_thong_quan">Ngày thông quan</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" id="datepicker" class="form-control" placeholder="dd/mm/yyyy"
                                        value="{{ \Carbon\Carbon::parse($nhapHang->ngay_thong_quan)->format('d/m/Y') }}"
                                        name="ngay_thong_quan" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label class="label-text" for="">Đại lý</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="chu-hang-dropdown-search" name="ma_chu_hang">
                                    @foreach ($chuHangs as $chuHang)
                                        <option></option>
                                        @if ($nhapHang->ma_chu_hang == $chuHang->ma_chu_hang)
                                            <option value="{{ $chuHang->ma_chu_hang }}" selected>
                                                {{ $chuHang->ten_chu_hang }}
                                                ({{ $chuHang->ma_chu_hang }})
                                            </option>
                                        @else
                                            <option value="{{ $chuHang->ma_chu_hang }}">
                                                {{ $chuHang->ten_chu_hang }}
                                                ({{ $chuHang->ma_chu_hang }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="label-text" for="phuong_tien_vt_nhap">Phương tiện vận
                                    tải</label> <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="phuong_tien_vt_nhap"
                                    name="phuong_tien_vt_nhap" placeholder="Nhập phương tiện vận tải" maxlength="50"
                                    required value={{ $nhapHang->phuong_tien_vt_nhap }}>
                            </div>
                            <div class="col">
                                <label class="label-text" for="trong_luong">Trọng lượng
                                    (Tấn)</label> <span class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2" id="trong_luong" name="trong_luong"
                                    placeholder="Nhập tổng trọng lượng (Tấn)" value={{ $nhapHang->trong_luong }} required>
                            </div>
                            <div class="col">
                                <label class="label-text mb-2" for="xuat_xu">Xuất xứ</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="xuat-xu-dropdown-search" name="xuat_xu">
                                    <option value="">Nhập xuất xứ của sản phẩm</option>
                                    @foreach ($xuatXus as $xuatXu2)
                                        @if ($xuatXu2 == $xuatXu)
                                            <option value="{{ $xuatXu2 }}" selected>
                                                {{ $xuatXu2 }}
                                            </option>
                                        @else
                                            <option value="{{ $xuatXu2 }}">
                                                {{ $xuatXu2 }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="label-text" for="ten_doan_tau">Đoàn tàu</label>
                                <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="ten_doan_tau" name="ten_doan_tau" value="{{ $nhapHang->ten_doan_tau ?? '' }}"
                                    placeholder="Nhập đoàn tàu" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h3 class="text-center text-dark">Thông tin hàng hóa</h3>

            <div class="row">
                <div class="col-8">
                    <div class="card p-3 mt-3">
                        <div class="row">
                            <div class="col-12">
                                <label class="label-text" for="ten_hang">Tên hàng</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2 reset-input" id="ten_hang"
                                    maxlength="255" name="ten_hang" placeholder="Nhập tên hàng hóa" required>
                                <input hidden type="text" id="ma_hang"name="ma_hang" value=0>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="loai_hang">Loại hàng</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <select class="form-control" id="loai-hang-dropdown-search" name="loai_hang">
                                        @foreach ($loaiHangs as $loaiHang)
                                            <option></option>
                                            <option value="{{ $loaiHang->ten_loai_hang }}">
                                                {{ $loaiHang->ten_loai_hang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <label class="label-text" for="">Số container</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="so_container" maxlength="50"
                                    name="so_container" placeholder="Nhập số container" required>
                            </div>
                            <div class="col-4">
                                <label class="label-text" for="so_seal">Số seal</label>
                                <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="so_seal" name="so_seal"
                                    placeholder="Nhập số seal" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card p-3 mt-3">
                        <div class="row">
                            <div class="col-6">
                                <label class="label-text" for="so_luong_khai_bao">Số lượng</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="so_luong_khai_bao"
                                    name="so_luong_khai_bao" placeholder="Nhập số lượng sản phẩm" required>
                            </div>
                            <div class="col-6">
                                <label class="label-text mb-2" for="don_vi_tinh">Đơn vị tính</label> <span
                                    class="text-danger missing-input-text "></span>

                                <select name="don_vi_tinh" class="form-control mt-2 reset-input"
                                    id="don-vi-tinh-dropdown-search">
                                    <option value="">Chọn đơn vị tính</option>
                                    @foreach ($donViTinhs as $donViTinh)
                                        <option value="{{ $donViTinh }}">
                                            {{ $donViTinh }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="label-text" for="don_gia">Đơn giá (USD)</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="don_gia"
                                    placeholder="USD" name="don_gia" required>
                            </div>
                            <div class="col-6">
                                <label class="label-text" for="tri_gia">Trị giá (USD)</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="tri_gia"
                                    placeholder="USD" name="tri_gia" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-5">
                <center>
                    <button type="button" class="btn btn-primary mt-1" id="addRowButton">Thêm dòng
                        mới</button>
                </center>
            </div>



            <!-- Your existing table -->
            <table class="table table-bordered" id="displayTableNhapHang">
                <thead style="vertical-align: middle; text-align: center;">
                    <tr>
                        <th>STT</th>
                        <th hidden>Mã hàng</th>
                        <th>Tên hàng</th>
                        <th>Loại hàng</th>
                        <th>Số lượng</th>
                        <th>Đơn vị tính</th>
                        <th>Đơn giá (USD)</th>
                        <th>Trị giá (USD)</th>
                        <th>Số container</th>
                        <th>Số seal</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <center>
                <button type="button" id="xacNhanBtn" class="btn btn-success mb-5">Sửa tờ khai</button>
            </center>

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
                    <p class="fw-bold">Tên hàng, Số lượng, ĐVT, Trị Giá (USD)</p>
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
                    <div class="row">
                        <div class="col-12">
                            <label class="label-text mb-2 fw-bold" for="loai_hang">Loại hàng</label> <span
                                class="text-danger missing-input-text"></span>
                            <select class="form-control" id="loai-hang-2-dropdown-search" name="loai_hang_2">
                                <option></option>
                                @foreach ($loaiHangs as $loaiHang)
                                    <option value="{{ $loaiHang->ten_loai_hang }}">
                                        {{ $loaiHang->ten_loai_hang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="label-text fw-bold" for="">Số container</label>
                            <input type="text" class="form-control mt-2 px-3" id="so-container-2" maxlength="50"
                                name="so_container" placeholder="Nhập số container" required>
                        </div>
                        <div class="col-6">
                            <label class="label-text fw-bold" for="">Số seal</label>
                            <input type="text" class="form-control mt-2 px-3" id="so-seal-2" maxlength="50"
                                name="so_seal" placeholder="Nhập số seal" required>
                        </div>
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
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận sửa tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận sửa tờ khai này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('nhap-hang.submit-sua-to-khai-nhap') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ma_chu_hang" id="ma_chu_hang_hidden">
                        <input type="hidden" name="ma_hai_quan" id="ma_hai_quan">
                        <input type="hidden" name="so_container" id="so_container_hidden">
                        <input type="hidden" name="ma_loai_hinh" id="ma_loai_hinh">
                        <input type="hidden" name="xuat_xu" id="xuat_xu_hidden">
                        <input type="hidden" name="phuong_tien_vt_nhap" id="phuong_tien_vt_nhap_hidden">
                        <input type="hidden" name="trong_luong" id="trong_luong_hidden">
                        <input type="hidden" name="so_to_khai_nhap" id="so_to_khai_nhap_hidden">
                        <input type="hidden" name="so_to_khai_nhap_goc" value="{{ $nhapHang->so_to_khai_nhap }}">
                        <input type="hidden" name="ngay_thong_quan" id="ngay_thong_quan_hidden">
                        <input type="hidden" name="ten_doan_tau" id="ten_doan_tau_hidden">
                        <button type="submit" class="btn btn-success">Sửa tờ khai</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const hangHoaRows = @json($hangHoaRows);
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
        // Update hidden fields when dropdowns change
        document.getElementById('hai-quan-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_hai_quan').value = this.value;
        });
        document.getElementById('loai-hinh-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_loai_hinh').value = this.value;
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            // Fetch data from @json($hangHoaRows)
            const hangHoaRows = @json($hangHoaRows);
            let rowsData = [];

            // Populate the rowsData array
            rowsData = hangHoaRows.map(row => ({
                ma_hang: row.ma_hang,
                ten_hang: row.ten_hang,
                loai_hang: row.loai_hang,
                so_luong_khai_bao: row.so_luong_khai_bao,
                don_vi_tinh: row.don_vi_tinh,
                don_gia: row.don_gia,
                tri_gia: row.tri_gia,
                so_container: row.so_container_khai_bao,
                so_seal: row.so_seal
            }));
            displayRows();

            function displayRows() {
                const tableBody = $("#displayTableNhapHang tbody");
                tableBody.empty();
                rowsData.forEach((row, index) => {
                    const newRow = `
                        <tr data-index="${index}">
                            <td>${index + 1}</td>
                            <td hidden>${row.ma_hang}</td>
                            <td>${row.ten_hang}</td>
                            <td>${row.loai_hang}</td>
                            <td>${row.so_luong_khai_bao}</td>
                            <td>${row.don_vi_tinh}</td>
                            <td>${row.don_gia}</td>
                            <td>${row.tri_gia}</td>
                            <td>${row.so_container}</td>
                            <td>${row.so_seal}</td>
                            <td>
                                <button class="btn btn-warning btn-sm editRowButton">Sửa</button>
                                <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(newRow);
                });
            }

            $("#addRowButton").click(function() {
                const ma_hang = $("#ma_hang").val();
                const ten_hang = $("#ten_hang").val();
                const loai_hang = $("#loai-hang-dropdown-search").val();
                const so_luong_khai_bao = $("#so_luong_khai_bao").val();
                const don_vi_tinh = $("#don-vi-tinh-dropdown-search").val();
                const don_gia = $("#don_gia").val();
                const tri_gia = $("#tri_gia").val();
                const so_container = $("#so_container").val();
                const so_seal = $("#so_seal").val();

                let isValid = true;

                const fields = [{
                        id: "#ten_hang",
                        value: ten_hang
                    },
                    {
                        id: "#loai_hang",
                        value: loai_hang
                    },
                    {
                        id: "#so_luong_khai_bao",
                        value: so_luong_khai_bao
                    },
                    {
                        id: "#don_vi_tinh",
                        value: don_vi_tinh
                    },
                    {
                        id: "#don_gia",
                        value: don_gia
                    },
                    {
                        id: "#tri_gia",
                        value: tri_gia
                    },
                    {
                        id: "#so_container",
                        value: so_container
                    },
                    {
                        id: "#so_seal",
                        value: so_seal
                    },
                ];

                fields.forEach(field => {
                    const warningText = $(field.id).siblings(".missing-input-text");
                    if (!field.value) {
                        warningText.text("*Thiếu thông tin").show();
                        isValid = false;
                    } else {
                        warningText.hide();
                    }
                });

                if (isValid) {
                    rowsData.push({
                        ma_hang,
                        ten_hang,
                        loai_hang,
                        so_luong_khai_bao,
                        don_vi_tinh,
                        don_gia,
                        tri_gia,
                        so_container,
                        so_seal,
                    });
                    displayRows();
                    $(".reset-input").val('');
                    $("#ma_hang").val(0);
                    $("#don-vi-tinh-dropdown-search").val('').trigger("change");
                    $("#loai-hang-dropdown-search").val('').trigger('change');
                    $(".missing-input-text").hide();
                }
            });

            $(document).on("click", ".editRowButton", function() {
                const rowIndex = $(this).closest("tr").data("index");
                const rowData = rowsData[rowIndex];
                $("#ma_hang").val(rowData.ma_hang);
                $("#ten_hang").val(rowData.ten_hang);
                $("#loai_hang").val(rowData.loai_hang);
                $("#so_luong_khai_bao").val(rowData.so_luong_khai_bao);
                $("#don_vi_tinh").val(rowData.don_vi_tinh);
                $("#don_gia").val(rowData.don_gia);
                $("#tri_gia").val(rowData.tri_gia);
                $("#so_container").val(rowData.so_container);
                $("#so_seal").val(rowData.so_seal);

                $("#don-vi-tinh-dropdown-search").val(rowData.don_vi_tinh).trigger("change");
                $("#loai-hang-dropdown-search").val(rowData.loai_hang).trigger("change");
                rowsData.splice(rowIndex, 1);
                displayRows();
            });

            $(document).on("click", ".deleteRowButton", function() {
                const rowIndex = $(this).closest("tr").data("index");
                rowsData.splice(rowIndex, 1);
                displayRows();
            });

            // Form submission handler
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                // Get values from dropdowns
                const maHaiQuan = document.getElementById('hai-quan-dropdown-search').value;
                const maLoaiHinh = document.getElementById('loai-hinh-dropdown-search').value;
                const maChuHang = document.getElementById('chu-hang-dropdown-search').value;
                const xuatXu = document.getElementById('xuat-xu-dropdown-search').value;
                const ngayThongQuan = $('#datepicker').val();
                const soToKhaiNhap = $("#so_to_khai_nhap").val();
                const phuongTienVanTaiNhap = $("#phuong_tien_vt_nhap").val();
                const trongLuong = $("#trong_luong").val();
                const tenDoanTau = $("#ten_doan_tau").val();

                if (!maHaiQuan) {
                    alert('Vui lòng chọn hải quan');
                    return;
                } else if (!ngayThongQuan) {
                    alert('Vui lòng chọn ngày thông quan');
                    return;
                } else if (!soToKhaiNhap) {
                    alert('Vui lòng điền số tờ khai nhập');
                    return;
                } else if (!phuongTienVanTaiNhap) {
                    alert('Vui lòng điền phương tiện vận tải');
                    return;
                } else if (!maLoaiHinh) {
                    alert('Vui lòng chọn loại hình');
                    return;
                } else if (!maChuHang) {
                    alert('Vui lòng chọn đại lý');
                    return;
                } else if (!trongLuong) {
                    alert('Vui lòng điền trọng lượng');
                    return;
                }

                // Get all rows from the table
                const tableRows = document.querySelectorAll('#displayTableNhapHang tbody tr');

                // Check if there are any rows
                if (tableRows.length === 0) {
                    alert('Vui lòng thêm ít nhất một hàng hóa');
                    return;
                }

                // Map the table data to an array of objects
                const rowsData = Array.from(tableRows).map(row => ({
                    ma_hang: row.cells[1].textContent,
                    ten_hang: row.cells[2].textContent,
                    loai_hang: row.cells[3].textContent,
                    so_luong: row.cells[4].textContent,
                    don_vi_tinh: row.cells[5].textContent,
                    don_gia: row.cells[6].textContent,
                    tri_gia: row.cells[7].textContent,
                    so_container: row.cells[8].textContent,
                    so_seal: row.cells[9].textContent,
                }));

                // Set values for hidden inputs
                document.getElementById('rowsDataInput').value = JSON.stringify(rowsData);
                document.getElementById('ma_hai_quan').value = maHaiQuan;
                document.getElementById('ma_loai_hinh').value = maLoaiHinh;
                document.getElementById('phuong_tien_vt_nhap_hidden').value = phuongTienVanTaiNhap;
                document.getElementById('trong_luong_hidden').value = trongLuong;
                document.getElementById('so_to_khai_nhap_hidden').value = soToKhaiNhap;
                document.getElementById('ngay_thong_quan_hidden').value = ngayThongQuan;
                document.getElementById('ma_chu_hang_hidden').value = maChuHang;
                document.getElementById('xuat_xu_hidden').value = xuatXu;
                document.getElementById('ten_doan_tau_hidden').value = tenDoanTau;

                // Submit the form
                $('#xacNhanModal').modal('show');
            });

            $("#uploadHys").on("click", function() {
                var file = $("#hys_file")[0].files[0];
                if (!file) {
                    alert("Xin hãy chọn 1 file!");
                    return;
                }
                const loai_hang = $("#loai-hang-2-dropdown-search").val();
                const so_container = $("#so-container-2").val();
                const so_seal = $("#so-seal-2").val();

                var formData = new FormData();
                formData.append("hys_file", file);
                formData.append("loai_hang", loai_hang);
                formData.append("so_container", so_container);
                formData.append("so_seal", so_seal);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('nhap-hang.upload-file-nhap') }}", // Define the route in Laravel
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (!response.data) {
                            alert(response);
                        } else {
                            var tbody = $("#displayTableNhapHang tbody");
                            tbody.empty();

                            $.each(response.data, function(index, row) {
                                var tr = `<tr>
                                        <td>${index + 1}</td>
                                        <td>${row.ten_hang}</td>
                                        <td>${row.loai_hang}</td>
                                        <td>${row.so_luong_khai_bao}</td>
                                        <td>${row.don_vi_tinh}</td>
                                        <td>${row.don_gia}</td>
                                        <td>${row.tri_gia}</td>
                                        <td>${row.so_container}</td>
                                        <td>${row.so_seal}</td>
                                        <td>
                                            <button class="btn btn-warning btn-sm editRowButton">Sửa</button>
                                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                        </td>                                    
                                    </tr>`;
                                tbody.append(tr);
                                rowsData.push({
                                    ten_hang: row.ten_hang,
                                    loai_hang: row.loai_hang,
                                    so_luong_khai_bao: row.so_luong_khai_bao,
                                    don_vi_tinh: row.don_vi_tinh,
                                    don_gia: row.don_gia,
                                    tri_gia: row.tri_gia,
                                    so_container: row.so_container,
                                    so_seal: row.so_seal,
                                });
                            });
                            displayRows();
                            alert("Nhập file thành công");
                            $('#chonFileModal').modal('hide');
                        }

                    },
                    error: function(xhr) {
                        alert(xhr.responseText);
                    }
                });
            });

            // Additional listeners for calculations
            const soLuongInput = document.getElementById("so_luong_khai_bao");
            const donGiaInput = document.getElementById("don_gia");
            const triGiaInput = document.getElementById("tri_gia");

            function calculateTriGia() {
                const soLuong = parseFloat(soLuongInput.value) || 0;
                const donGia = parseFloat(donGiaInput.value) || 0;
                const triGia = soLuong * donGia;

                triGiaInput.value = triGia;
            }

            soLuongInput.addEventListener("input", calculateTriGia);
            donGiaInput.addEventListener("input", calculateTriGia);


        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi', // Set language to Vietnamese
                endDate: '0d',
                keyboardNavigation: true, // Allow keyboard navigation
                forceParse: true // Ensure manually typed dates are parsed
            }).on('changeDate', function(e) {
                // When a date is selected via the datepicker UI
                handleDateChange(e.date);
            });

            // Handle manually typed date
            $('#datepicker').on('blur', function() {
                const typedDate = $(this).val();
                const parsedDate = moment(typedDate, "DD/MM/YYYY", true);

                if (parsedDate.isValid()) {
                    // Update the datepicker with the manually entered date
                    $('#datepicker').datepicker('setDate', parsedDate.toDate());
                    handleDateChange(parsedDate.toDate());
                } else {
                    alert("Invalid date format. Please enter in DD/MM/YYYY format.");
                }
            });

            function handleDateChange(selectedDate) {
                const currentDate = new Date();
                const diffTime = Math.abs(currentDate - selectedDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                console.log("Selected Date:", selectedDate);
                console.log("Days Difference:", diffDays);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#xuat-xu-2-dropdown-search').select2({
                placeholder: "Chọn xuất xứ",
                allowClear: true,
            });
            $('#chonFileModal ').on('shown.bs.modal', function() {
                $('#xuat-xu-2-dropdown-search').select2('destroy');
                $('#xuat-xu-2-dropdown-search').select2({
                    placeholder: "Chọn xuất xứ",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonFileModal .modal-body'),
                });
            });
            $('#loai-hang-2-dropdown-search').select2({
                placeholder: "Chọn xuất xứ",
                allowClear: true,
            });
            $('#chonFileModal ').on('shown.bs.modal', function() {
                $('#loai-hang-2-dropdown-search').select2('destroy');
                $('#loai-hang-2-dropdown-search').select2({
                    placeholder: "Chọn loại hàng",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonFileModal .modal-body'),
                });
            });
        });
    </script>
@stop
