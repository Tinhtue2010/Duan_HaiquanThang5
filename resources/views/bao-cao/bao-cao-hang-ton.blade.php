@extends('layout.user-layout')

@section('title', 'Kết xuất báo cáo')

@section('content')
    <div id="layoutSidenav_content">
        <div class=" px-4">
            <div class="card shadow mb-4">
                <div class="card-header pt-3">
                    <div class="row">
                        @if (session('alert-success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                                <strong>{{ session('alert-success') }}</strong>
                            </div>
                        @elseif (session('alert-danger'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                                <strong>{{ session('alert-danger') }}</strong>
                            </div>
                        @endif
                        <div class="col-9">
                            <h4 class="font-weight-bold text-primary">Kết xuất báo cáo</h4>
                        </div>
                        <div class="col-3">
                        </div>
                    </div>
                </div>
                <div class="container-fluid card-body">
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 1)->first()?->phan_quyen == 1)
                                <h4>Báo cáo số lượng container lưu tại cảng</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.so-luong-container-tai-cang') }}" method="GET">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 2)->first()?->phan_quyen == 1)
                                <h4>Báo cáo hàng tồn tại cảng</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.hang-ton-tai-cang') }}" method="GET">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 3)->first()?->phan_quyen == 1)
                                <h4>Báo cáo theo dõi trừ lùi xuất hàng tất cả các ngày</h4>
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                    <form action="{{ route('export.theo-doi-tru-lui-tat-ca') }}" method="GET">
                                        <input type="text" class="form-control" id="so_to_khai_nhap"
                                            name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif

                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 4)->first()?->phan_quyen == 1)
                                <h4>Báo cáo tiếp nhận hằng ngày</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.tiep-nhan-hang-ngay') }}" method="GET">
                                        <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                        <input type="text" id="datepicker15" class="form-control"
                                            placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            <h4>Báo cáo theo dõi trừ lùi xuất hàng cuối ngày</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                <form action="{{ route('export.theo-doi-tru-lui-cuoi-ngay') }}" method="GET">
                                    <input type="text" class="form-control" id="so_to_khai_nhap" name="so_to_khai_nhap"
                                        placeholder="Nhập số tờ khai" required>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                            cáo</button>
                                    </center>
                                </form>
                            </div>

                        </div>
                        <div class="card p-3 ms-3 col-5">
                            
                        </div>
                    </div> --}}
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 5)->first()?->phan_quyen == 1)
                                <h4>Báo cáo theo dõi trừ lùi theo ngày</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.theo-doi-tru-lui-theo-ngay') }}" method="GET">
                                        <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="idNhap" name="so_to_khai_nhap"
                                                placeholder="Nhập số tờ khai" required>
                                            <button type="button" id="searchLanTruLui"
                                                class="btn btn-secondary">Tìm</button>
                                        </div>

                                        <label class="label-text mb-1 mt-2" for="">Chọn ngày</label>
                                        <select class="form-control" id="lan-xuat-canh-dropdown-search" name="ma_theo_doi"
                                            required>
                                            <option></option>
                                        </select>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 6)->first()?->phan_quyen == 1)
                                <h4>Báo cáo đăng ký xuất khẩu hàng hóa</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.dang-ky-xuat-khau-hang-hoa') }}" method="GET">
                                        <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công
                                            ty</label>
                                        <select class="form-control" id="doanh-nghiep-dropdown-search-2"
                                            name="ma_doanh_nghiep" required>
                                            <option value="" data-ten-doanh-nghiep="">Chọn doanh nghiệp</option>
                                            @foreach ($doanhNghieps as $doanhNghiep)
                                                <option value="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                    data-ten-doanh-nghiep="{{ $doanhNghiep->ten_doanh_nghiep }}">
                                                    {{ $doanhNghiep->ten_doanh_nghiep }}
                                                    ({{ $doanhNghiep->chuHang->ten_chu_hang ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <label class="label-text mb-2" for="ma_to_khai">Ngày</label>
                                        <input type="text" id="datepicker2" class="form-control" placeholder="dd/mm/yyyy"
                                            name="tu_ngay" readonly>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 7)->first()?->phan_quyen == 1)
                                <h4>Báo cáo hàng tồn theo tờ khai nhập</h4>
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                    <form action="{{ route('export.hang-ton-theo-to-khai') }}" method="GET">
                                        <input type="text" class="form-control" id="so_to_khai_nhap"
                                            name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 8)->first()?->phan_quyen == 1)
                                <h4>Báo cáo hàng hóa xuất nhập khẩu</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.bao-cao-hang-hoa-xuat-nhap-khau') }}" method="GET">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                                <input type="text" id="datepicker3" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                                <input type="text" id="datepicker4" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                            </div>
                                        </div>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 9)->first()?->phan_quyen == 1)
                                <h4>Báo cáo hàng tồn theo Doanh nghiệp</h4>
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                    <form action="{{ route('export.hang-ton-doanh-nghiep') }}" method="GET">
                                        <select class="form-control" id="doanh-nghiep-dropdown-search-3"
                                            name="ma_doanh_nghiep" required>
                                            <option value="" data-ten-doanh-nghiep="">Chọn doanh nghiệp</option>
                                            @foreach ($doanhNghieps as $doanhNghiep)
                                                <option value="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                    data-ten-doanh-nghiep="{{ $doanhNghiep->ten_doanh_nghiep }}">
                                                    {{ $doanhNghiep->ten_doanh_nghiep }}
                                                    ({{ $doanhNghiep->chuHang->ten_chu_hang ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <!-- Hidden input to send the ten_doanh_nghiep -->
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 10)->first()?->phan_quyen == 1)
                                <div class="form-group">
                                    <h4>Báo cáo hàng tồn theo đại lý</h4>
                                    <label class="label-text mb-2" for="chu_hang">Tên đại lý</label>
                                    <form action="{{ route('export.hang-ton-chu-hang') }}" method="GET">
                                        <select class="form-control" id="chu-hang-dropdown-search" name="ma_chu_hang"
                                            onchange="updateTenChuHang()" required>
                                            <option value="" data-ten-chu-hang="">Chọn đại lý</option>
                                            @foreach ($chuHangs as $chuHang)
                                                <option value="{{ $chuHang->ma_chu_hang }}"
                                                    data-ten-chu-hang="{{ $chuHang->ten_chu_hang }}">
                                                    {{ $chuHang->ten_chu_hang }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="ten-chu-hang" name="ten_chu_hang" value="">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 11)->first()?->phan_quyen == 1)
                                <h4>Báo cáo chi tiết hàng hóa xuất nhập khẩu</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.chi-tiet-xnk-trong-ngay') }}" method="GET">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                                <input type="text" id="datepicker" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                                <input type="text" id="datepicker2" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                            </div>
                                        </div>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 12)->first()?->phan_quyen == 1)
                                <h4>Báo cáo thống kê hàng hóa sang cont, chuyển tàu, kiểm tra hàng</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.bao-cao-sang-cont-chuyen-tau') }}" method="GET">
                                        <label class="label-text mb-1 mt-2" for="">Cán bộ công chức</label>
                                        <select class="form-control" id="cong-chuc-dropdown-search-2"
                                            name="ma_cong_chuc">
                                            <option></option>
                                            <option value="Tất cả">
                                                Tất cả
                                            </option>
                                            @foreach ($congChucs as $congChuc)
                                                <option value="{{ $congChuc->ma_cong_chuc }}">
                                                    {{ $congChuc->ten_cong_chuc }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                                <input type="text" id="datepicker10" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                                <input type="text" id="datepicker11" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                            </div>
                                        </div>
                                        <center>
                                            <button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 13)->first()?->phan_quyen == 1)
                                <h4>Báo cáo doanh nghiệp xuất nhập khẩu hàng hóa</h4>
                                <br>
                                <br>
                                <br>
                                <div class="form-group">
                                    <form action="{{ route('export.doanh-nghiep-xnk') }}" method="GET">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 14)->first()?->phan_quyen == 1)
                                <h4>Báo cáo doanh nghiệp xuất nhập khẩu hàng hóa</h4>
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_doanh_nghiep">Tên Doanh nghiệp/Công ty</label>
                                    <form action="{{ route('export.doanh-nghiep-xnk-theo-dn') }}" method="GET">
                                        <select class="form-control" id="doanh-nghiep-dropdown-search-5"
                                            name="ma_doanh_nghiep" required>
                                            <option value="">Chọn doanh nghiệp</option>
                                            @foreach ($doanhNghieps as $doanhNghiep)
                                                <option value="{{ $doanhNghiep->ma_doanh_nghiep }}">
                                                    {{ $doanhNghiep->ten_doanh_nghiep }}
                                                    ({{ $doanhNghiep->chuHang->ten_chu_hang ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <center>
                                            <button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 15)->first()?->phan_quyen == 1)
                                <h4>Báo cáo hàng chuyển cửa khẩu xuất (Quay về kho)</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.chuyen-cua-khau-xuat') }}" method="GET">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 16)->first()?->phan_quyen == 1)
                                <h4>Theo dõi hàng hóa quá 15 ngày chưa thực xuất</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.hang-hoa-chua-thuc-xuat') }}" method="GET">
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button>
                                        </center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="card p-3 me-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 17)->first()?->phan_quyen == 1)
                                <h4>Báo cáo số lượng tờ khai xuất hết</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.so-luong-to-khai-xuat-het') }}" method="GET">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                                <input type="text" id="datepicker8" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                                <input type="text" id="datepicker9" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                            </div>
                                        </div>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="card p-3 ms-3 col-5">
                            @if (Auth::user()->loai_tai_khoan === 'Cán bộ công chức' &&
                                    Auth::user()->congChuc->phanQuyenBaoCao->where('ma_bao_cao', 18)->first()?->phan_quyen == 1)
                                <h4>Báo cáo giám sát hàng hóa xuất khẩu</h4>
                                <div class="form-group">
                                    <form action="{{ route('export.bao-cao-giam-sat-xuat-khau') }}" method="GET">
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Từ ngày</label>
                                                <input type="text" id="datepicker12" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="tu_ngay" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="label-text mb-2" for="ma_to_khai">Đến ngày</label>
                                                <input type="text" id="datepicker13" class="form-control"
                                                    placeholder="dd/mm/yyyy" name="den_ngay" readonly>
                                            </div>
                                            <label class="label-text mb-1 mt-2" for="">Cán bộ công chức</label>
                                            <select class="form-control" id="cong-chuc-dropdown-search"
                                                name="ma_cong_chuc">
                                                <option></option>
                                                @foreach ($congChucs as $congChuc)
                                                    <option value="{{ $congChuc->ma_cong_chuc }}">
                                                        {{ $congChuc->ten_cong_chuc }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo
                                                cáo</button></center>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- <div class="card p-3 ms-3 col-5">
                            <h4>In phiếu xuất hàng</h4>
                            <form action="{{ route('xuat-hang.export-to-khai-xuat') }}" method="GET">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="so_to_khai_nhap"
                                                name="so_to_khai_nhap" placeholder="Nhập số tờ khai" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" id="lan_xuat_canh"
                                                name="lan_xuat_canh" placeholder="Nhập lần xuất" required>
                                        </div>
                                    </div>
                                    <center><button type="submit" class="btn btn-primary mt-1">Tải xuống</button>
                                    </center>
                                </div>
                            </form>
                        </div> --}}
    {{-- <div class="card p-3 ms-3 col-5">
                            <h4>Theo dõi hàng hóa xuất nhập khẩu</h4>
                            <div class="form-group">
                                <label class="label-text mb-2" for="ma_to_khai">Số tờ khai nhập</label>
                                <form action="{{ route('export.theo-doi-hang-hoa') }}" method="GET">
                                    <input type="text" class="form-control" id="nhapHangId" name="so_to_khai_nhap"
                                        placeholder="Nhập số tờ khai" required>
                                    <center>
                                        <button type="button" class="btn btn-primary mt-2 mb-2"
                                            id="fetchHangHoa">Chọn</button>
                                    </center>
                                    <select id="hang-hoa-dropdown-search" name="ma_hang" class="mt-2"></select>
                                    <center>
                                        <button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div>
                        </div> --}}
    {{-- <h4>Phiếu đăng ký kế hoạch xuất nhập khẩu theo xuồng</h4>
                            <div class="form-group">
                                <form action="{{ route('export.phieu-xuat-theo-xuong') }}" method="GET">
                                    <label class="label-text mb-2" for="ngay_thong_quan">Ngày đăng ký</label>
                                    <input type="text" id="datepicker9" class="form-control"
                                                placeholder="dd/mm/yyyy" name="tu_ngay" readonly>

                                    <label class="label-text mb-2" for="ngay_thong_quan">Doanh nghiệp</label>
                                    <select class="form-control" id="doanh-nghiep-dropdown-search-3" name="ma_doanh_nghiep"
                                        required>
                                        <option value="" data-ten-doanh-nghiep="">Chọn doanh nghiệp</option>
                                        @foreach ($doanhNghieps as $doanhNghiep)
                                            <option value="{{ $doanhNghiep->ma_doanh_nghiep }}"
                                                data-ten-doanh-nghiep="{{ $doanhNghiep->ten_doanh_nghiep }}">
                                                {{ $doanhNghiep->ten_doanh_nghiep }}
                                                (Mã {{ $doanhNghiep->ma_doanh_nghiep }} Đại lý: {{ $doanhNghiep->chuHang->ten_chu_hang ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="label-text mb-2" for="ngay_thong_quan">Chọn phương tiện vận tải</label>
                                    <select class="form-control" name="so_ptvt_xuat_canh" id="ptvt-dropdown-search">
                                        <option value=""></option>
                                        @foreach ($ptvtXuatCanhs as $ptvtXuatCanh)
                                            <option value="{{ $ptvtXuatCanh->so_ptvt_xuat_canh }}">
                                                {{ $ptvtXuatCanh->ten_phuong_tien_vt }} (Số:
                                                {{ $ptvtXuatCanh->so_ptvt_xuat_canh }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <center><button type="submit" class="btn btn-primary mt-2">Tải xuống báo cáo</button>
                                    </center>
                                </form>
                            </div> --}}
    <script>
        function updateTenChuHang() {
            const dropdown = document.getElementById('chu-hang-dropdown-search');
            const selectedOption = dropdown.options[dropdown.selectedIndex];
            const tenChuHang = selectedOption.getAttribute('data-ten-chu-hang'); // Fixed variable name here

            // Update the hidden input field
            document.getElementById('ten-chu-hang').value = tenChuHang || '';
        }
    </script>
    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker2').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker3').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker4').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker5').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker6').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker7').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker8').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker9').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker10').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker11').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker12').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker13').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker14').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });
            $('#datepicker15').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize all datepickers
            $('[id^=datepicker]').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi',
                endDate: '0d'
            });

            // Function to validate date fields in a form
            function validateDateFields(form) {
                const tuNgay = $(form).find('[name="tu_ngay"]').val();
                const denNgay = $(form).find('[name="den_ngay"]').val();

                // if (!tuNgay || !denNgay) {
                //     alert("Vui lòng chọn đủ hai ngày trước khi tải xuống báo cáo.");
                //     return false; // Prevent form submission
                // }
                return true; // Allow form submission
            }

            // Attach submit event to all forms with date fields
            $('form').on('submit', function(event) {
                const formHasDates = $(this).find('[name="tu_ngay"], [name="den_ngay"]').length > 0;
                if (formHasDates && !validateDateFields(this)) {
                    event.preventDefault(); // Prevent submission if validation fails
                }
            });
        });
        $('#fetchHangHoa').on('click', function() {
            const so_to_khai_nhap = $('#nhapHangId').val();
            console.log(so_to_khai_nhap);
            $.ajax({
                url: `/get-hang-hoa/${so_to_khai_nhap}`,
                method: 'GET',
                success: function(response) {
                    const dropdown = $('#hang-hoa-dropdown-search');
                    dropdown.empty(); // Clear existing options

                    response.forEach(hangHoa => {
                        dropdown.append(
                            `<option value="${hangHoa.ma_hang}">${hangHoa.ten_hang}</option>`
                        );
                    });
                },
                error: function() {
                    alert('Failed to fetch hangHoa!');
                }
            });
        });
        $('#searchLanTruLui').on('click', function() {
            const so_to_khai_nhap = $('#idNhap').val();
            $.ajax({
                url: `/get-lan-tru-lui/${so_to_khai_nhap}`,
                method: 'GET',
                success: function(response) {
                    const dropdown = $('#lan-xuat-canh-dropdown-search');
                    dropdown.empty();
                    console.log(response);
                    response.forEach(theoDoiTruLuis => {
                        dropdown.append(
                            `<option value="${theoDoiTruLuis.ma_theo_doi}">${theoDoiTruLuis.cong_viec} - Ngày ${theoDoiTruLuis.ngay_them}</option>`
                        );
                    });
                },
                error: function() {
                    alert('Không tìm thấy tờ khai nhập');
                }
            });
        });
    </script>
    <script>
        $('#cong-chuc-dropdown-search-2').select2({
            placeholder: "Chọn lần xuất cảnh",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            ajax: {
                dataType: 'json',
                delay: 250, // Delay for AJAX search
                processResults: function(data) {
                    return {
                        results: data.items
                    };
                }
            },
        });
        $('#cong-chuc-dropdown-search-3').select2({
            placeholder: "Chọn lần xuất cảnh",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            ajax: {
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: data.items
                    };
                }
            },
        });
        $('#cong-chuc-dropdown-search-2').select2({
            placeholder: "Chọn công chức",
            allowClear: true,
        });
        $('#cong-chuc-dropdown-search-3').select2({
            placeholder: "Chọn công chức",
            allowClear: true,
        });
    </script>

@stop
