@extends('layout.user-layout')

@section('title', 'Thông tin biên bản bàn giao hồ sơ')

@section('content')
    @php
        use App\Models\DoanhNghiep;
    @endphp
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                @if (session('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-success') }}</strong>
                    </div>
                @elseif (session('alert-danger'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                        <strong>{{ session('alert-danger') }}</strong>
                    </div>
                @endif
                <div class="col-6">
                    <a class="return-link" href="/quan-ly-ban-giao-ho-so">
                        <p>
                            < Quay lại danh sách biên bản bàn giao hồ sơ </p>
                    </a>
                </div>
                <div class="col-6">
                    <a
                        href="{{ route('ban-giao.export-bien-ban-ban-giao', ['ma_ban_giao' => $bienBan->ma_ban_giao]) }}">
                        <button class="btn btn-success float-end mx-1">In biên bản bàn giao</button>
                    </a>
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">BIÊN BẢN BÀN GIAO HỒ SƠ</h2>
                    <h2 class="text-center">Công chức phụ trách giám sát: {{ $bienBan->congChuc->ten_cong_chuc ?? '' }}</h2>

                    <h2 class="text-center">Từ ngày {{ \Carbon\Carbon::parse($bienBan->tu_ngay)->format('d-m-Y') }}
                        đến ngày {{ \Carbon\Carbon::parse($bienBan->den_ngay)->format('d-m-Y') }}</h2>
                    <h2 class="text-center">Ngày tạo: {{ \Carbon\Carbon::parse($bienBan->ngay_tao)->format('d-m-Y') }} </h2>

                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai</th>
                                <th>Doanh nghiệp</th>
                                <th>Ngày xuất hết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_to_khai_nhap }}</td>
                                    <td>{{ $chiTiet->nhapHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                    <td>{{ \Carbon\Carbon::parse($chiTiet->nhapHang->ngay_xuat_het)->format('d-m-Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@stop
