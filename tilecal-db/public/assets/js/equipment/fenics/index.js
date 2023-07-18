function fetchDataTables(){
    $('#equipments-table').DataTable({
        rowID: "id",
        columns: [
            { data: 'id' },
            { data: 'location' },
            { data: 'status' },
            { data: 'parent_id' },    
            { data: 'comments' }
        ],
        scrollY: 500,
        scrollCollapse: true,
        paging: false
    });
}

$(document).ready(fetchDataTables());