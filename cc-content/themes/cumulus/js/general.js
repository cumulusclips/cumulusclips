$(document).ready(function(){

    var baseURL = $('[name=baseURL]').attr('content');

    // Default text Focus
    $('.defaultText').focus(function(){

        // Swap hidden password fields if applicable
        if ($(this).hasClass('defaultTextPassword')) {
            var password = $(this).next();
            $(this).hide();
            password.show().focus();
        }

        // Clear default text
        if ($(this).val() == $(this).attr('title') && !$(this).hasClass('customText')) {
            $(this).addClass('customText');
            $(this).val('');
        }

    });

    // Default text Blur
    $('.defaultText').blur(function(){

        // Add default text
        if ($(this).val() == '') {
            $(this).removeClass('customText');
            $(this).val($(this).attr('title'));

            // Swap password field / Make hidden
            if ($(this).attr('type') == 'password') {
                var password = $(this).prev();
                $(this).hide();
                password.show().blur();
            }
        }
        
    });




    // Show/Hide Block
    $('.showhide').click(function(){
        var block = $(this).attr('data-block');
        if ($('#'+block).css('display') == 'none') {
            $('#'+block).show();
        } else {
            $('#'+block).hide();
        }
        return false;
    });




    // Attach submit action to buttons
    $('.button').click(function(){
        var parentForm = $(this).closest('form');
        if (parentForm.length) {
            parentForm.submit();
            return false;
        }
    });

    // Allow submission of forms with Return key
    $('form input').keydown(function(event){
        var code = event.keyCode ? event.keyCode : event.which;
        var parentForm = $(this).closest('form');
        if (code == 13 && parentForm.length) {
            parentForm.submit();
            return false;
        }
    });




    // Attach confirm popup to confirm action links
    $('.confirm').click(function() {
        
        // Code to execute once string is retrieved
        var location = $(this).attr('href')
        var callback = function (confirmString){
            var agree = confirm (confirmString);
            if (agree) window.location = location;
        }

        // Retrieve confirm string
        GetText (callback, $(this).data('node'), $(this).data('replacements'));
        return false;
    });

























    $('.add-favorites').click(function(){
        var url = baseURL+'/actions/favorite/';
        var data = {video_id: $(this).attr('data-video')};
        executeAction (url, data, 'favorite');
        return false;

        // Callback
            // Display Message
    });



    $('.flag').click(function(){
        var url = baseURL+'/actions/flag/';
        var data = {type: $(this).attr('data-flag-type'), id: $(this).attr('data-id')};
        executeAction (url, data, 'flag');
        return false;

        // Callback
            // Display Message
    });



    $('.subscribe').click(function(){
        var url = baseURL+'/actions/subscribe/';
        var data = {action: $(this).attr('data-action'), member: $(this).attr('data-member')};
        executeAction (url, data, this.id);
        return false;

        // Callback
            // Display Message
            // Update Button Text and Attributes
    });



    $('.rating').click(function(){
        var url = baseURL+'/actions/rate/';
        var data = {video_id: $(this).attr('data-video'), rating: $(this).attr('data-rating')};
        executeAction (url, data, 'rate');
        return false;

        // Callback
            // Display Message
            // Update Rating Count/Text

    });



    $('.comments-form').submit(function(){
        var url = baseURL+'/actions/comment/';
        executeAction (url, $(this).serialize(), 'comment');
        return false;

        // Callback
            // Display Message
            // Append comment
            // Clear form
    });



    $('.status-form').submit(function(){
        var url = baseURL+'/actions/post/';
        executeAction (url, $(this).serialize(), 'comment');
        return false;

        // Callback
            // Display Message
            // Append Post
            // Clear form
    });



    function executeAction (url, data, callback) {

        $.ajax({
            type    : 'POST',
            data    : data,
            url     : url,
            success : callback
        });

    }



    function displayMessage (result, message) {
        var cssClass = (result == 1) ? 'success' : 'error';
        var existing = ($('.success').length != 0) ? '.success' : '.error';
        $(existing).removeClass(existing);
        $(existing).addClass(cssClass);
    }




















}); // END jQuery





/****************
GENERAL FUNCTIONS
****************/

// Retrieve localised string via AJAX
function GetText(callback, node, replacements) {
    $.ajax({
        url         : '/language/get/',
        data        : {node:node, replacements:replacements},
        success     : callback
    });
}