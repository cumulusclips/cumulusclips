// Global vars
var cumulusClips = cumulusClips || {};
cumulusClips.baseUrl = $('meta[name="baseUrl"]').attr('content');
cumulusClips.mobileBaseUrl = $('meta[name="mobileBaseUrl"]').attr('content');
cumulusClips.themeUrl = $('meta[name="themeUrl"]').attr('content');
cumulusClips.thumbUrl = $('meta[name="thumbUrl"]').attr('content');
cumulusClips.loggedIn = $('meta[name="loggedIn"]').attr('content');

// Coordinate actions during page load process
$('body').pagecontainer({
    create: function(event, ui){
        cumulusClips.originalPage = $('[data-role="page"]');
        init();
    },
    hide: function(event, ui) {
        // Clear any messages on outgoing page
        ui.prevPage.find('.message').each(function(index){
            clearMessage(this);
        });
        
        // Reset play tabs when navigating away
        if (ui.prevPage.attr('id') === 'mobile_play') {
            ui.prevPage.find('.tab-blocks > div').hide();
            ui.prevPage.find('.about-container').show();
            ui.prevPage.find('[data-block="about-container"]').addClass('ui-btn-active');
            resetVideoPlayer();
        }
        
        // Reset video upload form when navigating away
        if (
            cumulusClips.originalPage.attr('id') === ui.prevPage.attr('id')
            && ui.prevPage.attr('id') === 'mobile_account_upload'
        ) {
            resetUploadForm();
            removeQueuedVideoUpload();
        }
    },
    show: function(event, ui){
        // Run controller for video upload page
        if (ui.toPage.attr('id') === 'mobile_account_upload') {
            videoUploadController();
        }
        
        // Run controller for play page
        if (ui.toPage.attr('id') === 'mobile_play') {
            playController();
        }
        
        // Run controller for play page
        if (ui.toPage.attr('id') === 'mobile_videos') {
            videosController();
        }
        
        // Run controller for play page
        if (ui.toPage.attr('id') === 'mobile_search') {
            searchController();
        }
    }
});

/**
 * Performs initialization 
 */
function init()
{
    $.mobile.defaultPageTransition = 'slide';
    
    // Attach auto complete functionality to search field
    $("#search-field input").autocomplete({
        source: cumulusClips.baseUrl + '/search/suggest/',
        appendTo: '#results',
        minLength: 3
    });
    
    $(document).on('touchstart', '.ui-autocomplete li', function(event){
        var item = this;
        setTimeout(function(){
            $(item).trigger('click');
        }, 100);
        event.preventDefault();
    });

    // Display search form when search icon is clicked
    $(document).on('touchstart, click', '.icon-search', function(event){
        $('#search-overlay').show();
        $('body').toggleClass('search-visible');
        $('#search-form input').focus();
        event.preventDefault();
    });
    
    // Hide search form when cancel link is clicked
    $(document).on('touchstart', '#search-form .cancel, #search-overlay', function(event){
        cancelSearch();
        $('#search-form input').blur();
        event.stopPropagation();
        event.preventDefault();
    });
    
    // Clear search field when clear icon is clicked
    $('#search-field .icon-clear').on('click', function(event){
        $(this).hide();
        $('#search-field input').val('').focus();
    });
    
    // Display clear search when text is typed
    $('#search-field input').on('keyup', function(event){
        if ($(this).val() !== '') {
            $('#search-field .icon-clear').css('display', 'block');
        } else {
            $('#search-field .icon-clear').hide();
        }
    });
    
    // Init global login popup
    $('#login').enhanceWithin().popup();

    // Validate and submit login form
    cumulusClips.loginFormValidator = $('#login form').validate({
        rules: {
            username: 'required',
            password: 'required'
        },
        messages: {
            username: cumulusClips.lang.error_username,
            password: cumulusClips.lang.error_password
        },
        errorPlacement: function(error, element) {
            element.parent().after(error);
        },
        invalidHandler: function(event, validator) { event.preventDefault(); },
        submitHandler: function(form){
            var url = $(form).attr('action');
            var formValues = $(form).serialize();
            $.ajax({
                url: url,
                method: 'post',
                data: formValues,
                dataType: 'json',
                success: function(data, textStatus, jqXHR){
                    if (data.result) {
                        window.location = cumulusClips.mobileBaseUrl + '/?welcome';
                    } else {
                        displayMessage(data.result, data.message, $('#login-message'));
                    }
                }
            });
            return false;
        }
    });
    
    // Reset the login form after closing it's popup
    $('#login').popup({
        afterclose: function(){
            cumulusClips.loginFormValidator.resetForm();
            $(this).find('form')[0].reset();
            clearMessage($(this).find('.message')[0]);
        }
    });
    
    // Validate category when select is changed on video upload form
    $(document).on('change', 'select', function(event){
        $(this).valid();
    });
}

/**
 * Retrieve a new private url for a video and update it's value on the form
 */
function regeneratePrivateUrl()
{
    $.ajax({
        type: 'get',
        url: cumulusClips.baseUrl + '/private/get/',
        success: function(responseData, textStatus, jqXHR) {
            $('#private_url span').text(responseData);
            $('#private_url input').val(responseData);
        }
    });
}

