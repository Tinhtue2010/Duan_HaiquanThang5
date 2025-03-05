@extends('layout.base')

@section('title', 'Đăng nhập')

@section('content')
    <style>
        .custom-button {
            width: 25vw;
        }

        .custom-background {
            background-color: #f5f5f5;
        }
    </style>
        <center>
            <div class="custom-line"></div>
        </center>
    <div class="row container-fluid">
        <div class="col-6 ps-5">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-7">
                    <center class="my-4 pt-5">
                        <h1><strong>Hệ thống sổ điện tử</strong></h1>
                    </center>
                    <h3 class="mt-5 pt-5"><strong>Đăng nhập vào hệ thống</strong></h3>
                    @if (session('alert-danger'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <strong>Thông tin đăng nhập không chính xác</strong>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('submit-dang-nhap') }}">
                        @csrf
                        <div class="form-group first mt-3">
                            <strong><label for="ten_dang_nhap">Tên đăng nhập</label></strong>
                            <input type="text" class="form-control mt-1" placeholder="Username" name="ten_dang_nhap"
                                id="ten_dang_nhap" required>
                        </div>
                        <div class="form-group last mb-3 mt-3">
                            <strong><label for="mat_khau">Mật khẩu</label></strong>
                            <input type="password" class="form-control mt-1" placeholder="Password" name="password"
                                id="password" required>
                        </div>
                        @error('ten_dang_nhap')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <center><input type="submit" value="Đăng nhập" class="btn btn-block btn-primary custom-button"></center>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-6 p-5">
            <div class="image-div" style="background-image: url('{{ asset('images/22.jpg') }}');"></div>
        </div>

    </div>
@stop
