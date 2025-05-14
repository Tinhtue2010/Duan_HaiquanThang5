$(document).ready(function () {
    $('#hai-quan-dropdown-search').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $(document).on('select2:open', function () {
        $('.select2-search__field').attr('placeholder', 'Nhập để tìm kiếm'); // Set the placeholder
    });

    $('#doanh-nghiep-dropdown-search-2').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
        searchInputPlaceholder: "Nhập để tìm kiếm", // Customize your placeholder text
    });
    $('#doanh-nghiep-dropdown-search-3').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
        searchInputPlaceholder: "Nhập để tìm kiếm", // Customize your placeholder text
    });
    $('#doanh-nghiep-dropdown-search-4').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
        searchInputPlaceholder: "Nhập để tìm kiếm", // Customize your placeholder text
    });
    $('#doanh-nghiep-dropdown-search-5').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
        searchInputPlaceholder: "Nhập để tìm kiếm", // Customize your placeholder text
    });
    $('#chu-hang-dropdown-search').select2({
        placeholder: "Chọn đại lý",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $('#themModal .modal-body'), // Change this line
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#lan-xuat-canh-dropdown-search').select2({
        placeholder: "Chọn lần xuất cảnh",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $('#inToKhaiModal .modal-body'), // Change this line
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#loai-hang-dropdown-search').select2({
        placeholder: "Chọn loại hàng",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#xuat-xu-dropdown-search').select2({
        placeholder: "Chọn xuất xứ",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#don-vi-tinh-dropdown-search').select2({
        placeholder: "Chọn đơn vị tính",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });


    $('#container-dropdown-search').select2({
        tags: true,
        placeholder: "Chọn container",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#so-to-khai-nhap-dropdown-search').select2({
        placeholder: "Chọn số tờ khai nhập",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#loai-hinh-dropdown-search').select2({
        placeholder: "Chọn loại hình tờ khai",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#ptvt-dropdown-search').select2({
        placeholder: "Chọn phương tiện vận tải",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#ptvt-xc-dropdown-search').select2({
        placeholder: "Chọn phương tiện vận tải",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });
    $('#hang-hoa-dropdown-search').select2({
        placeholder: "Chọn hàng hóa",
        allowClear: true,
        language: "vi", // Set the language (if needed)
        minimumInputLength: 0,
        dropdownAutoWidth: true, // Automatically adjust the dropdown width
        dropdownParent: $(document.body), // Ensure dropdown appears under the input field
        ajax: {
            dataType: 'json',
            delay: 250, // Delay for AJAX search
            processResults: function (data) {
                return {
                    results: data.items
                };
            }
        },
    });

    $('#so-seal-dropdown-search').select2();
    $('#cong-chuc-dropdown-search').select2();
    $('#loai-seal-dropdown-search').select2();
    $('#loai-seal-dropdown-search-2').select2();
    $('#cong-chuc-dropdown-search-2').select2();

    // Reinitialize Select2 when modal opens
    $('#xacNhanNhaphangModal ').on('shown.bs.modal', function () {
        $('#loai-seal-dropdown-search').select2('destroy');
        $('#cong-chuc-dropdown-search').select2('destroy');
        $('#loai-seal-dropdown-search').select2({
            placeholder: "Chọn seal niêm phong",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#xacNhanNhaphangModal .modal-body'),
        });
        $('#cong-chuc-dropdown-search').select2({
            placeholder: "Chọn cán bộ công chức",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#xacNhanNhaphangModal .modal-body'),
        });
    });
    $('#xacNhanModal ').on('shown.bs.modal', function () {
        $('#loai-seal-dropdown-search').select2('destroy');
        $('#loai-seal-dropdown-search').select2({
            placeholder: "Chọn loại seal",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#xacNhanModal .modal-body'),
        });
        $('#cong-chuc-dropdown-search').select2('destroy');
        $('#cong-chuc-dropdown-search').select2({
            placeholder: "Chọn cán bộ công chức",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#xacNhanModal .modal-body'),
        });
    });
    $('#thayDoiCongChucModal ').on('shown.bs.modal', function () {
        $('#cong-chuc-dropdown-search-2').select2('destroy');
        $('#cong-chuc-dropdown-search-2').select2({
            tags: true,
            placeholder: "Chọn công chức",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#thayDoiCongChucModal .modal-body'),
        });
    });
    $('#suaSealModal ').on('shown.bs.modal', function () {
        $('#loai-seal-dropdown-search-2').select2('destroy');
        $('#loai-seal-dropdown-search-2').select2({
            placeholder: "Chọn loại seal",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#suaSealModal .modal-body'),
        });
    });
    $('#xoaNhanhModal ').on('shown.bs.modal', function () {
        $('#cong-chuc-dropdown-search-2').select2('destroy');
        $('#cong-chuc-dropdown-search-2').select2({
            placeholder: "Chọn công chức",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#xoaNhanhModal .modal-body'),
        });
    });
    $('#duyetToKhaiModal ').on('shown.bs.modal', function () {
        $('#cong-chuc-dropdown-search').select2('destroy');
        $('#cong-chuc-dropdown-search').select2({
            placeholder: "Chọn cán bộ công chức",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#duyetToKhaiModal .modal-body'),
        });
    });
    $('#inToKhaiModal ').on('shown.bs.modal', function () {
        $('#lan-xuat-canh-dropdown-search').select2('destroy');
        $('#lan-xuat-canh-dropdown-search').select2({
            placeholder: "Chọn lần xuất cảnh",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#inToKhaiModal .modal-body'),
        });

        $('#doanh-nghiep-dropdown-search').select2('destroy');
        $('#doanh-nghiep-dropdown-search').select2({
            placeholder: "Chọn doanh nghiệp",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#inToKhaiModal .modal-body'),
        });

        $('#thuyen-truong-dropdown-search').select2('destroy');
        $('#thuyen-truong-dropdown-search').select2({
            tags: true,
            placeholder: "Chọn thuyền trưởng",
            allowClear: true,
            language: "vi",
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $('#inToKhaiModal .modal-body'),
        });
    });


    $('.select2-search__field').attr('placeholder', 'Tìm kiếm...');
});

