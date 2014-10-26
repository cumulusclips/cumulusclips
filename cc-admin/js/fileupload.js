cumulusClips.errorFormat = 'Your file is not in one of the accepted file formats. Please try your upload again.';
cumulusClips.errorGeneral = 'Errors were encountered during the processing of your file, and it cannot be uploaded at this time. We apologize for this inconvenience.';
cumulusClips.errorSize = 'Your file exceeded the maximum filesize limit. Please try your upload again.';
cumulusClips.uploadType = $('input[name="upload-type"]').val();
    
$(function(){
    $('#upload').fileupload({
        url: $('input[name="upload-handler"]').val(),
        dataType: 'json',
        type: 'POST',
        formData: function(form){return form.serializeArray();},
        add: function(event, data)
        {
            cumulusClips.uploadFileData = data;
            var file = data.files[0];
            
            // Validate file type
            var matches = file.name.match(/\.[a-z0-9]+$/i);
            var fileTypes = $.parseJSON($('input[name="file-types"]').val());
            var filesizeLimit = $('input[name="upload-limit"]').val();
            if (!matches || $.inArray(matches[0].substr(1),fileTypes) == -1) {
                displayMessage(false, cumulusClips.errorFormat);
                return false;
            }
            
            // Validate filesize
            if (file.size > filesizeLimit) {
                displayMessage(false, cumulusClips.errorSize);
                return false;
            }
            
            $('#upload_status').show();
            $('#upload_status .fill').css('width', '0%');
            $('#upload_status .percentage').text('0%');
            $('#upload_status .title').text(file.name + ' (' + formatBytes(file.size, 0) + ')');
        },
        progress: function(event, data)
        {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#upload_status .percentage').text(progress + '%');
            $('#upload_status .fill').css('width', progress + '%');
        },
        fail: function(event, data)
        {
            // Determine reason for failure
            if (data.errorThrown === 'abort') {
                // Upload was cancelled (either via API or by user)
                return false;
            } else {
                resetProgress();
                displayMessage(false, cumulusClips.errorGeneral);
            }
        },
        done: function(event, data)
        {
            // Determine result from server validation
            if (data.result.result === true) {
                // Perform success actions based on what was being uploaded
                var fileName = cumulusClips.uploadFileData.files[0].name;
                $('input[name="temp-file"]').val(data.result.other.temp);
                if (cumulusClips.uploadType == 'video') {
                    $('input[name="original-video-name"]').val(fileName);
                    $('.videoUploadComplete').css('display', 'inline-block').text(fileName + ' - has been uploaded');
                    resetProgress();
                } else {
                    $('form').submit();
                }
            } else {
                resetProgress();
                displayMessage(false, data.result.message);
            }
        }
    });

    // Attach upload event to upload button
    $('#upload_button').click(function(){
        event.preventDefault();
        if (cumulusClips.uploadFileData !== undefined) {
            cumulusClips.jqXHR = cumulusClips.uploadFileData.submit();
        }
    });
    
    // Attach cancel event to cance button
    $('#upload_status a').click(function(event){
        event.preventDefault();
        if (cumulusClips.jqXHR !== undefined) {
            cumulusClips.jqXHR.abort();
        }
        resetProgress();
        $('#upload').val('');
        cumulusClips.jqXHR = undefined;
        cumulusClips.uploadFileData = undefined;
    });
});