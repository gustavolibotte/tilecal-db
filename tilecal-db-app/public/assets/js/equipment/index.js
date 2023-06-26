function fetchDataTables(){
    $('#equipments-table').DataTable({
        "processing": "true",
        "serverSide": "true",
        "ajax": {
            "url": '/fetch-equipments',
            "type": 'POST',
        },
        "search": {
            "return": "true",
        },
        "columns": [
            { "data": 'id' },
            { "data": 'model' },
            { "data": 'location' },
            { "data": 'status' },
            { "data": 'parent_id' },
            { "data": 'comments' }
        ],
        "language": {
            "infoFiltered": "",  // This removes info from how much samples in table exist
            "loadingRecords": "Please wait - loading..."
        }
    });
}

$(document).ready(fetchDataTables());