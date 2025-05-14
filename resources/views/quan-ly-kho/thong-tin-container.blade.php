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
                    <h2 class="text-center">Danh sách tờ khai trong container: {{ $container->so_container }} - Số seal niêm
                        phong: {{ $container->niemPhong->so_seal ?? '' }} </h2>
                    <!-- Table for displaying added rows -->
                    <table class="table table-bordered mt-5" id="dataTable">
                        <thead>
                            <th>
                                STT
                            </th>
                            <th>
                                Số tờ khai nhập
                            </th>
                            <th>
                                Doanh nghiệp
                            </th>
                            <th>
                                Số lượng
                            </th>
                        </thead>
                        <tbody class="clickable-row">
                            @foreach ($nhapHangs as $index => $nhapHang)
                                @if ($nhapHang->total_so_luong > 0)
                                    <tr class="clickable-row"
                                        onclick="window.location='{{ route('nhap-hang.vi-tri-hang-hien-tai', $nhapHang->so_to_khai_nhap) }}'">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $nhapHang->so_to_khai_nhap }}</td>
                                        <td>{{ $nhapHang->doanhNghiep->ten_doanh_nghiep }}</td>
                                        <td>{{ number_format($nhapHang->total_so_luong) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
