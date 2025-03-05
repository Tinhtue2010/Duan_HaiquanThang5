@extends('layout.base')

@section('title', 'Trang chủ')

@section('content')
    <!-- Tạo banner /slider -->
    <div class="container mt-4">
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('images/img/10.jpg') }}" class="d-block w-100" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/25.jpg') }}" class="d-block w-100" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/img/2.jpg') }}" class="d-block w-100" alt="Banner 3">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/img/3.jpg') }}" class="d-block w-100" alt="Banner 4">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/img/4.jpg') }}" class="d-block w-100" alt="Banner 5">
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/img/5.jpg') }}" class="d-block w-100" alt="Banner 6">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- Tạo giới thiệu Các hoạt động -->
    <div class="container my-5">
        <hr />
        <h2 class="text-center mb-4">Các hoạt động </h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <a class='clickable-card' href="/quan-ly-nhap-hang">
                        <img src="{{ asset('images/img/5.jpg') }}" class="card-img-top" alt="Nhập kho">
                        <div class="card-body">
                            <h5 class="card-title">Nhập kho</h5>
                            <p class="card-text justified-text">Giúp người dùng dễ dàng quản lý và theo dõi quá trình nhập
                                hàng vào kho, giúp quản lý thông tin hàng hóa trong kho một cách nhanh chóng, chính xác.
                            </p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <a class='clickable-card' href="#">
                        <img src="{{ asset('images/img/7.jpg') }}" class="card-img-top" alt="Xuất hàng">
                        <div class="card-body">
                            <h5 class="card-title">Xuất kho</h5>
                            <p class="card-text justified-text">Hỗ trợ người dùng giám sát và kiểm soát quá trình xuất
                                hàng ra khỏi
                                kho một cách hiệu quả. Đảm bảo việc xuất hàng diễn ra đúng quy trình, chính xác và tuân
                                thủ các quy định của hải quan.</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <a class='clickable-card' href="#">
                        <img src="{{ asset('images/img/8.jpg') }}" class="card-img-top" alt="Kho bãi">
                        <div class="card-body" href="#">
                            <h5 class="card-title">Hàng tồn </h5>
                            <p class="card-text justified-text">Giúp người dùng nắm bắt chính xác số lượng hàng hóa hiện
                                có trong kho
                                theo thời gian thực, cung cấp thông tin chi tiết về tình trạng, số lượng, và vị trí của
                                từng loại hàng
                                hóa.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="why-choose-section">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-lg-6">
                    <h2 class="section-title">Về chúng tôi</h2>
                    <p>Hải quan là cơ quan nhà nước có chức năng quản lý việc nhập khẩu và xuất khẩu hàng hóa qua các cửa
                        khẩu, biên giới của một quốc gia</p>

                    <div class="row my-5">
                        <div class="col-6 col-md-6">
                            <div class="feature">
                                <div class="icon">
                                    <img src="{{ asset('images/svg/truck.svg') }}" alt="Image" class="imf-fluid">
                                </div>
                                <h3>Quản lý Xuất nhập khẩu</h3>
                                <p>Hải quan Việt Nam đảm nhiệm việc giám sát và quản lý hàng hóa xuất nhập khẩu, đảm bảo
                                    tuân thủ quy định và luật pháp quốc gia.</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-6">
                            <div class="feature">
                                <div class="icon">
                                    <img src="{{ asset('images/svg/bag.svg') }}" alt="Image" class="imf-fluid">
                                </div>
                                <h3>Phòng, chống Buôn lậu</h3>
                                <p>Với nhiệm vụ kiểm soát biên giới, hải quan tích cực ngăn chặn buôn lậu và các hành vi
                                    gian lận thương mại, góp phần bảo vệ nền kinh tế.</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-6">
                            <div class="feature">
                                <div class="icon">
                                    <img src="{{ asset('images/svg/support.svg') }}" alt="Image" class="imf-fluid">
                                </div>
                                <h3>Tạo nguồn ngân sách</h3>
                                <p>Hải quan thu thuế từ hoạt động xuất nhập khẩu, đóng góp quan trọng vào nguồn thu ngân
                                    sách quốc gia và thúc đẩy phát triển kinh tế.</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-6">
                            <div class="feature">
                                <div class="icon">
                                    <img src="{{ asset('images/svg/return.svg') }}" alt="Image" class="imf-fluid">
                                </div>
                                <h3>Thương mại Quốc tế</h3>
                                <p>Bằng cách áp dụng công nghệ và đơn giản hóa thủ tục, hải quan Việt Nam tạo điều kiện
                                    thuận lợi cho các doanh nghiệp tham gia thương mại toàn cầu.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mt-5">
                    <div class="img-wrap">
                        <img src="{{ asset('images/img/9.jpg') }}" alt="Image" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex height=100 text-center text-light bg-image position-relative"
        style="background-image: url('{{ asset('images/22.jpg') }}'); background-position: center; height: 100vh;">
        <div class="mask position-absolute w-100 h-100" style="background-color: rgba(0, 0, 0, 0.6);">
            <div
                class="cover-container d-flex w-100 h-100 p-3 mx-auto justify-content-center align-items-center flex-column">
                <h1 class="text-light">Bạn muốn liên hệ với chúng tôi?</h1>
                <center class="lead">Hãy gửi tin nhắn cho chúng tôi tại đây</center>
                <p class="lead mt-3">
                    <a href="/lien-he" class="btn btn-lg btn-secondary fw-bold border-white bg-white text-dark">Liên
                        hệ tại đây</a>
                </p>
            </div>
        </div>
    </div>
@stop
