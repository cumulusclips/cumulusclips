$(function(){

    // Attach upload plugin events to upload browse field
    $(document).on('click', '#upload', function(event){

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
                var filename = '';
                var callback;

                // Validate file type
                var filenameLower = file.name.toLowerCase();
                var matches = filenameLower.match(/\.[a-z0-9]+$/i);

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

                // Prepare upload progress box
                $('.message').hide();
                $('#uploaded-file').hide();
                $('#upload-status').show();
                $('#upload-status .fill').css('width', '0%');
                $('#upload-status .percentage').text('0%');

                // Set upload filename
                $('#filename').val('');
                filename = file.name + ' (' + formatBytes(file.size, 0) + ')';
                $('#upload-status .title').text(filename);
            },
            progress: function(event, data)
            {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#upload-status .percentage').text(progress + '%');
                $('#upload-status .fill').css('width', progress + '%');
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
                    alert(data.errorThrown);
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
                // Reset upload form
                resetProgress();
                $('#upload').val('');
                cumulusClips.jqXHR = undefined;
                cumulusClips.uploadFileData = undefined;

                // Determine result from server validation
                var response = data.result;
                if (response.result === true) {
                    var file = data.files[0];
                    $('#uploaded-file span').text(file.name + ' (' + formatBytes(file.size, 0) + ')');
                    $('#uploaded-file').show();
                    $('#filename').val(response.other.temp).valid();
                } else {
                    displayMessage(false, response.message);
                }
            }
        });

    });

    // Attach upload event to upload button
    $(document).on('click', '#upload-button', function(event){
        if (cumulusClips.uploadFileData !== undefined) {
            $('#upload-status .fill').addClass('in-progress');
            cumulusClips.jqXHR = cumulusClips.uploadFileData.submit();
        }
        event.preventDefault();
    });

    // Cancel queued video upload
    $(document).on('click', '#upload-status a', function(event){
        removeQueuedVideoUpload();
        event.preventDefault();
    });

    // Cancel already uploaded video
    $(document).on('click', '#uploaded-file a', function(event){
        $('#filename').val('');
        $('#uploaded-file span').text('');
        $('#uploaded-file').hide();
        event.preventDefault();
    });
});

function removeQueuedVideoUpload()
{
    if (cumulusClips.jqXHR !== undefined) {
        cumulusClips.jqXHR.abort();
    }
    resetProgress();
    $('#upload').val('');
    cumulusClips.jqXHR = undefined;
    cumulusClips.uploadFileData = undefined;
}

/**
 * Resets progress bar, enables browse button,
 * clears file title, and hides progress bar
 */
function resetProgress()
{
    $('#upload-status .fill').removeClass('in-progress');
    $('#upload-status').hide();
    $('#upload-status .title').text('');
    $('#upload-status .fill').css('width', '0%');
    $('#upload-status .percentage').text('0%');
}