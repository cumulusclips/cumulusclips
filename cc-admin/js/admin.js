// Global vars
var cumulusClips = {};
cumulusClips.settings = retrieveSettings();
cumulusClips.customSlug = false;
cumulusClips.baseUrl = $('meta[name="baseURL"]').attr('content');

$(function(){

    // Toggle expand/collapse of sidebar sub-menus
    $("#sidebar").on('click', '[data-toggle="collapse"]', function(){
        var name = $(this).attr('href').replace('#menu-', '');
        var updatedSetting = (cumulusClips.settings[name] == 0) ? 1 : 0;
        updateSettings(name, updatedSetting);
    });

    // Trigger confirmation popup for confirm action links
    $('.confirm').click(function() {
        var location = $(this).attr('href')
        var agree = confirm ($(this).data('confirm'));
        if (agree) window.location = location;
        return false;
    });

    // Generate and update slug as page title changes
    $('#page-title').change(function(){

        if (cumulusClips.customSlug) return false;   // URL has been modified directly

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
        
        // Retrieve id of current page if any
        var pageId = ($('input[name="pageId"]').val() != '') ? $('input[name="pageId"]').val() : 0;

        // Submit page title for AJAX validation
        $.ajax({
            url         : cumulusClips.baseUrl + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:pageId,action:'title',title:$(this).val()},
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
        
        // Retrieve id of current page if any
        var pageId = ($('input[name="pageId"]').val() != '') ? $('input[name="pageId"]').val() : 0;

        // Submit custom slug for AJAX validation
        $.ajax({
            url         : cumulusClips.baseUrl + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:pageId,action:'slug',slug:editField.val()},
            dataType    : 'json',
            success     : function(data, textStatus, jqXHR){

                // Returned slug is empty
                if ($.trim(data.msg) == '') return emptyCallback();

                // Return slug is valid, update URL & fields
                cumulusClips.customSlug = true;
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
        $('#pre-updates').hide();
        document.title = $('#updates-in-progress h1').text();
        $('#updates-in-progress').show();

        // Poll server to check update status
        setInterval(function(){
            $.ajax({
                cache       : false,
                async       : false,
                url         : cumulusClips.baseUrl + '/.updates/status',
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

    // Toggle display of a block via select box change
    $('select[data-toggle]').change(function(){
        var targetBlock = '#' + $(this).data('toggle');
        $(targetBlock).toggleClass('hide').trigger('toggle');
    });

    // Toggle Move Videos / Delete Category Forms
    $('.category-action').click(function(){
        var action = $(this).data('action');
        var parent = $(this).parents('li');
        var show = (action == 'move') ? '.move-videos' : '.delete-category';
        var hide = (action == 'move') ? '.delete-category' : '.move-videos';
        
        $('.category-action-effect').addClass('hide');
        parent.find('.category-action-effect').removeClass('hide');
        parent.find('input[name="action"]').val(action);
        parent.find(show).show();
        parent.find(hide).hide();
        return false;
    });

    // Add Show/Hide password link to password fields
    $('.mask').after(function(){
        var name = $(this).attr('name');
        var field = '<input style="display:none;" type="text" class="form-control" name="'+name+'-show" />';
        var link = '<a href="" class="mask-link" data-for="'+name+'" tabindex="-1">Show Password</a>';
        return field+link;
    });

    // Toggle password visibility when mask link is clicked
    $('.form-group').on('click', '.mask-link', function(event){

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

        event.preventDefault();
    });

    // Load mothership message
    if ($('#dashboard').length == 1) {
        $.ajax({
            type: 'POST',
            url: cumulusClips.baseUrl + '/cc-admin/',
            data: {news:'true'},
            error: function(){$('#news div').html('<strong>Nothing to report.</strong>');},
            success: function(data){$('#news .panel-body').html(data);}
        });
    }

    // Show/Hide Block
    $('.showhide').click(function(){

        // Retrieve and toggle targeted block
        var block = $(this).data('block');
        $('#'+block).toggleClass('hide');

        // Hide other blocks on same level as toggled block
        $('.showhide-block:not(#'+block+')').addClass('hide');

        // Prevent link click through
        if ($(this).is('a')) return false;
    });

    // Regenerate Private URL
    $('#private-url a').click(function(){
        $.ajax({
            type: 'get',
            url: cumulusClips.baseUrl + '/private/get/',
            success: function (responseData, textStatus, jqXHR) {
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
    cumulusClips.settings[name] = value;
    $.cookie('cc_admin_settings',$.param(cumulusClips.settings));
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
 * Display message sent from the server handler script for page actions
 * @param boolean result The result of the requested action (1 = Success, 0 = Error)
 * @param string message The textual message for the result of the requested action
 * @return void Message block is displayed and styled accordingly with message.
 * If message block is already visible, then it is updated.
 */
function displayMessage(result, message)
{
    var cssClass = (result == true) ? 'alert-success' : 'alert-danger';
    var existingClass = ($('.alert').hasClass('alert-success')) ? 'alert-success' : 'alert-danger';
    $('.alert').show();
    $('.alert').html(message);
    $('.alert').removeClass(existingClass);
    $('.alert').addClass(cssClass);
}

/**
 * Resets progress bar, enables browse button,
 * clears file title, and hides progress bar
 */
function resetProgress()
{
    $('#upload_status .fill').removeClass('in-progress');
    $('#upload_status').hide();
    $('#upload_status .title').text('');
    $('#upload_status .fill').css('width', '0%');
    $('#upload_status .percentage').text('0%');
}

// Disable text selection on sidebar header links (<= IE8 fix)
$.fn.disableSelection = function() {
    $(this).attr('unselectable', 'on')
   .each(function() {
       this.onselectstart = function() {return false;};
    });
};