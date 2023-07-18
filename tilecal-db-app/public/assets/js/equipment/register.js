function fetchDataTables(){
    $('#equipments-table').DataTable({
        // "processing": "true",
        // "serverSide": "true",
        // "ajax": {
        //     "url": '/fetch-equipments',
        //     "type": 'POST',
        // },
        // "search": {
        //     "return": "true",
        // },
        "rowID": "id",
        "columns": [
            { "data": 'id' },
            { "data": 'model' }
        ],
        // "language": {
        //     "infoFiltered": "",  // This removes info from how much samples in table exist
        //     "loadingRecords": "Please wait - loading..."
        // },
        searching: false,
        scrollColapse: true,
        scrollY: '65vh',
        paging: false
    });
}

$(document).ready(fetchDataTables());