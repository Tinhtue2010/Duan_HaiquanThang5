@extends('layout.user-layout')

@section('title', 'Thông tin yêu cầu niêm phong')

@section('content')
    @php
        use App\Models\DoanhNghiep;
    @endphp
    <div id="layoutSidenav_content">
        <div class="container-fluid px-5 mt-3">
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
                <div class="col-6">
                    <a class="return-link" href="/danh-sach-yeu-cau-niem-phong">
                        <p>
                            < Quay lại danh sách yêu cầu niêm phong </p>
                    </a>
                </div>
                <div class="col-6">
                </div>
            </div>

            <div class="card p-3">
                <div id="divPrint">
                    <h2 class="text-center">{{ $doanhNghiep->ten_doanh_nghiep }}
                    </h2>
                    <h2 class="text-center">YÊU CẦU NIÊM PHONG CONTAINER</h2>
                    <h2 class="text-center">Số {{ $yeuCau->ma_yeu_cau }} - Ngày yêu cầu:
                        {{ \Carbon\Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y') }}</h2>


                    <h1 class="text-center">Yêu cầu ban đầu</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $yeuCau->ten_doan_tau }}</h2>

                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số container</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_container }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <center>
                        <div class="custom-line mb-2"></div>
                    </center>
                    <h1 class="text-center">Yêu cầu sau khi sửa</h1>
                    <h2 class="text-center">Đoàn tàu số: {{ $suaYeuCau->ten_doan_tau }}</h2>

                    <table class="table table-bordered mt-5" id="displayTable"
                        style="vertical-align: middle; text-align: center;">
                        <thead class="align-middle">
                            <tr>
                                <th>STT</th>
                                <th>Số container</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chiTiets as $index => $chiTiet)
                                <tr>
                                    <td>{{ $index + 1 }}</td> <!-- Display index (1-based) -->
                                    <td>{{ $chiTiet->so_container }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-3"></div>
                <div class="col-6">
                    <div class="text-center">
                        @if (Auth::user()->loai_tai_khoan == 'Cán bộ công chức' && Auth::user()->congChuc->is_yeu_cau == 1)
                            <hr />
                            <div class="row mt-3">
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanModal"
                                            class="btn btn-success ">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/approved2.png') }}">
                                            Duyệt yêu cầu sửa</button>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#">
                                        <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                            class="btn btn-danger px-4">
                                            <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                            Hủy yêu cầu sửa
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @elseif (Auth::user()->loai_tai_khoan == 'Doanh nghiệp' &&
                                DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep ==
                                    $yeuCau->ma_doanh_nghiep)
                            <div class="row">
                                <center>
                                    <div class="col-6">
                                        <a href="#">
                                            <button data-bs-toggle="modal" data-bs-target="#xacNhanHuyModal"
                                                class="btn btn-danger px-4">
                                                <img class="side-bar-icon" src="{{ asset('images/icons/cancel.png') }}">
                                                Hủy yêu cầu
                                            </button>
                                        </a>
                                    </div>
                                </center>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Tình trạng: Chờ duyệt --}}
    <div class="modal fade" id="xacNhanModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Xác nhận duyệt yêu cầu sửa</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.duyet-sua-yeu-cau-niem-phong') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5>Xác nhận duyệt yêu cầu sửa này ?</h5>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ma_sua_yeu_cau" value="{{ $suaYeuCau->ma_sua_yeu_cau }}">
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <button type="submit" class="btn btn-success">Xác nhận duyệt</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
    </div>

    {{-- Xác nhận Hủy --}}
    <div class="modal fade" id="xacNhanHuyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="exampleModalLabel">Xác nhận hủy tờ khai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('quan-ly-kho.huy-sua-yeu-cau-niem-phong') }}" method="POST">
                    @csrf
                    <div class="modal-body text-danger">
                        <p class="text-danger">Xác nhận hủy yêu cầu sửa này?</p>
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú" name="ghi_chu" maxlength="200"></textarea>
                        <input type="hidden" name="ma_yeu_cau" value="{{ $yeuCau->ma_yeu_cau }}">
                        <input type="hidden" name="ma_sua_yeu_cau" value="{{ $suaYeuCau->ma_sua_yeu_cau }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var suaSealModal = document.getElementById('suaSealModal')
            suaSealModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget
                var containerNumber = button.getAttribute('data-container')
                var containerInput = suaSealModal.querySelector('#so_container_hidden')
                containerInput.value = containerNumber
            })
        })
    </script>
    <script>
        $(document).ready(function() {
            // Function to update the correct seal dropdown in the same row
            function updateSealDropdown(row) {
                let maCongChuc = $('#cong-chuc-dropdown-search').val();
                let loaiSeal = row.find('.loai-seal-dropdown-search').val();
                let sealDropdown = row.find('.seal-dropdown-search');

                if (maCongChuc && loaiSeal == "Seal định vị điện tử") {
                    $.ajax({
                        url: "{{ route('quan-ly-kho.getSeals') }}", // Adjust with your route
                        type: "GET",
                        data: {
                            ma_cong_chuc: maCongChuc,
                            loai_seal: loaiSeal
                        },
                        success: function(response) {
                            sealDropdown.empty().append('<option value="">Chọn seal</option>');
                            $.each(response.seals, function(index, seal) {
                                sealDropdown.append(
                                    `<option value="${seal.so_seal}">${seal.so_seal}</option>`
                                );
                            });

                        }
                    });
                } else {
                    sealDropdown.empty().append('<option value="">Chọn seal</option>');
                }
            }

            $(document).on('change', '.seal-dropdown-search, .loai-seal-dropdown-search', function() {
                let row = $(this).closest('tr');
                updateSealDropdown(row);
            });
            const tableBody = document.querySelector('#displayTableYeuCau tbody');
            const rowsDataInput = document.getElementById('rowsDataInput');
            $(document).on('change', '.seal-dropdown-search, .loai-seal-dropdown-search', function() {
                let row = $(this).closest('tr');
                let cells = row.find('td');

                const rows = $('#displayTableYeuCau tbody tr').map(function() {
                    let cells = $(this).find('td');
                    return {
                        stt: $(cells[0]).text().trim(),
                        so_container: $(cells[1]).text().trim(),
                        loai_seal: $(cells[2]).find('.loai-seal-dropdown-search').val() || "",
                        so_seal: $(this).find('.seal-dropdown-search').val() || "",
                    };
                }).get();

                // Check for duplicates
                let sealValues = rows.map(row => row.so_seal).filter(seal => seal !== "");
                let duplicates = sealValues.filter((value, index, self) => self.indexOf(value) !== index);

                if (duplicates.length > 0) {
                    alert("Trùng số seal: " + duplicates.join(", "));
                }
                $('#rowsDataInput').val(JSON.stringify(rows));
            });


            $('#cong-chuc-dropdown-search').on('change', function() {
                $('#displayTableYeuCau tr').each(function() {
                    updateSealDropdown($(this));
                });
            });




            function updateSealDropdown2() {
                let maCongChuc = document.getElementById("maCongChuc").value;
                let loaiSeal = document.getElementById("loai-seal-dropdown-search-2").value;
                let sealDropdown = $("#seal-dropdown-search-2"); // Use jQuery for easier manipulation

                if (maCongChuc && loaiSeal == "Seal định vị điện tử") {
                    $.ajax({
                        url: "{{ route('quan-ly-kho.getSeals') }}", // Adjust with your route
                        type: "GET",
                        data: {
                            ma_cong_chuc: maCongChuc,
                            loai_seal: loaiSeal
                        },
                        success: function(response) {
                            sealDropdown.empty().append(
                                '<option value="">Chọn seal</option>'); // Clear and add default

                            if (response.seals && response.seals.length > 0) {
                                $.each(response.seals, function(index, seal) {
                                    sealDropdown.append(
                                        `<option value="${seal.so_seal}">${seal.so_seal}</option>`
                                    );
                                });
                            } else {
                                alert("Không có số seal phù hợp.");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });
                } else {
                    sealDropdown.empty().append('<option value="">Chọn seal</option>');
                }
            }


            $(document).on('change', '#loai-seal-dropdown-search-2', function() {
                updateSealDropdown2();
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            $('#xacNhanModal ').on('shown.bs.modal', function() {
                $('select[name="so_seal"]').select2({
                    placeholder: "Chọn seal",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#xacNhanModal .modal-body'),

                });

                $('.container-row').on('show', function() {
                    $(this).find('select[name="so_seal"]').select2({
                        placeholder: "Select a vehicle",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#xacNhanModal .modal-body'),
                    });
                });

            });
        });
    </script>


@stop
