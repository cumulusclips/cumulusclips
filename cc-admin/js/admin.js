// Global vars
var settings = retrieveSettings();
var customSlug = false;
var baseURL = $('meta[name="baseURL"]').attr('content');

$('document').ready(function(){

    $("#sidebar h3").disableSelection();    // Disable selection (<= IE8 fix)



    // Attach sidebar click events
    $("#sidebar h3").click(function(){
        var name = $(this).attr('class');
        $(this).parent().toggleClass('down-icon');
        var updatedSetting = (settings[name] == 0) ? 1 : 0;
        $(this).next().slideToggle('fast');
        updateSettings(name, updatedSetting);
    });



    // Attach record hover events
    $('.list tr').hover(function(){$(this).find('.record-actions').toggleClass('invisible');});
    


    // Attach confirm popup to confirm action links
    $('.confirm').click(function() {
        var location = $(this).attr('href')
        var agree = confirm ($(this).data('confirm'));
        if (agree) window.location = location;
        return false;
    });



    // Attach change event to status dropdown
    $('select[name="status"]').change(function(){
        var jumpLoc = $(this).data('jump');
        var alternateLoc = $(this).find('option:selected').data('url');
        if (typeof alternateLoc == 'undefined') {
            window.location = jumpLoc+'?status='+$(this).val();
        } else {
            window.location = alternateLoc;
        }
    });



    $('#page-title').change(function(){

        if (customSlug) return false;

        if ($.trim($(this).val()) == '') {
            $('#view-slug').hide();
            $('#empty-slug').show();
            $('#page-slug input[name="slug"]').val('');
            return false;
        }

        $.ajax({
            url         : baseURL + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:0,action:'title',title:$(this).val()},
            dataType    : 'json',
            success     : function(data, textStatus, jqXHR){
                $('#empty-slug').hide();
                $('#view-slug').show();
                $('#page-slug span').text(data.msg);
                $('#page-slug input[name="slug"]').val(data.msg);
            }
        });
        
    });
    
    $('#page-slug .done').click(function(){

        var editField = $('#page-slug input[name="edit-slug"]');
        $('#edit-slug').hide();

        if ($.trim(editField.val()) == '') {
            $('#empty-slug').show();
            $('#page-slug input[name="slug"]').val('');
            return false;
        }

        $.ajax({
            url         : baseURL + '/cc-admin/pages_slug.php',
            type        : 'POST',
            data        : {page_id:0,action:'slug',slug:editField.val()},
            dataType    : 'json',
            success     : function(data, textStatus, jqXHR){
                customSlug = true;
                $('#view-slug').show();
                $('#page-slug span').text(data.msg);
                $('#page-slug input[name="slug"]').val(data.msg);
            }
        });
        return false;

    });

















    $('#page-slug .edit').click(function(){
        $('#empty-slug').hide();
        $('#view-slug').hide();
        $('#edit-slug').css('display','inline');
        $('#edit-slug input').focus().val($('#page-slug input[name="slug"]').val());
        return false;
    });


    $('#page-slug .cancel').click(function(){
        $('#edit-slug').hide();
        if ($('#page-slug input[name="slug"]').val() == '') {
            $('#empty-slug').show();
        } else {
            $('#view-slug').css('display','inline');
        }
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
       this.onselectstart = function() { return false; };
    });
};