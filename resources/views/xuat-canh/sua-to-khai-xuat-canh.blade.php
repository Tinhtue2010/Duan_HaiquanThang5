@extends('layout.user-layout')

@section('title', 'Sửa tờ khai xuất cảnh')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link"
                href={{ route('xuat-canh.thong-tin-xuat-canh', ['ma_xuat_canh' => $xuatCanh->ma_xuat_canh]) }}>
                <p>
                    < Quay lại thông tin tờ khai xuất cảnh</p>
            </a>
            <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center">TỜ KHAI XUẤT CẢNH</h2>
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <div class="row justify-content-center">
                            <h3 class="text-center">Thông tin tờ khai</h3>
                            <div class="col-3">
                                <div class="form-group mt-4">
                                    <label for="ptvtxc" class="mb-1 fw-bold">Phương tiện vận tải xuất cảnh</label>
                                    <select class="form-control" id="ptvt-xc-dropdown-search" name="ptvtxc">
                                        <option selected value="{{ $xuatCanh->so_ptvt_xuat_canh }}">
                                            {{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} Số:
                                            {{ $xuatCanh->so_ptvt_xuat_canh }}
                                        </option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn thuyền trưởng</label>
                                    <select class="form-control" id="thuyen-truong-dropdown-search" name="ten_thuyen_truong"
                                        required>
                                        <option value="{{ $xuatCanh->ten_thuyen_truong }}" selected>
                                            {{ $xuatCanh->ten_thuyen_truong }}</option>
                                        @foreach ($thuyenTruongs as $thuyenTruong)
                                            <option value="{{ $thuyenTruong }}"> {{ $thuyenTruong }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn doanh nghiệp/ Chủ
                                        hàng</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                @if ($xuatCanh->ma_doanh_nghiep_chon == $doanhNghiep->ma_doanh_nghiep) selected @endif>
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                            </option>
                                        @endforeach
                                        <option value="0">Không</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group mt-4">
                                    <label class="label-text mb-1 fw-bold" for="">Ngày xuất cảnh</label>
                                    <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                        id="ngay-dang-ky" readonly
                                        value="{{ \Carbon\Carbon::parse($xuatCanh->ngay_dang_ky)->format('d/m/Y') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row card p-2">
                <h3 class="text-center">Danh sách các phiếu xuất</h3>
                <center><button id="cap-nhat-lai-btn" class="btn btn-primary">Cập nhật lại</button></center>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered" id="toKhaiXuatTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai xuất</th>
                                <th>Tên doanh nghiệp</th>
                                <th>Tên đại lý</th>
                                <th>Loại hình</th>
                                <th>Số lượng</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTiet->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->chuHang->ten_chu_hang ?? '' }}</td>
                                    <td>{{ $chiTiet->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ $chiTiet->tong_so_luong_xuat }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold;background-color: #f8f9fa;">
                                <td colspan="5">Tổng:</td>
                                <td id="totalQty"></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <center>
                <button id="xacNhanBtn" class="btn btn-success mt-5">Sửa tờ khai xuất cảnh</button>
            </center>
            </form>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận sửa phiếu xuất hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận sửa tờ khai xuất cảnh này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('xuat-canh.sua-to-khai-xc-submit') }}" method="POST" id="mainForm"
                        name='xuatCanhForm'>
                        @csrf
                        <input type="hidden" name="ma_doanh_nghiep_chon" id="ma_doanh_nghiep_chon_hidden">
                        <input type="hidden" name="ten_thuyen_truong" id="ten_thuyen_truong_hidden">
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ma_xuat_canh" id="ma_xuat_canh_hidden"
                            value={{ $xuatCanh->ma_xuat_canh }}>
                        <input type="hidden" name="ngay_dang_ky" id="ngay_dang_ky_hidden">
                        <button id="submitData" type="submit" class="btn btn-success">Sửa tờ khai xuất cảnh</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            calculateTotal();
            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                let tenThuyenTruong = document.getElementById('thuyen-truong-dropdown-search').value.trim();
                let ngayDangKy = document.getElementById('ngay-dang-ky').value;
                let maDoanhNghiepChon = document.getElementById('doanh-nghiep-dropdown-search').value
                    .trim();
                document.getElementById('ten_thuyen_truong_hidden').value = tenThuyenTruong;
                document.getElementById('ma_doanh_nghiep_chon_hidden').value = maDoanhNghiepChon;
                document.getElementById('ngay_dang_ky_hidden').value = ngayDangKy;

                const rows = $('#toKhaiXuatTable tbody tr')
                    .map(function() {
                        const cells = $(this).find('td');
                        return {
                            so_to_khai_xuat: $(cells[1]).text(),
                        };
                    })
                    .get();

                $('#rowsDataInput').val(JSON.stringify(rows));

                if (!tenThuyenTruong) {
                    alert('Vui lòng chọn tên thuyền trưởng');
                    return false;
                }
                if (!maDoanhNghiepChon) {
                    alert('Vui lòng chọn doanh nghiệp');
                    return false;
                }
                if (!ngayDangKy) {
                    alert('Vui lòng chọn ngày đăng ký');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });

            function convertDateFormat(dateStr) {
                return dateStr.split("-").reverse().join("-");
            }

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#toKhaiXuatTable tbody tr").forEach(row => {
                    let quantityCell = row.cells[5];
                    let quantity = parseFloat(quantityCell.textContent || 0);
                    total += isNaN(quantity) ? 0 : quantity;
                });

                document.getElementById("totalQty").textContent = total;
            }

            function updateTable() {
                let so_ptvt_xuat_canh = $('#ptvt-xc-dropdown-search').val();
                $.ajax({
                    url: "{{ route('xuat-canh.getPhieuXuats') }}", // Adjust with your route
                    type: "GET",
                    data: {
                        so_ptvt_xuat_canh: so_ptvt_xuat_canh,
                    },

                    success: function(response) {
                        let tbody = $("#toKhaiXuatTable tbody");
                        let tfoot = $("#toKhaiXuatTable tfoot");
                        tbody.empty(); // Clear previous data
                        let totalTongSoLuongXuat = 0; // Initialize total sum
                        let doanhNghiepDropdown = $("#doanh-nghiep-dropdown-search");
                        doanhNghiepDropdown.empty().append(
                            '<option value="">Chọn doanh nghiệp</option>');

                        let addedDoanhNghieps = new Set(); // Track unique doanh nghiep

                        if (response.xuatHangs && response.xuatHangs.length > 0) {
                            $.each(response.xuatHangs, function(index, item) {
                                totalTongSoLuongXuat += parseFloat(item.tong_so_luong_xuat) ||
                                    0;

                                tbody.append(`
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.so_to_khai_xuat}</td>
                                        <td>${item.ten_doanh_nghiep}</td>
                                        <td>${item.ten_chu_hang}</td>
                                        <td>${item.ma_loai_hinh}</td>
                                        <td>${item.tong_so_luong_xuat}</td>
                                        <td>${convertDateFormat(item.ngay_dang_ky)}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                        </td>     
                                    </tr>
                                `);

                                if (!addedDoanhNghieps.has(item.ma_doanh_nghiep)) {
                                    addedDoanhNghieps.add(item.ma_doanh_nghiep);
                                    doanhNghiepDropdown.append(
                                        `<option value="${item.ma_doanh_nghiep}">${item.ten_doanh_nghiep}</option>`
                                    );
                                }
                            });
                            calculateTotal();
                        } else {
                            tbody.append('<tr><td colspan="9">Không có dữ liệu</td></tr>');
                        }
                        if (addedDoanhNghieps.size === 0) {
                            doanhNghiepDropdown.append(
                                `<option value="0">Không</option>`
                            );
                        }
                    }

                });
            }

            const tableBody = document.querySelector('#toKhaiXuatTable tbody');

            tableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('deleteRowButton')) {
                    const row = e.target.closest('tr');
                    row.remove();

                    // Reorder the STT column after deletion
                    Array.from(tableBody.querySelectorAll('tr')).forEach((tr, index) => {
                        tr.querySelector('td:first-child').textContent = index + 1;
                    });
                    calculateTotal();
                }
            });

            $('#cap-nhat-lai-btn').on('click', function() {
                updateTable($(this));
            });

            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
        });
    </script>

@stop
