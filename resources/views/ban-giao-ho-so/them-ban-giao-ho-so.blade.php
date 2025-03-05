@extends('layout.user-layout')

@section('title', 'Thêm biên bản bàn giao hồ sơ')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/quan-ly-ban-giao-ho-so">
                <p>
                    < Quay lại quản lý biên bản bàn giao hồ sơ</p>
            </a>
            <h2>Thêm biên bản bàn giao hồ sơ</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="form-group">
                                <div class="row justify-content-center">
                                    <div class="card p-3 me-3 col-5">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="label-text" for="ma_to_khai">Từ ngày</label>
                                                    <input type="text" id="datepicker1" class="form-control"
                                                        placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                                </div>
                                                <div class="col-4">
                                                    <label class="label-text" for="ma_to_khai">Đến ngày</label>
                                                    <input type="text" id="datepicker2" class="form-control"
                                                        placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                                </div>
                                                <div class="col-4">
                                                    <label class="label-text" for="ma_to_khai">Công chức</label>
                                                    <input type="text" id="" class="form-control"
                                                        value="{{ $congChuc->ten_cong_chuc }}" readonly>
                                                </div>
                                            </div>
                                            <center>
                                                <button type="submit" id="idNhap" class="btn btn-primary">Xem</button>
                                            </center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered" id="displayTableYeuCau"
                    style="vertical-align: middle; text-align: center;">
                    <thead>
                        <tr style="vertical-align: middle; text-align: center;">
                            <th>STT</th>
                            <th>Số tờ khai</th>
                            <th>Doanh nghiệp</th>
                            <th>Loại hàng</th>
                            <th>Ngày xuất hết</th>
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
                        <form action="{{ route('ban-giao.them-ban-giao-ho-so-submit') }}" method="POST" id="mainForm">
                            @csrf
                            <input type="hidden" name="tu_ngay" id="tu_ngay_hidden">
                            <input type="hidden" name="den_ngay" id="den_ngay_hidden">
                            <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                const nhapYeuCauButton = document.getElementById('xacNhanBtn');
                nhapYeuCauButton.addEventListener('click', function() {
                    let tuNgay = document.getElementById('datepicker1').value;
                    let denNgay = document.getElementById('datepicker2').value;
                    if (!tuNgay || !denNgay) {
                        alert('Vui lòng nhập ngày');
                        return false;
                    }
                    document.getElementById('tu_ngay_hidden').value = tuNgay;
                    document.getElementById('den_ngay_hidden').value = denNgay;
                    $('#xacNhanModal').modal('show');
                });
                // Initialize the datepicker with Vietnamese localization
                $('#datepicker1').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'vi',
                    endDate: '0d'
                });
                $('#datepicker2').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'vi',
                    endDate: '0d'
                });
            });

            function convertDateFormat(dateStr) {
                return dateStr.split("-").reverse().join("-");
            }

            $('#idNhap').on('click', function() {
                let tuNgay = document.getElementById('datepicker1').value;
                let denNgay = document.getElementById('datepicker2').value;
                $.ajax({
                    url: "{{ route('ban-giao.getToKhaiDaXuatHet') }}", // Adjust with your route
                    type: "GET",
                    data: {
                        tu_ngay: tuNgay,
                        den_ngay: denNgay,
                    },
                    success: function(response) {
                        let tbody = $("#displayTableYeuCau tbody");
                        tbody.empty(); // Clear previous data
                        console.log(response);
                        if (response.nhapHangs && response.nhapHangs.length > 0) {
                            $.each(response.nhapHangs, function(index, item) {
                                tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.so_to_khai_nhap}</td>>
                                    <td>${item.ten_doanh_nghiep}</td>>
                                    <td>${item.loai_hang}</td>>
                                    <td>${convertDateFormat(item.ngay_xuat_het)}</td>
                                </tr>
                            `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="6">Không có dữ liệu</td></tr>');
                        }
                    }
                });
            });
        </script>

    @stop
