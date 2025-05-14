@extends('layout.user-layout')

@section('title', 'Thêm phương tiện vận tải xuất cảnh')

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
            <a class="return-link" href="/danh-sach-ptvt-xc"">
                <p>
                    < Quay lại quản lý tờ khai phương tiện vận tải</p>
            </a>
            <h2>TỜ KHAI PHƯƠNG TIỆN VẬN TẢI</h2>
            <form action="{{ route('phuong-tien-vt.them-to-khai-ptvt-xc-submit') }}" method="POST">
                @csrf
                @method('POST')
                <!-- Input fields for each column -->
                <div class="row">
                    <div class="col-12">
                        <div class="card px-3 pt-3 mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="label-text" for="ten_phuong_tien">Tên phương tiện vận tải</label>
                                        <span class="text-danger missing-input-text"></span>
                                        <input type="text" class="form-control mt-2  reset-input" id="ten_phuong_tien_vt"
                                            maxlength="100" name="ten_phuong_tien_vt" placeholder="Nhập phương tiện vận tải"
                                            required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="label-text" for="cang_den">Cảng đến</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id="cang_den"
                                        maxlength="100" value="PHÒNG THÀNH, TRUNG QUỐC" name="cang_den"
                                        placeholder="Nhập cảng đến" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <label class="label-text" for="ten_thuyen_truong">Tên thuyền trưởng</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id="ten_thuyen_truong"
                                        maxlength="100" name="ten_thuyen_truong" placeholder="Nhập tên thuyền trưởng"
                                        required>
                                </div>
                                <div class="col-4">
                                    <label class="label-text" for="quoc_tich_tau">Quốc tịch tàu</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id="quoc_tich_tau"
                                        maxlength="100" value="TRUNG QUỐC" name="quoc_tich_tau"
                                        placeholder="Nhập quốc tịch tàu" required>
                                </div>

                                <div class="col-4">
                                    <label class="label-text" for="so_giay_chung_nhan">Số giấy chứng nhận</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id="so_giay_chung_nhan"
                                        maxlength="100" name="so_giay_chung_nhan" placeholder="Nhập số giấy chứng nhận"
                                        required>
                                </div>
                            </div>
                            <div class="row">
                                <h5>Thông số xuồng đến</h5>
                                <div class="col-3">
                                    <label class="label-text" for="ten_thuyen_truong">Draft</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="20" name="draft_den" placeholder="Nhập Draft" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="quoc_tich_tau">DWT</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="dwt_den" placeholder="Nhập DWT" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">LOA</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="loa_den" placeholder="Nhập LOA" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">Breadth</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="breadth_den" placeholder="Nhập Breadth" required>
                                </div>
                            </div>

                            <div class="row">
                                <h5>Thông số xuồng rời</h5>
                                <div class="col-3">
                                    <label class="label-text" for="ten_thuyen_truong">Draft</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="20" name="draft_roi" placeholder="Nhập Draft" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="quoc_tich_tau">DWT</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="dwt_roi" placeholder="Nhập DWT" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">LOA</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="loa_roi" placeholder="Nhập LOA" required>
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">Breadth</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="breadth_roi" placeholder="Nhập Breadth" required>
                                </div>
                            </div>


                            <center>
                                <button class="btn btn-success mb-3" type="submit">Thêm phương tiện</button>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
