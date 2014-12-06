$(function(){

    // Initialize fancybox lightbox for theme and language preview
    $('.iframe').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250,
        'type'          : 'iframe',
        'width'         : '95%',
        'height'        : '95%',
        'autoScale'     : false
    });

    // Initialize fancybox lightbox for mobile theme preview
    $('.iframe-mobile').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250,
        'type'          : 'iframe',
        'width'         : '30%',
        'height'        : '80%',
        'autoScale'     : false
    });
    
    // Watch video directly from list
    $('.watch').click(function(){
        
        var player = $('video').clone().css('display','block');
        var h264Url = $('meta[name="h264Url"]').attr('content') + '/' + $(this).data('filename') + '.mp4';
        var theoraUrl = $('meta[name="theoraUrl"]').attr('content') + '/' + $(this).data('filename') + '.ogv';
        var thumbUrl = $('meta[name="thumbUrl"]').attr('content') + '/' + $(this).data('filename') + '.jpg';
        
        // Check if vp8 is enabled
        if ($('meta[name="vp8Url"]').length) {
            var vp8Url = $('meta[name="vp8Url"]').attr('content') + '/' + $(this).data('filename') + '.webm';
            player.find('source[type="video/webm"]').attr('src', vp8Url);
        }
        
        player.find('source[type="video/mp4"]').attr('src', h264Url);
        player.find('source[type="video/ogg"]').attr('src', theoraUrl);
        player.attr('poster', thumbUrl);
        var content = player[0];
        
        // Init modal
        $.fancybox({
            'transitionIn'  : 'elastic',
            'transitionOut' : 'elastic',
            'speedIn'       : 250,
            'content'       : content
        });
        return false;
    });
});