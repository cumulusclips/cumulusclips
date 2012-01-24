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
        window.location = jumpLoc+'?status='+$(this).val();
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




    // Display update in progress message & status
    $('.begin-update').click(function(){

        // Display message
        $('#updates-begin').hide();
        document.title = $('#updates-in-progress h1').text();
        $('#updates-in-progress').show();

        // Poll server to check update status
        setInterval(function(){
            $.ajax({
                cache       : false,
                async       : false,
                url         : baseURL+'/.updates/status',
                type        : 'GET',
                success     : function(data, textStatus, jqXHR){
                    $('#updates-in-progress .status').html(data);
                }
            });
        }, 3000);

    });




    // Append tipsy tooltip on more info links
    $('.more-info').click(function(){return false;});
    $('.more-info').tipsy({
        fade    : true,
        gravity : 's'
    });




    // Toggle SMTP settings visibility based on SMTP enable field
    $('#settings-email [name="smtp_enabled"]').change(function(){
        $('#smtp_auth').toggleClass('hide');
    });




    // Toggle Move Videos / Delete Category Forms
    $('.category-action').click(function(){
        var action = $(this).data('action');
        var parent = $(this).parents('.block');
        var show = (action == 'move') ? '.move-videos' : '.delete-category';
        var hide = (action == 'move') ? '.delete-category' : '.move-videos';
        
        $('.hide').hide();
        parent.find('.hide').show();
        parent.find('input[name="action"]').val(action);
        parent.find(show).show();
        parent.find(hide).hide();
        return false;
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




    // Load mothership message
    if ($('#dashboard').length == 1) {
        $.ajax({
            'type'      : 'POST',
            'url'       : baseURL+'/cc-admin/',
            'data'      : {news:'true'},
            'error'     : function(){$('#news div').html('<strong>Nothing to report.</strong>');},
            'success'   : function(data){$('#news div').html(data);}
        });
    }




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
       this.onselectstart = function() {return false;};
    });
};