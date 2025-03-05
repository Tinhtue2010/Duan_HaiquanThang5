@extends('layout.user-layout')

@section('title', 'Duyệt phiếu xuất')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/to-khai-da-xuat-hang">
                <p>
                    < Quay lại quản lý phiếu xuất hàng</p>
            </a>
            <h2 class="text-center">Duyệt thực xuất phiếu xuất</h2>
            <h2 class="text-center">Công chức : {{ $congChucHienTai->ten_cong_chuc }}</h2>

            {{-- <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <center>
                                <div class="col-6">
                                    <label for="" class="mb-1 fw-bold">Phương tiện vận tải xuất cảnh</label>
                                    <div class="form-group">
                                        <select class="form-control" name="so_ptvt_xuat_canh" id="ptvt-xc-dropdown-search">
                                            <option value=""></option>
                                            @foreach ($ptvtXuatCanhs as $ptvtXuatCanh)
                                                <option value="{{ $ptvtXuatCanh->so_ptvt_xuat_canh }}">
                                                    {{ $ptvtXuatCanh->ten_phuong_tien_vt }} (Số:
                                                    {{ $ptvtXuatCanh->so_ptvt_xuat_canh }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            <center>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="row card p-2">
                <div class="table-responsive">
                    <table class="table table-bordered" id="hangTrongContTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th>
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>
                                    STT
                                </th>
                                <th>
                                    Số
                                </th>
                                <th>
                                    Loại hình
                                </th>
                                <th>
                                    Công ty
                                </th>
                                <th>
                                    Ngày đăng ký
                                </th>
                                <th>
                                    Số lượng
                                </th>
                                <th>
                                    Tên xuồng
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <center>
                <button id="xacNhanBtn" class="btn btn-success mt-5"> Duyệt thực xuất</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận duyệt phiếu xuất hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận duyệt phiếu xuất hàng này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-hang.duyet-nhanh-thuc-xuat-submit') }}" method="POST" id="mainForm"
                        name='xuatHangForm'>
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="so_ptvt_xuat_canh" id="so_ptvt_xuat_canh_hidden">
                        <button id="submitData" type="submit" class="btn btn-success">Nhập phiếu xuất hàng</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Xử lý chọn số lượng xuất
            // let so_ptvt_xuat_canh = $('#ptvt-xc-dropdown-search').val();
            let so_ptvt_xuat_canh = 1;
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            let rowIndex = 0;
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            updateXuatHangTable();

            nhapYeuCauButton.addEventListener('click', function() {
                //Table 1
                const rows = $('#hangTrongContTable tbody tr')
                    .filter(function() {
                        return $(this).find('.row-checkbox').is(':checked');
                    })
                    .map(function() {
                        const cells = $(this).find('td');
                        return {
                            so_to_khai_xuat: $(cells[2]).text(),
                        };
                    })
                    .get();

                // document.getElementById('so_ptvt_xuat_canh_hidden').value = document.getElementById(
                //     'ptvt-xc-dropdown-search').value.trim();

                if (rows.length === 0) {
                    alert('Vui lòng chọn ít nhất một phiếu xuất để duyệt.');
                    return false;
                }
                $('#rowsDataInput').val(JSON.stringify(rows));
                $('#xacNhanModal').modal('show');
            });

            function convertDateFormat(dateStr) {
                return dateStr.split("-").reverse().join("-");
            }

            function updateXuatHangTable() {
                const so_to_khai_nhap = $("#so_to_khai_nhap").val();

                $.ajax({
                    url: "{{ route('xuat-hang.getPhieuXuatDaXuatHangCuaPTVT') }}", // Adjust with your route
                    type: "GET",
                    data: {
                        so_ptvt_xuat_canh: so_ptvt_xuat_canh,
                    },
                    success: function(response) {
                        let tbody = $("#hangTrongContTable tbody");
                        tbody.empty();
                        if (response.xuatHangs && response.xuatHangs.length > 0) {
                            $.each(response.xuatHangs, function(index, item) {
                                tbody.append(`
                                <tr>
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td>${index + 1}</td>
                                    <td>${item.so_to_khai_xuat}</td>
                                    <td>${item.ma_loai_hinh}</td>
                                    <td>${item.ten_doanh_nghiep}</td>
                                    <td>${convertDateFormat(item.ngay_dang_ky)} </td>
                                    <td>${item.tong_so_luong}</td>
                                    <td>${item.ten_phuong_tien_vt}</td>
                                </tr>
                            `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="9">Không có dữ liệu</td></tr>');
                        }
                    }
                });

            }
            $('#ptvt-xc-dropdown-search').on('change', function() {
                updateXuatHangTable();
            });
            $('#so_to_khai_nhap').on('input', function() {
                updateXuatHangTable();
            });
        });
    </script>
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
@stop
