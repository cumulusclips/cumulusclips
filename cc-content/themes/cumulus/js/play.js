$('document').ready(function(){

    // Display player
    $f("video", {src:video.host+"/cc-content/player/flowplayer-3.2.5.swf",wmode:"transparent"},
        {
            canvas: {backgroundColor:'#000000'},
            clip: {
                onStart:function(clip){
                    pageTracker._trackEvent('Videos', 'Start', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(clip.duration));
                },
                onFinish:function(clip){
                    pageTracker._trackEvent('Videos', 'Finish', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(clip.duration));
                },
                onPause:function(){
                    pageTracker._trackEvent('Videos', 'Pause', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(this.getTime()));
                },
                onResume:function(){
                    pageTracker._trackEvent('Videos', 'Resume', video.host+'/videos/'+video.id+'/'+video.slug+'/');
                },
                onStop:function(){
                    pageTracker._trackEvent('Videos', 'Stop', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(this.getTime()));
                },
                onFullscreen:function(){
                    pageTracker._trackEvent('Videos', 'Full Screen', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(this.getTime()));
                },
                onFullscreenExit:function(){
                    pageTracker._trackEvent('Videos', 'Full Screen Exit', video.host+'/videos/'+video.id+'/'+video.slug+'/',Math.ceil(this.getTime()));
                },
                onSeek:function(clip,endPosition){
                    pageTracker._trackEvent('Videos', 'Seek', video.host+'/videos/'+video.id+'/'+video.slug+'/', Math.ceil(endPosition));
                }
            },
            plugins: {
                controls:{
                    url:video.host+'/cc-content/player/flowplayer.controls-3.2.3.swf',
                    borderRadius:"0px",
                    timeColor:"#ffffff",
                    slowForward:true,
                    bufferGradient:"none",
                    backgroundColor:"rgba(0, 0, 0, 1)",
                    volumeSliderGradient:"none",
                    slowBackward:false,
                    timeBorderRadius:20,
                    progressGradient:"none",
                    time:true,
                    height:23,
                    volumeColor:"rgba(51, 204, 255, 1)",
                    tooltips:{
                        marginBottom:5,
                        volume:true,
                        scrubber:true,
                        buttons:false
                    },
                    fastBackward:false,
                    opacity:1,
                    timeFontSize:11,
                    border:"0px",
                    volumeSliderColor:"#ffffff",
                    bufferColor:"#a3a3a3",
                    buttonColor:"#ffffff",
                    mute:true,
                    autoHide:{
                        enabled:true,
                        hideDelay:500,
                        mouseOutDelay:500,
                        hideStyle:"fade",
                        hideDuration:400,
                        fullscreenOnly:true
                    },
                    backgroundGradient:[0.5,0.4,0.3,0.2,0,0,0,0],
                    width:"100pct",
                    display:"block",
                    sliderBorder:"1px solid rgba(128, 128, 128, 0.7)",
                    buttonOverColor:"#ffffff",
                    fullscreen:true,
                    timeBgColor:"rgb(0, 0, 0, 0)",
                    scrubberBarHeightRatio:0.2,
                    bottom:0,
                    stop:false,
                    zIndex:2,
                    sliderColor:"#000000",
                    scrubberHeightRatio:0.6,
                    tooltipTextColor:"#ffffff",
                    spacing:{
                        time:6,
                        volume:8,
                        all:2
                    },
                    sliderGradient:"none",
                    timeBgHeightRatio:0.8,
                    volumeSliderHeightRatio:0.6,
                    name:"controls",
                    timeSeparator:" ",
                    volumeBarHeightRatio:0.2,
                    left:"50pct",
                    tooltipColor:"rgba(0, 0, 0, 0)",
                    playlist:false,
                    durationColor:"rgba(51, 204, 255, 1)",
                    play:true,
                    fastForward:true,
                    progressColor:"rgba(51, 204, 255, 1)",
                    timeBorder:"0px solid rgba(0, 0, 0, 0.3)",
                    volume:true,
                    scrubber:true,
                    builtIn:false,
                    volumeBorder:"1px solid rgba(128, 128, 128, 0.7)",
                    margins:[2,6,2,12]
                }

            }   // END Plugins

        }

    ); // END FlowPlayer call


    $('#add-favorites').click(function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        var data = {action:'favorite'};
        executeAction(url,data,'favorite');
        return false;
    });



    $('#report-abuse').click(function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        var data = {action:'flag', flag:'video'};
        executeAction(url,data,'flag');
        return false;
    });



    $('.flag-comment').live('click',function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        var data = {action:'flag-comment', comment:$(this).attr('id').substr(8)};
        executeAction(url,data,'flag-comment');
        return false;
    });



    $('.subscribe').click(function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        var data = {action:this.id};
        executeAction(url,data,this.id);
        return false;
    });



    $('.rating').click(function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        var data = {action:'rate', rating:this.title};
        executeAction(url,data,'rate');
        return false;
    });



    $('#comments-form form').submit(function(){
        var url = video.host+'/play-actions/'+video.id+'/';
        executeAction(url,$('#comments-form form').serialize(),'comment');
        return false;
    });



    function executeAction (url, data, action) {

        $.post(url,data,function(returnData) {

            console.log(returnData);

            // Display message to user
            displayMessage (returnData.result, returnData.msg);

            // Perform action specific changes
            switch (action) {

                case 'rate':
                    if (returnData.result == 1) $('#rating-text').html(returnData.other);
                    break;

                case 'comment':
                    if (returnData.result == 1) {
                        $('#comments').prepend (returnData.other);
                        $('#comments-form form')[0].reset();
                    }
                    break;

                case 'subscribe':
                    if (returnData.result == 1) {
                        $('.subscribe').attr('id','unsubscribe');
                        $('.subscribe').attr('title','Unsubscribe');
                    }
                    break;

                case 'unsubscribe':
                    if (returnData.result == 1) {
                        $('.subscribe').attr('id','subscribe');
                        $('.subscribe').attr('title','Subscribe');
                    }
                    break;

            }

        }, 'json');

    }



    function displayMessage (result, message) {

        id = (result == 1) ? 'success' : 'error';

        // Add new message block
        if ($('#success').length == 0 && $('#error').length == 0) {
            block = '<div id="' + id + '">' + message + '</div>';
            $('#comments-form').before(block);
        } else {
            // Update existing message block
            var existing = ($('#success').length != 0) ? '#success' : '#error';
            $(existing).html(message);
            $(existing).attr('id', id);
        }
    }

});