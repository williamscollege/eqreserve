/* This function causes an alert to be displayed on the page. If the alert is of type success or error then it will fade away in a few seconds
REQUIRES: a div of id page_alert
 */
function eqrUtil_setTransientAlert(alertType,alertMessage) {
    $('#page_alert').removeClass("in_progress_alert");
    $('#page_alert').removeClass("success_alert");
    $('#page_alert').removeClass("error_alert");
    if (alertType == 'progress') {
        $('#page_alert').html('<i class="icon-time"></i> '+alertMessage);
        $('#page_alert').addClass("in_progress_alert");
    }
    else if (alertType == 'success') {
        $('#page_alert').html('<i class="icon-ok"></i> '+alertMessage);
        $('#page_alert').addClass("success_alert");
        $('#page_alert').fadeOut({duration: 3000, queue: false}); //,function(){$('#page_alert').addClass("hide");})
    }
    else if (alertType == 'error') {
        $('#page_alert').html('<i class="icon-exclamation-sign"></i> '+alertMessage);
        $('#page_alert').addClass("error_alert");
        $('#page_alert').fadeOut({duration: 10000, queue: false});//,function(){$('#page_alert').addClass("hide");})
    }
    //$('#page_alert').removeClass("hide");
    $('#page_alert').fadeIn({duration: 10, queue: false});
}