/**
 * Clears and resets message block
 * @param DomNode messageDomNode HTML Dom Node of message to be cleared
 */
function clearMessage(messageDomNode)
{
    $(messageDomNode).removeClass('success errors').html('').hide();
}

/**
 * Reset the upload video form and progress fields to their original default state
 */
function resetUploadForm()
{
    regeneratePrivateUrl();
    cumulusClips.uploadFormValidator.resetForm();
    $('#upload-form')[0].reset();
    $('#uploaded-file span').text('');
    $('#uploaded-file').hide();
    $('#filename').val('');
    $('#private_url').hide();
}

/**
 * Resets video progress
 */
function resetVideoPlayer()
{
    var video = $(document).find('video');
    if (video.length) {
        video[0].pause();
        video[0].currentTime = 0;
    }
}

/**
 * Cancel a search action and reset & hide the search widget
 */
function cancelSearch()
{
    setTimeout(function(){$('#search-overlay').hide()}, 800);
    $('body').toggleClass('search-visible');
    $('#search-field .icon-clear').hide();
    $('#search-field input').val('');
}

/**
 * Format number of bytes into human readable format
 * @param int bytes Total number of bytes
 * @param int precision Accuracy of final round
 * @return string Returns human readable formatted bytes
 */
function formatBytes(bytes, precision)
{
    var units = ['b', 'KB', 'MB', 'GB', 'TB'];
    bytes = Math.max(bytes, 0);
    var pwr = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
    pwr = Math.min(pwr, units.length - 1);
    bytes /= Math.pow(1024, pwr);
    return Math.round(bytes, precision) + units[pwr];
}

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
        type: 'POST',
        url: cumulusClips.baseUrl+'/language/get/',
        data: {node:node, replacements:replacements},
        success: callback
    });
}

/**
 * Display message sent from the server handler script for page actions
 * @param boolean result The result of the requested action (true = Success, false = Error)
 * @param string message The textual message for the result of the requested action
 * @param jQuery Object target  (optional) The node to target for message operations
 * @return void Message block is displayed and styled accordingly with message.
 * If message block is already visible, then it is updated.
 */
function displayMessage(result, message, target)
{
    var domNode = (target) ? target : $('.message');
    var cssClass = (result === true) ? 'success' : 'errors';
    var existingClass = (domNode.hasClass('success')) ? 'success' : 'errors';
    domNode.show();
    domNode.html(message);
    domNode.removeClass(existingClass);
    domNode.addClass(cssClass);
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
    commentCard.find('.comment-author').text(commentCardData.author.username);
    
    // Set comment date
    var commentDate = new Date(commentCardData.comment.dateCreated.split(' ')[0]);
    monthPadding = (String(commentDate.getMonth()+1).length === 1) ? '0' : '';
    datePadding = (String(commentDate.getDate()).length === 1) ? '0' : '';
    commentCard.find('.comment-date').text(monthPadding + (commentDate.getMonth()+1) + '/' + datePadding + commentDate.getDate() + '/' + commentDate.getFullYear());
    
    // Set comment reply link
    if (cumulusClips.loggedIn === '1') {
        commentCard.find('.comment-reply')
            .attr('href', '#post-comment-' + commentCardData.comment.videoId)
            .text(cumulusClips.lang.reply)
            .attr('data-parent-comment', commentCardData.comment.commentId);
    } else {
        commentCard.find('.comment-reply').remove();
    }
        
    // Set reply to text if apl.
    if (commentCardData.comment.parentId !== 0) {
        commentCard.find('.reply').text(cumulusClips.lang.replyTo + ' ' + commentCardData.parentAuthor.username);
    } else {
        commentCard.find('.reply').remove();
    }
 
    // Set comment text
    commentCardData.comment.comments = commentCardData.comment.comments.replace(/</g, '&lt;');
    commentCardData.comment.comments = commentCardData.comment.comments.replace(/>/g, '&gt;');
    commentCard.find('.comment-text').html(commentCardData.comment.comments.replace(/\r\n|\n|\r/g, '<br>'));
    
    return commentCard;
}

/**
 * Builds a video card from the video card template
 * @param string videoCardTemplate The HTML template to build the video card
 * @param object video The video which will be represented by the card
 * @return object Returns jQuery object Representing the new video card
 */
function buildVideoCard(videoCardTemplate, video)
{
    var videoCard = $(videoCardTemplate);
    var url = getVideoUrl(video);
    videoCard.find('img').attr('src', cumulusClips.baseUrl + '/cc-content/uploads/thumbs/' + video.filename + '.jpg');
    videoCard.find('.duration').text(video.duration);
    videoCard.find('a').attr('href', url);
    videoCard.find('p').text(video.title);
    return videoCard;
}

/**
 * Retrieve the full URL to a video
 * @param object video The video whose URL is being retrieved
 * @return string Returns the complete URL to given video
 */
function getVideoUrl(video)
{
    var url = cumulusClips.mobileBaseUrl;
    url += '/v/' + video.videoId + '/';
    return url;
}