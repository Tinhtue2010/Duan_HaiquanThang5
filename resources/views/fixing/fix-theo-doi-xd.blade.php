@extends('layout.user-layout')

@section('title', 'Sửa tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif(session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <a class="return-link" href="/quan-ly-nhap-hang">
                <p>
                    < Quay lại quản lý nhập hàng</p>
            </a>
            <h2 class="text-center text-dark">{{ $nhapHang->doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
            <!-- Input fields for each column -->
            <h3 class="text-center text-dark">Thông tin hàng hóa</h3>
            <table class="table table-bordered" id="displayTableNhapHang"
                style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã hàng Cont</th>
                        <th>Tên hàng</th>
                        <th>Số lượng hiện tại</th>
                        <th>Số Cont hiện tại</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hangHoas as $hangHoa)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $hangHoa->ma_hang_cont }}
                            </td>
                            <td>
                                {{ $hangHoa->ten_hang }}
                            </td>
                            <td>
                                {{ $hangHoa->so_luong }}
                            </td>
                            <td>
                                {{ $hangHoa->so_container }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <table class="table table-bordered" id="displayTableNhapHang"
                style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã hàng</th>
                        <th>Mã hàng Cont</th>
                        <th>Mã xuất hàng cont</th>
                        <th>Tên hàng</th>
                        <th>Số lượng</th>
                        <th>Số container</th>
                        <th>Ngày</th>
                        <th>Mã xuất hàng</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($xuatHangConts as $xuatHangCont)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $xuatHangCont->ma_hang }}
                            </td>
                            <td class="text-danger">
                                {{ $xuatHangCont->ma_hang_cont }}
                            </td>
                            <td class="text-danger">
                                {{ $xuatHangCont->ma_xuat_hang_cont }}
                            </td>

                            <td>
                                {{ $xuatHangCont->ten_hang }}
                            </td>
                            <td>
                                {{ $xuatHangCont->so_luong_xuat }}
                            </td>
                            <td>
                                {{ $xuatHangCont->so_container }}
                            </td>
                            <td>
                                {{ Carbon\Carbon::parse($xuatHangCont->ngay_dang_ky)->format('d-m-Y') }}
                            </td>
                            <td>
                                {{ $xuatHangCont->so_to_khai_xuat }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row">
                <div class="col-1">

                </div>
                <div class="col-4">
                    <form action="{{ route('fix.clone-xuat-hang') }}" method="POST" id="mainForm">
                        @csrf
                        <span>Mã xuất hàng cont</span>
                        <input type="number" class="form-control mb-3" name="ma_xuat_hang_cont">
                        <span>Mã hàng cont</span>
                        <input type="number" class="form-control mb-3" name="ma_hang_cont">
                        <span>Số lượng xuất</span>
                        <input type="number" class="form-control mb-3" name="so_luong_xuat">

                        <button type="submit" class="btn btn-success">Clone</button>
                    </form>
                </div>
                <div class="col-2">
                    <form action="{{ route('fix.update-sl-hien-tai') }}" method="POST" id="mainForm">
                        @csrf
                        <span>Mã hàng cont</span>
                        <input type="number" class="form-control mb-3" name="ma_hang_cont">
                        <span>Số lượng hiện tại</span>
                        <input type="number" class="form-control mb-3" name="so_luong">

                        <button type="submit" class="btn btn-success">Fix</button>
                    </form>
                </div>
                <div class="col-4">
                    <form action="{{ route('fix.xoa-xuat-hang') }}" method="POST" id="mainForm">
                        @csrf
                        <span>Mã xuất hàng cont</span>
                        <input type="number" class="form-control mb-3" name="ma_xuat_hang_cont">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>



            <table class="table table-bordered" id="displayTableNhapHang"
                style="vertical-align: middle; text-align: center;">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Thời gian</th>
                        <th>Tên hàng</th>
                        <th>Số lượng</th>
                        <th>Số container</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($theoDoiHangHoas as $theoDoiHangHoa)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $theoDoiHangHoa->thoi_gian }}
                            </td>
                            <td>
                                {{ $theoDoiHangHoa->ten_hang }}
                            </td>
                            <td>
                                {{ $theoDoiHangHoa->so_luong_xuat }}
                            </td>
                            <td>
                                {{ $theoDoiHangHoa->so_container }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi', // Set language to Vietnamese
                endDate: '0d',
                keyboardNavigation: true, // Allow keyboard navigation
                forceParse: true // Ensure manually typed dates are parsed
            }).on('changeDate', function(e) {
                // When a date is selected via the datepicker UI
                handleDateChange(e.date);
            });

            // Handle manually typed date
            $('#datepicker').on('blur', function() {
                const typedDate = $(this).val();
                const parsedDate = moment(typedDate, "DD/MM/YYYY", true);

                if (parsedDate.isValid()) {
                    // Update the datepicker with the manually entered date
                    $('#datepicker').datepicker('setDate', parsedDate.toDate());
                    handleDateChange(parsedDate.toDate());
                } else {
                    alert("Invalid date format. Please enter in DD/MM/YYYY format.");
                }
            });

            function handleDateChange(selectedDate) {
                const currentDate = new Date();
                const diffTime = Math.abs(currentDate - selectedDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                console.log("Selected Date:", selectedDate);
                console.log("Days Difference:", diffDays);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2-dropdown').select2({
                placeholder: "Chọn loại hàng",
                allowClear: true,
                width: 'resolve' // makes sure it inherits width from Bootstrap
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input.number').forEach(function(input) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                });
            });
        });
    </script>
@stop
