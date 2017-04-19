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

    // Display edit language entry form
    $('#languages-edit .edit').on('click', function(event) {
        var cell = $(this).parents('td');
        var originalText = cell.data('original');
        cell.find('.entry').addClass('hidden');

        // Append edit form
        cell.append(
            '<div class="form-group edit-form">'
                + '<textarea class="form-control">' + originalText + '</textarea>'
                + '<input type="button" class="button submit" value="Save" />'
                + '<input type="button" class="button cancel" value="Cancel" />'
            + '</div>'
        );
        cell.find('textarea').focus();
        event.preventDefault();
    });

    // Reset language entry to it's original value
    $('#languages-edit .reset').on('click', function(event) {

        var cell = $(this).parents('td');
        var key = cell.data('key');
        var language = cell.data('language');

        // Make AJAX call to remove the custom entry if any
        $.ajax({
            url: cumulusClips.baseUrl + '/cc-admin/languages_save.php',
            method: 'POST',
            dataType: 'json',
            data: {
                language: language,
                key: key,
                text: '',
                action: 'reset'
            },
            success: function(data, textStatus, jqXHR) {
                cell.find('.entry').removeClass('hidden');
                cell.find('.form-group').remove();
                cell.find('.text').text(data.data.original.truncate(70, '...'));
                displayMessage(true, '"' + data.data.key + '" has been reset to it\'s original value');
                window.scrollTo(0, 0);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                displayMessage(false, 'Errors occurred while reseting the language entry. It cannot be reset at this time. '
                    + 'Please contact <a href="https://support.cumulusclips.com/">CumulusClips Support</a> if this issue continues.');
            }
        });
        event.preventDefault();
    });

    // Cancel edit language entry form
    $('#languages-edit').on('click', '.cancel', function(event) {
        var cell = $(this).parents('td');
        cell.find('.entry').removeClass('hidden');
        cell.find('.form-group').remove();
    });

    // Handle submission of edit language entry form
    $('#languages-edit').on('click', '.submit', function(event) {

        var cell = $(this).parents('td');
        var key = cell.data('key');
        var language = cell.data('language');
        var text = cell.find('textarea').val().trim();

        // Make AJAX call to save custom entry value
        $.ajax({
            url: cumulusClips.baseUrl + '/cc-admin/languages_save.php',
            method: 'POST',
            dataType: 'json',
            data: {
                language: language,
                key: key,
                text: text,
                action: 'edit'
            },
            success: function(data, textStatus, jqXHR) {
                cell.find('.entry').removeClass('hidden');
                cell.find('.form-group').remove();
                cell.find('.text').text(data.data.text.truncate(70, '...'));
                displayMessage(true, '"' + data.data.key + '" has been updated');
                window.scrollTo(0, 0);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                displayMessage(false, 'Errors occurred while updating the language entry. It cannot be updated at this time. '
                    + 'Please contact <a href="https://support.cumulusclips.com/">CumulusClips Support</a> if this issue continues.');
            }
        });
    });

    // Toggle "+" icon when expanding an accordion panel
    $('#accordion .panel').on('show.bs.collapse', function() {
        $(this).find('.glyphicon-plus')
            .removeClass('glyphicon-plus')
            .addClass('glyphicon-minus');
    });

    // Toggle "-" icon when collapsing an accordion panel
    $('#accordion .panel').on('hide.bs.collapse', function() {
        $(this).find('.glyphicon-minus')
            .removeClass('glyphicon-minus')
            .addClass('glyphicon-plus');
    });

    // Start duration counter for bulk video imports
    if ($('#videos-imports .time-since').length) {

        $('#videos-imports .time-since').each(function(index, element){

            var self = this;
            var startDateString = $(this).data('start');
            var startDate = new Date(startDateString);

            // Refresh counter each second
            setInterval(function(){
                $(self).text(timeSince(startDate));
            }, 1000);
        });
    }

    // Cancel out of attachment form
    $('#video-attachments').on('click', '.cancel', function(event){
        $('#video-attachments .add').show();
        $(this).parents('.attachment-form').addClass('hidden');
        event.preventDefault();
    });

    // Discard attachment
    $('#video-attachments').on('click', '.attachment .remove', function(event){

        var $attachment = $(this).parents('.attachment');

        // Update existing attachment list and set corresponding link as "unselected"
        if ($attachment.hasClass('existing-file')) {
            var fileId = $attachment.attr('id').replace(/^existing\-file\-/, '');
            $('#select-existing-file-' + fileId).removeClass('selected');
        }

        $attachment.remove();
        event.preventDefault();
    });

    // Display upload new attachments form
    $('#video-attachments .new').on('click', function(event){
        $('#video-attachments .add').hide();
        $('#video-attachments .attachment-form-upload').removeClass('hidden');
        event.preventDefault();
    });

    // Append uploaded attachment
    $('#video-attachments').on('uploadcomplete', '.uploader', function(event){

        $uploadWidget = getUploadWidget(this);

        // Build attachment widget
        var name = $uploadWidget.find('.name').val();
        var size = $uploadWidget.find('.size').val();
        var temp = $uploadWidget.find('.temp').val();
        var index = $('#video-attachments .attachments .attachment').length;
        var $attachment = buildAttachmentCard(index, name, size, temp);

        // Append attachment
        $('#video-attachments .attachments').append($attachment);

        // Reset upload form
        resetProgress($uploadWidget);
    });

    // Display existing attachments form
    $('#video-attachments .existing').on('click', function(event){
        $('#video-attachments .add').hide();
        $('.attachment-form-existing').removeClass('hidden');

        event.preventDefault();
    });

    // Select from existing attachments
    $('#video-attachments .attachment-form-existing li a').on('click', function(event){

        event.preventDefault();

        var fileId = $(this).data('file');

        // Remove attachment if "unselecting" file
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $('#existing-file-' + fileId).remove();
            return;
        }

        // Build attachment widget
        var name = $(this).attr('title');
        var size = $(this).data('size');
        var index = $('#video-attachments .attachments .attachment').length;
        var $attachment = buildAttachmentCard(index, name, size, fileId);

        // Mark as selected
        $(this).addClass('selected');

        // Append attachment
        $('#video-attachments .attachments').append($attachment);
    });
});

