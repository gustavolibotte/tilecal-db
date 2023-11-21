function getEquipmentIdButton(id){
    return `<a type="button" class="btn btn-primary" href="${getEquipViewRoute(id)}">${id}</a>`;
}
equipmentTableOrder = [[8, 'desc']]

equipmentTableColumns = [
    {
        "render": function(data, type, row, meta){return getEquipmentIdButton(row.id)},
        "title": "ID"
    },
    {
        "data": "type_label",
        "title": "Type"
    },
    {
        "data": "location_name",
        "title": "Location"
    },
    {
        "data": "detector_location_name",
        "title": "Detector Location"
    },
    {
        "data": "status_name",
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
    }
]