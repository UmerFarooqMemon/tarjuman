'use strict';

$(function () {
    $('.dt-scrollableTable').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            return;
        }

        const fixedLeft = parseInt(this.dataset.fixedColumnsLeft || '0', 10);
        const options = {
            scrollX: true,
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false,
                },
                {
                    targets: 'status-toggle-col',
                    orderable: false,
                    searchable: false,
                },
            ],
            order: [],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                emptyTable: 'No data available in table',
                zeroRecords: 'No matching records found',
            },
        };

        if (fixedLeft > 0) {
            options.fixedColumns = { left: fixedLeft };
            options.scrollCollapse = true;
        }

        $(this).DataTable(options);
    });
});
