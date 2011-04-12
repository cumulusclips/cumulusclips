$(document).ready(function() {

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
        'script'        : host+'/myaccount/upload-validate/',
        'cancelImg'     : theme+'/images/cancel.png',
        'buttonImg'     : theme+'/images/button-browse.png',
        'fileDesc'      : 'Supported Video Formats: (*.flv) (*.wmv) (*.avi) (*.ogg) (*.mpg) (*.mp4) (*.mov) (*.mv4)',
        'fileExt'       : '*.flv;*.wmv;*.avi;*.ogg;*.mpg;*.mp4;*.mov;*.m4v',
        'onError'       : function(event, queueID, fileObj, errorObj) {

//            console.log(event);
//            console.log(queueID);
//            console.log(fileObj);
//            console.log(errorObj);
//            console.log(host);
//            console.log(token);
//            console.log(theme);
//            console.log(limit);

            if (errorObj.type == 'File Size') {

                msg = 'Errors were found! Your video exceeded the maximum size limit. Please try again.<br /><br />';
                msg += '<a href="'+host+'/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a>';

            } else {

                msg = 'Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                msg += 'this feature please <a href="'+host+'/contact/" title="Contact">contact us</a> for further ';
                msg += 'assistance.';

            }

            $('#error').show();
            $('#error').html(msg);

        },
        'onComplete': function(event, queueID, fileObj, response, data) {

//            console.log(event);
//            console.log(queueID);
//            console.log(fileObj);
//            console.log(response);
//            console.log(data);
//            console.log(host);
//            console.log(token);
//            console.log(theme);
//            console.log(limit);
            
            switch (response) {

                case 'extension':

                    msg = 'Errors were found! Your video is not of the accepted file format. Please try again.<br /><br />';
                    msg += '<a href="'+host+'/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a>';
                    $('#error').show();
                    $('#error').html(msg);
                    break;


                case 'nofile':

                    msg = 'Errors were found! No video was uploaded. Please try again.<br /><br />';
                    msg += '<a href="'+host+'/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a>';
                    $('#error').show();
                    $('#error').html(msg);
                    break;


                case 'filesize':

                    msg = 'Errors were found! Your video exceeded the maximum size limit. Please try again.<br /><br />';
                    msg += '<a href="'+host+'/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a>';
                    $('#error').show();
                    $('#error').html(msg);
                    break;


                case 'success':

                    top.location.href = host+'/myaccount/upload-complete/';
                    break;


                default:

                    msg = 'Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                    msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                    msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                    msg += 'this feature please <a href="'+host+'/contact/" title="Contact">contact us</a> for further ';
                    msg += 'assistance.';

                    $('#error').show();
                    $('#error').html(msg);
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
            url: host+'/myaccount/grab-validate/',
            data: postData,
            success: function(serverResponse, textStatus){

                if (serverResponse == 'success') {
                    top.location.href = host+"/myaccount/upload-complete/";
                } else if (serverResponse == 'invalidurl') {

                    $('.loading').hide();

                    msg = 'The URL you have submitted seems to be invalid. Please make sure you\'re providing is for the video page.';
                    msg += ' Double check you entered the entire URL and did not leave of any part of the video identifier.';

                    $('#error').show();
                    $('#error').html(msg);

                } else {

                    $('.loading').hide();

                    msg = 'Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                    msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                    msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                    msg += 'this feature please <a href="'+host+'/contact/" title="Contact">contact us</a> for further ';
                    msg += 'assistance.';

                    $('#error').show();
                    $('#error').html(msg);

                }

            }   // END success event

        }); // END AJAX call

        return false;

    }); // END form submission

});