/**
 * Generates attachment card HTML to be appended to attachment list on video upload/edit page
 *
 * @param {Number} index Index of newly created attachment within list of attachments
 * @param {String} name Full name of file to be attached
 * @param {Number} size Size of attached file in bytes
 * @param {Number|String} file If file is an existing attachment then file ID is expected, otherwise absolute path to upload temp file
 * @return {jQuery} Returns jQuery object reprensenting attachment card
 */
function buildAttachmentCard(index, name, size, file)
{
    var fieldName = (typeof file === 'number') ? 'file' : 'temp';
    var displayFilename = (name.length > 35) ? name.substring(0, 35) + '...' : name;
    displayFilename += ' (' + formatBytes(size, 0) + ')';

    // Build card
    var $attachment = $('<div class="attachment">'

        // Append form values
        + '<input type="hidden" name="attachment[' + index + '][name]" value="' + name + '" />'
        + '<input type="hidden" name="attachment[' + index + '][size]" value="' + size + '" />'
        + '<input type="hidden" name="attachment[' + index + '][' + fieldName + ']" value="' + file + '" />'

        // Append progress bar template
        + '<div class="upload-progress">'
            + '<a class="remove" href=""><span class="glyphicon glyphicon-remove"></span></a>'
            + '<span class="title">' + displayFilename + '</span>'
            + '<span class="pull-right glyphicon glyphicon-ok"></span>'
        + '</div>'

    + '</div>');

    // Mark attachment as existing
    if (typeof file === 'number') {
        $attachment
            .addClass('existing-file')
            .attr('id', 'existing-file-' + file);
    }

    return $attachment;
}

/**
 * Pads a string to the right with the given characted
 *
 * @param {String} string The string to be padded
 * @param {Number} size The final desired length of the output string
 * @param {String} character The string to pad with
 * @return {String} Returns the original string with padding
 */
function padRight(string, size, character)
{
    var s = string + "";
    while (s.length < size) s = character + s;
    return s;
}

/**
 * Calculates elapsed time since given date
 *
 * @param {Date} startDate Starting date
 * @return {String} Returns elapsed time in format "D Days HH:MM:SS"
 */
function timeSince(startDate)
{
    var display = '';
    var currentDate = new Date();
    var diffMilliseconds = currentDate - startDate;
    var remainingSeconds = Math.floor(diffMilliseconds/1000);

    // Generate days
    if (remainingSeconds >= 86400) {
        var days = Math.floor(remainingSeconds/86400);
        display += days + ' Days ';
        remainingSeconds = remainingSeconds%86400;
    }

    // Generate hours
    if (remainingSeconds >= 3600) {
        var hours = Math.floor(remainingSeconds/3600);
        display += padRight(hours, 2, '0') + ':';
        remainingSeconds = remainingSeconds%3600;
    } else {
        display += '00:';
    }

    // Generate minutes
    if (remainingSeconds >= 60) {
        var minutes = Math.floor(remainingSeconds/60);
        display += padRight(minutes, 2, '0') + ':';
        remainingSeconds = remainingSeconds%60;
    } else {
        display += '00:';
    }

    // Generate seconds
    if (remainingSeconds > 0) {
        display += padRight(remainingSeconds, 2, '0');
    } else {
        display += '00';
    }

    return display;
}

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

    // Update setting
    cumulusClips.settings[name] = value;

    // Set cookie options
    var parser = document.createElement('a');
    parser.href = cumulusClips.baseUrl;
    var options = {
        domain: parser.hostname,
        secure: (parser.protocol === 'https:') ? true : false,
        path: parser.pathname.replace(/\/$/, '') + '/cc-admin/',
        expires: null,
    };

    // Save cookie
    $.cookie('cc_admin_settings', $.param(cumulusClips.settings), options);
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
 * Truncates string to given length and appends decorator to end if provided
 * @param {int} length The maximum allowed length of the string before truncating it
 * @params {string} decorator (optional) The string to append to the resulting string in the event of truncating
 * @return {string} Returns the original string if it's length was <= length, truncated string otherwise
 */
String.prototype.truncate = function(length, decorator) {
    if (this.length > length) {
        return this.substring(0, length) + (typeof decorator !== 'undefined' ? decorator : '');
    } else {
        return this;
    }
}

// Disable text selection on sidebar header links (<= IE8 fix)
$.fn.disableSelection = function() {
    $(this).attr('unselectable', 'on')
   .each(function() {
       this.onselectstart = function() {return false;};
    });
};