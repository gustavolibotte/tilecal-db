function fetchRelatedEquipTable(equipmentId){
    fixedToastr('Loading Data', 'Related Equipments Table')
    const requestUrl = `/api/equipments/get?relatedEquipmentId=${equipmentId}`
    var tableColumns = [
        {
            "render": function(data, type, row, meta){return capitalize(row.relation_type)},
            "title": "Relation"
        },
        {
            "render": function(data, type, row, meta){return snake2Capitalized(row.slot_name)},
            "title": "Slot"
        },
    ]
    tableColumns = tableColumns.concat(equipmentTableColumns)
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        order: [[9, 'desc']],
        columns: tableColumns
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
            toastr.success('Loaded', 'Related Equipments Table')
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

function fetchRelatedFilesTable(equipmentId){
    fixedToastr('Loading data', 'Related Files Table');
    const requestUrl = `/api/files/get?relatedEquipmentId=${equipmentId}`
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        order: fileTableOrder,
        columns: fileTableColumns
    }
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#related-files-table').DataTable(tableParams);
            toastr.clear()
            toastr.success('Loaded')
        },
        error: function(response, textStatus, errorThrown){
            $('#related-files-table').DataTable(tableParams);
            toastr.clear()
            toastrError(
                response.responseJSON.messages.error,
                response.responseJSON.status,
                errorThrown
            )
        }
    })
}

function fetchData(){
    const equipmentId = getEquipmentIdFromViewRoute(window.location.href)
    fetchRelatedEquipTable(equipmentId)
    fetchRelatedFilesTable(equipmentId)
}

$(document).ready(fetchData)