$('#dataTable').DataTable({
            language: {
                searchPlaceholder: "Tìm kiếm",
                search: "",
                "sInfo": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                "sInfoEmpty": "Hiển thị 0 đến 0 của 0 mục",
                "sInfoFiltered": "Lọc từ _MAX_ mục",
                "sLengthMenu": "Hiện _MENU_ mục",
                "sEmptyTable": "Không có dữ liệu",

            },
            stateSave: true,
            dom: '<"clear"><"row"<"col"l><"col"f>>rt<"row"<"col"i><"col"p>><"row"<"col"B>>',
            buttons: [
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    }, title: ''
                }, {
                    extend: 'pdf',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    }, title: ''
                }
            ]
        });

$('.dataTables_filter input[type="search"]').css({
    width: '350px',
    display: 'inline-block',
    height: '40px',
});
