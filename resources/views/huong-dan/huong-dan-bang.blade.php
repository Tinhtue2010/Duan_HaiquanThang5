@extends('layout.base')

@section('title', 'Hướng dẫn tương tác các bảng')

@section('content')

    <center>
        <div class="custom-line"></div>
    </center>
    <div class="container">
        <div class="row">
            <h2>Hướng dẫn tương tác các bảng</h2>
            <em>
                <h5>Dưới đây là hướng dẫn tương tác các bảng</h5>
            </em>
            <br />
            <div class="col-2"></div>
            <div class="col-8">
                <div class="card shadow mb-4  mt-5">
                    <div class="card-body m-2">
                        <center><img src="{{ asset('images/huong-dan/1.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Tại các bảng như thế này người dùng có thể tương tác với bảng như
                            sau:</span>
                        </p>
                        <p class="post-content">Người dùng có thể chọn đầu cột để xếp thứ tự từ trên xuống hoặc từ dưới lên
                            ví dụ cột "STT"</p>
                        <center><img src="{{ asset('images/huong-dan/2.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Hoặc sắp xếp cột "Ngày đăng ký" từ ngày gần nhất đến ngày xa nhất</p>
                        <center><img src="{{ asset('images/huong-dan/3.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Người dùng có thể tìm kiếm theo từ khóa trong bảng</p>
                        <center><img src="{{ asset('images/huong-dan/4.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Hoặc chọn tìm kiếm theo ngày</p>
                        <center><img src="{{ asset('images/huong-dan/5.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Người dùng có thể chọn hiển thị số lượng dòng trong một trang</p>
                        <center><img src="{{ asset('images/huong-dan/6.PNG') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
