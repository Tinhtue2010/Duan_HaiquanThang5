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
            @if (Auth::user()->ten_dang_nhap == 'admin2')
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

                            <select class="form-control" name="trang_thai">
                                <option value="1">Đang chờ duyệt</option>
                                <option value="2">Đã duyệt</option>
                                <option value="3">Doanh nghiệp yêu cầu sửa phiếu đã thực xuất hàng</option>
                                <option value="4">Doanh nghiệp yêu cầu sửa phiếu đã duyệt</option>
                                <option value="5">Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC</option>
                                <option value="6">Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng</option>
                                <option value="7">Doanh nghiệp yêu cầu hủy phiếu đã thực xuất hàng</option>
                                <option value="8">Doanh nghiệp yêu cầu hủy phiếu đã duyệt</option>
                                <option value="9">Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC</option>
                                <option value="10">Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng</option>
                                <option value="11">Đã chọn phương tiện xuất cảnh</option>
                                <option value="12">Đã duyệt xuất hàng</option>
                                <option value="13">Đã thực xuất hàng</option>
                                <option value="0">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Done</button>
                </form>
                <hr>

                <form action="{{ route('quan-ly-khac.action-3') }}" method="POST">
                    @csrf
                    @method('POST')
                    <h5>Kiểm tra đúng tàu</h5>
                    <button class="btn btn-primary" type="submit">Done</button>
                </form>
                <hr>

                <form action="{{ route('quan-ly-khac.action-4') }}" method="POST">
                    @csrf
                    @method('POST')
                    <h5>Kiểm tra</h5>
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
                    <input type="text" class="form-control" name="so_to_khai_nhap" placeholder="Số tờ khai nhập">
                    <button class="btn btn-primary mt-2" type="submit">Done</button>
                </form>
                <hr>
            @endif
            <form action="{{ route('quan-ly-khac.action-10') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Niêm phong lại</h5>
                <div class="row">
                    <div class="col-2">
                        <h6>Nhập số tờ khai nhập</h6>
                        <input type="text" class="form-control" name="so_to_khai_nhap" placeholder="Số tờ khai nhập">
                    </div>
                    <div class="col-2">
                        <h6>Chọn ngày niêm phong</h6>
                        <input type="text" class="form-control datepicker" placeholder="dd/mm/yyyy"
                            name="ngay_niem_phong" readonly>
                    </div>
                    <div class="col-2">
                        <h6>Nhập số seal</h6>
                        <input type="text" class="form-control" name="so_seal" placeholder="Số seal">
                    </div>
                </div>
                <button class="btn btn-primary mt-2" type="submit">Done</button>
            </form>

            <hr>
            <form action="{{ route('quan-ly-khac.action-11') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Sửa số container ban đầu</h5>
                <div class="row">
                    <div class="col-2">
                        <h6>Nhập số tờ khai nhập</h6>
                        <input type="text" class="form-control" name="so_to_khai_nhap" placeholder="Số tờ khai nhập">
                    </div>
                    <div class="col-2">
                        <h6>Số container cũ</h6>
                        <input type="text" class="form-control" name="so_container_cu" placeholder="Số container cũ">
                    </div>
                    <div class="col-2">
                        <h6>Số container mới</h6>
                        <input type="text" class="form-control" name="so_container_moi" placeholder="Số container mới">
                    </div>
                </div>

                <button class="btn btn-primary mt-2" type="submit">Done</button>
            </form>

            <hr>
            <form action="{{ route('quan-ly-khac.action-12') }}" method="POST">
                @csrf
                @method('POST')
                <h5>Sửa tên tàu ban đầu</h5>
                <div class="row">
                    <div class="col-2">
                        <h6>Nhập số tờ khai nhập</h6>
                        <input type="text" class="form-control" name="so_to_khai_nhap" placeholder="Số tờ khai nhập">
                    </div>
                    <div class="col-2">
                        <h6>Tên tàu mới</h6>
                        <input type="text" class="form-control" name="so_tau_moi" placeholder="Tên tàu mới">
                    </div>
                </div>

                <button class="btn btn-primary mt-2" type="submit">Done</button>
            </form>




            {{-- <form action="{{ route('quan-ly-khac.action-7') }}" method="POST" id="mainForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    Xác nhận thêm yêu cầu này?
                    <br>
                    <label class="mb-1"><strong>Chọn file đính kèm:</strong></label>
                    <br>
                    <div class="file-upload">
                        <input type="file" name="excel_file" class="file-upload-input" id="fileInput">
                        <button type="button" class="file-upload-btn">
                            <svg class="file-upload-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Chọn File
                        </button>
                        <span class="file-name" id="fileName"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Thêm yêu cầu</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </form> --}}
        </div>
    </div>

    <script>
        // const fileInput = document.getElementById('fileInput');
        // const fileName = document.getElementById('fileName');
        // const fileUpload = document.querySelector('.file-upload');
        // document.getElementById("fileInput").addEventListener("change", function() {
        //     let file = this.files[0]; // Get the selected file

        //     if (file && file.size > 5 * 1024 * 1024) { // 5MB = 5 * 1024 * 1024 bytes
        //         alert("File quá lớn! Vui lòng chọn tệp dưới 5MB.");
        //         this.value = ""; // Clear the file input
        //     } else {
        //         if (this.files && this.files[0]) {
        //             fileName.textContent = this.files[0].name;
        //             fileUpload.classList.add('file-selected');
        //         } else {
        //             fileName.textContent = '';
        //             fileUpload.classList.remove('file-selected');
        //         }
        //     }
        // });
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
        });
    </script>

@stop
