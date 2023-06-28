function fetchDataTables(){
    $('#equipments-table').DataTable({
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

$(document).ready(fetchDataTables());

$('#add-file-btn').click(function(){
    $('#add-files-col').append(
        '<input type="text" class="form-control mt-2" name="files[]" placeholder="File name">')
});