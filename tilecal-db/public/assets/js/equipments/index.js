function fetchEquipTableIndex(){
    fixedToastr('Loading data')
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            $('#equipment-table-index').DataTable({
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
            $('#equipment-table-index').DataTable({
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

$(document).ready(fetchEquipTableIndex)