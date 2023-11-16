function fetchRelatedEquipTable(equipmentId){
    toastr.info('Loading Data', 'Related Equipments Table')
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
        searching: true,
        paging: true,
        order: [[10, 'desc']],
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
    toastr.info('Loading data', 'Related Files Table');
    const requestUrl = `/api/files/get?relatedEquipmentId=${equipmentId}`
    const tableParams = {
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

function fetchHistTable(equipmentId){
    toastr.info('Loading data', 'Equip Hist Table');
    const requestUrl = `/api/equipments/getHist`
    const tableParams = {
        searching: true,
        pageLength: 3,
        lengthMenu: [1, 3, 5, 10, 15, 20],
        order: [[0, 'desc']],
        columns: [
            {
                "render": function(data, type, row, meta){
                    return `<b>${row.created_at}</b>`
                },
                "title": "Created at"
            },
            {
                "data": "user",
                "title": "User"
            },
            {
                "render": function(data, type, row, meta){
                    return capitalize(row.action)
                },
                "title": "Action"
            },
            {
                "render": function(data, type, row, meta){
                    return getHistMessage(row)
                },
                "title": "Description"
            }
        ]
    }
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        data: {"id": equipmentId},
        success: function(response){
            tableParams.data = response.data
            $('#history-table-full').DataTable(tableParams);
            toastr.clear()
        },
        error: function(response, textStatus, errorThrown){
            $('#history-table-full').DataTable(tableParams);
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
    fetchHistTable(equipmentId)
}

$(document).ready(fetchData)