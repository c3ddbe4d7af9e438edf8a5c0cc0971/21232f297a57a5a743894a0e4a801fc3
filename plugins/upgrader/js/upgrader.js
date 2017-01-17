function upload_upgrade_file(t) {
    var form = $(t);
    var button = form.find('button');
    button.attr('disabled', 'disabled');

    form.ajaxSubmit({
        url : baseUrl + 'admincp/upgrade/upload',
        uploadProgress : function(event, position, total, percent) {

            var uI = $(".upgrade-upload-indicator");
            uI.html(percent + '%').fadeIn();
            uI.attr('value', percent).prop('value', percent);
            if (percent == 100) {
                //uI.fadeOut().html("0%")
            }
        },
        success : function(data) {
            if (data == 0) {
                alert("Please only zip file is allowed and make sure allow_max_filesize is set to higher value in php.ini settings");
            } else {
                window.location = data;
            }
            percent = 0;
            var uI = $(".upgrade-upload-indicator");
            uI.html(percent + '%').fadeIn();
            uI.attr('value', percent).prop('value', percent);
            button.prop('disabled', '').removeAttr('disabled');
        }, error : function() {
            percent = 0;
            var uI = $(".upgrade-upload-indicator");
            uI.html(percent + '%').fadeIn();
            uI.attr('value', percent).prop('value', percent);
            button.prop('disabled', '').removeAttr('disabled');
            alert("Please only zip file is allowed and make sure allow_max_filesize is set to higher value in php.ini settings");
        }
    })
    return false;
}

function upgrade_now(t) {
    var form = $(t);
    var button = form.find('button');
    button.attr('disabled', 'disabled');

    form.ajaxSubmit({
        url : baseUrl + 'admincp/upgrade/now',

        success : function(data) {
            var json = jQuery.parseJSON(data);
            if (json.status == 0) {
                alert(json.message);
            } else {
                alert(json.message);
                window.location = json.url;
            }

            button.prop('disabled', '').removeAttr('disabled');
        }, error : function() {

            button.prop('disabled', '').removeAttr('disabled');
            alert("Failed to finish up upgrade");
        }
    })
    return false;
}