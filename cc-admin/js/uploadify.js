$('document').ready(function() {

    var host = $('meta[name="baseURL"]').attr('content');


    // Set video upload method to file upload
    $('#upload-choice').click(function(){
        $('#upload-video').show();
        $('#grab-video').hide();
    });




    // Initialize Uploadify for video uploads
    $('#upload').uploadify({
        'width'         : 150,
        'height'        : 28,
        'fileDataName'  : 'upload',
        'scriptData'    : {'token':'token'},
        'uploader'      : host+'/cc-admin/extras/uploadify/uploadify.swf',
        'script'        : host+'/myaccount/upload/validate/',
        'cancelImg'     : host+'/cc-admin/extras/uploadify/cancel.png',
        'hideButton'    : true,
        'wmode'         : 'transparent',
        'fileDesc'      : 'Supported Video Formats: (*.flv) (*.wmv) (*.avi) (*.ogg) (*.mpg) (*.mp4) (*.mov) (*.mv4)',
        'fileExt'       : '*.flv;*.wmv;*.avi;*.ogg;*.mpg;*.mp4;*.mov;*.m4v',
        'onError'       : function(event, queueID, fileObj, errorObj) {

            console.log(event);
            console.log(queueID);
            console.log(fileObj);
            console.log(errorObj);


            // Determine reason for failure
            if (errorObj.type == 'File Size') {
            } else {
            }



        },
        'onComplete': function(event, queueID, fileObj, response, data) {

            var node;
            var replacements;
            var callback;
            var error = $('#error');

            console.log(event);
            console.log(queueID);
            console.log(fileObj);
            console.log(response);
            console.log(data);

            // Determine result from server validation
            switch (response) {

                case 'extension':
                    break;


                case 'nofile':
                    break;


                case 'filesize':
                    break;


                case 'success':
                    break;


                default:
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