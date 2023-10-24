function fetchFileTableFull(){
    fixedToastr('Loading data');
    $.ajax({
        type: 'GET',
        url: `/api/files/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            $('#file-table-index').DataTable({
                data: response.data,
                autoWidth: true,
                searching: true,
                paging: true,
                order: fileTableOrder,
                columns: fileTableColumns
            });
            toastr.clear()
            toastr.success('Loaded')
        },
        error: function(response, textStatus, errorThrown){
            $('#file-table-index').DataTable({
                autoWidth: true,
                searching: true,
                paging: true,
                order: fileTableOrder,
                columns: fileTableColumns
            });
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
