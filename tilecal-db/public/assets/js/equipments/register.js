function fetchEquipmentSideTable(){
    fixedToastr('Loading data', 'Last Edited Equipments')
    const tableColumns = [
        {
            "render": function(data, type, row, meta){return getEquipmentIdButton(row.id)},
            "title": "ID"
        },
        {
            "data": "type_label",
            "title": "Type"
        }
    ]
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get?orderBy=updatedAt&returnType=minimal`,
        dataType: "json",
        async: true,
        success: function(response){
            $('#equipment-side-table').DataTable({
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
            toastr.success('Loaded', 'Last Edited Equipments')
        },
        error: function(response, textStatus, errorThrown){
            $('#equipment-side-table').DataTable({
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
                errorThrown
            )
        } 
    })
}

$(document).ready(fetchEquipmentSideTable)