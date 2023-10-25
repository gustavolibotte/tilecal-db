function fetchFileSideTable(){
    const toastrTitle = 'Uploaded Files'
    fixedToastr('Loading data', toastrTitle)
    const tableColumns = [
        {
            "render": function(data, type, row, meta){return getFileViewButton(row.id, row.name)},
            "title": "Name"
        }
    ]
    $.ajax({
        type: 'GET',
        url: `${getFileRoute}?orderBy=updatedAt&returnType=minimal`,
        dataType: "json",
        async: true,
        success: function(response){
            $('#file-table-side').DataTable({
                data: response.data,
                autoWidth: true,
                searching: true,
                paging: true,
                columns: tableColumns,
                searching: false,
                scrollColapse: true,
                scrollY: '70vh',
                order: [],
            });
            toastr.clear()
            toastr.success('Loaded', toastrTitle)
        },
        error: function(response, textStatus, errorThrown){
            $('#file-table-side').DataTable({
                autoWidth: true,
                searching: true,
                paging: true,
                columns: tableColumns,
                searching: false,
                scrollColapse: true,
                scrollY: '70vh',
                order: [],
            });
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