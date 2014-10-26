$(function(){
    $('#upload').fileupload({
        url: $('form[name="upload"]').attr('action'),
        dataType: 'json',
        type: 'POST',
        formData: function(form){return form.serializeArray();},
        add: function(event, data)
        {
            cumulusClips.uploadFileData = data;
            var file = data.files[0];
            var filesizeLimit;
            var callback;
            
            // Validate file type
            var matches = file.name.match(/\.[a-z0-9]+$/i);
            var fileTypes = $.parseJSON($('#file-types').val());
            var filesizeLimit = $('#upload-limit').val();
            if (!matches || $.inArray(matches[0].substr(1),fileTypes) == -1) {
                callback = function(data){
                    displayMessage(false, data);
                    window.scrollTo(0, 0);
                }
                getText(callback, 'error_upload_extension');
                return false;
            }
            
            // Validate filesize
            if (file.size > filesizeLimit) {
                callback = function(data){
                    displayMessage(false, data);
                    window.scrollTo(0, 0);
                }
                getText(callback, 'error_upload_filesize');
                return false;
            }
            
            $('.message').hide();
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
            var textEntry;
            var replacements = {host:cumulusClips.baseUrl};
            
            // Determine reason for failure
            if (data.errorThrown === 'abort') {
                // Upload was cancelled (either via API or by user)
                return false;
            } else {
                textEntry = 'error_upload_system';
            }

            // Retrieve and output corresponding error text from language xml
            var callback = function(data){
                resetProgress();
                displayMessage(false, data);
                window.scroll(0,0);
            }
            getText(callback, textEntry, replacements);
        },
        done: function(event, data)
        {
            // Determine result from server validation
            response = data.result;
            if (data.result.result === true) {
                // Perform success actions based on what was being uploaded
                if ($('#upload-type').val() == 'avatar') {
                    resetProgress();
                    displayMessage(true, data.result.message);
                    window.scroll(0,0);
                    $('.avatar img').attr('src', data.result.other);
                } else {
                    top.location.href = cumulusClips.baseUrl + '/account/upload/complete/';
                }
            } else {
                resetProgress();
                displayMessage(true, data.result.message);
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

/**
 * Resets progress bar, enables browse button,
 * clears file title, and hides progress bar
 */
function resetProgress()
{
    $('#upload_status').hide();
    $('#upload_status .title').text('');
    $('#upload_status .fill').css('width', '0%');
    $('#upload_status .percentage').text('0%');
}