// Global vars
var cumulusClips = cumulusClips || {};
cumulusClips.baseUrl = $('meta[name="baseUrl"]').attr('content');
cumulusClips.mobileBaseUrl = $('meta[name="mobileBaseUrl"]').attr('content');
cumulusClips.themeUrl = $('meta[name="themeUrl"]').attr('content');

$.mobile.defaultPageTransition = 'slide';
 
$(function(){
    
    // Play video when play icon is clicked
    $(document).on('click', '.icon-play', function(){
        var video = videojs($('.ui-content video')[0]);
        video.play();
    });
    
    // Init global login popup
    $('#login').enhanceWithin().popup();
    
    // Show/hide tab blocks when tabs are clicked on play page
    $(document).on('tap', '#play-tabs a', function(event){
        $('#tab-blocks > div').hide();
        var tabBlock = $(this).data('block');
        $(tabBlock).show();
        event.preventDefault();
    });

    // Attach auto complete functionality to search field
    $("#search-field input").autocomplete({
        source: cumulusClips.baseUrl + '/search/suggest/',
        appendTo: '#results'
    });
    
    $(document).on('touchstart', '.ui-autocomplete li', function(event){
        var item = this;
        setTimeout(function(){
            $(item).trigger('click');
        }, 100);
        event.preventDefault();
    });

    // Display search form when search icon is clicked
    $(document).on('touchstart', '.icon-search', function(event){
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
    
    // Validate login form
    $('#login form').validate({
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
        }
    });
    
    // Submit login form for server side authentication
    $('#login').on('submit', 'form', function(event){
        var url = $(this).attr('action');
        var formValues = $(this).serialize();
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
        event.preventDefault();
    });
});

function cancelSearch()
{
    setTimeout(function(){$('#search-overlay').hide()}, 800);
    $('body').toggleClass('search-visible');
    $('#search-field .icon-clear').hide();
    $('#search-field input').val('');
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