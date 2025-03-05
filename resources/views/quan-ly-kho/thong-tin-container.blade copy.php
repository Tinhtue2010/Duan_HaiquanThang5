@extends('layout.user-layout')

@section('title', 'Thông tin container')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/tra-cuu-container">
                        <p>
                            < Quay lại tra cứu </p>
                    </a>
                </div>
                <div class="col-6">
                    {{-- <a href="{{ route('nhap-hang.export-to-khai', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]) }}">
                        <button class="btn btn-success float-end mx-1">Xuất Excel </button>
                    </a> --}}
                    {{-- <button onclick="printToKhai('divPrint')" class="btn btn-success float-end">In thông tin</button> --}}
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">Danh sách hàng hóa trong container: {{ $container->so_container }} - Số seal niêm phong: {{ $container->niemPhong->so_seal ?? '' }} </h2>
                    <!-- Table for displaying added rows -->
                    <table class="table table-bordered mt-5" id="dataTable">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số tờ khai nhập</th>
                                <th>Tên hàng</th>
                                <th>Xuất xứ</th>
                                <th>Loại hàng</th>
                                <th>Số lượng trong container</th>
                                <th>Đơn vị tính</th>
                            </tr>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($containers as $index => $container)
                            <tr class="clickable-row">
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $container->so_to_khai_nhap }}</td>
                                    <td>{{ $container->ten_hang }}</td>
                                    <td>{{ $container->xuat_xu }}</td>
                                    <td>{{ $container->loai_hang }}</td>
                                    <td>{{ number_format($container->so_luong) }}</td>
                                    <td>{{ $container->don_vi_tinh }}</td>
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
