@extends('layout.base')

@section('title', 'Hướng dẫn quản lý yêu cầu')

@section('content')
    <center>
        <div class="custom-line"></div>
    </center>
    <div class="container">
        <div class="row">
            <h2>Hướng dẫn quản lý yêu cầu</h2>
            <em>
                <h5>Dưới đây là hướng dẫn các bước thực hiện quản lý yêu cầu</h5>
            </em>
            <br />
            <div class="col-2"></div>
            <div class="col-8">
                <div class="card shadow mb-4  mt-5">
                    <div class="card-body m-2">
                        <p class="post-content fw-bold">1. Thêm yêu cầu chuyển container</span></p>
                        <center><img src="{{ asset('images/huong-dan/image049.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Yêu cầu chuyển container”</span>
                            trong thanh điều hướng và chọn nút <span class="text-success">“Nhập yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image050.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp chọn số tờ khai nhập cần chuyển container từ ô chọn và ấn nút
                            <span class="text-primary">“Chọn”</span>
                        </p>
                        <center><img src="{{ asset('images/huong-dan/image051.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Cửa sổ mới sẽ hiện lên cho phép doanh nghiệp chọn số container mới để chuyển
                            hàng sang</p>
                        <center><img src="{{ asset('images/huong-dan/image052.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp có thể chọn nhiều tờ khai để chuyển container. Sau khi hoàn
                            thành việc chọn, nhấn nút <span class="text-success">“Nhập yêu cầu”</span> để thêm yêu cầu
                            chuyển container</p>
                        <center><img src="{{ asset('images/huong-dan/image053.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Yêu cầu chuyển hàng mới thêm sẽ ở trạng thái đang chờ duyệt, doanh nghiệp có
                            thể <span class="text-danger">“Hủy yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image054.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi được cán bộ công chức duyệt, doanh nghiệp có thể chọn nút <span
                                class="text-success">“In phiếu yêu cầu”</span> để in ra phiếu</p>
                        <center><img src="{{ asset('images/huong-dan/image055.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>




                        <p class="post-content fw-bold mt-5">2. Thêm yêu cầu chuyển tàu</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Yêu cầu chuyển tàu”</span> trong
                            thanh điều hướng và chọn nút <span class="text-success">“Nhập yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image056.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp chọn số tờ khai nhập cần chuyển tàu từ ô chọn và ấn nút <span
                                class="text-primary">“Chọn”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image057.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Cửa sổ mới sẽ hiện lên cho phép doanh nghiệp nhập tên tàu mới để chuyển hàng
                            sang</p>
                        <center><img src="{{ asset('images/huong-dan/image058.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp có thể chọn nhiều tờ khai để chuyển tàu. Sau khi hoàn thành
                            việc chọn, nhấn nút <span class="text-success">“Nhập yêu cầu”</span> để thêm yêu cầu chuyển tàu
                        </p>
                        <center><img src="{{ asset('images/huong-dan/image059.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Yêu cầu chuyển tàu mới thêm sẽ ở trạng thái đang chờ duyệt, doanh nghiệp có
                            thể chọn <span class="text-danger">“Hủy yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image060.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi được cán bộ công chức duyệt, doanh nghiệp có thể chọn nút <span
                                class="text-success">“In phiếu yêu cầu chuyển tàu”</span> để in ra phiếu</p>

                        <p class="post-content fw-bold mt-5">3. Thêm yêu cầu kiểm tra hàng</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Yêu cầu kiểm tra hàng”</span> trong
                            thanh điều hướng và chọn nút <span class="text-success">“Nhập yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image061.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp chọn số tờ khai nhập cần kiểm tra từ ô chọn và ấn nút <span
                                class="text-primary">“Chọn”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image062.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp có thể chọn nhiều tờ khai để yêu cầu kiểm tra. Sau khi hoàn
                            thành việc chọn, nhấn nút <span class="text-success">“Nhập yêu cầu”</span> để thêm yêu cầu kiểm
                            tra</p>
                        <center><img src="{{ asset('images/huong-dan/image063.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Yêu cầu chuyển tàu mới thêm sẽ ở trạng thái đang chờ duyệt, doanh nghiệp có
                            thể chọn <span class="text-danger">“Hủy yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image064.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi được cán bộ công chức duyệt, doanh nghiệp có thể chọn nút <span
                                class="text-success">“In phiếu yêu cầu”</span> để in ra phiếu</p>
                        <center><img src="{{ asset('images/huong-dan/image065.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>

                        <p class="post-content fw-bold mt-5">4. Thêm yêu cầu niêm phong container</p>
                        <p class="post-content">Doanh nghiệp chọn <span class="fw-bold">“Yêu cầu niêm phong
                                container”</span> trong thanh điều hướng và chọn nút <span class="text-success">“Nhập yêu
                                cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image066.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Doanh nghiệp nhập số container cần niêm phong từ ô chọn và ấn nút <span
                                class="text-primary">“Thêm dòng”</span>, doanh nghiệp có thể thêm nhiều container để yêu
                            cầu niêm phong. Sau khi hoàn thành việc chọn, nhấn nút <span class="text-success">“Nhập yêu
                                cầu”</span> để thêm yêu cầu kiểm tra</p>
                        <center><img src="{{ asset('images/huong-dan/image067.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Yêu cầu niêm phong container mới thêm sẽ ở trạng thái đang chờ duyệt, doanh
                            nghiệp có thể chọn <span class="text-danger">“Hủy yêu cầu”</span></p>
                        <center><img src="{{ asset('images/huong-dan/image068.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                        <p class="post-content">Sau khi được cán bộ công chức duyệt, container sẽ được tiến hành niêm phong
                            với seal niêm phong mới</p>
                        <center><img src="{{ asset('images/huong-dan/image069.jpg') }}" class="mb-3 img-fluid"
                                style="max-width: 100%; height: auto;"></center>
                    </div>


                </div>
            </div>
        </div>
    </div>
@stop
