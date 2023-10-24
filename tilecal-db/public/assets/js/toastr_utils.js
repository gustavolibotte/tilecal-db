function fixedToastr(message, title=null, level='info'){
    const timeOut = toastr.options.timeOut
    const extendedTimeOut = toastr.options.extendedTimeOut
    const closeButton = toastr.options.closeButton
    toastr.options.timeOut = 0
    toastr.options.extendedTimeOut = 0
    toastr.options.closeButton = true
    if (level == 'info'){
        toastr.info(message, title)
    } else if (level == 'error'){
        toastr.error(message, title)
    }
    toastr.options.timeOut = timeOut
    toastr.options.extendedTimeOut = extendedTimeOut
    toastr.options.closeButton = closeButton
}

function setToastrOptions(){
    toastr.options.closeButton = true;
    toastr.options.progressBar = true;
}

function toastrError(message, errorCode, errorThrown){
    fixedToastr(
        message,
        `${errorCode} - ${errorThrown}`,
        'error'
    )
}

$(document).ready(setToastrOptions)