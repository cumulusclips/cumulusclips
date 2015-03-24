

/**
 * Perform operations for video play page
 */
function playController()
{
    // Establish play page vars
    var playPage = ($('[data-role="page"]').length > 1) ? $('.ui-page-active') : $('#mobile_play');
    cumulusClips.lastCommentId = playPage.find('.comment-list .comment').last().data('comment');
    cumulusClips.loadMoreComments = (cumulusClips.commentCount > 5) ? true : false;

    // Retrieve comments partial
    $.get(cumulusClips.themeUrl + '/blocks/comment.html', function(responseData, textStatus, jqXHR){cumulusClips.commentCardTemplate = responseData;});
    
    // Show/hide tab blocks when tabs are clicked on play page
    playPage.find('.play-tabs a').off('tap').on('tap', function(event){
        playPage.find('.tab-blocks > div').hide();
        var tabBlock = '.' + $(this).data('block');
        playPage.find(tabBlock).show();
        event.preventDefault();
    });
    
    // Validate and submit comments form
    cumulusClips.commentFormValidator = $('.post-comment form').validate({
        rules: {
            comments: 'required'
        },
        messages: {
            comments: cumulusClips.lang.errorComment
        },
        invalidHandler: function(event, validator){ event.preventDefault(); },
        submitHandler: function(form){
            var url = $(form).attr('action');
            var formValues = {
                videoId: $(form).find('[name="video-id"]').val(),
                comments: $(form).find('textarea').val(),
                parentCommentId: $(form).find('[name="parent-comment-id"]').val()
            };
            $.ajax({
                url: url,
                method: 'post',
                data: formValues,
                dataType: 'json',
                success: function(responseData, textStatus, jqXHR){
                    if (responseData.result) {

                        form.reset();
                        
                        // Append new comment if auto-approve comments is on
                        if (responseData.other.autoApprove === true) {
                            var commentCardElement = buildCommentCard(cumulusClips.commentCardTemplate, responseData.other.commentCard);
                            var commentCard = responseData.other.commentCard;
                            var commentList = playPage.find('.comments-container ul');

                            // Remove 'no comments' message if this is first comment
                            commentList.find('.no-comments').remove();

                            // Update comment count text
                            playPage.find('.comments-container .header span').text(responseData.other.commentCount);

                            // Append comment to list
                            if (commentCard.comment.parentId !== 0) {
                                var parentComment = $('[data-comment="' + commentCard.comment.parentId + '"]');
                                parentComment.after(commentCardElement)
                            } else {
                                commentList.append(commentCardElement);
                            }
                            commentList.listview('refresh');
                        }
                    }
                    displayMessage(responseData.result, responseData.message, playPage.find('.post-comment .message'));
                }
            });
            return false;
        }
    });
    
    // Reset the comment form after closing it's popup
    $('.post-comment').popup({
        afterclose: function(){
            cumulusClips.commentFormValidator.resetForm();
            $(this).find('form')[0].reset();
            clearMessage($(this).find('.message')[0]);
            $(this).find('input[name="parent-comment-id"]').val('');
        }
    });
    
    // Set parent comment value in comment form when replying to comment
    $('.comments-container').on('click', '.comment-reply', function(event){
        var commentId = $(this).parents('li').data('comment');
        playPage.find('input[name="parent-comment-id"]').val(commentId);
    });
    
    // Display login popup when sign in link is clicked
    $('.login-link').on('click', function(event){
        $('#login').popup('open', {transition: 'pop', positionTo: 'window'});
        event.preventDefault();
    });
    
    // Load more comments
    $('.comments-container .load-more').on('click', function(event){

        var loadMoreButton = $(this);
        var limit = loadMoreButton.data('limit');
        var data = {videoId:cumulusClips.videoId, lastCommentId:cumulusClips.lastCommentId, limit: limit};

        // Retrieve subsequent comments
        $.ajax({
            url: cumulusClips.baseUrl + '/actions/comments/get/',
            data: data,
            dataType: 'json',
            beforeSend: function(){
                $.mobile.loading('show');
            },
            success: function(responseData, textStatus, jqXHR){

                var lastCommentKey = responseData.other.commentCardList.length-1;
                cumulusClips.lastCommentId = responseData.other.commentCardList[lastCommentKey].comment.commentId;                    

                // Build comment cards and append them to the list
                $.each(responseData.other.commentCardList, function(key, commentCard){
                    playPage.find('[data-comment="' + commentCard.comment.commentId + '"]').remove();
                    var commentCardElement = buildCommentCard(cumulusClips.commentCardTemplate, commentCard);
                    loadMoreButton.before(commentCardElement);
                });

                // Hide load more button if no more comments are available
                if (playPage.find('.comment').length < cumulusClips.commentCount) {
                    cumulusClips.loadMoreComments = true;
                } else {
                    cumulusClips.loadMoreComments = false;
                    loadMoreButton.remove();
                }

                // Refresh list
                $.mobile.loading('hide');
                playPage.find('.comment-list').listview('refresh');
            }
        });
        event.preventDefault();
    });
}

