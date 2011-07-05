// Global vars
var settings = retrieveSettings();
var customSlug = false;
var baseURL = $('meta[name="baseURL"]').attr('content');

$('document').ready(function(){

    $("#sidebar h3").disableSelection();    // Disable selection (<= IE8 fix)




    // Toggle expand/collapse of sidebar sub-menus
    $("#sidebar h3").click(function(){
        var name = $(this).attr('class');
        $(this).parent().toggleClass('down-icon');
        var updatedSetting = (settings[name] == 0) ? 1 : 0;
        $(this).next().slideToggle('fast');
        updateSettings(name, updatedSetting);
    });




    // Display record actions when hovering over record
    $('.list tr').hover(function(){$(this).find('.record-actions').toggleClass('invisible');});
    



    // Trigger confirmation popup for confirm action links
    $('.confirm').click(function() {
        var location = $(this).attr('href')
        var agree = confirm ($(this).data('confirm'));
        if (agree) window.location = location;
        return false;
    });




    // Redirect user to requested location when status dropdown is updated
    $('.jump select').change(function(){
        var jumpLoc = $(this).data('jump');
        var alternateLoc = $(this).find('option:selected').data('url');
        if (typeof alternateLoc == 'undefined') {
            window.location = jumpLoc+'?status='+$(this).val();
        } else {
            window.location = alternateLoc;
        }
    });




    // Generate and update slug as page title changes
    $('#page-title').change(function(){

        if (customSlug) return false;   // URL has been modified directly

        // Callback to execute in case of empty page title or slug
        var emptyCallback = function() {
                $('#view-slug').hide();
                $('#edit-slug').hide();
                $('#empty-slug').show();
                $('#page-slug input[name="slug"]').val('');
                return false;
        }

        // Page title is empty
        if ($.trim($(this).val()) == '') return emptyCallback();

        // Submit page title for AJAX validation
        $.ajax({
            url         : baseURL + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:0,action:'title',title:$(this).val()},
            dataType    : 'json',
            success     : function(data, textStatus, jqXHR){

                // Returned slug is empty
                if ($.trim(data.msg) == '') return emptyCallback();

                // Return slug is valid, update URL & fields
                $('#empty-slug').hide();
                $('#edit-slug').hide();
                $('#view-slug').show();
                $('#page-slug span').text(data.msg);
                $('#page-slug input[name="slug"]').val(data.msg);
            }
        });
        
    });




    // Validate custom page slug when done editing
    $('#page-slug .done').click(function(){

        $('#edit-slug').hide();
        var editField = $('#page-slug input[name="edit-slug"]');

        // Callback to execute in case of empty slug
        var emptyCallback = function() {
            $('#empty-slug').show();
            $('#page-slug input[name="slug"]').val('');
            return false;
        }

        // Custom slug is empty
        if ($.trim(editField.val()) == '') return emptyCallback();

        // Submit custom slug for AJAX validation
        $.ajax({
            url         : baseURL + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:0,action:'slug',slug:editField.val()},
            dataType    : 'json',
            success     : function(data, textStatus, jqXHR){

                // Returned slug is empty
                if ($.trim(data.msg) == '') return emptyCallback();

                // Return slug is valid, update URL & fields
                customSlug = true;
                $('#view-slug').show();
                $('#page-slug span').text(data.msg);
                $('#page-slug input[name="slug"]').val(data.msg);
            }
        });
        return false;

    });




    // Display edit page slug field
    $('#page-slug .edit').click(function(){
        $('#empty-slug').hide();
        $('#view-slug').hide();
        $('#edit-slug').show();
        $('#edit-slug input').focus().val($('#page-slug input[name="slug"]').val());
        return false;
    });




    // Hide edit slug field & display proper view of slug
    $('#page-slug .cancel').click(function(){
        $('#edit-slug').hide();
        if ($('#page-slug input[name="slug"]').val() == '') {
            $('#empty-slug').show();
        } else {
            $('#view-slug').show();
        }
        return false;
    });




    // Initialize fancybox lightbox
    $('.fancybox').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250
    });

    $('.iframe').fancybox({
        'transitionIn'	: 'elastic',
        'transitionOut'	: 'elastic',
        'speedIn'       : 250,
        'type'          : 'iframe',
        'width'         : '95%',
        'height'        : '95%',
        'autoScale'     : false
    });



});



/**
 * Retrieve the admin settings from the settings cookie
 * @return object Admin settings stored in cookie are returned as object
 */
function retrieveSettings(){
    var settings = {};
    var stringSettings = $.cookie('cc_admin_settings');
    var preSettings = stringSettings.split('&');
    $.each (preSettings,function(index,value){
        var placeHolder = value.split('=');
        settings[placeHolder[0]] = placeHolder[1];
    });
    return settings;
}



/**
 * Update the value of a global admin setting
 * @param string name The name of the setting to be updated
 * @param mixed value The new value to assign to the setting
 * @return void Global settings object and cookie are updated
 */
function updateSettings(name, value){
    settings[name] = value;
    $.cookie('cc_admin_settings',$.param(settings));
}



// Disable text selection on sidebar header links (<= IE8 fix)
$.fn.disableSelection = function() {
    $(this).attr('unselectable', 'on')
   .each(function() {
       this.onselectstart = function() { return false; };
    });
};