function fetchEquipmentSideTable(){
    toastr.info('Loading data', 'Last Edited Equipments')
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
                "render": function(data, type, row, meta){return getEquipmentIdButton(row.id)},
                "title": "ID"
            },
            {
                "data": "type_label",
                "title": "Type"
            }
        ]
    }
    $.ajax({
        type: 'GET',
        url: `/api/equipments/get?orderBy=updatedAt&returnType=minimal`,
        dataType: "json",
        async: true,
        success: function(response){
            tableParams.data = response.data
            $('#equipment-side-table').DataTable(tableParams);
            toastr.clear()
        },
        error: function(response, textStatus, errorThrown){
            $('#equipment-side-table').DataTable(tableParams);
            toastr.clear()
            toastrError(
                response.responseJSON.messages.error,
                response.responseJSON.status,
                errorThrown
            )
        } 
    })
}

function disableEnterOnId(){
    $('#id').on('keydown', function(e){
        if (e.key === 'Enter') { // Check if the key pressed is Enter (key code 13)
            e.preventDefault(); // Prevent the default form submission
    }})
}

$(document).ready(function(){
    fetchEquipmentSideTable()
    disableEnterOnId()
})