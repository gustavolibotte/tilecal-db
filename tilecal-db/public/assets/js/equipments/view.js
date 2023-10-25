function fetchRelatedEquipTable(){
    fixedToastr('Loading Data', 'Related Equipments Table')
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/equipments/get?relatedEquipmentId=${searchParams.get('id')}`
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

function fetchRelatedFilesTable(){
    fixedToastr('Loading data', 'Related Files Table');
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/files/get?relatedEquipmentId=${searchParams.get('id')}`
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
    fetchRelatedEquipTable()
    fetchRelatedFilesTable()
}

$(document).ready(fetchData)