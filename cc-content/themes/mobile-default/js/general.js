// Global vars
var cumulusClips = {};
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
    
    // Clear search form on submit
//    $('#search-form form').on('submit', function(event){
//        cancelSearch();
//    });
    
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
});

function cancelSearch()
{
    setTimeout(function(){$('#search-overlay').hide()}, 800);
    $('body').toggleClass('search-visible');
    $('#search-field .icon-clear').hide();
    $('#search-field input').val('');
}