function fetchDataTables(){
    $('#files-table').DataTable({
        "rowID": "id",
        "columnDefs": [
            {"className": "dt-center", "targets": "_all"}
        ],
        searching: false,
        scrollResize: true,
        scrollY: 50,
        scrollCollapse: true,
        paging: false
    });
}

$(document).ready(fetchDataTables());
