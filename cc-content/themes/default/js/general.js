// Global vars
var baseUrl = $('meta[name="baseUrl"]').attr('content');
var videoId = $('meta[name="videoId"]').attr('content');

$(document).ready(function(){

    // Default text Focus
    $('.defaultText').focus(function(){
        if ($(this).val() == $(this).attr('title') && !$(this).hasClass('customText')) {
            $(this).addClass('customText');
            $(this).val('');
        }
    });

    // Default text Blur
    $('.defaultText').blur(function(){
        if ($(this).val() == '') {
            $(this).removeClass('customText');
            $(this).val($(this).attr('title'));
        }
    });


    // Tabs Show/Hide Block
    $('.tabs a').click(function(){
        
        // Skip for non show/hide tabs
        if ($(this).data('block') == 'undefined') return false;
        
        // Hide all blocks except for targeted block
        var block = '#' + $(this).data('block');
        $('.tab_block').not(block).hide();
        
        // Toggle targeted block
        if ($(this).parent().hasClass('keepOne')) {
            $(block).show();
        } else {
            $(block).toggle({
                complete:function(){$(block).trigger('tabToggled');},
                duration:0
            });
        }
        return false;
    });  


    // Show/Hide Block
    $('.showhide').click(function(){

        // Retrieve and toggle targeted block
        var block = $(this).data('block');
        $('#'+block).toggle();

        // Prevent link click through
        if ($(this).is('a')) return false;
    });


    // Attach confirm popup to confirm action links
    $('.confirm').click(function() {
        
        // Code to execute once string is retrieved
        var location = $(this).attr('href')
        var callback = function(confirmString){
            var agree = confirm(confirmString);
            if (agree) window.location = location;
        }

        // Retrieve confirm string
        getText(callback, $(this).data('node'), $(this).data('replacements'));
        return false;
    });


    // Add video to playlist on play page
    $('#addToPlaylist li a').click(function(){
        var link = $(this);
        var url = baseUrl+'/actions/playlist/';
        var data = {
            action: 'add',
            video_id: videoId,
            playlist_id: $(this).data('playlist_id'),
            action: 'add'
        };
        var callback = function(addToPlaylistResponse){
            var newNameAndCount = link.text().replace(/\([0-9]+\)/, '(' + addToPlaylistResponse.other.count + ')');
            link.text(newNameAndCount);
            link.addClass('added');
        };
        executeAction(url, data, callback);
        return false;
    });
    
    // Create new playlist on play page
    $('#addToPlaylist form').submit(function(event){
        var createPlaylistForm = $(this);
        var data = $(this).serialize();
        var url = baseUrl+'/actions/playlist/';
        var callback = function(createPlaylistResponse){
            $('#addToPlaylist ul').append('<li><a data-playlist_id="' + createPlaylistResponse.other.playlistId + '" class="added" href="">' + createPlaylistResponse.other.name + ' (' + createPlaylistResponse.other.count + ')</a></li>');
            createPlaylistForm.find('input[type="text"]').val('');
            createPlaylistForm.find('select').val('public');
        };
        executeAction(url, data, callback);
        event.preventDefault();
    });


    // Attach flag action to flag links / buttons
    $('.flag').click(function(){
        var url = baseUrl+'/actions/flag/';
        var data = {type: $(this).data('type'), id: $(this).data('id')};
        executeAction(url, data);
        window.scrollTo(0, 0);
        return false;
    });


    // Attach Subscribe & Unsubscribe action to buttons
    $('.subscribe').click(function(){
        var subscribeType = $(this).data('type');
        var url = baseUrl+'/actions/subscribe/';
        var data = {type: subscribeType, user: $(this).data('user')};
        var subscribeButton = $(this);

        // Callback for AJAX call - Update button if the action (subscribe / unsubscribe) was successful
        var callback = function(responseData) {
            if (responseData.result == 1) {
                subscribeButton.text(responseData.other);
                if (subscribeType == 'subscribe') {
                    subscribeButton.data('type','unsubscribe');
                } else if (subscribeType == 'unsubscribe') {
                    subscribeButton.data('type','subscribe');
                }
            }
            window.scrollTo(0, 0);
        }

        executeAction(url, data, callback);
        return false;
    });


    // Attach rating action to like & dislike links
    $('.rating').click(function(){
        var url = baseUrl+'/actions/rate/';
        var data = {video_id: videoId, rating: $(this).data('rating')};
        var callback = function(responseData) {
            if (responseData.result == 1) {
                $('.actions .left .like').text(responseData.other.likes);
                $('.actions .left .dislike').text(responseData.other.dislikes);
            }
            window.scrollTo(0, 0);
        }
        executeAction(url, data, callback);
        return false;
    });


    // Attach comment action to comment forms
    $('#comments form').submit(function(){
        var url = baseUrl+'/actions/comment/';
        var callback = function(responseData) {
            $('#comments form').find('input[type="text"], textarea').val('');
            if (responseData.other.auto_approve == 1) {
                $('#comments .comments_list').prepend(responseData.other.output);
            }
            window.scrollTo(0, 0);
        }
        executeAction(url, $(this).serialize(), callback);
        return false;
    });


    // Expand collapsed comment form was activated
    $('#comments form textarea').focus(function(){
        $(this).val('').parents('.form').removeClass('collapsed');
    });
    
    
//    $('.play_comments_form textarea, .play_comments_form input').blur(function(){
//        var formContainer = $('.play_comments_form');
//        var textArea = formContainer.find('textarea');
//        if (
//            formContainer.find('input[type="text"]').val() == ''
//            && textArea.val() == ''
//        ) {
//            formContainer.addClass('collapsed');
//            textArea.val(textArea.prev().text());
//        }
//    });


    // Regenerate Private URL
    $('#private_url a').click(function(){
        $.ajax({
            type    : 'get',
            url     : baseUrl + '/private/get/',
            success : function(responseData, textStatus, jqXHR) {
                $('#private_url span').text(responseData);
                $('#private_url input').val(responseData);
            }
        });
        return false;
    });
    
    
    // Scrollbar for Play page 'Add Video To' widget
    if ($('.play').length > 0) {
        $('#addToPlaylist > div:first-child > div').jScrollPane();
        $('#addToPlaylist').on('tabToggled',function(){
            if ($(this).css('display') == 'block') {
                api = $('#addToPlaylist > div:first-child > div').data('jsp');
                api.reinitialise();
            }
        });
        
        // Attach scrollbar to playlist widget if viewing a playlist
        if ($('#playlistVideos .videos_list').length > 0) {
            $('#playlistVideos .videos_list').jScrollPane();
            var playlistScrollApi = $('#playlistVideos .videos_list').data('jsp');
            var activePlaylistVideo = $('#playlistVideos .videos_list .active');
            playlistScrollApi.scrollTo(0, activePlaylistVideo.index()*76);
        }
        
        // Make entire playlist video tile clickable
        $('#playlistVideos .video_small').click(function(){
            location = $(this).find('div > a').attr('href');
        });
    }

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
function getText(callback, node, replacements)
{
    $.ajax({
        type        : 'POST',
        url         : baseUrl+'/language/get/',
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
function executeAction(url, data, callback)
{
    $.ajax({
        type        : 'POST',
        data        : data,
        dataType    : 'json',
        url         : url,
        success     : function(responseData, textStatus, jqXHR){
            displayMessage(responseData.result, responseData.msg);
            if (typeof callback != 'undefined') callback(responseData, textStatus, jqXHR);
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
function displayMessage(result, message)
{
    var cssClass = (result == 1) ? 'success' : 'errors';
    var existingClass = ($('.message').hasClass('success')) ? 'success' : 'errors';
    $('.message').show();
    $('.message').html(message);
    $('.message').removeClass(existingClass);
    $('.message').addClass(cssClass);
}