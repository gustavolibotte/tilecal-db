function getEquipViewRoute(id){
    return `/equipments/view/${id}`
}

function getFileViewRoute(id){
    return `/files/view/${id}`
}

function getFileDownloadRoute(id){
    return `/files/download/${id}`
}

function getFileHistDownloadRoute(id){
    return `/api/files/histDownload?id=${id}`
}

function getEquipmentIdFromViewRoute(routeStr){
    splitted = splitRoute(routeStr)
    return splitted.tail
}

function getFileIdFromViewRoute(routeStr){
    splitted = splitRoute(routeStr)
    return splitted.tail
}

function getEquipDisassembleRoute(){
    return `/api/equipments/disassemble`
}

function splitRoute(routeStr){
    const splitted = routeStr.split('/')
    const tail = splitted.pop()
    const base = splitted.join('/')
    return {
        "base": base, 
        "tail": tail
    }
}

getEquipmentRoute = '/api/equipments/get'
getFileRoute = '/api/files/get'