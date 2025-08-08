<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>@yield('title', 'HẢI QUAN CỬA KHẨU CẢNG VẠN GIA')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (must be included before Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Bootstrap Datepicker CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.vi.min.js">
    </script>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styles2.css') }}">
    <link rel="stylesheet" href="{{ asset('js/DataTables/datatables.min.css') }}">

    <style>
        .sidenavToggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1031;
            padding: 0.75rem;
            background-color: #fff;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sidenavToggle:hover {
            background-color: #f8f9fa;
        }

        .sidenavToggle i {
            font-size: 1.25rem;
            color: #212529;
        }

        @media (min-width: 992px) {
            .sb-sidenav-toggled .layoutSidenav_nav {
                transform: translateX(-225px);
            }

            .sb-sidenav-toggled .layoutSidenav_content {
                margin-left: 0;
            }
        }
    </style>
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
                                    hàng</a>
                            </li>
                            <li><a class="dropdown-item" href="/huong-dan-yeu-cau">Hướng dẫn Quản lý yêu cầu</a>
                            </li>
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
                        </ul>
                    </li>

                    <!-- Menu có dropdown Hoạt động-->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="courseDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Hoạt động
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="courseDropdown">
                            <li><a class="dropdown-item" href="/quan-ly-nhap-hang">Quản lý nhập
                                    hàng</a></li>
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
                        <li class="nav-item text-primary text-center ms-2">
                            @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức')
                                <span class="text-primary">{{ Auth::user()->CongChuc->ten_cong_chuc }}</span>
                                (<span>{{ Auth::user()->CongChuc->ma_cong_chuc }}</span>)
                            @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp')
                                <div id="doanh-nghiep-text">
                                    {{ Auth::user()->doanhNghiep->ten_doanh_nghiep }}
                                    ({{ Auth::user()->doanhNghiep->chuHang->ten_chu_hang ?? '' }})

                                </div>
                            @endif
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
    <center>
        <div class="custom-line mb-2"></div>
    </center>
    <button class="sidenavToggle" id="sidebarToggle">
        <img class="side-bar-icon" src="{{ asset('images/icons/sidenav-toggle.png') }}">
    </button>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' || Auth::user()->loai_tai_khoan === 'Doanh nghiệp')
                            <div class="sb-sidenav-menu-heading">Quản lý nhập hàng</div>
                            <a class="nav-link" href="/quan-ly-nhap-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/import-goods.png') }}"></div>
                                Quản lý tờ khai nhập
                            </a>
                            <a class="nav-link" href="/to-khai-da-nhap-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/import-goods-done.png') }}"></div>
                                Tờ khai đã nhập hàng
                            </a>
                            <a class="nav-link" href="/to-khai-nhap-da-huy">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/import-goods-cancel.png') }}"></div>
                                Tờ khai đã hủy
                            </a>
                        @endif
                        @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' || Auth::user()->loai_tai_khoan === 'Doanh nghiệp')
                            <div class="sb-sidenav-menu-heading">Quản lý xuất hàng</div>
                            <a class="nav-link" href="/quan-ly-xuat-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-goods.png') }}"></div>
                                Quản lý phiếu xuất
                            </a>
                            <a class="nav-link" href="/to-khai-da-xuat-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-goods-done.png') }}"></div>
                                Phiếu đã duyệt
                            </a>
                            <a class="nav-link" href="/to-khai-xuat-da-huy">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-goods-cancel.png') }}"></div>
                                Phiếu đã hủy
                            </a>
                        @endif
                        @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' || Auth::user()->loai_tai_khoan === 'Doanh nghiệp')
                            <div class="sb-sidenav-menu-heading">Quản lý xuất nhập cảnh</div>

                            <a class="nav-link" href="/quan-ly-nhap-canh">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/import.svg') }}"></div>
                                Tờ khai nhập cảnh
                            </a>

                            <a class="nav-link" href="/quan-ly-xuat-canh">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/ptvtxc.png') }}"></div>
                                Tờ khai xuất cảnh
                            </a>
                        @endif
                        @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức')
                            <a class="nav-link" href="/danh-sach-xnc">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-import.png') }}"></div>
                                Theo dõi xuất nhập cảnh
                            </a>
                        @endif


                        @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' || Auth::user()->loai_tai_khoan === 'Doanh nghiệp')
                            <a class="nav-link" href="/danh-sach-ptvt-xc">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/ptvtxc.png') }}"></div>
                                Danh sách phương tiện vận tải
                            </a>
                            <div class="sb-sidenav-menu-heading">Quản lý yêu cầu</div>
                            <a class="nav-link" href="/danh-sach-yeu-cau-niem-phong">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/check-container.png') }}"></div>
                                Yêu cầu niêm phong container
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-tau-cont">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/taucont.png') }}"></div>
                                Yêu cầu chuyển container và tàu
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-container">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/move-container.png') }}"></div>
                                Yêu cầu chuyển container
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-chuyen-tau">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/move-ship.png') }}"></div>
                                Yêu cầu chuyển tàu
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-kiem-tra">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/check-product.png') }}"></div>
                                Yêu cầu kiểm tra hàng
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-tieu-huy">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/dispose.png') }}"></div>
                                Yêu cầu tiêu hủy hàng
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-hang-ve-kho">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/moveback.png') }}"></div>
                                Yêu cầu đưa hàng trở lại kho ban đầu
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-gia-han">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/giahan.png') }}"></div>
                                Yêu cầu gia hạn tờ khai
                            </a>
                            <a class="nav-link" href="/danh-sach-yeu-cau-go-seal">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/check-container.png') }}"></div>
                                Yêu cầu gỡ seal điện tử
                            </a>
                        @endif
                        @if (Auth::user()->loai_tai_khoan === 'Doanh nghiệp')
                            <div class="sb-sidenav-menu-heading">Quản lý tồn kho</div>
                            <a class="nav-link" href="/bao-cao-hang-theo-doanh-nghiep">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/report.png') }}"></div>
                                Kết xuất báo cáo
                            </a>
                        @elseif (Auth::user()->loai_tai_khoan === 'Cán bộ công chức')
                            <div class="sb-sidenav-menu-heading">Quản lý tồn kho</div>
                            <a class="nav-link" href="/bao-cao-hang-ton">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/report.png') }}"></div>
                                Kết xuất báo cáo
                            </a>
                            <a class="nav-link" href="/tra-cuu-container">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/container.png') }}"></div>
                                Tra cứu container
                            </a>
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' && Auth::user()->congChuc->is_ban_giao === 1)
                                <div class="sb-sidenav-menu-heading">Quản lý khác</div>
                                <a class="nav-link" href="/quan-ly-ban-giao-ho-so">
                                    <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                            src="{{ asset('images/icons/report.png') }}"></div>
                                    Bàn giao hồ sơ
                                </a>
                                <a class="nav-link" href="/danh-sach-lien-he">
                                    <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                            src="{{ asset('images/icons/report.png') }}"></div>
                                    Quản lý liên hệ
                                </a>
                            @endif
                        @elseif (Auth::user()->loai_tai_khoan === 'Thủ kho')
                            <div class="sb-sidenav-menu-heading">Quản lý seal niêm phong</div>
                            <a class="nav-link" href="/quan-ly-chi-niem-phong">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/seal.png') }}"></div>
                                Danh sách seal niêm phong
                            </a>
                            <a class="nav-link" href="/quan-ly-seal-dien-tu">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/seal.png') }}"></div>
                                Danh sách seal điện tử
                            </a>
                        @elseif (Auth::user()->loai_tai_khoan === 'Admin')
                            <div class="sb-sidenav-menu-heading">Quản lý thông tin</div>
                            <a class="nav-link" href="/quan-ly-tai-khoan">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/account.png') }}"></div>
                                Danh sách tài khoản
                            </a>
                            <a class="nav-link" href="/quan-ly-hai-quan">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/hai-quan.png') }}"></div>
                                Danh sách hải quan
                            </a>
                            <a class="nav-link" href="/quan-ly-chu-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/product-owner.jpg') }}"></div>
                                Danh sách đại lý
                            </a>
                            <a class="nav-link" href="/quan-ly-doanh-nghiep">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/company.png') }}"></div>
                                Danh sách doanh nghiệp
                            </a>
                            <a class="nav-link" href="/quan-ly-cong-chuc">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/officer.png') }}"></div>
                                Danh sách công chức
                            </a>
                            <a class="nav-link" href="/quan-ly-thu-kho">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/storage.png') }}"></div>
                                Danh sách thủ kho
                            </a>
                            <a class="nav-link" href="/quan-ly-thiet-bi-dang-nhap">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/login.png') }}"></div>
                                Quản lý đăng nhập
                            </a>
                            <div class="sb-sidenav-menu-heading">Quản lý khác</div>
                            <a class="nav-link" href="/quan-ly-loai-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/product-type.png') }}"></div>
                                Danh sách loại hàng
                            </a>
                            <a class="nav-link" href="/quan-ly-loai-hinh">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/document.png') }}"></div>
                                Danh sách loại hình
                            </a>
                            <a class="nav-link" href="/quan-ly-thuc-xuat">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-goods-done.png') }}"></div>
                                Quản lý thực xuất
                            </a>
                            <a class="nav-link" href="/quan-ly-yeu-cau-sua-xnc">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/export-import.png') }}"></div>
                                Yêu cầu sửa xuất nhập cảnh
                            </a>
                        @elseif (Auth::user()->loai_tai_khoan === 'Lãnh đạo')
                            <div class="sb-sidenav-menu-heading">Quản lý thông tin</div>
                            <a class="nav-link" href="/quan-ly-duyet-xuat-hang">
                                <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                        src="{{ asset('images/icons/document.png') }}"></div>
                                Danh sách yêu cầu sửa xuất hàng
                            </a>
                        @endif
                        <div class="sb-sidenav-menu-heading">Tài khoản</div>
                        <a class="nav-link" href="/thay-doi-mat-khau">
                            <div class="sb-nav-link-icon"><img class="side-bar-icon"
                                    src="{{ asset('images/icons/password.png') }}"></div>
                            Thay đổi mật khẩu
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        @yield('content')
    </div>

    <script src="{{ asset('js/idle-logout.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/temp-input-table.js') }}"></script>
    <script src="{{ asset('js/DataTables/datatables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
        });
        document.addEventListener('DOMContentLoaded', function() {
            const doanhNghiepElement = document.getElementById('doanh-nghiep-text');
            const text = doanhNghiepElement.innerText.trim();
            const words = text.split(/\s+/); // Split by whitespace
            const maxWordsPerLine = 6;
            let formattedText = '';

            for (let i = 0; i < words.length; i += maxWordsPerLine) {
                const line = words.slice(i, i + maxWordsPerLine).join(' ');
                formattedText += line + '\n'; // Add a newline after each line
            }

            doanhNghiepElement.innerText = formattedText.trim();
        });
    </script>
</body>

</html>
