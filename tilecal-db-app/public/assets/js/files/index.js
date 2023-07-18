function fetchDataTables(){
    $('#equipments-table').DataTable({
        rowID: "id",
        scrollY: 500,
        scrollCollapse: true,
        paging: false
    });
}

$(document).ready(fetchDataTables());