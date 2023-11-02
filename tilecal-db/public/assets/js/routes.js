function getEquipViewRoute(id){
    return `/equipments/view/${id}`
}

function getFileViewRoute(id){
    return `/files/view/${id}`
}

function getFileDownloadRoute(id){
    return `/files/download/${id}`
}

function getEquipmentIdFromViewRoute(routeStr){
    [,id] = splitRoute(routeStr)
    return id
}

function getFileIdFromViewRoute(routeStr){
    [,id] = splitRoute(routeStr)
    return id
}

function splitRoute(routeStr){
    const splitted = routeStr.split('/')
    const tail = splitted.pop()
    const base = splitted.join('/')
    return base, tail
}

getEquipmentRoute = '/api/equipments/get'
getFileRoute = '/api/files/get'