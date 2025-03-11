<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'HẢI QUAN CỬA KHẨU CẢNG VẠN GIA')</title>
    <!-- Link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styles2.css') }}">
</head>

<body>
    <!-- Tạo thanh điều hướng -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <!-- Thêm logo vào .navbar-brand -->
            {{-- <a class="navbar-brand" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            </a> --}}
            <a class="navbar-brand" href="/">HỆ THỐNG THEO DÕI XUẤT - NHẬP - TỒN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Trang chủ</a>
                    </li>

                    <!-- Menu có dropdown Giới thiệu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="/" id="courseDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Giới thiệu
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="courseDropdown">
                            <li>
                                <strong class="ms-3">Hướng dẫn doanh nghiệp</strong>
                            </li>
                            <li><a class="dropdown-item" href="/huong-dan-bang">Hướng dẫn tương tác các bảng</a></li>
                            <li><a class="dropdown-item" href="/huong-dan-nhap-hang">Hướng dẫn Quản lý nhập
                                    hàng</a></li>
                            <li><a class="dropdown-item" href="/huong-dan-xuat-hang">Hướng dẫn Quản lý xuất
                                    hàng</a></li>
                            <li><a class="dropdown-item" href="/huong-dan-yeu-cau">Hướng dẫn Quản lý yêu cầu</a>
                            </li>
                            @if (Auth::user())
                                @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức')
                                    <li>
                                        <hr class="dropdown-divider">
                                        <strong class="ms-3">Hướng dẫn công chức</strong>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Hướng dẫn Quản lý nhập hàng</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Hướng dẫn Quản lý xuất hàng</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Hướng dẫn Quản lý yêu cầu</a>
                                    </li>
                                @endif
                            @endif

                        </ul>
                    </li>

                    <!-- Menu có dropdown Hoạt động-->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="courseDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Hoạt động
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="courseDropdown">
                            <li><a class="dropdown-item" href="/quan-ly-nhap-hang">Quản lý nhập hàng</a></li>
                            <li><a class="dropdown-item" href="/quan-ly-xuat-hang">Quản lý xuất hàng</a></li>
                            <li><a class="dropdown-item" href="/tra-cuu-container">Quản lý tồn kho</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/bao-cao-hang-ton">Tổng hợp, kết xuất báo biểu</a>
                            </li>
                        </ul>
                    </li>
                    @if (Auth::user())
                        <li class="nav-item">
                            <form action="{{ route('dang-xuat') }}" method="POST" style="display: none;"
                                id="logout-form">
                                @csrf
                            </form>
                            <a class="nav-link" href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Đăng xuất
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="/dang-nhap">Đăng nhập</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    @yield('content')

    <!--- tạo Footer-->
    <footer class="pt-4 pb-2 text-white" style="background-color: blue;">
        <div class="container">
            <div class="row">

                <!-- Phần Giới thiệu -->
                <div class="col-md-4 mb-3">
                    <h5 class="text-white">Giới thiệu</h5>
                    <p>Website này cung cấp cổng đăng ký thực hiện các thủ tục nhập kho, xuất kho, quản lý hàng
                        trong kho,...</p>
                </div>

                <!-- Phần liên hệ -->
                <div class="col-md-4 mb-3">
                    <h5 class="text-white">Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt-fill"></i> Địa chỉ: Vĩnh Thực, Móng Cái, Quảng Ninh</li>
                        <li><i class="bi bi-telephone-fill"></i> Điện thoại: 0901581975 </li>
                        <li><i class="bi bi-envelope-fill"></i> Email: haiquanvangia@customs.gov.vn</li>
                    </ul>
                </div>

                <!-- Phần Liên kết mạng xã hội -->
                <div class="col-md-4 mb-3">
                    <h5 class="text-white">Theo dõi chúng tôi</h5>
                    <ul class="list-inline">
                        <li class="list-inline-item"><a href="#" class="text-white"><i
                                    class="bi bi-facebook"></i> Facebook</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i
                                    class="bi bi-twitter"></i> Twitter</a></li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i
                                    class="bi bi-instagram"></i> Instagram</a>
                        </li>
                        <li class="list-inline-item"><a href="#" class="text-white"><i
                                    class="bi bi-linkedin"></i> LinkedIn</a></li>
                    </ul>
                </div>

            </div>

            <!-- Dòng Bản Quyền -->
            <div class="text-center pt-3">
                <p>Used by VanGia Port Border Gate Customs Branch ©2024
                    rights reserved</p>
            </div>
        </div>
    </footer>

    <!-- Đặt lệnh để các thành phần động của Bootstrap hoạt động -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
</body>

</html>
