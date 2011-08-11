$('document').ready(function() {

    var host = $('[name="uploadify:host"]').attr('content');
    var token = $('[name="uploadify:token"]').attr('content');
    var theme = $('[name="uploadify:theme"]').attr('content');
    var limit = $('[name="uploadify:limit"]').attr('content');


    // Set video upload method to file upload
    $('#upload-choice').click(function(){
        $('#upload-video').show();
        $('#grab-video').hide();
    });

    // Set video upload method to YouTube grab
    $('#grab-choice').click(function(){
        $('#grab-video').show();
        $('#upload-video').hide();
    });



    // Initialize Uploadify for video uploads
    $('#select-file').uploadify({
        'width'         : 145,
        'height'        : 42,
        'sizeLimit'     : limit,
        'fileDataName'  : 'uploadify',
        'scriptData'    : {'token':token},
        'uploader'      : theme+'/flash/uploadify.swf',
        'script'        : host+'/myaccount/upload/validate/',
        'cancelImg'     : theme+'/images/cancel.png',
        'buttonImg'     : theme+'/images/button-browse.png',
        'fileDesc'      : 'Supported Video Formats: (*.flv) (*.wmv) (*.avi) (*.ogg) (*.mpg) (*.mp4) (*.mov) (*.mv4)',
        'fileExt'       : '*.flv;*.wmv;*.avi;*.ogg;*.mpg;*.mp4;*.mov;*.m4v',
        'onError'       : function(event, queueID, fileObj, errorObj) {

            console.log(event);
            console.log(queueID);
            console.log(fileObj);
            console.log(errorObj);


            var node;
            var replacements;
            var error = $('#error');

            // Determine reason for failure
            if (errorObj.type == 'File Size') {
                node = 'error_uploadify_filesize';
                replacements = {link:host+'/myaccount/upload-video/'};
            } else {
                node = 'error_uploadify_system';
                replacements = {link:host+'/contact/'};
            }

            // Retrieve and output corresponding error text from language xml
            var callback = function(data){
                error.show();
                error.html(data);
            }
            GetText(callback, node, replacements);

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
                    
                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_extension';
                    replacements = {link:host+'/myaccount/upload-video/'};
                    callback = function(data){
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);
                    break;


                case 'nofile':

                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_empty';
                    replacements = {link:host+'/myaccount/upload-video/'};
                    callback = function(data){
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);
                    break;


                case 'filesize':

                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_filesize';
                    replacements = {link:host+'/myaccount/upload-video/'};
                    callback = function(data){
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);
                    break;


                case 'success':

                    top.location.href = host+'/myaccount/upload-complete/';
                    break;


                default:

                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_system';
                    replacements = {link:host+'/contact/'};
                    callback = function(data){
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);
                    break;


            }   // END response switch

        }   // END onComplete

    });

    // Attach upload event to upload button
    $('#begin-upload').click(function(){
        $('#select-file').uploadifyUpload();
        return false;
    });



    // Submit YouTube grab form
    $('#grab-form').submit(function(){

        var tokenValue = token;
        var videoUrl = $('#video-url').val();
        var postData = ({token:tokenValue, url:videoUrl});

        $('.loading').show();

        $.ajax({
            type: "POST",
            url: host+'/myaccount/grab/validate/',
            data: postData,
            success: function(serverResponse, textStatus){

                var loading = $('.loading');
                var error = $('#error');

                if (serverResponse == 'success') {
                    top.location.href = host+"/myaccount/upload-complete/";
                } else if (serverResponse == 'invalidurl') {

                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_url';
                    replacements = {link:host+'/contact/'};
                    callback = function(data){
                        loading.hide();
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);

                } else {

                    // Retrieve and output corresponding error text from language xml
                    node = 'error_uploadify_system';
                    replacements = {link:host+'/contact/'};
                    callback = function(data){
                        loading.hide();
                        error.show();
                        error.html(data);
                    }
                    GetText(callback, node, replacements);

                }

            }   // END success event

        }); // END AJAX call

        return false;

    }); // END form submission

});