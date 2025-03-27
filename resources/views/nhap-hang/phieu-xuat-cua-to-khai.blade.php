@extends('layout.user-layout')

@section('title', 'Phiếu xuất hàng có tờ khai')

@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/thong-tin-nhap-hang/{{ $so_to_khai_nhap }}">
                        <p>
                            < Quay lại quản lý nhập hàng </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center mt-5">Các phiếu xuất có tờ khai nhập: {{ $so_to_khai_nhap }}</h2>
                    <!-- Table for displaying added rows -->
                    <table class="table table-bordered mt-2" id="displayTable">
                        <thead class="align-middle">
                            <tr>
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
                                <th>
                                    Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($xuatHangs as $index => $xuatHang)
                                <tr class="clickable-row"
                                    onclick="window.location='{{ route('xuat-hang.thong-tin-xuat-hang', $xuatHang->so_to_khai_xuat) }}'">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $xuatHang->so_to_khai_xuat }}</td>
                                    <td>{{ $xuatHang->ma_loai_hinh }}</td>
                                    <td>{{ $xuatHang->doanhNghiep->ten_doanh_nghiep ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y') }}</td>
                                    <td>{{ $xuatHang->tong_so_luong }}</td>
                                    <td>{{ $xuatHang->ten_phuong_tien_vt }}</td>
                                    @if ($xuatHang->trang_thai == 1)
                                        <td class="text-primary">Đang chờ duyệt</td>
                                    @elseif($xuatHang->trang_thai == 3)
                                        <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu chờ duyệt</td>
                                    @elseif($xuatHang->trang_thai == 4)
                                        <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã duyệt</td>
                                    @elseif($xuatHang->trang_thai == 5)
                                        <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC</td>
                                    @elseif($xuatHang->trang_thai == 6)
                                        <td class="text-warning">Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng</td>
                                    @elseif($xuatHang->trang_thai == 7)
                                        <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu chờ duyệt</td>
                                    @elseif($xuatHang->trang_thai == 8)
                                        <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã duyệt</td>
                                    @elseif($xuatHang->trang_thai == 9)
                                        <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC</td>
                                    @elseif($xuatHang->trang_thai == 10)
                                        <td class="text-danger">Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng</td>
                                    @elseif($xuatHang->trang_thai == 2)
                                        <td class="text-success">Đã duyệt</td>
                                    @elseif($xuatHang->trang_thai == 11)
                                        <td class="text-success">Đã chọn phương tiện xuất cảnh</td>
                                    @elseif($xuatHang->trang_thai == 12)
                                        <td class="text-success">Đã duyệt xuất hàng</td>
                                    @elseif($xuatHang->trang_thai == 13)
                                        <td class="text-success">Đã thực xuất hàng</td>
                                    @elseif($xuatHang->trang_thai == 0)
                                        <td class="text-danger">Đã hủy</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
