// Global vars
var baseURL = $('meta[name="baseURL"]').attr('content');
var videoID = $('[meta[name="videoID"]').attr('content');

$(document).ready(function(){

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

        // Retrieve and toggle targeted block
        var block = $(this).data('block');
        $('#'+block).toggle();

        // Hide other blocks on same level as toggled block
        $('.showhide-block:not(#'+block+')').hide();

        // Prevent link click through
        if ($(this).is('a')) return false;
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




    // Attach favorite action to favorite links / buttons
    $('.favorite').click(function(){
        var url = baseURL+'/actions/favorite/';
        var data = {video_id: videoID};
        executeAction (url, data);
        return false;
    });




    // Attach flag action to flag links / buttons
    $('.flag').click(function(){
        var url = baseURL+'/actions/flag/';
        var data = {type: $(this).attr('data-type'), id: $(this).attr('data-id')};
        executeAction (url, data);
        return false;
    });




    // Attach Subscribe & Unsubscribe action to buttons
    $('.subscribe').click(function(){

        var subscribeType = $(this).attr('data-type');
        var url = baseURL+'/actions/subscribe/';
        var data = {type: subscribeType, user: $(this).attr('data-user')};
        var subscribeButton = $(this);


        // Callback for AJAX call - Update button if the action (subscribe / unsubscribe) was successful
        var callback = function (responseData) {
            
            if (responseData.result == 1) {

                subscribeButton.text(responseData.other);
                if (subscribeType == 'subscribe') {
                    subscribeButton.attr('data-type','unsubscribe');
                } else if (subscribeType == 'unsubscribe') {
                    subscribeButton.attr('data-type','subscribe');
                }

            }
        }

        executeAction (url, data, callback);
        return false;
        
    });




    // Attach rating action to like & dislike links
    $('.rating').click(function(){
        var url = baseURL+'/actions/rate/';
        var data = {video_id: videoID, rating: $(this).attr('data-rating')};
        var callback = function (responseData) {
            if (responseData.result == 1) {

                var likeText = responseData.other.like_text;
                likeText += ' (' + responseData.other.likes + '+)';
                $('.like-text').text(likeText);

                var dislikeText = responseData.other.dislike_text;
                dislikeText += ' (' + responseData.other.dislikes + '-)';
                $('.dislike-text').text(dislikeText);
                
            }
        }
        executeAction (url, data, callback);
        return false;
    });




    // Attach comment action to comment forms
    $('#comments-form').submit(function(){
        var url = baseURL+'/actions/comment/';
        var callback = function (responseData) {
            $('#comments-form')[0].reset();
            if (responseData.other.auto_approve == 1) {
                $('#comments').prepend(responseData.other.output);
            }
        }
        executeAction (url, $(this).serialize(), callback);
        return false;
    });




    // Attach post status update action to status update forms
    $('#status-form').submit(function(){
        var url = baseURL+'/actions/post/';
        var callback = function(responseData) {
            $('#status-posts').prepend(responseData.other);
            $('#no-updates').remove();
            $('#status-form')[0].reset();
            $('#status-form .text').css('height', '20')
        }
        executeAction (url, $(this).serialize(), callback);
        return false;
    });

    // Make status update field expand on initial focus
    $('#status-form .text').focus(function(){
        $(this).css('height', '80');
    });
    $('#status-form .text').blur(function(){
        if ($(this).val() == '') $(this).css('height', '20');
    });




    // Initialize VideoJS on play page
    if ($('.video-js-box').length > 0) {
        VideoJS.setupAllWhenReady();
    }




    // Regenerate Private URL
    $('#private-url a').click(function(){
        $.ajax({
            type    : 'get',
            url     : baseURL + '/private/get/',
            success : function (responseData, textStatus, jqXHR) {
                $('#private-url span').text(responseData);
                $('#private-url input').val(responseData);
            }
        });
        return false;
    });

}); // END jQuery





/****************
GENERAL FUNCTIONS
****************/

/**
 * Retrieve localised string via AJAX
 * @param function callback Code to be executed once AJAX call to retrieve text is complete
 * @param string node Name of term node in language file to retrieve
 * @param json replacements (Optional) List of key/value replacements in JSON format
 * @return void Requested string, with any replacements made, is passed to callback
 * for any futher behaviour
 */
function GetText(callback, node, replacements) {
    $.ajax({
        type        : 'POST',
        url         : baseURL+'/language/get/',
        data        : {node:node, replacements:replacements},
        success     : callback
    });
}




/**
 * Send AJAX request to the action's server handler script
 * @param string url Location of the action's server handler script
 * @param json || string data The data to be passed to the server handler script
 * @param function callback (Optional) Code to be executed once AJAX call to handler script is complete
 * @return void Message is display according to server response. Any other
 * follow up behaviour is performed within the callback
 */
function executeAction (url, data, callback) {
    $.ajax({
        type        : 'POST',
        data        : data,
        dataType    : 'json',
        url         : url,
        success     : function(responseData, textStatus, jqXHR){
            displayMessage (responseData.result, responseData.msg);
            if (typeof callback != 'undefined') callback (responseData, textStatus, jqXHR);
        }
    });
}




/**
 * Display message sent from the server handler script for page actions
 * @param boolean result The result of the requested action (1 = Success, 0 = Error)
 * @param string message The textual message for the result of the requested action
 * @return void Message block is displayed and styled accordingly with message.
 * If message block is already visible, then it is updated.
 */
function displayMessage (result, message) {
    var cssClass = (result == 1) ? 'success' : 'error';
    var existingClass = ($('#message').hasClass('success')) ? 'success' : 'error';
    $('#message').show();
    $('#message').html(message);
    $('#message').removeClass(existingClass);
    $('#message').addClass(cssClass);
}