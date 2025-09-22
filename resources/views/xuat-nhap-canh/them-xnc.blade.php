@extends('layout.user-layout')

@section('title', 'Thêm theo dõi xuất nhập cảnh')

@section('content')

    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/danh-sach-xnc">
                <p>
                    < Quay lại quản lý theo dõi xuất nhập cảnh</p>
            </a>
            <h2 class="text-center">THEO DÕI NHẬP XUẤT CẢNH</h2>
            <div class="row">
                <div class="col-1"></div>
                <div class="col-10">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group mt-3">
                                        <label for="ptvtxc" class="mb-2 fw-bold">Phương tiện vận tải</label>
                                        <select class="form-control" id="ptvt-xc-dropdown-search"
                                            onchange="handleChange(this)" name="ptvtxc">
                                            <option value="">--Chọn phương tiện--</option>
                                            @foreach ($PTVTXuatCanhs as $PTVTXuatCanh)
                                                <option value="{{ $PTVTXuatCanh->so_ptvt_xuat_canh }}"
                                                    data-dwt="{{ $PTVTXuatCanh->dwt_roi }}">
                                                    {{ $PTVTXuatCanh->ten_phuong_tien_vt }} - Số:
                                                    {{ $PTVTXuatCanh->so_ptvt_xuat_canh }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-2 fw-bold" for="so-the">Số thẻ</label>
                                        <input type="number" class="form-control" id="so-the" name="so_the"
                                            placeholder="Nhập số thẻ" required>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-2 fw-bold" for="so-luong-may">Tổng số lượng máy</label>
                                        <input type="number" class="form-control" id="so-luong-may" name="so_luong_may"
                                            placeholder="Nhập số lượng máy" required>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-2 fw-bold" for="so-luong-may">Tổng trọng tải
                                            (Tấn)</label>
                                        <input type="number" class="form-control" id="tong-trong-tai" name="tong_trong_tai"
                                            placeholder="Nhập tổng trọng tải" required>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group mt-3">
                                        <label class="label-text mb-2 fw-bold" for="so-luong-may">Đại lý</label>
                                        <select class="form-control" id="chu-hang-dropdown-search" name="ma_chu_hang">
                                            <option></option>
                                            @foreach ($chuHangs as $chuHang)
                                                <option value="{{ $chuHang->ma_chu_hang }}">
                                                    {{ $chuHang->ten_chu_hang }}
                                                    ({{ $chuHang->ma_chu_hang }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <div class="radio-group">
                                        <div class="radio-title">Loại hàng</div>
                                        <label class="radio-container">Hàng nóng
                                            <input type="radio" checked="checked" name="radio1" value="1">
                                            <span class="checkmark"></span>
                                        </label>
                                        <label class="radio-container">Hàng lạnh
                                            <input type="radio" name="radio1" value="2">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label for="thoi_gian_nhap_canh" class="form-label">
                                        <strong>Giờ nhập cảnh</strong>
                                    </label>
                                    <input type="text" class="form-control" id="thoi-gian-nhap-canh"
                                        name="thoi_gian_nhap_canh" placeholder="Nhập giờ nhập cảnh">

                                    <label for="thoi_gian_xuat_canh" class="form-label">
                                        <strong>Giờ xuất cảnh</strong>
                                    </label>
                                    <input type="text" class="form-control" id="thoi-gian-xuat-canh"
                                        name="thoi_gian_xuat_canh" placeholder="Nhập giờ xuất cảnh">
                                </div>
                                <div class="col-4">
                                    <label for="thoi_gian_nhap_canh" class="form-label">
                                        <strong>Ghi chú</strong>
                                    </label>
                                    <textarea type="time" class="form-control" id="ghi-chu" rows="3" name="ghi_chu" placeholder="Nhập ghi chú"
                                        required></textarea>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-2">

                </div>
            </div>

            <center>
                <button id="xacNhanBtn" class="btn btn-success mt-5">Nhập theo dõi xuất nhập cảnh</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm theo dõi xuất nhập cảnh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm theo dõi xuất nhập cảnh này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-nhap-canh.them-xnc-submit') }}" method="POST" id="mainForm"
                        name='xuatCanhForm'>
                        @csrf
                        <input type="hidden" name="so_ptvt_xuat_canh" id="so_ptvt_xuat_canh_hidden">
                        <input type="hidden" name="so_the" id="so_the_hidden">
                        <input type="hidden" name="tong_trong_tai" id="tong_trong_tai_hidden">
                        <input type="hidden" name="ma_chu_hang" id="ma_chu_hang_hidden">
                        <input type="hidden" name="is_hang_lanh" id="is_hang_lanh_hidden">
                        <input type="hidden" name="is_hang_nong" id="is_hang_nong_hidden">
                        <input type="hidden" name="so_luong_may" id="so_luong_may_hidden">
                        <input type="hidden" name="thoi_gian_nhap_canh" id="thoi_gian_nhap_canh_hidden">
                        <input type="hidden" name="thoi_gian_xuat_canh" id="thoi_gian_xuat_canh_hidden">
                        <input type="hidden" name="ghi_chu" id="ghi_chu_hidden">

                        <button id="submitData" type="submit" class="btn btn-success">Nhập tờ khai nhập cảnh</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function handleChange(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const rawDwt = selectedOption.getAttribute('data-dwt') || '';
            const numericDwt = rawDwt.match(/\d+(\.\d+)?/); // Matches "123", "123.45", etc.

            document.getElementById('tong-trong-tai').value = numericDwt ? numericDwt[0] : '';
        }
        $(document).ready(function() {
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            let ptvtXuatCanh = document.getElementById('ptvt-xc-dropdown-search');
            let soThe = document.getElementById('so-the');
            let soLuongMay = document.getElementById('so-luong-may');
            let tongTrongTai = document.getElementById('tong-trong-tai');
            let maChuHang = document.getElementById('chu-hang-dropdown-search');
            let thoiGianNhapCanh = document.getElementById('thoi-gian-nhap-canh');
            let thoiGianXuatCanh = document.getElementById('thoi-gian-xuat-canh');
            let ghiChu = document.getElementById('ghi-chu');



            nhapYeuCauButton.addEventListener('click', function() {
                document.getElementById('so_ptvt_xuat_canh_hidden').value = ptvtXuatCanh.value.trim();
                document.getElementById('so_the_hidden').value = soThe.value.trim();
                document.getElementById('so_luong_may_hidden').value = soLuongMay.value.trim();
                document.getElementById('tong_trong_tai_hidden').value = tongTrongTai.value.trim();
                document.getElementById('ma_chu_hang_hidden').value = maChuHang.value.trim();
                document.getElementById('thoi_gian_xuat_canh_hidden').value = thoiGianXuatCanh.value.trim();
                document.getElementById('thoi_gian_nhap_canh_hidden').value = thoiGianNhapCanh.value.trim();
                document.getElementById('ghi_chu_hidden').value = ghiChu.value.trim();

                const selected = document.querySelector('input[name="radio1"]:checked');
                if (selected.value == 1) {
                    document.getElementById('is_hang_nong_hidden').value = 1;
                    document.getElementById('is_hang_lanh_hidden').value = 0;
                } else if (selected.value == 2) {
                    document.getElementById('is_hang_nong_hidden').value = 0;
                    document.getElementById('is_hang_lanh_hidden').value = 1;
                } else {
                    alert("Vui lòng chọn loại hàng");
                }

                if (!ptvtXuatCanh.value) {
                    alert('Vui lòng chọn phương tiện xuất cảnh');
                    return false;
                }
                if (!soThe.value) {
                    alert('Vui lòng nhập số thẻ');
                    return false;
                }
                if (!soLuongMay.value) {
                    alert('Vui lòng nhập số lượng máy');
                    return false;
                }
                if (!tongTrongTai.value) {
                    alert('Vui lòng nhập tổng trọng tải');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });
        });
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            let hours = String(now.getHours()).padStart(2, '0');
            let minutes = String(now.getMinutes()).padStart(2, '0');
            let formattedTime = `${hours}H${minutes}`;

            document.getElementById("thoi-gian-nhap-canh").value = formattedTime;
        });
    </script>
@stop
