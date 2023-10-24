function getFileViewButton(fileId, buttonText){
    return `<a type="button" class="btn btn-primary" href="${getFileViewRoute(fileId)}">${buttonText}</a>`;
}

function getFileDownloadButton(fileId){
    return `<a type="button" class="btn btn-secondary" href="${getFileDownloadRoute(fileId)}"><i class="bi bi-download"></i></a>`
}