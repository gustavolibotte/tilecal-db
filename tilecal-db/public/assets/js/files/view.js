function fetchRelatedEquipsTable(){
    fixedToastr('Loading data')
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/equipments/get?relatedFileId=${searchParams.get('id')}`
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        order: equipmentTableOrder,
        columns: equipmentTableColumns
    }
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#related-equipments-table').DataTable(tableParams);
            toastr.clear()
            toastr.success('Loaded')
        },
        error: function(response, textStatus, errorThrown){
            $('#related-equipments-table').DataTable(tableParams);
            toastr.clear()
            toastrError(
                response.responseJSON.messages.error,
                response.responseJSON.status,
                errorThrown
            )
        } 
    })
}

$(document).ready(fetchRelatedEquipsTable)