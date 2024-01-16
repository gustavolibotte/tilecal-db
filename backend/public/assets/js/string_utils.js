function capitalize(str){
    return str.charAt(0).toUpperCase() + str.slice(1)
}

function snake2Capitalized(str){
    return str.split('_').map(capitalize).join(' ')
}