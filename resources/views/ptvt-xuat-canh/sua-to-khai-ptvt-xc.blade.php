@extends('layout.user-layout')

@section('title', 'Thêm phương tiện vận tải xuất cảnh')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (Session::has('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ Session::get('alert-success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a class="return-link" href="/thong-tin-ptvt-xc/{{ $phuong_tien_vt->so_ptvt_xuat_canh }}">
                <p>
                    < Quay lại quản lý phương tiện vận tải xuất cảnh</p>
            </a>
            <h2>PHƯƠNG TIỆN VẬN TẢI XUẤT CẢNH</h2>
            <form action="{{ route('phuong-tien-vt.sua-to-khai-ptvt-xc-submit') }}" method="POST">
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
                                        <input type="text" class="form-control mt-2" maxlength="100"
                                            name="ten_phuong_tien_vt" placeholder="Nhập phương tiện vận tải" required
                                            value="{{ trim($phuong_tien_vt->ten_phuong_tien_vt) }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="label-text" for="cang_den">Cảng đến</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2" maxlength="100" name="cang_den"
                                        placeholder="Nhập cảng đến" required value="{{ trim($phuong_tien_vt->cang_den) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <label class="label-text" for="ten_thuyen_truong">Tên thuyền trưởng</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2" maxlength="100" name="ten_thuyen_truong"
                                        placeholder="Nhập tên thuyền trưởng" required
                                        value="{{ trim($phuong_tien_vt->ten_thuyen_truong) }}">
                                </div>
                                <div class="col-4">
                                    <label class="label-text" for="quoc_tich_tau">Quốc tịch tàu</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2" maxlength="100" name="quoc_tich_tau"
                                        placeholder="Nhập quốc tịch tàu" required
                                        value="{{ trim($phuong_tien_vt->quoc_tich_tau) }}">
                                </div>
                                <div class="col-4">
                                    <label class="label-text" for="so_giay_chung_nhan">Số giấy chứng nhận</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2" maxlength="100"
                                        name="so_giay_chung_nhan" placeholder="Nhập số giấy chứng nhận" required
                                        value="{{ trim($phuong_tien_vt->so_giay_chung_nhan) }}">
                                </div>
                            </div>
                            <div class="row">
                                <h5>Thông số xuồng đến</h5>
                                <div class="col-3">
                                    <label class="label-text" for="ten_thuyen_truong">Draft</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="20" name="draft_den" placeholder="Nhập Draft" required
                                        value="{{ trim($phuong_tien_vt->draft_den) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="quoc_tich_tau">DWT</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="dwt_den" placeholder="Nhập DWT" required
                                        value="{{ trim($phuong_tien_vt->dwt_den) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">LOA</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="loa_den" placeholder="Nhập LOA" required
                                        value="{{ trim($phuong_tien_vt->loa_den) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">Breadth</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="breadth_den" placeholder="Nhập Breadth" required
                                        value="{{ trim($phuong_tien_vt->breadth_den) }}">
                                </div>
                            </div>

                            <div class="row">
                                <h5>Thông số xuồng rời</h5>
                                <div class="col-3">
                                    <label class="label-text" for="ten_thuyen_truong">Draft</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="20" name="draft_roi" placeholder="Nhập Draft" required
                                        value="{{ trim($phuong_tien_vt->draft_roi) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="quoc_tich_tau">DWT</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="dwt_roi" placeholder="Nhập DWT" required
                                        value="{{ trim($phuong_tien_vt->dwt_roi) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">LOA</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="loa_roi" placeholder="Nhập LOA" required
                                        value="{{ trim($phuong_tien_vt->loa_roi) }}">
                                </div>
                                <div class="col-3">
                                    <label class="label-text" for="so_giay_chung_nhan">Breadth</label> <span
                                        class="text-danger missing-input-text"></span>
                                    <input type="text" class="form-control mt-2  reset-input" id=""
                                        maxlength="10" name="breadth_roi" placeholder="Nhập Breadth" required
                                        value="{{ trim($phuong_tien_vt->breadth_roi) }}">
                                </div>
                            </div>
                            <input type="hidden" name="so_ptvt_xuat_canh"
                                value={{ $phuong_tien_vt->so_ptvt_xuat_canh }}>
                            <center>
                                <button class="btn btn-success mb-3" type="submit">Sửa thông tin</button>
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
