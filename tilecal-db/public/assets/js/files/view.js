function fetchRelatedEquipsTable(fileId){
    fixedToastr('Loading data', 'Related Equipments Table')
    const requestUrl = `/api/equipments/get`
    const tableParams = {
        searching: true,
        paging: true,
        order: equipmentTableOrder,
        columns: equipmentTableColumns
    }
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get`,
        data: {"relatedFileId": fileId},
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

function fetchHistTable(fileId){
    fixedToastr('Loading data', 'File Hist Table');
    const requestUrl = `/api/files/getHist`
    const tableParams = {
        searching: true,
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
        data: {"id": fileId},
        success: function(response){
            tableParams.data = response.data
            $('#history-table-full').DataTable(tableParams);
            toastr.clear()
            toastr.success('Loaded', 'File Hist Table')
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
    const fileId = getFileIdFromViewRoute(window.location.href)
    fetchRelatedEquipsTable(fileId)
    fetchHistTable(fileId)
}

$(document).ready(fetchData)