function fetchEquipTableFull(){
    fixedToastr('Loading data')
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get${window.location.search}`,
        dataType: "json",
        async: true,
        success: function(response){
            console.log(`Server received ${response.type_name}`)
            $('#equipment-table-full').DataTable({
                data: response.data,
                autoWidth: true,
                rowId: "id",
                searching: true,
                paging: true,
                order: [[7, 'desc']],
                columns: [
                    {
                        "render": function(data, type, row, meta){return getEquipmentIdButton(row.id)},
                        "title": "ID"
                    },
                    {
                        "data": "type_label",
                        "title": "Type"
                    },
                    {
                        "data": "location",
                        "title": "Location"
                    },
                    {
                        "data": "status",
                        "title": "Status"
                    },
                    {
                        "data": "owner_name",
                        "title": "Owner"
                    },
                    {
                        "data": "comments",
                        "title": "Comments"
                    },
                    {
                        "data": "created_at",
                        "title": "Created At"
                    },
                    {
                        "data": "updated_at",
                        "title": "Updated At"
                    },
                ]
            });
            toastr.clear()
            toastr.success('Loaded')
        }
    })
}

$(document).ready(fetchEquipTableFull)