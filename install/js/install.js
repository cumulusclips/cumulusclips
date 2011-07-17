$('document').ready(function(){

    $('.more-info').tipsy({
        fade    : true,
        gravity : 's',
        html    : true,
        title   : function(){
            if ($(this).attr('data-content') != 'undefined') {
                return $('#'+$(this).data('content')).html();
            } else {
                return $(this).attr('title');
            }
        }
    });

});




