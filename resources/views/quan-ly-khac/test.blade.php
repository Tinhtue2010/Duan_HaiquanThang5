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
            <form action="{{ route('quan-ly-khac.action-7') }}" method="POST" id="mainForm" enctype="multipart/form-data">
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
            </form>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const fileUpload = document.querySelector('.file-upload');
        document.getElementById("fileInput").addEventListener("change", function() {
            let file = this.files[0]; // Get the selected file

            if (file && file.size > 5 * 1024 * 1024) { // 5MB = 5 * 1024 * 1024 bytes
                alert("File quá lớn! Vui lòng chọn tệp dưới 5MB.");
                this.value = ""; // Clear the file input
            } else {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    fileUpload.classList.add('file-selected');
                } else {
                    fileName.textContent = '';
                    fileUpload.classList.remove('file-selected');
                }
            }
        });
    </script>
@stop
