@extends('layout.user-layout')

@section('title', 'Thêm tờ khai')

@section('content')
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
            @if (session('alert-success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-success') }}</strong>
                </div>
            @elseif(session('alert-danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="myAlert">
                    <strong>{{ session('alert-danger') }}</strong>
                </div>
            @endif
            <div class="row">
                <div class="col-6">
                    <a class="return-link" href="/quan-ly-nhap-hang">
                        <p>
                            < Quay lại quản lý nhập hàng</p>
                    </a>
                </div>
                <div class="col-6">
                    <a class="float-end" href="#">
                        <button data-bs-toggle="modal" data-bs-target="#chonFileModal" class="btn btn-success ">
                            Nhập từ file</button>
                    </a>
                </div>
            </div>

            <h2 class="text-center text-dark">{{ $doanhNghiep->ten_doanh_nghiep }}</h2>
            <h2 class="text-center text-dark">TỜ KHAI NHẬP KHẨU HÀNG HÓA</h2>
            <!-- Input fields for each column -->
            <div class="row">
                <div class="col-12">
                    <div class="card px-3 pt-3 mt-4">
                        <h3 class="text-center text-dark">Thông tin tờ khai</h3>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_hai_quan">Chi cục Hải quan</label>
                                    <select class="form-control" id="hai-quan-dropdown-search" name="ma_hai_quan">
                                        @foreach ($haiQuans as $haiQuan)
                                            <option></option>
                                            <option value="{{ $haiQuan->ma_hai_quan }}">
                                                {{ $haiQuan->ten_hai_quan }}
                                                ({{ $haiQuan->ma_hai_quan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="">Số tờ khai nhập</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2" id="so_to_khai_nhap" maxlength="12"
                                    name="so_to_khai_nhap" placeholder="Nhập số tờ khai nhập" required>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-1" for="ma_loai_hinh">Loại hình tờ khai</label>
                                    <select class="form-control" id="loai-hinh-dropdown-search" name="ma_loai_hinh">
                                        @foreach ($loaiHinhs as $loaiHinh)
                                            <option></option>
                                            <option value="{{ $loaiHinh->ma_loai_hinh }}">
                                                {{ $loaiHinh->ten_loai_hinh }}
                                                ({{ $loaiHinh->ma_loai_hinh }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="ngay_thong_quan">Ngày thông quan</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <input type="text" id="datepicker" class="form-control" placeholder="dd/mm/yyyy"
                                        name="ngay_thong_quan" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <label class="label-text" for="">Đại lý</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="chu-hang-dropdown-search" name="ma_chu_hang">
                                    @foreach ($chuHangs as $chuHang)
                                        <option></option>
                                        <option value="{{ $chuHang->ma_chu_hang }}">
                                            {{ $chuHang->ten_chu_hang }}
                                            ({{ $chuHang->ma_chu_hang }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="">Số container</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="so_container" maxlength="50"
                                    name="so_container" placeholder="Nhập số container" required>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="phuong_tien_vt_nhap">Phương tiện vận
                                    tải</label> <span class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2" id="phuong_tien_vt_nhap"
                                    name="phuong_tien_vt_nhap" placeholder="Nhập phương tiện vận tải" maxlength="50"
                                    required>
                            </div>
                            <div class="col-3">
                                <label class="label-text" for="trong_luong">Trọng lượng
                                    (Tấn)</label> <span class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2" id="trong_luong" name="trong_luong"
                                    placeholder="Nhập tổng trọng lượng (Tấn)" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h3 class="text-center text-dark">Thông tin hàng hóa</h3>

            <div class="row">
                <div class="col-8">
                    <div class="card p-3 mt-3">
                        <div class="row">
                            <div class="col-12">
                                <label class="label-text" for="ten_hang">Tên hàng</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="text" class="form-control mt-2 reset-input" id="ten_hang"
                                    name="ten_hang" placeholder="Nhập tên hàng hóa" maxlength="250" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="label-text mb-2" for="loai_hang">Loại hàng</label>
                                    <span class="text-danger missing-input-text"></span>
                                    <select class="form-control" id="loai-hang-dropdown-search" name="loai_hang">
                                        @foreach ($loaiHangs as $loaiHang)
                                            <option></option>
                                            <option value="{{ $loaiHang->ten_loai_hang }}">
                                                {{ $loaiHang->ten_loai_hang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="label-text mb-2" for="xuat_xu">Xuất xứ</label> <span
                                    class="text-danger missing-input-text"></span>
                                <select class="form-control" id="xuat-xu-dropdown-search" name="xuat_xu">
                                    <option value="">Nhập xuất xứ của sản phẩm</option>
                                    <option value="China">China</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="MaCao">MaCao</option>
                                    <option value="HongKong">HongKong</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Brunei">Brunei</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cabo Verde">Cabo Verde</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
                                    <option value="Congo (Congo-Kinshasa)">Congo (Congo-Kinshasa)</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czechia (Czech Republic)">Czechia (Czech Republic)</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Eswatini (fmr. &quot;Swaziland&quot;)">Eswatini (fmr. "Swaziland")
                                    </option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran">Iran</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Ivory Coast">Ivory Coast</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Kosovo">Kosovo</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Laos">Laos</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libya">Libya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Marshall Islands">Marshall Islands</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Micronesia">Micronesia</option>
                                    <option value="Moldova">Moldova</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar (formerly Burma)">Myanmar (formerly Burma)</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="North Korea">North Korea</option>
                                    <option value="North Macedonia">North Macedonia</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palau">Palau</option>
                                    <option value="Palestine">Palestine</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russia">Russia</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines
                                    </option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Korea">South Korea</option>
                                    <option value="South Sudan">South Sudan</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syria">Syria</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-Leste (East Timor)">Timor-Leste (East Timor)</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States of America">United States of America</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Vatican City">Vatican City</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Vietnam">Vietnam</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card p-3 mt-3">
                        <div class="row">
                            <div class="col-6">
                                <label class="label-text" for="so_luong_khai_bao">Số lượng</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="so_luong_khai_bao"
                                    name="so_luong_khai_bao" placeholder="Nhập số lượng sản phẩm" required>
                            </div>
                            <div class="col-6">
                                <label class="label-text mb-2" for="don_vi_tinh">Đơn vị tính</label> <span
                                    class="text-danger missing-input-text "></span>

                                <select name="don_vi_tinh" class="form-control mt-2 reset-input"
                                    id="don-vi-tinh-dropdown-search">
                                    <option value="">Chọn đơn vị tính</option>
                                    <option value="Kiện">Kiện</option>
                                    <option value="Hộp">Hộp</option>
                                    <option value="Bao">Bao</option>
                                    <option value="PP">PP</option>
                                    <option value="Pallet">Pallet</option>
                                    <option value="Kiện/Hộp/Bao">Kiện/Hộp/Bao</option>
                                    <option value="Thùng">Thùng</option>
                                    <option value="Đôi">Đôi</option>
                                    <option value="Tá">Tá</option>
                                    <option value="Chục">Chục</option>
                                    <option value="Cuộn">Cuộn</option>
                                    <option value="Sợi">Sợi</option>
                                    <option value="Tờ">Tờ</option>
                                    <option value="Quyển">Quyển</option>
                                    <option value="Viên">Viên</option>
                                    <option value="Vỉ">Vỉ</option>
                                    <option value="Cặp">Cặp</option>
                                    <option value="Thẻ">Thẻ</option>
                                    <option value="Bao">Bao</option>
                                    <option value="Lon">Lon</option>
                                    <option value="Chai">Chai</option>
                                    <option value="Ống">Ống</option>
                                    <option value="Tuýp">Tuýp</option>
                                    <option value="Bịch">Bịch</option>
                                    <option value="Miếng">Miếng</option>
                                    <option value="Tấm">Tấm</option>
                                    <option value="Cây">Cây</option>
                                    <option value="Khối">Khối</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="label-text" for="don_gia">Đơn giá (USD)</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="don_gia"
                                    placeholder="USD" name="don_gia" required>
                            </div>
                            <div class="col-6">
                                <label class="label-text" for="tri_gia">Trị giá (USD)</label> <span
                                    class="text-danger missing-input-text"></span>
                                <input type="number" class="form-control mt-2 reset-input" id="tri_gia"
                                    placeholder="USD" name="tri_gia" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-5">
                <center>
                    <button type="button" class="btn btn-primary mt-1" id="addRowButton">Thêm dòng
                        mới</button>
                </center>
            </div>



            <!-- Your existing table -->
            <table class="table table-bordered" id="displayTableNhapHang">
                <thead style="vertical-align: middle; text-align: center;">
                    <tr>
                        <th>STT</th>
                        <th>Tên hàng</th>
                        <th>Loại hàng</th>
                        <th>Xuất xứ</th>
                        <th>Số lượng</th>
                        <th>Đơn vị tính</th>
                        <th>Đơn giá (USD)</th>
                        <th>Trị giá (USD)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="vertical-align: middle; text-align: center;">Tổng cộng:</th>
                        <th id="sumSoLuong">0</th>
                        <th></th>
                        <th></th>
                        <th id="sumTriGia">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>

            <center>
                <button type="button" id="xacNhanBtn" class="btn btn-success mb-5">Nhập tờ khai</button>
            </center>

        </div>
    </div>
    {{-- Modal xác nhận --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Xác nhận thêm tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Xác nhận thêm tờ khai này?
                </div>
                <div class="modal-footer">
                    <form action="{{ route('nhap-hang.submit-to-khai-nhap') }}" method="POST" id="mainForm">
                        @csrf
                        <input type="hidden" name="rows_data" id="rowsDataInput">
                        <input type="hidden" name="ma_hai_quan" id="ma_hai_quan">
                        <input type="hidden" name="so_container" id="so_container_hidden">
                        <input type="hidden" name="ma_chu_hang" id="ma_chu_hang_hidden">
                        <input type="hidden" name="ma_loai_hinh" id="ma_loai_hinh">
                        <input type="hidden" name="phuong_tien_vt_nhap" id="phuong_tien_vt_nhap_hidden">
                        <input type="hidden" name="trong_luong" id="trong_luong_hidden">
                        <input type="hidden" name="so_to_khai_nhap" id="so_to_khai_nhap_hidden">
                        <input type="hidden" name="ngay_thong_quan" id="ngay_thong_quan_hidden">
                        <button type="submit" class="btn btn-success">Nhập tờ khai</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chonFileModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Chọn file để nhập dữ liệu</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold mb-0 fs-5">Hãy đảm bảo file có đủ các cột sau:</p>
                    <p class="fw-bold">Tên hàng, <label class="text-danger">Số lượng</label>, ĐVT, Trị Giá (USD)</p>
                    <div class="file-upload">
                        <input type="file" id="hys_file" class="file-upload-input">
                        <button type="button" class="file-upload-btn">
                            <svg class="file-upload-icon" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            Chọn File
                        </button>
                        <span class="file-name" id="fileName"></span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="label-text mb-2 fw-bold" for="xuat_xu">Xuất xứ</label> <span
                                class="text-danger missing-input-text"></span>
                            <select class="form-control" id="xuat-xu-2-dropdown-search" name="xuat_xu_2">
                                <option value="">Nhập xuất xứ của sản phẩm</option>
                                <option value="China">China</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="MaCao">MaCao</option>
                                <option value="HongKong">HongKong</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cabo Verde">Cabo Verde</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
                                <option value="Congo (Congo-Kinshasa)">Congo (Congo-Kinshasa)</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czechia (Czech Republic)">Czechia (Czech Republic)</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Eswatini (fmr. &quot;Swaziland&quot;)">Eswatini (fmr. "Swaziland")
                                </option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Greece">Greece</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-Bissau">Guinea-Bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Ivory Coast">Ivory Coast</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Kosovo">Kosovo</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Laos">Laos</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libya">Libya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova">Moldova</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar (formerly Burma)">Myanmar (formerly Burma)</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="North Korea">North Korea</option>
                                <option value="North Macedonia">North Macedonia</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Palestine">Palestine</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Romania">Romania</option>
                                <option value="Russia">Russia</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines
                                </option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="South Sudan">South Sudan</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syria">Syria</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-Leste (East Timor)">Timor-Leste (East Timor)</option>
                                <option value="Togo">Togo</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States of America">United States of America</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City">Vatican City</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="label-text mb-2 fw-bold" for="loai_hang">Loại hàng</label> <span
                                class="text-danger missing-input-text"></span>
                            <select class="form-control" id="loai-hang-2-dropdown-search" name="loai_hang_2">
                                <option></option>
                                @foreach ($loaiHangs as $loaiHang)
                                    <option value="{{ $loaiHang->ten_loai_hang }}">
                                        {{ $loaiHang->ten_loai_hang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" id="uploadHys" class="btn btn-success">Xác nhận</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const fileInput = document.getElementById('hys_file');
        const fileName = document.getElementById('fileName');
        const fileUpload = document.querySelector('.file-upload');
        document.getElementById("hys_file").addEventListener("change", function() {
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
    <script>
        // Update hidden fields when dropdowns change
        document.getElementById('hai-quan-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_hai_quan').value = this.value;
        });
        document.getElementById('loai-hinh-dropdown-search').addEventListener('change', function() {
            document.getElementById('ma_loai_hinh').value = this.value;
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            let hangHoaRows = @json($hangHoaRows ?? []);
            let rowsData = [];
            // Populate the rowsData array
            rowsData = hangHoaRows.map(row => ({
                ten_hang: row.ten_hang,
                loai_hang: row.loai_hang,
                xuat_xu: row.xuat_xu,
                so_luong_khai_bao: row.so_luong_khai_bao,
                don_vi_tinh: row.don_vi_tinh,
                don_gia: row.don_gia,
                tri_gia: row.tri_gia
            }));
            displayRows();

            function displayRows() {
                const tableBody = $("#displayTableNhapHang tbody");
                tableBody.empty();
                rowsData.forEach((row, index) => {
                    const newRow = `
                    <tr data-index="${index}">
                        <td>${index + 1}</td>
                        <td>${row.ten_hang}</td>
                        <td>${row.loai_hang}</td>
                        <td>${row.xuat_xu}</td>
                        <td>${row.so_luong_khai_bao}</td>
                        <td>${row.don_vi_tinh}</td>
                        <td>${row.don_gia}</td>
                        <td>${row.tri_gia}</td>
                        <td>
                            <button class="btn btn-warning btn-sm editRowButton">Sửa</button>
                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                        </td>
                    </tr>
                `;
                    tableBody.append(newRow);
                });
                updateTotals();
            }

            $("#addRowButton").click(function() {
                const ten_hang = $("#ten_hang").val();
                const loai_hang = $("#loai-hang-dropdown-search").val();
                const xuat_xu = $("#xuat-xu-dropdown-search").val();
                const so_luong_khai_bao = $("#so_luong_khai_bao").val();
                const don_vi_tinh = $("#don-vi-tinh-dropdown-search").val();
                const don_gia = $("#don_gia").val();
                const tri_gia = $("#tri_gia").val();

                let isValid = true;

                const fields = [{
                        id: "#ten_hang",
                        value: ten_hang
                    },
                    {
                        id: "#loai_hang",
                        value: loai_hang
                    },
                    {
                        id: "#xuat_xu",
                        value: xuat_xu
                    },
                    {
                        id: "#so_luong_khai_bao",
                        value: so_luong_khai_bao
                    },
                    {
                        id: "#don_vi_tinh",
                        value: don_vi_tinh
                    },
                    {
                        id: "#don_gia",
                        value: don_gia
                    },
                    {
                        id: "#tri_gia",
                        value: tri_gia
                    },
                ];

                fields.forEach(field => {
                    const warningText = $(field.id).siblings(".missing-input-text");
                    if (!field.value) {
                        warningText.text("*Thiếu thông tin").show();
                        isValid = false;
                    } else {
                        warningText.hide();
                    }
                });

                if (isValid) {
                    rowsData.push({
                        ten_hang,
                        loai_hang,
                        xuat_xu,
                        so_luong_khai_bao,
                        don_vi_tinh,
                        don_gia,
                        tri_gia,
                    });
                    displayRows();
                    $(".reset-input").val('');
                    $("#don-vi-tinh-dropdown-search").val('').trigger("change");
                    $("#xuat-xu-dropdown-search").val('').trigger("change");
                    $("#loai-hang-dropdown-search").val('').trigger('change');
                    $(".missing-input-text").hide();
                }
            });

            $(document).on("click", ".editRowButton", function() {
                const rowIndex = $(this).closest("tr").data("index");
                const rowData = rowsData[rowIndex];
                $("#ten_hang").val(rowData.ten_hang);
                $("#loai_hang").val(rowData.loai_hang);
                $("#so_luong_khai_bao").val(rowData.so_luong_khai_bao);
                $("#don_vi_tinh").val(rowData.don_vi_tinh);
                $("#don_gia").val(rowData.don_gia);
                $("#tri_gia").val(rowData.tri_gia);

                $("#don-vi-tinh-dropdown-search").val(rowData.don_vi_tinh).trigger("change");
                $("#xuat-xu-dropdown-search").val(rowData.xuat_xu).trigger("change");
                $("#loai-hang-dropdown-search").val(rowData.loai_hang).trigger("change");
                rowsData.splice(rowIndex, 1);
                displayRows();
            });

            $(document).on("click", ".deleteRowButton", function() {
                const rowIndex = $(this).closest("tr").data("index");
                rowsData.splice(rowIndex, 1);
                displayRows();
            });

            const nhapYeuCauButton = document.getElementById('xacNhanBtn');
            nhapYeuCauButton.addEventListener('click', function() {
                // Get values from dropdowns
                const maHaiQuan = document.getElementById('hai-quan-dropdown-search').value;
                const maLoaiHinh = document.getElementById('loai-hinh-dropdown-search').value;
                const maChuHang = document.getElementById('chu-hang-dropdown-search').value;
                const soContainer = $("#so_container").val();
                const ngayThongQuan = $('#datepicker').val();
                const soToKhaiNhap = $("#so_to_khai_nhap").val();
                const phuongTienVanTaiNhap = $("#phuong_tien_vt_nhap").val();
                const trongLuong = $("#trong_luong").val();

                // Validate required fields
                if (!maHaiQuan) {
                    alert('Vui lòng chọn hải quan');
                    return;
                } else if (!soContainer) {
                    alert('Vui lòng điền số container');
                    return;
                } else if (!ngayThongQuan) {
                    alert('Vui lòng chọn ngày thông quan');
                    return;
                } else if (!soToKhaiNhap) {
                    alert('Vui lòng điền số tờ khai nhập');
                    return;
                } else if (!phuongTienVanTaiNhap) {
                    alert('Vui lòng điền phương tiện vận tải');
                    return;
                } else if (!maChuHang) {
                    alert('Vui lòng chọn đại lý');
                    return;
                } else if (!maLoaiHinh) {
                    alert('Vui lòng chọn loại hình');
                    return;
                } else if (!trongLuong) {
                    alert('Vui lòng điền trọng lượng');
                    return;
                }
                if (!/^\d{12}$/.test(soToKhaiNhap)) {
                    alert("Số tờ khai nhập phải có đúng 12 chữ số!");
                    return;
                }
                // Get all rows from the table
                const tableRows = document.querySelectorAll('#displayTableNhapHang tbody tr');

                // Check if there are any rows
                if (tableRows.length === 0) {
                    alert('Vui lòng thêm ít nhất một hàng hóa');
                    return;
                }

                // Map the table data to an array of objects
                const rowsData = Array.from(tableRows).map(row => ({
                    ten_hang: row.cells[1].textContent,
                    loai_hang: row.cells[2].textContent,
                    xuat_xu: row.cells[3].textContent,
                    so_luong: row.cells[4].textContent,
                    don_vi_tinh: row.cells[5].textContent,
                    don_gia: row.cells[6].textContent,
                    tri_gia: row.cells[7].textContent,
                }));

                // Set values for hidden inputs
                document.getElementById('rowsDataInput').value = JSON.stringify(rowsData);
                document.getElementById('ma_hai_quan').value = maHaiQuan;
                document.getElementById('ma_loai_hinh').value = maLoaiHinh;
                document.getElementById('so_container_hidden').value = soContainer;
                document.getElementById('phuong_tien_vt_nhap_hidden').value = phuongTienVanTaiNhap;
                document.getElementById('ma_chu_hang_hidden').value = maChuHang;
                document.getElementById('trong_luong_hidden').value = trongLuong;
                document.getElementById('so_to_khai_nhap_hidden').value = soToKhaiNhap;
                document.getElementById('ngay_thong_quan_hidden').value = ngayThongQuan;

                // Submit the form
                $('#xacNhanModal').modal('show');
            });

            $("#uploadHys").on("click", function() {
                var file = $("#hys_file")[0].files[0];
                if (!file) {
                    alert("Xin hãy chọn 1 file!");
                    return;
                }
                const loai_hang = $("#loai-hang-2-dropdown-search").val();
                const xuat_xu = $("#xuat-xu-2-dropdown-search").val();

                var formData = new FormData();
                formData.append("hys_file", file);
                formData.append("loai_hang", loai_hang);
                formData.append("xuat_xu", xuat_xu);
                formData.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('nhap-hang.upload-file-nhap') }}", // Define the route in Laravel
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (!response.data) {
                            alert(response);
                        } else {
                            var tbody = $("#displayTableNhapHang tbody");
                            tbody.empty();

                            $.each(response.data, function(index, row) {
                                var tr = `<tr>
                                        <td>${index + 1}</td>
                                        <td>${row.ten_hang}</td>
                                        <td>${row.loai_hang}</td>
                                        <td>${row.xuat_xu}</td>
                                        <td>${row.so_luong_khai_bao}</td>
                                        <td>${row.don_vi_tinh}</td>
                                        <td>${row.don_gia}</td>
                                        <td>${row.tri_gia}</td>
                                        <td>
                                            <button class="btn btn-warning btn-sm editRowButton">Sửa</button>
                                            <button class="btn btn-danger btn-sm deleteRowButton">Xóa</button>
                                        </td>                                    
                                    </tr>`;
                                tbody.append(tr);
                                rowsData.push({
                                    ten_hang: row.ten_hang,
                                    loai_hang: row.loai_hang,
                                    xuat_xu: row.xuat_xu,
                                    so_luong_khai_bao: row.so_luong_khai_bao,
                                    don_vi_tinh: row.don_vi_tinh,
                                    don_gia: row.don_gia,
                                    tri_gia: row.tri_gia
                                });
                            });
                            displayRows();
                            alert("Nhập file thành công");
                            $('#chonFileModal').modal('hide');
                        }

                    },
                    error: function(xhr) {
                        alert(xhr.responseText);
                    }
                });
            });
            //Tính trị giá
            const soLuongInput = document.getElementById("so_luong_khai_bao");
            const donGiaInput = document.getElementById("don_gia");
            const triGiaInput = document.getElementById("tri_gia");

            function calculateTriGia() {
                const soLuong = parseFloat(soLuongInput.value) || 0;
                const donGia = parseFloat(donGiaInput.value) || 0;
                const triGia = soLuong * donGia;

                // Store the actual numeric value, not the formatted string
                triGiaInput.value = triGia;
            }

            soLuongInput.addEventListener("input", calculateTriGia);
            donGiaInput.addEventListener("input", calculateTriGia);
        });

        function updateTotals() {
            let sumSoLuong = 0;
            let sumTriGia = 0;

            $("#displayTableNhapHang tbody tr").each(function() {
                let soLuong = parseFloat($(this).find("td:nth-child(5)").text()) || 0;
                let triGia = parseFloat($(this).find("td:nth-child(8)").text()) || 0;

                sumSoLuong += soLuong;
                sumTriGia += triGia;
            });

            $("#sumSoLuong").text(sumSoLuong.toLocaleString());
            $("#sumTriGia").text(sumTriGia.toLocaleString());
        }
    </script>
    <script>
        $(document).ready(function() {
            // Initialize the datepicker with Vietnamese localization
            $('#datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                language: 'vi', // Set language to Vietnamese
                endDate: '0d',
                keyboardNavigation: true, // Allow keyboard navigation
                forceParse: true // Ensure manually typed dates are parsed
            }).on('changeDate', function(e) {
                // When a date is selected via the datepicker UI
                handleDateChange(e.date);
            });

            // Handle manually typed date
            $('#datepicker').on('blur', function() {
                const typedDate = $(this).val();
                const parsedDate = moment(typedDate, "DD/MM/YYYY", true);

                if (parsedDate.isValid()) {
                    // Update the datepicker with the manually entered date
                    $('#datepicker').datepicker('setDate', parsedDate.toDate());
                    handleDateChange(parsedDate.toDate());
                } else {
                    alert("Invalid date format. Please enter in DD/MM/YYYY format.");
                }
            });

            function handleDateChange(selectedDate) {
                const currentDate = new Date();
                const diffTime = Math.abs(currentDate - selectedDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                // if (diffDays > 15) {
                //     alert('Tờ khai đã quá hạn 15 ngày');
                //     $('#datepicker').val(''); // Clear the input field
                // } 
                // else if (maLoaiHinh == "VCDL" && diffDays > 2) {
                //     alert('Tờ khai VCDL đã quá hạn 2 ngày');
                //     $('#datepicker').val(''); /
                // }
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#xuat-xu-2-dropdown-search').select2({
                placeholder: "Chọn xuất xứ",
                allowClear: true,
            });
            $('#chonFileModal ').on('shown.bs.modal', function() {
                $('#xuat-xu-2-dropdown-search').select2('destroy');
                $('#xuat-xu-2-dropdown-search').select2({
                    placeholder: "Chọn xuất xứ",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonFileModal .modal-body'),
                });
            });
            $('#loai-hang-2-dropdown-search').select2({
                placeholder: "Chọn xuất xứ",
                allowClear: true,
            });
            $('#chonFileModal ').on('shown.bs.modal', function() {
                $('#loai-hang-2-dropdown-search').select2('destroy');
                $('#loai-hang-2-dropdown-search').select2({
                    placeholder: "Chọn loại hàng",
                    allowClear: true,
                    language: "vi",
                    minimumInputLength: 0,
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: $('#chonFileModal .modal-body'),
                });
            });
        });
    </script>
@stop
