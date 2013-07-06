$(document).ready(function() {

    // Retrieve vars from meta tags
    var sizeLimit = $('input[name="uploadLimit"]').val();
    var fileTypes = $.parseJSON($('input[name="fileTypes"]').val());
    var uploadType = $('input[name="uploadType"]').val();
    var message;

    // Initialize Uploadify for video uploads
    $('#upload').uploadify({
        width: 100,
        height: 28,
        fileSizeLimit: sizeLimit,
        fileObjName: 'upload',
        formData: {uploadType:uploadType},
        swf: baseURL+'/cc-admin/extras/uploadify/uploadify.swf',
        uploader: baseURL+'/cc-admin/upload_ajax.php',
        buttonText: 'Browse',
        buttonClass: 'button',
        auto: false,
        multi: false,
        overrideEvents: ['onUploadProgress','onSelect'],
        onSelect: function(file)
        {
            // Validate file type
            var matches = file.name.match(/\.[a-z0-9]+$/i);
            if (!matches || $.inArray(matches[0].substr(1),fileTypes) == -1) {
                message = 'Your file is not in one of the accepted file formats. Please try your upload again.';
                displayMessage(false, message);
                $('#upload').uploadify('cancel');
                return false;
            }
            
            // Validate filesize
            if (file.size > sizeLimit) {
                message = 'Your file exceeded the maximum filesize limit. Please try your upload again.';
                displayMessage(false, message);
                $('#upload').uploadify('cancel');
                return false;
            }
            
            $('.message').hide();
            $('#upload_status').show();
            $('#upload_status .fill').css('width', '0%');
            $('#upload_status .percentage').text('0%');
            $('#upload_status .title').text(file.name + ' (' + formatBytes(file.size, 0) + ')');
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
            // Determine reason for failure
            if (errorString == 'Cancelled') {
                // Upload was cancelled (either via API or by user)
                return false;
            } else {
                message = 'Errors were encountered during the processing of your file, and it cannot be uploaded at this time. We apologize for this inconvenience.';
                displayMessage(false, message);
            }
            
            resetProgress();
        },
        onUploadSuccess: function(file, responseRaw, status)
        {
            // Determine result from server validation
            response = $.parseJSON(responseRaw);
            if (response.result === true) {
                $('input[name="tempFile"]').val(response.other.temp);
                if (uploadType == 'video') {
                    message = file.name + ' - has been uploaded';
                    $('input[name="originalVideoName"]').val(file.name);
                    $('.videoUploadComplete').show().text(message);
                    resetProgress();
                } else {
                    $('form').submit();
                }
            } else {
                switch (response.message) {
                    case 'extension':
                        message = 'Your file is not in one of the accepted file formats. Please try your upload again.';
                        break;
                    case 'nofile':
                        message = 'No file was uploaded. Please try your upload again.';
                        break;
                    case 'filesize':
                        message = 'Your file exceeded the maximum filesize limit. Please try your upload again.';
                        break;
                    default:
                        message = 'Errors were encountered during the processing of your file, and it cannot be uploaded at this time. We apologize for this inconvenience.';
                        break;
                }
                resetProgress();
                displayMessage(false, message);
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

/**
 * Display message sent from the server handler script for page actions
 * @param boolean result The result of the requested action (1 = Success, 0 = Error)
 * @param string message The textual message for the result of the requested action
 * @return void Message block is displayed and styled accordingly with message.
 * If message block is already visible, then it is updated.
 */
function displayMessage(result, message)
{
    var cssClass = (result == true) ? 'success' : 'error';
    var existingClass = ($('.message').hasClass('success')) ? 'success' : 'error+';
    $('.message').show();
    $('.message').html(message);
    $('.message').removeClass(existingClass);
    $('.message').addClass(cssClass);
}

/**
 * Format number of bytes into human readable format
 * @param int bytes Total number of bytes
 * @param int precision Accuracy of final round
 * @return string Returns human readable formatted bytes
 */
function formatBytes(bytes, precision)
{
    var units = ['b', 'KB', 'MB', 'GB', 'TB'];
    bytes = Math.max(bytes, 0);
    var pwr = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
    pwr = Math.min(pwr, units.length - 1);
    bytes /= Math.pow(1024, pwr);
    return Math.round(bytes, precision) + units[pwr];
}