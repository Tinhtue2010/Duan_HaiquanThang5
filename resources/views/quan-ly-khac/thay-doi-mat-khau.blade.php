@extends('layout.user-layout')

@section('title', 'Thay đổi mật khẩu')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-4">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif (session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Thay đổi mật khẩu</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body px-5">
                    <form action="{{ route('tai-khoan.thay-doi-mat-khau-submit') }}" method="POST">
                        @csrf
                        @method('POST')
                        <label class="label-text fw-bold" for="">- Tên đăng nhập :
                            {{ Auth::user()->ten_dang_nhap ?? '' }}</label>
                        <br>
                        <label class="label-text fw-bold" for="">- Loại tài khoản :
                            {{ Auth::user()->loai_tai_khoan ?? '' }}</label>
                        <br>
                        <label class="label-text fw-bold" for="">- Mã tài khoản :
                            {{ Auth::user()->ma_tai_khoan ?? '' }}</label>
                        <br>
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức')
                            <label class="label-text fw-bold" for=""> - Mã công chức :
                                {{ Auth::user()->congChuc->ma_cong_chuc ?? '' }}</label>
                            <br>
                            <label class="label-text fw-bold" for=""> - Tên công chức :
                                {{ Auth::user()->congChuc->ten_cong_chuc ?? '' }}</label>
                        @elseif(Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                            <label class="label-text fw-bold" for=""> - Mã doanh nghiệp :
                                {{ Auth::user()->doanhNghiep->ma_doanh_nghiep ?? '' }}</label>
                            <br>
                            <label class="label-text fw-bold" for=""> - Tên doanh nghiệp :
                                {{ Auth::user()->doanhNghiep->ten_doanh_nghiep ?? '' }}</label>
                            <br>
                            <label class="label-text fw-bold" for=""> - Địa chỉ :
                                {{ Auth::user()->doanhNghiep->dia_chi ?? '' }}</label>
                            <br>
                            <label class="label-text fw-bold" for=""> - Đại lý :
                                {{ Auth::user()->doanhNghiep->chuHang->ten_chu_hang ?? '' }}</label>
                        @endif
                        {{-- <div class="row mt-2">
                            <label class="label-text fw-bold" for="">Tên đăng nhập</label>
                            <input class="form-control mt-2 mx-2" id="so_to_khai_nhap" maxlength="50" name="ten_dang_nhap"
                                value={{ Auth::user()->ten_dang_nhap }} required>
                        </div> --}}
                        <div class="row mt-2">
                            <label class="label-text fw-bold" for="">Mật khẩu</label>
                            <input class="form-control mt-2  mx-2" id="mat_khau" maxlength="50" name="mat_khau">
                        </div>
                        <input name="ma_tai_khoan" value={{ Auth::user()->ma_tai_khoan }} hidden>
                        <button type="submit" class="btn btn-success">Xác nhận</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop
