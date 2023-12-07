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
    tableColumns = tableColumns.concat([{
        "render": function(data, type, row, meta){
            return `<a type="button" class="btn btn-danger" id="delete_relation" equip_id="${row.id}" relation_type="${row.relation_type}"><i class="bi bi-trash" aria-hidden="true"></i></a>`
        },
        "title": "Remove Relation"
    }])
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

function disableEnterOnId(){
    $('#id').on('keydown', function(e){
        if (e.key === 'Enter') { // Check if the key pressed is Enter (key code 13)
            e.preventDefault(); // Prevent the default form submission
    }})

    $('#child_id').on('keydown', function(e){
        if (e.key === 'Enter') { // Check if the key pressed is Enter (key code 13)
            e.preventDefault(); // Prevent the default form submission
    }})

    $('#parent_id').on('keydown', function(e){
        if (e.key === 'Enter') { // Check if the key pressed is Enter (key code 13)
            e.preventDefault(); // Prevent the default form submission
    }})
}

function disassembleEquipment(swal,  equipmentId, rowEquipId, relationType){
    if (relationType == 'child'){
        var data = {
            "parent_id": equipmentId,
            "child_id": rowEquipId
        }
    } else if (relationType == 'parent'){
        var data = {
            "parent_id": rowEquipId,
            "child_id": equipmentId
        }
    }
    $.ajax({
        url: getEquipDisassembleRoute(),
        type: "POST",
        dataType: "json",
        data: data,
        success: function (data) {
            $('#related-equipments-table').DataTable().clear().destroy();
            $('#history-table-full').DataTable().clear().destroy();
            fetchRelatedEquipTable(equipmentId);
            fetchHistTable(equipmentId)
            swal.fire({
                title: 'Success',
                text: 'Equipment has been disassembled',
                icon: 'success'
            });
        },
        error: function(data){
            swal.fire({
                title: "Error",
                text: data.error,
                icon:'error'
            })
        }
    })
}

function cancelDisassemble(swal){
    swal.fire({
        title: 'Cancelled',
        text: 'No changes were made.',
        icon: 'error'
    })
}

function enableDisassemble(equipmentId){
    $(document).on("click", "#delete_relation", function (e) {
        e.preventDefault()

        const rowEquipId = $(this).attr("equip_id")

        const swal = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        })

        swal.fire({
            title: `Disassemble equipment ${rowEquipId}`,
            text: "This action will disassemble the equipment",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Disassemble',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                disassembleEquipment(
                    swal,
                    equipmentId,
                    rowEquipId,
                    $(this).attr("relation_type")
                )
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                cancelDisassemble(swal)
            }
        })
    });
}


function main(){
    const equipmentId = getEquipmentIdFromViewRoute(window.location.href)
    fetchRelatedEquipTable(equipmentId)
    fetchRelatedFilesTable(equipmentId)
    fetchHistTable(equipmentId)
    disableEnterOnId()
    enableDisassemble(equipmentId)
}

$(document).ready(main)