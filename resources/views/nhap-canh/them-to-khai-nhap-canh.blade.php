@extends('layout.user-layout')

@section('title', 'Thêm tờ khai nhập cảnh')

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
            @endif
            <a class="return-link" href="/quan-ly-nhap-canh">
                <p>
                    < Quay lại quản lý tờ khai nhập cảnh</p>
            </a>
            <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center">TỜ KHAI NHẬP CẢNH</h2>
            <div class="row">
                <div class="col-2">
                </div>
                <div class="col-8">
                    <div class="card px-3 pt-3 mt-4">

                        <div class="row justify-content-center">
                            <h3 class="text-center">Thông tin tờ khai</h3>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group mt-3">
                                        <label for="ptvtxc" class="mb-1 fw-bold">Phương tiện vận tải nhập cảnh</label>
                                        <select class="form-control" id="ptvt-xc-dropdown-search" name="ptvtxc">
                                            <option></option>
                                            @foreach ($PTVTXuatCanhs as $PTVTXuatCanh)
                                                <option value="{{ $PTVTXuatCanh->so_ptvt_xuat_canh }}">
                                                    {{ $PTVTXuatCanh->ten_phuong_tien_vt }} Số:
                                                    {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-1 fw-bold" for="">Chọn thuyền trưởng</label>
                                        <select class="form-control" id="thuyen-truong-dropdown-search"
                                            name="ten_thuyen_truong" required>
                                            <option></option>
                                            @foreach ($thuyenTruongs as $thuyenTruong)
                                                <option value="{{ $thuyenTruong }}"> {{ $thuyenTruong }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-1 fw-bold" for="">Ngày nhập cảnh</label>
                                        <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                            id="ngay-dang-ky" readonly>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <center>
                                    <div class="toggle-container">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="toggleControl">
                                            <span class="slider"></span>
                                        </label>
                                        <span>Nhập cảnh không hàng</span>
                                    </div>
                                </center>
                            </div>



                            <div class="row">
                                <div class="col-3">
                                    <label class="label-text mb-2 fw-bold" for="loai_hang">Loại hàng</label>
                                    <select class="form-control" id="loai-hang-dropdown-search" name="loai_hang">
                                        @foreach ($loaiHangs as $loaiHang)
                                            <option></option>
                                            <option value="{{ $loaiHang->ten_loai_hang }}">
                                                {{ $loaiHang->ten_loai_hang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-3">
                                    <label class="label-text mb-2 fw-bold" for="don_vi_tinh">Đơn vị tính</label>
                                    <select name="don_vi_tinh" class="form-control mt-2" id="don-vi-tinh-dropdown-search">
                                        <option value="">Chọn đơn vị tính</option>
                                        @foreach ($donViTinhs as $donViTinh)
                                            <option value="{{ $donViTinh }}">
                                                {{ $donViTinh }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-3">
                                    <label class="label-text mb-2 fw-bold" for="don_vi_tinh">Số lượng</label>
                                    <input type="number" class="form-control" id="so-luong" name="so_luong"
                                        placeholder="Nhập số lượng hàng" required>
                                </div>

                                <div class="col-3">
                                    <label class="label-text mb-2 fw-bold" for="don_vi_tinh">Trọng lượng (Tấn)</label>
                                    <input type="number" class="form-control" id="trong-luong" name="trong_luong"
                                        placeholder="Nhập tổng trọng lượng (Tấn)" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mt-1">
                                    <label class="label-text mb-1 fw-bold" for="">
                                        Thông tin hàng hóa
                                    </label>
                                    <textarea class="form-control" id="ten-hang-hoa" name="ten_hang_hoa" placeholder="Nhập thông tin hàng hóa"
                                        rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <label class="label-text mb-1 fw-bold" for="">
                                    Chủ hàng
                                </label>
                                <select class="form-control mt-2" id="doanh-nghiep-dropdown-search">
                                    <option value="">Chọn chủ hàng</option>
                                    @foreach ($doanhNghieps as $doanhNghiep)
                                        @if ($doanhNghiep->ma_doanh_nghiep == $maDoanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}" selected>
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                            </option>
                                        @else
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-2">

                </div>
            </div>

            <center>

                <button id="xacNhanBtn" class="btn btn-success mt-5">Nhập tờ khai nhập cảnh</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm phiếu nhập hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm tờ khai nhập cảnh này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('nhap-canh.them-to-khai-nhap-canh-submit') }}" method="POST" id="mainForm"
                        name='xuatCanhForm'>
                        @csrf
                        <input type="hidden" name="ten_thuyen_truong" id="ten_thuyen_truong_hidden">
                        <input type="hidden" name="so_ptvt_xuat_canh" id="so_ptvt_xuat_canh_hidden">
                        <input type="hidden" name="ngay_dang_ky" id="ngay_dang_ky_hidden">
                        <input type="hidden" name="so_luong" id="so_luong_hidden">
                        <input type="hidden" name="loai_hang" id="loai_hang_hidden">
                        <input type="hidden" name="don_vi_tinh" id="don_vi_tinh_hidden">
                        <input type="hidden" name="trong_luong" id="trong_luong_hidden">
                        <input type="hidden" name="ten_hang_hoa" id="ten_hang_hoa_hidden">
                        <input type="hidden" name="ma_doanh_nghiep" id="ma_doanh_nghiep_hidden">
                        <input type="hidden" name="is_khong_hang" id="is_khong_hang_hidden">

                        <button id="submitData" type="submit" class="btn btn-success">Nhập tờ khai nhập cảnh</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            let dropdownValue = document.getElementById('ptvt-xc-dropdown-search');
            let tenThuyenTruong = document.getElementById('thuyen-truong-dropdown-search');
            let ngayDangKy = document.getElementById('ngay-dang-ky');
            let donViTinh = document.getElementById('don-vi-tinh-dropdown-search');
            let loaiHang = document.getElementById('loai-hang-dropdown-search');
            let soLuong = document.getElementById('so-luong');
            let trongLuong = document.getElementById('trong-luong');
            let tenHangHoa = document.getElementById('ten-hang-hoa');
            let maDoanhNghiep = document.getElementById('doanh-nghiep-dropdown-search');
            const toggle = document.getElementById('toggleControl');

            toggle.addEventListener('change', function() {
                const shouldDisable = this.checked;

                // Disable or enable all fields
                soLuong.disabled = shouldDisable;
                donViTinh.disabled = shouldDisable;
                loaiHang.disabled = shouldDisable;
                trongLuong.disabled = shouldDisable;
                tenHangHoa.disabled = shouldDisable;
                // tenChuHang.disabled = shouldDisable;
                // diaChiChuHang.disabled = shouldDisable;

                // Clear values only when disabling
                if (shouldDisable) {
                    soLuong.value = '';
                    donViTinh.value = '';
                    loaiHang.value = '';
                    trongLuong.value = '';
                    tenHangHoa.value = '';
                }
            });

            nhapYeuCauButton.addEventListener('click', function() {
                document.getElementById('ten_thuyen_truong_hidden').value = tenThuyenTruong.value.trim();
                document.getElementById('ngay_dang_ky_hidden').value = ngayDangKy.value.trim();
                document.getElementById('don_vi_tinh_hidden').value = donViTinh.value.trim();
                document.getElementById('so_luong_hidden').value = soLuong.value.trim();
                document.getElementById('loai_hang_hidden').value = loaiHang.value.trim();
                document.getElementById('trong_luong_hidden').value = trongLuong.value.trim();
                document.getElementById('ten_hang_hoa_hidden').value = tenHangHoa.value.trim();
                document.getElementById('ma_doanh_nghiep_hidden').value = maDoanhNghiep.value.trim();
                document.getElementById('is_khong_hang_hidden').value = toggle.checked ? 1 : 0;

                if (!tenThuyenTruong.value) {
                    alert('Vui lòng chọn tên thuyền trưởng');
                    return false;
                }
                if (!dropdownValue.value) {
                    alert('Vui lòng chọn phương tiện vận tải');
                    return false;
                }
                if (!ngayDangKy.value) {
                    alert('Vui lòng chọn ngày nhập cảnh');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });
            $('#ptvt-xc-dropdown-search').on('change', function() {
                document.getElementById('so_ptvt_xuat_canh_hidden').value = document.getElementById(
                    'ptvt-xc-dropdown-search').value.trim();
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
        $(document).ready(function() {
            // Initialize Select2 for all dropdowns with the select2-dropdown class
            $('.select2-dropdown').select2({
                placeholder: "",
                allowClear: true,
                width: '100%' // You can adjust this as needed
            });
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            // If your rows are dynamically shown/hidden
            // Re-initialize Select2 when rows become visible
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
