function fetchFileTableFull(){
    fixedToastr('Loading data');
    $.ajax({
        type: 'GET',
        url: `/api/files/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            console.log(response.data)
            $('#file-table-full').DataTable({
                data: response.data,
                autoWidth: true,
                searching: true,
                paging: true,
                order: [[3, 'desc']],
                columns: [
                    {
                        "render": function(data, type, row, meta){
                            return getFileViewButton(row.id, row.name)
                        },
                        "title": "Name"
                    },
                    {
                        "data": "description",
                        "title": "Description"
                    },
                    {
                        "data": "created_at",
                        "title": "Created At"
                    },
                    {
                        "data": "updated_at",
                        "title": "Updated At"
                    },
                    {
                        "render": function(data, type, row, meta){
                            return getFileDownloadButton(row.id)
                        },
                        "title": "Download"
                    }
                ]
            });
            toastr.clear()
            toastr.success('Loaded')
        }
    })
}

$(document).ready(fetchFileTableFull)
