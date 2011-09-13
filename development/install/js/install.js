$('document').ready(function(){

    // Append tipsy tooltip on more info links
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




    // Add Show/Hide password link to password fields
    $('.mask').after(function(){
        var name = $(this).attr('name');
        var field = '<input style="display:none;" type="text" class="text" name="'+name+'-show" />';
        var link = '<a href="" class="mask-link" data-for="'+name+'" tabindex="-1">Show Password</a>';
        return field+link;
    });




    // Toggle password visibility when mask link is clicked
    $('.mask-link').live('click',function(){

        var name = $(this).data('for');

        // Determine whether to show or hide the password
        if ($('input[name="'+name+'"]').is(':hidden')) {

            // Password is currently visible - Hide it and update link text
            $('input[name="'+name+'"]').val( $('input[name="'+name+'-show"]').val() );
            $('input[name="'+name+'"]').show();
            $('input[name="'+name+'-show"]').hide();
            $('a[data-for="'+name+'"]').text('Show Password');

        } else {

            // Password is currently masked - Display it and update link text
            $('input[name="'+name+'-show"]').val( $('input[name="'+name+'"]').val() );
            $('input[name="'+name+'"]').hide();
            $('input[name="'+name+'-show"]').show();
            $('a[data-for="'+name+'"]').text('Hide Password');

        }

        return false;

    });

});