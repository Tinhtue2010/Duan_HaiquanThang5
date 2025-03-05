@extends('layout.base')

@section('title', 'Liên hệ')

@section('content')
    <center>
        <div class="custom-line"></div>
    </center>
    <div class="container my-5">
        @if (session('alert-success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                <strong>{{ session('alert-success') }}</strong>
            </div>
        @elseif(session('alert-danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                <strong>{{ session('alert-danger') }}</strong>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <img src="{{ asset('images/letter.jpg') }}"
                            class="ms-3 ps-3 letter-img d-flex justify-content-center" alt="letter"
                            style="max-width: 100%; height: auto;">
                    </div>

                    <div class="col-12 col-md-6">
                        <h2 class="text-center mb-4">Liên hệ</h2>
                        <form action="{{ route('lien-he-submit') }}" method="POST" id="mainForm">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên cá nhân /Tổ chức</label>
                                <input type="text" class="form-control" name="ten_ca_nhan" maxlength="200" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" maxlength="200" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Lời nhắn</label>
                                <textarea class="form-control" name="loi_nhan" rows="5" maxlength="2000" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary center">Gửi thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
