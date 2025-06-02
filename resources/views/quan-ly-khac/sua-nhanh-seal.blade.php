@extends('layout.user-layout')

@section('title', 'Sửa nhanh chì niêm phong')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/quan-ly-chi-niem-phong">
                <p>
                    < Quay lại quản lý chì niêm phong</p>
            </a>
            <h2 class="text-center">SỬA NHANH CHÌ NIÊM PHONG</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <div class="row">
                                <div class="form-group">
                                    <label for="loai_seal"><strong>Loại seal</strong></label>
                                    <select class="form-control" name="loai_seal" placeholder="Chọn loại seal"
                                        id="loai-seal" required>
                                        <option></option>
                                        <option value="1">Seal dây cáp đồng</option>
                                        <option value="2">Seal dây cáp thép</option>
                                        <option value="3">Seal container</option>
                                        <option value="4">Seal dây nhựa dẹt</option>
                                    </select>
                                    <label class="label-text mt-3" for=""><strong>Cán bộ công chức phụ
                                            trách</strong></label>
                                    <select class="form-control" id="cong-chuc-dropdown-search" name="ma_cong_chuc"
                                        required>
                                        <option></option>
                                        @foreach ($congChucs as $congChuc)
                                            <option value="{{ $congChuc->ma_cong_chuc }}">
                                                {{ $congChuc->ten_cong_chuc }}
                                                ({{ $congChuc->taiKhoan->ten_dang_nhap ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="label-text mt-3" for=""><strong>Trạng thái
                                        </strong></label>
                                    <select class="form-control" id="trang-thai-dropdown-search" name="trang_thai" required>
                                        <option value="0" selected>Chưa sử dụng</option>
                                        <option value="1">Đã sử dụng</option>
                                        <option value="2">Seal hỏng</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div>
                                    <label class="label-text mb-1 fw-bold" for="">Ngày cấp</label>
                                    <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                        id="ngay-cap" readonly>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label class="label-text mt-3" for=""><strong>Chọn cán bộ công chức mới</strong></label>
                        <select class="form-control" id="cong-chuc-dropdown-search-2" name="ma_cong_chuc" required>
                            <option></option>
                            @foreach ($congChucs as $congChuc)
                                <option value="{{ $congChuc->ma_cong_chuc }}">
                                    {{ $congChuc->ten_cong_chuc }}
                                    ({{ $congChuc->taiKhoan->ten_dang_nhap ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row card p-3 mt-4">
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
                                        Số seal
                                    </th>
                                    <th>
                                        Loại seal
                                    </th>
                                    <th>
                                        Ngày cấp
                                    </th>
                                    <th>
                                        Công chức
                                    </th>
                                    <th>
                                        Trạng thái
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <center>
                    <button id="xacNhanBtn" class="btn btn-warning mt-5"> Sửa các seal đã chọn</button>
                </center>
                </form>

            </div>
        </div>
        {{-- Modal xác nhận --}}
        <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Xác nhận sửa nhanh chì niêm phong</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Xác nhận sửa nhanh chì niêm phong?
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('quan-ly-khac.sua-nhanh-chi-niem-phong-submit') }}" method="POST"
                            id="mainForm" name='xuatHangForm'>
                            @csrf
                            <input type="hidden" name="rows_data" id="rowsDataInput">
                            <input type="hidden" name="ma_cong_chuc_moi" id="maCongChucMoi">
                            <button id="submitData" type="submit" class="btn btn-success">Sửa seal</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                let rowIndex = 0;
                const nhapYeuCauButton = document.getElementById('xacNhanBtn');
                nhapYeuCauButton.addEventListener('click', function() {
                    //Table 1
                    const rows = $('#hangTrongContTable tbody tr')
                        .filter(function() {
                            return $(this).find('.row-checkbox').is(':checked');
                        })
                        .map(function() {
                            const cells = $(this).find('td');
                            return {
                                so_seal: $(cells[2]).text(),
                            };
                        })
                        .get();

                    if (rows.length === 0) {
                        alert('Vui lòng chọn ít nhất một seal để sửa.');
                        return false;
                    }
                    let ma_cong_chuc_moi = $("#cong-chuc-dropdown-search-2").val();
                    if (!ma_cong_chuc_moi) {
                        alert('Vui lòng chọn cán bộ công chức mới.');
                        return false;
                    }

                    $('#maCongChucMoi').val(ma_cong_chuc_moi);
                    $('#rowsDataInput').val(JSON.stringify(rows));
                    $('#xacNhanModal').modal('show');
                });


                function updateTable() {
                    let ngay_cap = $('#ngay-cap').val();
                    let ma_cong_chuc = $("#cong-chuc-dropdown-search").val();
                    let trang_thai = $("#trang-thai-dropdown-search").val();
                    let loai_seal = $("#loai-seal").val();
                    console.log(trang_thai);
                    $.ajax({
                        url: "{{ route('quan-ly-khac.get-thong-tin-xoa-nhanh-seal') }}", // Adjust with your route
                        type: "GET",
                        data: {
                            ngay_cap: ngay_cap,
                            ma_cong_chuc: ma_cong_chuc,
                            loai_seal: loai_seal,
                            trang_thai: trang_thai,

                        },
                        success: function(response) {
                            console.log(response);
                            let tbody = $("#hangTrongContTable tbody");
                            tbody.empty();
                            if (response.seals && response.seals.length > 0) {
                                $.each(response.seals, function(index, item) {
                                    tbody.append(`
                                <tr>
                                    <td><input type="checkbox" class="row-checkbox"></td>
                                    <td>${index + 1}</td>
                                    <td>${item.so_seal}</td>
                                    <td>${item.loai_seal}</td>
                                    <td>${convertDateFormat(item.ngay_cap)} </td>
                                    <td>${item.ten_cong_chuc}</td>
                                    <td>${item.trang_thai}</td>
                                </tr>
                            `);
                                });
                            } else {
                                tbody.append('<tr><td colspan="9">Không có dữ liệu</td></tr>');
                            }
                        }
                    });

                }




                $('#cong-chuc-dropdown-search').on('change', function() {
                    updateTable();
                });
                $('#trang-thai-dropdown-search').on('change', function() {
                    updateTable();
                });
                $('#loai-seal').on('input', function() {
                    updateTable();
                });
                $('#ngay-cap').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true
                }).on('changeDate', function(e) {
                    updateTable(); // Your function
                });
                document.getElementById('checkAll').addEventListener('change', function() {
                    let checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                });

                function convertDateFormat(dateStr) {
                    return dateStr.split("-").reverse().join("-");
                }
            });
        </script>
    @stop
