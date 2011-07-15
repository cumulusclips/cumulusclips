$('document').ready(function(){

    // Initialize fancybox lightbox
    $('.fancybox').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250
    });

    $('.iframe').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250,
        'type'          : 'iframe',
        'width'         : '95%',
        'height'        : '95%',
        'autoScale'     : false
    });

});