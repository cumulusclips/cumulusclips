var baseURL = $('[name=baseURL]').attr('content');

$('document').ready(function(){

    $('.video').click(function(){
        window.location = $(this).find('a').attr('href');
    });



    $('#load-more').click(function(){

        var loadLocation = baseURL+'/actions/mobile-'+$('#loadLocation').val()+'/';

        var showing = $('#start').val();
        $('#load-more-text').hide();
        $('#loading-text').show();
        $.post(
            loadLocation,
            $('form').serialize(),
            function(data){
                $('#load-more').before(data);
                var new_showing = Number(showing)+20;
                $('#start').val(new_showing);
                $('#loading-text').hide();
                $('#load-more-text').show();
                if (new_showing >= $('#max').val()) $('#load-more').remove();
            },
            'html'
        );
    });



    $('.back').click(function(){
        history.go(-1);
        return false;
    });



    $('#search-field').blur(function(){
        if ($(this).val() == '') $(this).val($(this).attr('title'));
    });



    $('#search-field').focus(function(){
        if ($(this).val() == $(this).attr('title')) $(this).val('');
    });



    $('#search-field').blur();

});