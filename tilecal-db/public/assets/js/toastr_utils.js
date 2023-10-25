function fixedToastr(message, title=null, level='info', extraParams={}){
    const fixedToastrParams = {timeOut:0, extendedTimeOut:0, closeButton:true}
    Object.assign(fixedToastrParams, extraParams)
    if (level == 'info'){
        toastr.info(message, title, fixedToastrParams)
    } else if (level == 'error'){
        toastr.error(message, title, fixedToastrParams)
    }
}

function setToastrOptions(){
    toastr.options.closeButton = true;
    toastr.options.progressBar = true;
}

function toastrError(message, errorCode, errorThrown, title=null){
    if (!(title instanceof String)){
        title = ''
    }
    fixedToastr(
        `${errorCode} - ${errorThrown}</br>${message}`,
        title,
        'error',
        {allowHtml: true}
    )
}

$(document).ready(setToastrOptions)