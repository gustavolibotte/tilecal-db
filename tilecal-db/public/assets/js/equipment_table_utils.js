function getEquipmentIdButton(id){
    return `<a type="button" class="btn btn-primary" href="${getEquipViewRoute(id)}">${id}</a>`;
}
equipmentTableOrder = [[7, 'desc']]

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