function getEquipViewRoute(id){
    return `/equipments/view?id=${id}`
}

function getFileViewRoute(id){
    return `/files/view?id=${id}`
}

function getFileDownloadRoute(id){
    return `/files/download/${id}`
}

getEquipmentRoute = '/api/equipments/get'
getFileRoute = '/api/files/get'