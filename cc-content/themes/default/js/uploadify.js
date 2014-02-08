$(document).ready(function() {

    var handler = $('form[name="uploadify"]').attr('action');
    var buttonText = $('[name="uploadify:buttonText"]').attr('content');
    var theme = $('[name="uploadify:theme"]').attr('content');
    var token = $('#uploadToken').val();
    var fileTypes = $.parseJSON($('#fileTypes').val());
    var limit = $('#uploadLimit').val();
    var type = $('#uploadType').val();
    var timestamp = $('#uploadTimestamp').val();
    var debugUpload = $('#debugUpload').val() === 'true' ? true : false;

    // Initialize Uploadify for video uploads
    $('#upload').uploadify({
        debug: debugUpload,
        width: 150,
        height: 35,
        fileSizeLimit: limit,
        fileObjName: 'upload',
        formData: {token:token,timestamp:timestamp},
        swf: theme+'/flash/uploadify.swf',
        uploader: handler,
        buttonText: buttonText,
        buttonClass: 'button',
        auto: false,
        multi: false,
        overrideEvents: ['onUploadProgress','onSelect'],
        onSelect: function(file)
        {
            var callback;
            
            // Validate file type
            var matches = file.name.match(/\.[a-z0-9]+$/i);
            if (!matches || $.inArray(matches[0].substr(1),fileTypes) == -1) {
                callback = function(data){
                    displayMessage(0,data);
                    window.scrollTo(0, 0);
                }
                $('#upload').uploadify('cancel');
                getText(callback, 'error_uploadify_extension');
                return false;
            }
            
            // Validate filesize
            if (file.size > limit) {
                callback = function(data){
                    displayMessage(0,data);
                    window.scrollTo(0, 0);
                }
                $('#upload').uploadify('cancel');
                getText(callback, 'error_uploadify_filesize');
                return false;
            }
            
            $('.message').hide();
            $('#upload_status').show();
            $('#upload_status .fill').css('width', '0%');
            $('#upload_status .percentage').text('0%');
            $('#upload_status .title').text(file.name);
            $('#upload').uploadify('disable',true);
        },
        onUploadProgress: function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal)
        {
            var progress = parseInt(bytesUploaded / bytesTotal * 100, 10);
            $('#upload_status .percentage').text(progress + '%');
            $('#upload_status .fill').css('width', progress + '%');
        },
        onUploadError: function(file, errorCode, errorMsg, errorString)
        {
            var node;
            var replacements = {host:baseUrl};

            // Determine reason for failure
            if (errorString == 'Cancelled') {
                // Upload was cancelled (either via API or by user)
                return false;
            } else {
                node = 'error_uploadify_system';
            }

            // Retrieve and output corresponding error text from language xml
            var callback = function(data){
                resetProgress();
                displayMessage(0,data);
                window.scroll(0,0);
            }
            getText(callback, node, replacements);

        },  // END onUploadError
        onUploadSuccess: function(file, responseRaw, status)
        {
            // Determine result from server validation
            response = $.parseJSON(responseRaw);
            if (response.result == 1) {

                // Perform success actions based on what was being uploaded
                if (type == 'avatar') {
                    resetProgress();
                    displayMessage(1,response.msg);
                    window.scroll(0,0);
                    $('.avatar img').attr('src',response.other);
                } else {
                    top.location.href = baseUrl+'/myaccount/upload/complete/';
                }

            } else {
                resetProgress();
                displayMessage(0,response.msg);
            }

        }   // END onUploadSuccess
    });

    // Attach upload event to upload button
    $('#upload_button').click(function(){
        $('#upload').uploadify('upload');
        return false;
    });
    
    // Attach cancel event to cance button
    $('#upload_status a').click(function(){
        $('#upload').uploadify('cancel');
        resetProgress();
        return false;
    });
});

/**
 * Resets progress bar, enables browse button,
 * clears file title, and hides progress bar
 */
function resetProgress()
{
    $('#upload').uploadify('disable',false);
    $('#upload_status').hide();
    $('#upload_status .title').text('');
    $('#upload_status .fill').css('width', '0%');
    $('#upload_status .percentage').text('0%');
}