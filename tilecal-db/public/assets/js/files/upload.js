function fetchFileSideTable(){
    const toastrTitle = 'Uploaded Files'
    fixedToastr('Loading data', toastrTitle)
    const tableParams = {
        autoWidth: true,
        searching: true,
        paging: true,
        searching: false,
        scrollColapse: true,
        scrollY: '70vh',
        order: [],
        columns: [
            {
                "render": function(data, type, row, meta){return getFileViewButton(row.id, row.name)},
                "title": "Name"
            }
        ]
    }
    $.ajax({
        type: 'GET',
        url: `${getFileRoute}?orderBy=updatedAt&returnType=minimal`,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#file-table-side').DataTable(tableParams);
            toastr.clear()
        },
        error: function(response, textStatus, errorThrown){
            $('#file-table-side').DataTable(tableParams);
            toastr.clear()
            toastrError(
                response.responseJSON.messages.error,
                response.responseJSON.status,
                errorThrown,
                toastrTitle
            )
        } 
    })
}

$(document).ready(fetchFileSideTable)