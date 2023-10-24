function fetchRelatedEquipTable(){
    fixedToastr('Loading Data', 'Related Equipments Table')
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/equipments/get?relatedEquipmentId=${searchParams.get('id')}`
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        success: function(response){
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
            const tableOrder = [[9, 'desc']]
            $('#related-equipments-table').DataTable({
                data: response.data,
                autoWidth: true,
                searching: true,
                paging: true,
                order: tableOrder,
                columns: tableColumns
            });
            toastr.clear()
            toastr.success('Loaded', 'Related Equipments Table')
        },
        error: function(response, textStatus, errorThrown){
            $('#related-equipments-table').DataTable({
                autoWidth: true,
                searching: true,
                paging: true,
                order: tableOrder,
                columns: tableColumns
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

function fetchRelatedFilesTable(){
    fixedToastr('Loading data', 'Related Files Table');
    const searchParams = new URLSearchParams(window.location.search);
    const requestUrl = `/api/files/get?relatedEquipmentId=${searchParams.get('id')}`
    $.ajax({
        type: 'GET',
        url: requestUrl,
        dataType: "json",
        async: true,
        success: function(response){
            $('#related-files-table').DataTable({
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
            $('#related-files-table').DataTable({
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

function fetchData(){
    fetchRelatedEquipTable()
    fetchRelatedFilesTable()
}

$(document).ready(fetchData)