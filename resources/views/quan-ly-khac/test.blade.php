@extends('layout.user-layout')

@section('title', 'Danh sách loại hình')

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

            <form action="{{ route('quan-ly-khac.xoa-theo-doi-tru-lui') }}" method="POST">
                @csrf
                @method('POST')
                <p>Tên đăng nhập</p>
                <input type="text" class="form-control" name="ten_dang_nhap">
                <p>Mật khẩu</p>
                <input type="text" class="form-control" name="mat_khau">
                <button type="submit">Done</button>
            </form>

            <form action="{{ route('quan-ly-khac.xoa-theo-doi-hang') }}" method="POST">
                @csrf
                @method('POST')
                <p>STKN</p>
                <input type="text" class="form-control" name="so_to_khai_nhap">
                <p>STKX</p>
                <input type="text" class="form-control" name="so_to_khai_xuat">
                <button type="submit">Done</button>
            </form>


        </div>
    </div>
@stop
