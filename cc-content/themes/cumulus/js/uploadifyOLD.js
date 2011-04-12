$(document).ready(function() {

    $('#fileInput').fileUpload ({
        'uploader'  : '<?=HOST?>/flash/uploader.swf',
        'script'    : '<?=HOST?>/myaccount/upload-validate/',
        'cancelImg' : '<?=THEME?>/images/cancel.png',
        'sizeLimit' : <?php echo VIDEO_SIZE_LIMIT; ?>,
        'fileDesc'  : 'Supported Video Formats: (*.flv) (*.wmv) (*.avi) (*.ogg) (*.mpg) (*.mp4) (*.mov) (*.mv4)',
        'fileExt'   : '*.flv;*.wmv;*.avi;*.ogg;*.mpg;*.mp4;*.mov;*.m4v',
        'auto'      : true,
        'folder'    : '/uploads/temp',
        'scriptData': {'token':'<?php echo $token; ?>'},
        'onError'   : function(event, queueID, fileObj, errorObj) {

            $('#special').css('visibility', 'hidden');
            $('#special').css('height', '0px');
            
            if (errorObj.type == 'File Size') {

                msg = '<p>Errors were found! Your video exceeded the maximum size limit. Please try again.<br /><br />';
                msg += '<a href="<?=HOST?>/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a></p>';
                $('#errors-found').css('display', 'block');
                $('#errors-found').append(msg);

            } else {

                msg = '<p>Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                msg += 'this feature please <a href="<?=HOST?>/contact/" title="Contact">contact us</a> for further ';
                msg += 'assistance.</p>';

                $('#errors-found').css('display', 'block');
                $('#errors-found').append(msg);

            }

        },
        'onComplete': function(event, data, fileObj, response) {

            $('#special').css('visibility', 'hidden');
            $('#special').css('height', '0px');
            
            switch (response) {
                
                case 'extension':

                    msg = '<p>Errors were found! Your video is not of the accepted file format. Please try again.<br /><br />';
                    msg += '<a href="<?=HOST?>/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a></p>';
                    $('#errors-found').css('display', 'block');
                    $('#errors-found').append(msg);
                    break;

                    
                case 'nofile':

                    msg = '<p>Errors were found! No video was uploaded. Please try again.<br /><br />';
                    msg += '<a href="<?=HOST?>/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a></p>';
                    $('#errors-found').css('display', 'block');
                    $('#errors-found').append(msg);
                    break;

                    
                case 'filesize':

                    msg = '<p>Errors were found! Your video exceeded the maximum size limit. Please try again.<br /><br />';
                    msg += '<a href="<?=HOST?>/myaccount/upload-video/" title="Attempt to Upload">Attempt to upload again</a></p>';
                    $('#errors-found').css('display', 'block');
                    $('#errors-found').append(msg);
                    break;

                    
                case 'TRUE':

                    top.location.href="/myaccount/upload-complete/";
                    break;

                    
                default:

                    msg = '<p>Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                    msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                    msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                    msg += 'this feature please <a href="<?=HOST?>/contact/" title="Contact">contact us</a> for further ';
                    msg += 'assistance.</p>';

                    $('#errors-found').css('display', 'block');
                    $('#errors-found').append(msg);
                    break;
          
            }   // END response switch

        }   // END onComplete

    }); // END File Upload



    $("#upload-choice").click(function(){
       $("#upload-video").slideDown('normal', function(){
           $("#choices").slideUp('normal');
       });
    });



    $("#grab-choice").click(function(){
       $("#grab-video").slideDown('normal', function(){
           $("#choices").slideUp('normal');
       });
    });



    $('#grabButton').click(function(){

        var tokenValue = '<?php echo $token; ?>';
        var videoUrl = $('#videoUrl').val();
        var postData = ({token:tokenValue, url:videoUrl});

        $('#grabLoading').show();

        $.ajax({
            type: "POST",
            url: '/myaccount/grab-validate/',
            data: postData,
            success: function(serverResponse, textStatus){

                if (serverResponse == 'TRUE') {
                    top.location.href="/myaccount/upload-complete/";
                } else if (serverResponse == 'invalidurl') {

                    $('#grabLoading').hide();

                    msg = '<p>The URL you have submitted seems to be invalid. Please make sure you\'re providing is for the video page.';
                    msg += ' Double check you entered the entire URL and did not leave of any part of the video identifier.</p>';

                    $('#errors-found').css('display', 'block');
                    $('#errors-found').html(msg);

                } else {

                    $('#grabLoading').hide();

                    msg = '<p>Errors were encountered during the processing of your video, and it cannot be uploaded at ';
                    msg += 'this time. We apologize for this inconvenience. Our support team has been notified and will ';
                    msg += 'investigate into the cause and fix for this issue. If you continue to experience problems using ';
                    msg += 'this feature please <a href="<?=HOST?>/contact/" title="Contact">contact us</a> for further ';
                    msg += 'assistance.</p>';

                    $('#errors-found').css('display', 'block');
                    $('#errors-found').html(msg);

                }

            }   // END success event

        }); // END AJAX call

        return false;

    }); // END form submission



});