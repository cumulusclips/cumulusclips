$('document').ready(function(){

    $('#slideshow').cycle({
        fx:'fade',
        pager:'#slide-count',
        timeout:6000,
        prev:'#slideshow-container .previous',
        next:'#slideshow-container .next',
        before:function(currSlideElement, nextSlideElement, options, forwardFlag){
            setTimeout ( function(){$(nextSlideElement).find('.slide-text').animate({height:'show'})} , 1500 );
        },
        after:function(currSlideElement, nextSlideElement, options, forwardFlag){
            $(currSlideElement).find('.slide-text').hide();
        }
    });

   


});