$(document).ready(function(){
    $('#slideshow').cycle({
        fx:'fade',
        pager:'#slideshow_nav',
        timeout:6000,
        before:function(currSlideElement, nextSlideElement, options, forwardFlag){
            setTimeout ( function(){$(nextSlideElement).find('.slide-text').animate({height:'show'})} , 1500 );
        },
        after:function(currSlideElement, nextSlideElement, options, forwardFlag){
            $(currSlideElement).find('.slide-text').hide();
        }
    });
});