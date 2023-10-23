function fixedToastr(message, title=null){
    const timeOut = toastr.options.timeOut
    const extendedTimeOut = toastr.options.extendedTimeOut
    const closeButton = toastr.options.closeButton
    toastr.options.timeOut = 0
    toastr.options.extendedTimeOut = 0
    toastr.options.closeButton = false
    toastr.info(message, title)
    toastr.options.timeOut = timeOut
    toastr.options.extendedTimeOut = extendedTimeOut
    toastr.options.closeButton = closeButton
}

function setToastrOptions(){
    toastr.options.closeButton = true;
    toastr.options.progressBar = true;
}

$(document).ready(setToastrOptions)