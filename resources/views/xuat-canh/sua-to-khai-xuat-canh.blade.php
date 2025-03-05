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
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label for="ptvtxc" class="mb-1 fw-bold">Phương tiện vận tải xuất cảnh</label>
                                    <input class="form-control mt-2" value="{{ $xuatCanh->PTVTXuatCanh->ten_phuong_tien_vt }} Số:{{ $xuatCanh->so_ptvt_xuat_canh }}" readonly></input>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn thuyền trưởng (Hoặc nhập
                                        tên khác trong ô tìm kiếm)</label>
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
                            <div class="col-4">
                                <div class="form-group mt-3">
                                    <label class="label-text mb-1 mt-2 fw-bold" for="">Chọn doanh nghiệp/ Chủ
                                        hàng</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search" name="ma_doanh_nghiep"
                                        required>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                @if ($xuatCanh->ma_doanh_nghiep_chon == $doanhNghiep->ma_doanh_nghiep) selected 
                                                @endif>
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
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
                <h3 class="text-center">Danh sách các phiếu xuất</h3>
                <div class="table-responsive">
                    <table class="table table-bordered" id="toKhaiXuatTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead style="vertical-align: middle; text-align: center;">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai xuất</th>
                                <th>Tên doanh nghiệp</th>
                                <th>Loại hình</th>
                                <th>Số lượng</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $chiTiet->so_to_khai_xuat }}</td>
                                    <td>{{ $chiTiet->xuatHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ $chiTiet->xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ $chiTiet->tong_so_luong_xuat }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->xuatHang->ngay_dang_ky)->format('d-m-Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
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
                        <input type="hidden" name="ma_xuat_canh" value={{ $xuatCanh->ma_xuat_canh }}>
                        <button id="submitData" type="submit" class="btn btn-success">Sửa tờ khai xuất cảnh</button>
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
                let tenThuyenTruong = document.getElementById('thuyen-truong-dropdown-search').value.trim();
                let maDoanhNghiepChon = document.getElementById('doanh-nghiep-dropdown-search').value
            .trim();
                document.getElementById('ten_thuyen_truong_hidden').value = tenThuyenTruong;
                document.getElementById('ma_doanh_nghiep_chon_hidden').value = maDoanhNghiepChon;

                if (!tenThuyenTruong) {
                    alert('Vui lòng chọn tên thuyền trưởng');
                    return false;
                }
                if (!maDoanhNghiepChon) {
                    alert('Vui lòng chọn doanh nghiệp');
                    return false;
                }

                $('#xacNhanModal').modal('show');
            });
        });
    </script>
@stop
