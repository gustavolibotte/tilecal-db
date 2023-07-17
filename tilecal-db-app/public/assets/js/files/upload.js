function fetchDataTables(){
    $('#files-table').DataTable({
        "rowID": "id",
        "columnDefs": [
            {"className": "dt-center", "targets": "_all"}
        ],
        searching: false,
        scrollColapse: true,
        scrollY: '50vh',
        paging: false
    });
}

$(document).ready(fetchDataTables());
