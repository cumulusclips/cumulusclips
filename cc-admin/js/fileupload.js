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
            if (cumulusClips.uploadType !== 'library') {
                var matches = file.name.match(/\.[a-z0-9]+$/i);
                var fileTypes = $.parseJSON($('input[name="file-types"]').val());
                if (!matches || $.inArray(matches[0].substr(1).toLowerCase(), fileTypes) == -1) {
                    displayMessage(false, 'errors occurred');
                    return false;
                }
            }
            
            // Validate filesize
            var filesizeLimit = $('input[name="upload-limit"]').val();
            if (file.size > filesizeLimit) {
                displayMessage(false, cumulusClips.errorSize);
                return false;
            }
            
            // Prepare upload progress box
            $('#upload_status').show();
            $('#upload_status .fill').css('width', '0%');
            $('#upload_status .percentage').text('0%');
            
            // Set upload filename
            var filename = file.name;
            if (!cumulusClips.ie9) filename += ' (' + formatBytes(file.size, 0) + ')';
            $('#upload_status .title').text(filename);
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

                // Update form with temp path to uploaded file
                $('input[name="temp-file"]').val(data.result.other.temp);
                $('input[name="filesize"]').val(cumulusClips.uploadFileData.files[0].size);

                // Perform success actions based on what was being uploaded
                if ($.inArray(cumulusClips.uploadType, ['video', 'library']) !== -1 ) {
                    var fileName = cumulusClips.uploadFileData.files[0].name;
                    $('input[name="original-name"]').val(fileName);
                    $('.upload-complete')
                        .removeClass('hidden')
                        .addClass('show')
                        .text(fileName + ' - has been uploaded');
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
    $('.button-upload').click(function(event){
        if (cumulusClips.uploadFileData !== undefined) {
            $('#upload_status .fill').addClass('in-progress');
            cumulusClips.jqXHR = cumulusClips.uploadFileData.submit();
        }
        event.preventDefault();
    });
    
    // Attach cancel event to cance button
    $('#upload_status a').click(function(event){
        if (cumulusClips.jqXHR !== undefined) {
            cumulusClips.jqXHR.abort();
        }
        resetProgress();
        $('#upload').val('');
        cumulusClips.jqXHR = undefined;
        cumulusClips.uploadFileData = undefined;
        event.preventDefault();
    });
    
    // Detect IE9
    if ($('meta[name="ie9"]').length > 0) {
        $('body').addClass('ie9');
        cumulusClips.ie9 = true;
        $('#upload_status .percentage').hide();
    } else {
        cumulusClips.ie9 = false;
    }
});