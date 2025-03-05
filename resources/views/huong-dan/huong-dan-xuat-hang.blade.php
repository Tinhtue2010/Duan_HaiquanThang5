@extends('layout.base')

@section('title', 'Hướng dẫn xuất hàng')

@section('content')
    <center>
        <div class="custom-line"></div>
    </center>
    <div class="container">
        <div class="row">
            <h2>Hướng dẫn thực hiện xuất hàng</h2>
            <em>
                <h5>Dưới đây là hướng dẫn các bước thực hiện xuất hàng</h5>
            </em>
            <br />
            <div class="col-2"></div>
            <div class="col-8">
                <div class="card shadow mb-4  mt-5">
                    <div class="card-body m-2">
                        <p class="post-content fw-bold">1. Tạo phiếu xuất hàng</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Quản lý phiếu xuất”</span> trong
                            thanh điều hướng và chọn nút <span class="text-success">“Nhập phiếu xuất”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image030.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Tại đây doanh nghiệp chọn số tờ khai nhập mình muốn xuất hàng và nhấn nút
                            <span class="text-primary">“Xác nhận/Tìm kiếm”</span>
                        </p>
                        <center><img src="{{ asset('images/huong-dan/image031.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Doanh nghiệp có thể chọn nhiều dòng hàng hóa để xuất, nhưng chỉ được chọn
                            xuất hàng từ một tờ khai nhập hàng. Cửa sổ hiện ra gồm thông tin hàng hóa trong tờ khai nhập và
                            số lượng hàng:</p>
                        <p class="post-content">- Số lượng tồn: Số lượng hàng đang ở trong kho</p>
                        <p class="post-content">- Số lượng chờ xuất: Số lượng hàng xuất trong các phiếu xuất chưa thực hiện
                            xuất hàng</p>
                        <p class="post-content">- Số lượng có thể chọn: Số lượng hàng mà người dùng có thể chọn để xuất </p>
                        <p class="post-content">Người dùng chọn một dòng hàng hóa, nhập số lượng xuất và bấm nút <span
                                class="text-primary">“Chọn”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image032.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Dòng hàng mới sẽ được thêm vào phiếu xuất hàng, doanh nghiệp tiếp tục tiếp
                            tục chọn nút <span class="text-primary">“Xác nhận/Tìm kiếm”</span> để thêm dòng hàng mới</p>
                        <center><img src="{{ asset('images/huong-dan/image033.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Hệ thống sẽ hiển thị những dòng hàng còn lại chưa chọn phiếu xuất này</p>
                        <center><img src="{{ asset('images/huong-dan/image034.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Doanh nghiệp chọn Loại hình tờ khai và nhấn nút <span
                                class="text-success">“Nhập phiếu xuất hàng”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image035.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Hệ thống sẽ hiển thị phiếu xuất vừa tạo, doanh nghiệp có thể chọn nút <span
                                class="text-danger">“Hủy phiếu”</span> nếu đổi ý, hoặc chọn nút <span
                                class="text-primary">“Tờ khai nhập”</span> để xem thông tin của tờ khai nhập</p>
                        <center><img src="{{ asset('images/huong-dan/image036.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Phiếu xuất sau đó sẽ xuất hiện trong phần <span class="fw-bold">“Quản lý
                                phiếu xuất”</span> trong trạng thái <span class="text-primary">“Đang chờ duyệt”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image037.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Sau khi được cán bộ công chức duyệt phiếu xuất, doanh nghiệp có thể chọn
                            <span class="text-success">“In phiếu xuất”</span> để in phiếu xuất
                        </p>
                        <center><img src="{{ asset('images/huong-dan/image038.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Hoặc lưu phiếu dưới dạng PDF để in sau, chọn “More settings” để thay đổi cỡ
                            giấy,…</p>
                        <center><img src="{{ asset('images/huong-dan/image039.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content fw-bold mt-5">2. Thêm phương tiện vận tải xuất cảnh</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Phương tiện vận tải xuất
                                cảnh”</span> trong thanh điều hướng và chọn nút <span class="text-success">“Nhập tờ
                                khai”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image040.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Nhập thông tin phương tiện vận tải và nhấn nút <span
                                class="text-success">“Thêm tờ khai”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image041.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Phương tiện vận tải xuất cảnh mới của doanh nghiệp sẽ được thêm vào hệ thống
                        </p>
                        <center><img src="{{ asset('images/huong-dan/image042.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content fw-bold mt-5">3. Chọn hàng lên phương tiện vận tải</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Xếp hàng lên phương tiện”</span>
                            trong thanh điều hướng và chọn nút <span class="text-success">“Nhập tờ khai”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image043.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Doanh nghiệp chọn phiếu xuất đã được duyệt và nhấn nút “Thêm”</p>
                        <center><img src="{{ asset('images/huong-dan/image044.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>


                        <p class="post-content">Doanh nghiệp có thể chọn xuất nhiều phiếu xuất hàng trong một tờ khai. Chọn
                            phương tiện vận tải xuất cảnh vừa tạo và nhấn nút <span class="text-success">“Thêm tờ
                                khai”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image045.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Tờ khai mới sẽ được tạo trong trạng thái <span class="text-primary">“Đang
                                chờ duyệt”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image046.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Doanh nghiệp có thể chọn <span class="text-danger">“Xin hủy tờ
                                khai”</span>
                            để chọn phương tiện vận tải khác cho các phiếu xuất, khi đó tờ khai sẽ ở trong trạng thái <span
                                class="text-warning">“Xin hủy”</span>, và cán bộ công chức có thể chấp nhận yêu cầu hủy
                            hoặc từ chối yêu cầu hủy.</p>
                        <center><img src="{{ asset('images/huong-dan/image047.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content">Sau khi được cán bộ công chức duyệt, hàng hóa sẽ được xác nhận đã rời kho
                            và số lượng hàng trong kho sẽ trừ đi số lượng hàng xuất. Phiếu xuất hàng sẽ chuyển sang trạng
                            thái <span class="text-success">“Đã duyệt”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image048.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                    </div>


                </div>
            </div>
        </div>
    </div>
@stop
