function fetchSideTable(){
    $('#equipments-side-table').DataTable({
        "columns": [
            { "data": 'barcode' },
            { "data": 'model' }
        ],
        "columnDefs": [
            {"className": "dt-center", "targets": "_all"}
        ],
        searching: false,
        scrollColapse: true,
        scrollY: '65vh',
        paging: false
    });
}

$(document).ready(fetchSideTable);