$(document).ready(function () {
    $('#hai-quan-dropdown-search').select2({
        placeholder: "Chọn cục hải quan",
        allowClear: true,
    });
    $('#doanh-nghiep-dropdown-search').select2({
        placeholder: "Chọn doanh nghiệp",
        allowClear: true,
    });
    $('#doanh-nghiep-dropdown-search-2').select2({
        placeholder: "Chọn doanh nghiệp",
        allowClear: true,
    });
    $('#doanh-nghiep-dropdown-search-3').select2({
        placeholder: "Chọn doanh nghiệp",
        allowClear: true,
    });
    $('#doanh-nghiep-dropdown-search-4').select2({
        placeholder: "Chọn doanh nghiệp",
        allowClear: true,
    });
    $('#doanh-nghiep-dropdown-search-5').select2({
        placeholder: "Chọn doanh nghiệp",
        allowClear: true,
    });
    $('#chu-hang-dropdown-search').select2({
        placeholder: "Chọn đại lý",
        allowClear: true,
    });
    $('#loai-hang-dropdown-search').select2({
        placeholder: "Chọn loại hàng",
        allowClear: true,
    });
    $('#xuat-xu-dropdown-search').select2({
        placeholder: "Chọn xuất xứ",
        allowClear: true,
    });
    $('#don-vi-tinh-dropdown-search').select2({
        placeholder: "Chọn đơn vị tính",
        allowClear: true,
    });
    $('#container-dropdown-search').select2({
        tags: true,
        placeholder: "Chọn container",
        allowClear: true,
    });
    $('#container-dropdown-search-2').select2({
        tags: true,
        placeholder: "Chọn container",
        allowClear: true,
    });
    $('#container-dropdown-search-3').select2({
        tags: true,
        placeholder: "Chọn container",
        allowClear: true,
    });
    $('#thuyen-truong-dropdown-search').select2({
        tags: true,
        placeholder: "Chọn thuyền trưởng",
        allowClear: true,
    });
    $('#so-to-khai-nhap-dropdown-search').select2({
        placeholder: "Chọn tờ khai nhập",
        allowClear: false,
    });
    $('#loai-hinh-dropdown-search').select2({
        placeholder: "Chọn loại hình tờ khai",
        allowClear: true,
    });
    $('#ptvt-dropdown-search').select2({
        placeholder: "Chọn phương tiện vận tải",
        allowClear: true,
    });
    $('#ptvt-xc-dropdown-search').select2({
        placeholder: "Chọn phương tiện vận tải",
        allowClear: true,
    });
    $('#hang-hoa-dropdown-search').select2({
        placeholder: "Chọn hàng hóa",
        allowClear: true,
    });
    $('#lan-xuat-canh-dropdown-search').select2({
        placeholder: "Chọn lần xuất cảnh",
        allowClear: true,
    });
    $('#cong-chuc-dropdown-search').select2({
        placeholder: "Chọn công chức",
        allowClear: true,
    });
    $('#tau-dropdown-search').select2({
        placeholder: "Chọn tàu",
        allowClear: true,
    });
});


function printToKhai(divPrint) {
    var content = document.getElementById(divPrint).innerHTML; // Get the content of the div
    var originalContent = document.body.innerHTML; // Save the original content of the page

    // Inject style to hide headers and footers
    var landscapeStyle = `
        <style>
            @media print {
                @page {
                    size: landscape;
                    margin: 0cm 1cm; /* No headers or footers */
                }
                body {
                    margin: 0;
                }
            }
        </style>
    `;

    // Replace the body content with the div content and include the style
    document.body.innerHTML = landscapeStyle + content;

    window.print(); // Trigger the print dialog

    // Restore the original page content after printing
    document.body.innerHTML = originalContent;
}

document.addEventListener("DOMContentLoaded", function () {
    // Select all forms on the page
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            // Find the submit button within the form
            const submitButton = form.querySelector('button[type="submit"]');

            if (submitButton) {
                // Save the original button text
                const originalText = submitButton.textContent;

                // Disable the button to prevent multiple submissions
                submitButton.disabled = true;
                submitButton.textContent = "Đang xử lý..."; // Feedback for users

                // Re-enable the button and restore its text after 3 seconds
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText; // Restore the original text
                }, 5000); // 3000ms = 3 seconds
            }
        });
    });
});



setTimeout(() => {
    const alertElement = document.getElementById('myAlert');
    if (alertElement) { // Ensure the element exists before attempting to remove it
        alertElement.remove();
    }
}, 10000);
