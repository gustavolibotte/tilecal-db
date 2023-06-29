function fetchSideTable(){
    $('#equipments-side-table').DataTable({
        "rowID": "id",
        "columns": [
            { "data": 'id' },
            { "data": 'model' }
        ],
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

$(document).ready(fetchSideTable());