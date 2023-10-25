function fetchEquipTableIndex(){
    fixedToastr('Loading data')
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        order: equipmentTableOrder,
        columns: equipmentTableColumns
    }
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#equipment-table-index').DataTable(tableParams);
            toastr.clear()
            toastr.success('Loaded')
        },
        error: function(response, textStatus, errorThrown){
            $('#equipment-table-index').DataTable(tableParams);
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