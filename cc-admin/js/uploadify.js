$('document').ready(function() {

    // Retrieve vars from meta tags
    var handler = $('meta[name="uploadHandler"]').attr('content');
    var token = $('meta[name="token"]').attr('content');
    var fileDesc = $('meta[name="fileDesc"]').attr('content');
    var fileExt = $('meta[name="fileExt"]').attr('content');
    var sizeLimit = $('meta[name="sizeLimit"]').attr('content');
    var timestamp = $('#timestamp').val();




    // Initialize Uploadify for file uploads
    $('#upload').uploadify({
        'width'         : 150,
        'height'        : 28,
        'fileDataName'  : 'upload',
        'queueID'       : 'uploadQueue',
        'sizeLimit'     : sizeLimit,
        'scriptData'    : {'token':token,'timestamp':timestamp},
        'uploader'      : baseURL+'/cc-admin/extras/uploadify/uploadify.swf',
        'script'        : handler,
        'cancelImg'     : baseURL+'/cc-admin/extras/uploadify/cancel.png',
        'hideButton'    : true,
        'wmode'         : 'transparent',
        'fileDesc'      : fileDesc,
        'fileExt'       : fileExt,
        'onError'       : function(event, queueID, fileObj, errorObj) {

            var message;

            // Determine reason for failure
            if (errorObj.type == 'File Size') {
                message = 'Your file exceeded the maximum filesize limit. Please try your upload again.';
                $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
            } else {
                message = 'Errors were encountered during the processing of your file, and it cannot be uploaded at this time. We apologize for this inconvenience.';
                $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
            }

        },
        'onComplete': function(event, queueID, fileObj, response, data) {

            var message;

            // Determine result from server validation
            response = $.parseJSON(response);
            switch (response.status) {

                case 'extension':
                    message = 'Your file is not in one of the accepted file formats. Please try your upload again.';
                    $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
                    break;


                case 'nofile':
                    message = 'No file was uploaded. Please try your upload again.';
                    $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
                    break;


                case 'filesize':
                    message = 'Your file exceeded the maximum filesize limit. Please try your upload again.';
                    $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
                    break;


                case 'success':
                    $('#uploadQueue').html('<div class="uploadifyQueueItem"><span class="fileName">'+response.message+' - has been uploaded</span></div>');
                    break;


                default:
                    message = 'Errors were encountered during the processing of your file, and it cannot be uploaded at this time. We apologize for this inconvenience.';
                    $('#uploadQueue').html('<div class="uploadifyQueueItem uploadifyError"><span class="fileName">'+message+'</span></div>');
                    break;


            }   // END response switch

        }   // END onComplete

    });

    // Attach upload event to upload button
    $('#upload-button').click(function(){
        $('#upload').uploadifyUpload();
        return false;
    });

});