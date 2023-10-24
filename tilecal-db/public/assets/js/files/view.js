function fetchRelatedEquipsTable(){
    fixedToastr('Loading data')
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/equipments/get?relatedFileId=${searchParams.get('id')}`
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        success: function(response){
            $('#related-equipments-table').DataTable({
                data: response.data,
                autoWidth: true,
                searching: true,
                paging: true,
                order: equipmentTableOrder,
                columns: equipmentTableColumns
            });
            toastr.clear()
            toastr.success('Loaded')
        },
        error: function(response, textStatus, errorThrown){
            $('#related-equipments-table').DataTable({
                autoWidth: true,
                searching: true,
                paging: true,
                order: equipmentTableOrder,
                columns: equipmentTableColumns
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

$(document).ready(fetchRelatedEquipsTable)