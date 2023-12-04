function getHistMessage(data){
    if (data.hist_type == "equip"){
        return getEquipHistMessage(data)
    } else if (data.hist_type == "file"){
        return getFileHistMessage(data)
    } else if (data.hist_type == "file_rel"){
        return getFileRelHistMessage(data)
    } else if (data.hist_type == "parent_rel"){
        return getParentRelHistMessage(data)
    } else {
        return ''
    }
}

function getEquipHistMessage(data){
    var msg = ''
    msg += "<ul>"
    msg += listEntries(data, [
        'id',
        'status',
        'location',
        'type',
        'owner',
        'serial',
        'comments'
    ])
    msg += listEntries(data.type_params, 'all')
    msg += "</ul>"
    return msg
}

function getFileHistMessage(data){
    var msg = ''
    msg += "<ul>"
    msg += listEntries(data, [
        'name',
        'description'
    ])
    msg += "</ul>"
    return msg
}

function getFileRelHistMessage(data){
    return `Link ${getEquipLink(data.equipment_id)} to ${getFileLink(data.file_id, data.file_name)}`
}

function getParentRelHistMessage(data){
    return `${getEquipLink(data.parent_equip_id)} parent to ${getEquipLink(data.child_equip_id)}`
}

function listEntries(data, fields){
    list = ''
    if (fields == 'all'){
        for (let field in data){
            list += getListItem(field, data[field])
        }
    } else{
        for (let field of fields){
            list += getListItem(field, data[field])
        }
    }
    return list
}

function getListItem(name, value){
    return `<li> <b>${capitalize(name)}</b> - ${value} </li>`
}

function getEquipLink(id){
    return getLink(getEquipViewRoute(id), id)
}

function getFileLink(id, filename){
    return getLink(getFileViewRoute(id), filename)
}

function getLink(href, text){
    return `<a type="button" class="link-primary" href="${href}">${text}</a>`
}

function getEntryOperation(data){
    if (data.action == 'insert'){
        return 'Entry created'
    } else if(data.action == 'update'){
        return 'Entry updated'
    } else if (data.action == 'delete'){
        return '<p class="text-danger">Entry deleted</p>'
    } else if (data.action == 'restore'){
        return '<p class="text-success">Entry restored</p>'
    } else {
        return 'Entry operation'
    }
}