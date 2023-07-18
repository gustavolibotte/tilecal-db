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
        scrollColapse: true,
        scrollY: '65vh',
        paging: false
    });
}

function fetchFilesTable(){
    $('#related-files-table').DataTable({
        rowID: "id",
        searching: true,
        scrollColapse: true,
        scrollY: '33vh',
        paging: false
    });
}

function fetchTables(){
    fetchSideTable()
    fetchFilesTable()
}

$(document).ready(fetchTables);