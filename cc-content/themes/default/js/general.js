// Global vars
var cumulusClips = {};
cumulusClips.baseUrl = $('meta[name="baseUrl"]').attr('content');
cumulusClips.themeUrl = $('meta[name="theme"]').attr('content');
cumulusClips.videoId = $('meta[name="videoId"]').attr('content');


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


    // Attach flag action to flag links / buttons
    $('.flag').click(function(){
        var url = cumulusClips.baseUrl+'/actions/flag/';
        var data = {type: $(this).data('type'), id: $(this).data('id')};
        executeAction(url, data);
        window.scrollTo(0, 0);
        return false;
    });


    // Attach Subscribe & Unsubscribe action to buttons
    $('.subscribe').click(function(){
        var subscribeType = $(this).data('type');
        var url = cumulusClips.baseUrl+'/actions/subscribe/';
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
        var url = cumulusClips.baseUrl+'/actions/rate/';
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


    // Play Video Page
    if ($('.play').length > 0) {
        getText(function(responseData, textStatus, jqXHR){cumulusClips.replyToText = responseData;}, 'reply_to');
        getText(function(responseData, textStatus, jqXHR){cumulusClips.replyText = responseData;}, 'reply');
        getText(function(responseData, textStatus, jqXHR){cumulusClips.reportAbuseText = responseData;}, 'report_abuse');
        $.get(cumulusClips.themeUrl + '/blocks/comment.html', function(responseData, textStatus, jqXHR){cumulusClips.commentCardTemplate = responseData;});
        cumulusClips.lastCommentId = $('.commentList > div:last-child').data('comment');
        cumulusClips.commentCount = Number($('#comments .totals span').text());
        cumulusClips.loadMoreComments = (cumulusClips.commentCount > 5) ? true : false;


        // Scrollbar for 'Add Video To' widget
        var scrollableList = $('#addToPlaylist > div:first-child > div');
        scrollableList.jScrollPane();
        $('#addToPlaylist').on('tabToggled',function(){
            if ($(this).css('display') == 'block' && scrollableList.length > 0) {
                api = scrollableList.data('jsp');
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


        // Add video to playlist on play page
        $('#addToPlaylist li a').click(function(){
            var link = $(this);
            var url = cumulusClips.baseUrl+'/actions/playlist/';
            var data = {
                action: 'add',
                video_id: videoId,
                playlist_id: $(this).data('playlist_id')
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
            var url = cumulusClips.baseUrl+'/actions/playlist/';
            var callback = function(createPlaylistResponse){
                $('#addToPlaylist ul').append('<li><a data-playlist_id="' + createPlaylistResponse.other.playlistId + '" class="added" href="">' + createPlaylistResponse.other.name + ' (' + createPlaylistResponse.other.count + ')</a></li>');
                createPlaylistForm.find('input[type="text"]').val('');
                createPlaylistForm.find('select').val('public');
            };
            executeAction(url, data, callback);
            event.preventDefault();
        });


        // Attach comment action to comment forms
        $('#comments').on('submit', 'form', function(){
            var url = cumulusClips.baseUrl+'/actions/comment/add/';
            var commentForm = $(this).parent();
            var callback = function(responseData) {
                if (responseData.result === true) {
                    // Reset comment form
                    if (commentForm.hasClass('commentReplyForm')) {
                        commentForm.remove();
                    } else {
                        resetCommentForm(commentForm);
                    }

                    // Append new comment if auto-approve comments is on
                    if (responseData.other.autoApprove === true) {
                        var commentCardElement = buildCommentCard(cumulusClips.commentCardTemplate, responseData.other.commentCard);
                        var commentCard = responseData.other.commentCard;
                        
                        // Append comment to list
                        if (commentCard.comment.parentId !== 0) {
                            var parentComment = $('[data-comment="' + commentCard.comment.parentId + '"]');
                            // Determine indent class
                            var indentClass;
                            if (!parentComment.hasClass('commentIndentDouble') && !parentComment.hasClass('commentIndent')) {
                                indentClass = 'commentIndent';
                            } else {
                                indentClass = 'commentIndentDouble';
                            }
                            commentCardElement.addClass(indentClass);
                            parentComment.after(commentCardElement)
                        } else {
                            $('.commentList').append(commentCardElement);
                        }
                    }
                }
                window.scrollTo(0, 0);
            }
            executeAction(url, $(this).serialize(), callback);
            return false;
        });


        // Expand collapsed comment form was activated
        $('#comments .commentForm').focusin(function(){
            if ($(this).hasClass('collapsed')) {
                $('.commentReplyForm').remove();
                $(this).removeClass('collapsed');
                $(this).find('textarea').focus().val('');
            }
        });


        // Handle user cancelling comment form
        $('#comments').on('click', '.commentForm .cancel',  function(event){
            // Remove if reply form, collapse otherwise
            var commentForm = $(this).parents('.commentForm');
            if (commentForm.hasClass('commentReplyForm')) {
                commentForm.remove();
            } else {
                resetCommentForm(commentForm);
            }
            event.preventDefault();
        });


        // Handle reply to comment action
        $('#comments').on('click', '.commentAction .reply', function(event){
            var commentForm = $('#comments > .commentForm');
            resetCommentForm(commentForm);
            $('.commentReplyForm').remove();
            var parentComment = $(this).parents('.comment');
            var replyForm = commentForm.clone();
            replyForm.addClass('commentReplyForm');
            parentComment.after(replyForm);
            replyForm.removeClass('collapsed');
            replyForm.find('input[name="parentCommentId"]').val(parentComment.data('comment'));
            replyForm.find('textarea').focus().val('');
            event.preventDefault();
        });


        // Load more comments
        $('.loadMoreComments a').on('click', function(event){
            // Verify that more comments are available
            if (cumulusClips.loadMoreComments) {
                var data = {videoId:cumulusClips.videoId, lastCommentId:cumulusClips.lastCommentId, limit: 5};
                var loadingText = $(this).data('loading_text');
                var loadMoreText = $(this).text();
                $(this).text(loadingText);
                // Retrieve subsequent comments
                $.ajax({
                    type        : 'get',
                    data        : data,
                    dataType    : 'json',
                    url         : cumulusClips.baseUrl + '/actions/comments/get/',
                    success     : function(responseData, textStatus, jqXHR){
                        var lastCommentKey = responseData.other.commentCardList.length-1;
                        cumulusClips.lastCommentId = responseData.other.commentCardList[lastCommentKey].comment.commentId;
                        // Loop through comment data set, inject into comment template and append to list
                        $.each(responseData.other.commentCardList, function(key, commentCard){
                            $('.commentList').find('div[data-comment="' + commentCard.comment.commentId + '"]').remove();
                            var commentCardElement = buildCommentCard(cumulusClips.commentCardTemplate, commentCard);
                            
                            // Append comment to list
                            if (commentCard.comment.parentId !== 0) {
                                var parentComment = $('[data-comment="' + commentCard.comment.parentId + '"]');
                                // Determine indent class
                                var indentClass;
                                if (!parentComment.hasClass('commentIndentDouble') && !parentComment.hasClass('commentIndent')) {
                                    indentClass = 'commentIndent';
                                } else {
                                    indentClass = 'commentIndentDouble';
                                }
                                commentCardElement.addClass(indentClass);
                                parentComment.after(commentCardElement)
                            } else {
                                $('.commentList').append(commentCardElement);
                            }
                        });

                        // Hide load more button if no more comments are available
                        if ($('.commentList .comment').length < cumulusClips.commentCount) {
                            cumulusClips.loadMoreComments = true;
                            $('.loadMoreComments a').text(loadMoreText);
                        } else {
                            cumulusClips.loadMoreComments = false;
                            $('.loadMoreComments').remove();
                        }
                    }
                });
            }
            event.preventDefault();
        });
    }   // END Play Video page


    // Regenerate Private URL
    $('#private_url a').click(function(){
        $.ajax({
            type    : 'get',
            url     : cumulusClips.baseUrl + '/private/get/',
            success : function(responseData, textStatus, jqXHR) {
                $('#private_url span').text(responseData);
                $('#private_url input').val(responseData);
            }
        });
        return false;
    });


    // Add to Watch Later actions
    $('.video .watchLater a').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        
        var video = $(this).parents('.video');
        var url = cumulusClips.baseUrl+'/actions/playlist/';
        var data = {
            action: 'add',
            shortText: true,
            video_id: $(this).data('video'),
            playlist_id: $(this).data('playlist')
        };
        
        // Make call to API to attempt to add video to playlist
        $.ajax({
            type: 'POST',
            data: data,
            dataType: 'json',
            url: url,
            success: function(responseData, textStatus, jqXHR){
                // Append message to video thumbnail
                var resultMessage = $('<div></div>')
                    .addClass('message')
                    .html('<p>' + responseData.message + '</p>');
                video.find('.thumbnail').append(resultMessage);
                
                // Style message according to add results
                if (responseData.result === 1) {
                    resultMessage.addClass('success');
                } else {
                    if (responseData.other.status === 'DUPLICATE') resultMessage.addClass('errors');
                }

                // FadeIn message, pause, then fadeOut and remove
                resultMessage.fadeIn(function(){
                    setTimeout(function(){
                        resultMessage.fadeOut(function(){resultMessage.remove();});
                    }, 2000);
                });
            }
        });
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
function getText(callback, node, replacements)
{
    $.ajax({
        type        : 'POST',
        url         : cumulusClips.baseUrl+'/language/get/',
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
            displayMessage(responseData.result, responseData.message);
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
    var cssClass = (result === true) ? 'success' : 'errors';
    var existingClass = ($('.message').hasClass('success')) ? 'success' : 'errors';
    $('.message').show();
    $('.message').html(message);
    $('.message').removeClass(existingClass);
    $('.message').addClass(cssClass);
}

/**
 * Generates comment card HTML to be appended to comment list on play page
 * @param string commentCardTemplate The HTML template of the comment card
 * @param object commentCardData The CommentCard object for the comment being appended
 * @return object the jQuery object for the newly filled comment card element
 */
function buildCommentCard(commentCardTemplate, commentCardData)
{
    var commentCard = $(commentCardTemplate);
    commentCard.attr('data-comment', commentCardData.comment.commentId);
    
    // Set comment avatar
    if (commentCardData.avatar !== null) {
        commentCard.find('img').attr('src', commentCardData.avatar);
    } else {
        commentCard.find('img').attr('src', cumulusClips.themeUrl + '/images/avatar.gif');
    }
    
    // Set comment author
    commentCard.find('.commentAuthor a')
        .attr('href', cumulusClips.baseUrl + '/members/' + commentCardData.author.username)
        .text(commentCardData.author.username);
    
    // Set comment date
    var commentDate = new Date(commentCardData.comment.dateCreated);
    monthPadding = (String(commentDate.getMonth()+1).length === 1) ? '0' : '';
    datePadding = (String(commentDate.getDate()).length === 1) ? '0' : '';
    commentCard.find('.commentDate').text(monthPadding + (commentDate.getMonth()+1) + '/' + datePadding + commentDate.getDate() + '/' + commentDate.getFullYear());
    
    // Set comment action links
    commentCard.find('.commentAction .reply').text(cumulusClips.replyText);
    commentCard.find('.flag')
        .text(cumulusClips.reportAbuseText)
        .attr('data-id', commentCardData.comment.commentId);
        
    // Set reply to text if apl.
    if (commentCardData.comment.parentId !== 0) {
        commentCard.find('.commentReply').text(cumulusClips.replyToText + ' ');
        // Determine parent comment author's text
        var parentCommentAuthorText;
        parentCommentAuthorText = $('<a>')
            .attr('href', cumulusClips.baseUrl + '/members/' + commentCardData.parentAuthor.username)
            .text(commentCardData.parentAuthor.username);
        commentCard.find('.commentReply').append(parentCommentAuthorText);
    } else {
        commentCard.find('.commentReply').remove();
    }
 
    // Set comment text
    commentCardData.comment.comments = commentCardData.comment.comments.replace(/</g, '&lt;');
    commentCardData.comment.comments = commentCardData.comment.comments.replace(/>/g, '&gt;');
    commentCard.find('> div p:last-child').html(commentCardData.comment.comments.replace(/\r\n|\n|\r/g, '<br>'));
    
    return commentCard;
}

/**
 * Resets the main comment form to it's default state
 * @param object commentForm jQuery object for the comment form
 * @return void Form is reset
 */
function resetCommentForm(commentForm)
{
    commentForm.addClass('collapsed');
    var commentField = commentForm.find('textarea');
    commentField.val(commentField.attr('title'));
}
