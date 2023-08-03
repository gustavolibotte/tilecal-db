function fetchFilesTable(){
    $('#related-files-table').DataTable({
        rowID: "id",
        searching: true,
        scrollColapse: true,
        scrollY: '33vh',
        paging: false
    });
}

$(document).ready(fetchFilesTable);