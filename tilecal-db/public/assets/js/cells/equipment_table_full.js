$.get({
    url: "/api/equipments/get",
    dataType: "json",
    success: function(response){
        $('#equipment-table-full').DataTable({
            data: response.data,
            rowId: "id",
            searching: true,
            scrollColapse: true,
            scrollY: '70vh',
            paging: true,
            columns: [
                {
                    "data": "id",
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
    }
})
