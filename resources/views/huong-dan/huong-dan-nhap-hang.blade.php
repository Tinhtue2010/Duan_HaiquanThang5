@extends('layout.base')

@section('title', 'Hướng dẫn nhập hàng')

@section('content')
    <center>
        <div class="custom-line"></div>
    </center>
    <div class="container">
        <div class="row">
            <h2>Hướng dẫn quản lý nhập hàng</h2>
            <em>
                <h5>Dưới đây là hướng dẫn các bước thực hiện quản lý nhập hàng</h5>
            </em>
            <br />
            <div class="col-2"></div>
            <div class="col-8">
                <div class="card shadow mb-4  mt-5">
                    <div class="card-body m-2">
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Quản lý tờ khai nhập”</span> trong
                            thanh điều hướng và chọn nút <span class="text-success">“Nhập tờ khai”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image023.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Hệ thống sẽ chuyển sang trang nhập tờ khai nhập hàng hóa mới</p>
                        <p class="post-content">Phần đầu <span class="fw-bold">“Đang chờ duyệt”</span> là thông tin chung
                            của tờ khai </p>
                        <center><img src="{{ asset('images/huong-dan/image024.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Phần thứ 2 “Thông tin hàng hóa” cho phép doanh nghiệp nhập nhiều dòng hàng
                            hóa, sau khi điền đầy đủ thông tin hàng hóa, chọn “Thêm dòng mới” để thêm một dòng hàng hóa mới
                            vào tờ khai, doanh nghiệp có thể xóa dòng hàng hóa, hoặc sửa thông tin của dòng hàng hóa</p>
                        <p class="post-content">Sau khi thêm đủ dòng hàng vào tờ khai, chọn nút <span
                                class="text-success">“Nhập tờ khai”</span> để thêm tờ khai vào hệ thống</p>
                        <center><img src="{{ asset('images/huong-dan/image025.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi thêm tờ khai vào hệ thống, tờ khai sẽ ở trong trạng thái <span
                                class="text-primary">“Đang chờ duyệt”</span>, lúc này doanh nghiệp có thể chọn <span
                                class="text-danger">“Hủy nhập đơn”</span> nếu có nhầm lẫn</p>
                        <center><img src="{{ asset('images/huong-dan/image026.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Lúc này tờ khai nhập sẽ xuất hiện trong danh sách tờ khai nhập</p>
                        <center><img src="{{ asset('images/huong-dan/image027.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi được cán bộ công chức duyệt tờ khai nhập, tờ khai sẽ ở trong trạng
                            thái <span class="text-success">“Đã nhập hàng”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image028.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Tờ khai nhập sẽ xuất hiện trong <span class="fw-bold">“Danh sách tờ khai
                                nhập đã nhập hàng”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image029.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Lúc này, doanh nghiệp có thể lập phiếu xuất để xuất hàng từ tờ khai nhập đã
                            được duyệt</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
