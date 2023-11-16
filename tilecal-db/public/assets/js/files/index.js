function fetchFileTableFull(){
    toastr.info('Loading data');
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        order: fileTableOrder,
        columns: fileTableColumns
    }
    $.ajax({
        type: 'GET',
        url: `/api/files/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#file-table-index').DataTable(tableParams);
            toastr.clear()
        },
        error: function(response, textStatus, errorThrown){
            $('#file-table-index').DataTable(tableParams);
            toastr.clear()
            toastrError(
                response.responseJSON.messages.error,
                response.responseJSON.status,
                errorThrown
            )
        }
    })
}

$(document).ready(fetchFileTableFull)
