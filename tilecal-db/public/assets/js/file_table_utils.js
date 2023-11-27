function getFileViewButton(fileId, buttonText){
    return `<a type="button" class="btn btn-primary" href="${getFileViewRoute(fileId)}">${buttonText}</a>`;
}

function getFileDownloadButton(fileId){
    return `<a type="button" class="btn btn-secondary" href="${getFileDownloadRoute(fileId)}"><i class="bi bi-download"></i></a>`
}

function getFileHistDownloadButton(fileId){
    return `<a type="button" class="btn btn-secondary" href="${getFileHistDownloadRoute(fileId)}"><i class="bi bi-download"></i></a>`
}

fileTableOrder = [[3, 'desc']]

fileTableColumns = [
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