/**
 * Perform operations for video upload page
 */
function videoUploadController()
{
    // Validate and submit video upload form
    cumulusClips.uploadFormValidator = $('#upload-form').validate({
        ignore: [],
        rules: {
            filename: 'required',
            title: 'required',
            tags: 'required',
            description: 'required',
            category_id: 'required'
        },
        messages: {
            filename: cumulusClips.lang.error_video_upload,
            title: cumulusClips.lang.error_title,
            tags: cumulusClips.lang.error_tags,
            description: cumulusClips.lang.error_description,
            category_id: cumulusClips.lang.error_category
        },
        errorPlacement: function(error, element) {
            if (element.attr('name') === 'title' || element.attr('name') === 'tags' || element.attr('name') === 'category_id') {
                element.parent().after(error);
            } else {
                element.after(error);
            }
        },
        invalidHandler: function(event, validator) { event.preventDefault(); },
        submitHandler: function(form) {
            var url = $(form).attr('action');
            var formValues = $(form).serialize();
            $.ajax({
                url: url,
                method: 'post',
                data: formValues,
                dataType: 'json',
                success: function(data, textStatus, jqXHR){
                    // Clear form if upload was successful
                    if (data.result) {
                        resetUploadForm();
                        $.mobile.silentScroll(0);
                    }
                    displayMessage(data.result, data.message, $('#upload-message'));
                }
            });
            return false;
        }
    });
    
    // Toggle display of private url when video upload is marked as private
    $('#private').off('change').on('change', function(event){
        $('#private_url').toggle();
    });
    
    // Regenerate Private URL
    $('#private_url a').off('click').on('click', function(event){
        regeneratePrivateUrl();
        event.preventDefault();
    });
}

/**
 * Perform operations for browse videos page
 */
function videosController()
{
    // Load video card template
    $.get(cumulusClips.themeUrl + '/blocks/video.html', function(responseData, textStatus, jqXHR){cumulusClips.videoCardTemplate = responseData;});

    // Load More Videos
    $('.video-list .load-more').on('click', function(event){
        var loadMoreButton = $(this);
        var retrieveOffset = $('.video-list .video').length;
        var retrieveLimit = Number(loadMoreButton.data('limit'));
        var videoCount = loadMoreButton.data('count');

        $.ajax({
            url: cumulusClips.baseUrl + '/videos/load-more/',
            beforeSend: function(){
                $.mobile.loading('show');
            },
            data: {start: retrieveOffset, limit: retrieveLimit},
            dataType: 'json',
            success: function(responseData, textStatus, jqXHR){
                // Append video cards
                $.each(responseData.other.videoList, function(index, value){
                    var videoCard = buildVideoCard(cumulusClips.videoCardTemplate, value);
                    loadMoreButton.before(videoCard);
                });

                // Remove load more button
                if ($('.video-list .video').length === videoCount) {
                    loadMoreButton.remove();
                }

                // Refresh list
                $.mobile.loading('hide');
                $('.video-list').listview('refresh');
            }
        });
        event.preventDefault();
    });
}

/**
 * Perform operations for search results page
 */
function searchController()
{
    // Load video card template
    $.get(cumulusClips.themeUrl + '/blocks/video.html', function(responseData, textStatus, jqXHR){cumulusClips.videoCardTemplate = responseData;});

    // Load More Videos
    $('.video-list .load-more').on('click', function(event){
        var loadMoreButton = $(this);
        var retrieveOffset = $('.video-list .video').length;
        var keyword = loadMoreButton.data('keyword');
        var retrieveLimit = Number(loadMoreButton.data('limit'));
        var videoCount = loadMoreButton.data('count');

        $.ajax({
            url: cumulusClips.baseUrl + '/search/load-more/',
            method: 'post',
            beforeSend: function(){
                $.mobile.loading('show');
            },
            data: {keyword: keyword, start: retrieveOffset, limit: retrieveLimit},
            dataType: 'json',
            success: function(responseData, textStatus, jqXHR){
                // Append video cards
                $.each(responseData.other.videoList, function(index, value){
                    var videoCard = buildVideoCard(cumulusClips.videoCardTemplate, value);
                    loadMoreButton.before(videoCard);
                });

                // Remove load more button
                if ($('.video-list .video').length === videoCount) {
                    loadMoreButton.remove();
                }

                // Refresh list
                $.mobile.loading('hide');
                $('.video-list').listview('refresh');
            }
        });
        event.preventDefault();
    });
}