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

            <form action="{{ route('quan-ly-khac.action-1') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Kiểm tra chuẩn số lượng tồn</h5>
                <button class="btn btn-primary" type="submit">Done</button>
            </form>
            <hr>
            <form action="{{ route('quan-ly-khac.action-2') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Khôi phục xuất hàng</h5>
                <div class="row">
                    <div class="col-2">
                        <h6>Nhập số tờ khai xuất</h6>
                        <input type="text" class="form-control" name="so_to_khai_xuat">
                    </div>
                    <div class="col-2">
                        <h6>Nhập trạng thái</h6>
                        <input type="text" class="form-control" name="trang_thai">
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">Done</button>
            </form>
            <hr>

            <form action="{{ route('quan-ly-khac.action-3') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Kiểm tra xuất hết không đúng số lượng</h5>
                <button class="btn btn-primary" type="submit">Done</button>
            </form>
            <hr>

            <form action="{{ route('quan-ly-khac.action-4') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Kiểm tra xuất hết sai tên công chức</h5>
                <button class="btn btn-primary" type="submit">Done</button>
            </form>
            <hr>
            <form action="{{ route('quan-ly-khac.action-5') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Fix xuất hết</h5>
                <button class="btn btn-primary" type="submit">Done</button>
            </form>
            <hr>
            <form action="{{ route('quan-ly-khac.action-6') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Fix sai số lượng</h5>
                <input type="text" class="form-control" name="so_to_khai_nhap">
                <button class="btn btn-primary mt-2" type="submit">Done</button>
            </form>
        </div>
    </div>
@stop
