/**
 * Options:
 *
 * data-url: string Required - The URL to the upload file handler
 * data-text: string Required - The text for the Browse files button
 * data-limit: int Required - Filesize limit (in bytes) for uploaded files
 * data-extensions: string Required - URL encodeded JSON array of allowed file extensions
 * data-type: string Required - The type of upload. Allowed values are video, image, and library.
 * data-prepopulate: string Optional - URL encoded JSON object of uploaded file to pre-populate. Required properties of object are:
 *      - path: Absolute path to uploaded temp file
 *      - size: Filesize of uploaded temp file
 *      - name: Name of uploaded temp file
 * data-start-upload-button: string Optional - Default behavior is to automatically start uploading file once it is selected.
 *      If this value is provided, a seperate button is displayed to begin upload. This value will be used as the text for
 *      the button.
 * data-auto-submit: boolean Optional - Default false. Whether or not to submit the parent form. If true, the parent form is
 *      automatically submitted when the upload completes. Otherwise, the upload progress widget is updated to reflect the
 *      uploaded file's information once the upload is complete.
 */

// Global vars
var cumulusClips = cumulusClips || {};
getText(function(responseData, textStatus, jqXHR){cumulusClips.errorFormat = responseData;}, 'error_upload_extension');
getText(function(responseData, textStatus, jqXHR){cumulusClips.errorGeneral = responseData;}, 'error_upload_system');
getText(function(responseData, textStatus, jqXHR){cumulusClips.errorSize = responseData;}, 'error_upload_filesize');

$(function(){
    $('.uploader').fileupload({
        dataType: 'json',
        type: 'POST',
        paramName: 'upload',
        formData: function(form){
            return [{
                name: 'upload-type',
                value: $(this.fileInput).data('type')
            }];
        },
        add: function(event, data)
        {
            $(this).data('uploadFileData', data);
            var selectedFile = data.files[0];
            var filesizeLimit = Number($(this).data('limit'));
            var $uploadProgressWidget = getProgressWidget(this);
            var autoStart = $(this).data('start-upload-button') ? false : true;

            // Validate file type
            if ($(this).data('extensions')) {
                var allowedExtensions = $.parseJSON(decodeURIComponent($(this).data('extensions')));
                var matches = selectedFile.name.match(/\.[a-z0-9]+$/i);
                if (!matches || $.inArray(matches[0].substr(1).toLowerCase(), allowedExtensions) === -1) {
                    displayMessage(false, cumulusClips.errorFormat);
                    return false;
                }
            }

            // Validate filesize
            if (selectedFile.size > filesizeLimit) {
                displayMessage(false, cumulusClips.errorSize);
                return false;
            }

            // Prepare upload progress box
            $uploadProgressWidget.removeClass('hidden');
            $uploadProgressWidget.attr('data-progress', '0%');
            $uploadProgressWidget.find('.progress-fill').css('width', '0%');

            // Set upload filename
            var filename = selectedFile.name;
            var displayFilename = (filename.length > 35) ? filename.substring(0, 35) + '...' : filename;
            displayFilename += ' (' + formatBytes(selectedFile.size, 0) + ')';
            $uploadProgressWidget.find('.title').text(displayFilename);

            // Begin file upload if set to automatically start when file is selected
            if (autoStart) {
                $uploadProgressWidget.find('.progress-fill').addClass('in-progress');
                $(this).data('jqXHR', data.submit());
            } else {
                // Enable start upload button
                $(this).parents('.uploader-container').find('.button-upload').prop('disabled', false);
            }
        },
        progress: function(event, data)
        {
            var $uploadProgressWidget = getProgressWidget(this);
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update progress bar
            $uploadProgressWidget.attr('data-progress', progress + '%');
            $uploadProgressWidget.find('.progress-fill').css('width', progress + '%');
        },
        fail: function(event, data)
        {
            // Disable start upload button
            $(this).parents('.uploader-container').find('.button-upload').prop('disabled', true);

            // Determine reason for failure
            if (data.errorThrown === 'abort') {
                // Upload was cancelled (either via API or by user)
                return false;
            } else {
                resetProgress(getProgressWidget(this));
                displayMessage(false, cumulusClips.errorGeneral);
            }
        },
        done: function(event, data)
        {
            var uploadFileData = $(this).data('uploadFileData');
            var fieldName = $(this).attr('name');
            var $uploadProgressWidget = getProgressWidget(this);

            // Disable start upload button
            $(this).parents('.uploader-container').find('.button-upload').prop('disabled', true);

            // Determine result from server validation
            if (data.result.result === true) {

                // Update form with temp path to uploaded file
                $('input[name="' + fieldName + '[temp]"]').val(data.result.other.temp);
                $('input[name="' + fieldName + '[original-size]"]').val(uploadFileData.files[0].size);
                $('input[name="' + fieldName + '[original-name]"]').val(uploadFileData.files[0].name);

                // Mark progress widget as complete
                $uploadProgressWidget.find('.progress-track').addClass('hidden');
                $uploadProgressWidget.find('.cancel').addClass('hidden');
                $uploadProgressWidget.find('.glyphicon-ok').removeClass('hidden');

                // Submit parent form if auto-submit is turned on
                if ($(this).data('auto-submit')) {
                    $(this).parents('form').submit();
                }

            } else {
                resetProgress($uploadProgressWidget);
                displayMessage(false, data.result.message);
            }
        }
    });

    // Attach cancel event to cancel button
    $('body').on('click', '.upload-progress .cancel', function(event){

        var $uploader = getUploaderWidget(this);
        var $uploadProgressWidget = getProgressWidget($uploader[0]);

        // Disable start upload button
        $(this).parents('.uploader-container').find('.button-upload').prop('disabled', true);

        // Abort upload if in progress
        if ($uploader.data('jqXHR')) {
            $uploader.data('jqXHR').abort();
            $uploader.data('jqXHR', null);
            $uploader.data('uploadFileData', null);
        }

        resetProgress($uploadProgressWidget);
        event.preventDefault();
    });

    // Attach upload event to start upload button
    $('body').on('click', '.button-upload', function(event){

        var $uploader = getUploaderWidget(this);
        var $uploadProgressWidget = getProgressWidget(this);
        var uploadFileData = $uploader.data('uploadFileData');

        if (uploadFileData !== undefined) {
            $uploadProgressWidget.find('.progress-fill').addClass('in-progress');
            $uploader.data('jqXHR', uploadFileData.submit());
        }
        event.preventDefault();
    });



    var uploaderList = $('.uploader');
    $.each(uploaderList, function(index, uploader){

        var buttonText = $(uploader).data('text');
        var fieldName = $(uploader).attr('name');
        var startUploadButtonText = $(uploader).data('start-upload-button');

        // Build uploader and progress widgets
        $(uploader).wrap('<div class="uploader-container uploader-' + fieldName + '"><div class="button button-browse"></div></div>')
            .before('<span>' + buttonText + '</span>')
            .parents('.uploader-container')
            .append(

                // Append start upload button if the button's text is provied
                (startUploadButtonText ? '<input type="button" class="button button-upload" disabled value="' + startUploadButtonText + '" />' : '')

                // Append uploader settings
                + '<input type="hidden" name="' + fieldName + '[original-size]" value="" />'
                + '<input type="hidden" name="' + fieldName + '[temp]" value="" />'
                + '<input type="hidden" name="' + fieldName + '[original-name]" value="" />'

                // Append progress bar template
                + '<div class="upload-progress hidden" data-progress="0%">'
                    + '<a class="cancel" href=""><span class="glyphicon glyphicon-remove"></span></a>'
                    + '<span class="title"></span>'
                    + '<div class="progress-track">'
                        + '<div class="progress-fill"></div>'
                    + '</div>'
                    + '<span class="hidden pull-right glyphicon glyphicon-ok"></span>'
                + '</div>'
            );

        // Display upload widget with file pre-selected if applicable
        if ($(uploader).data('prepopulate')) {

            // Hide progress track and icons
            var $uploadProgressWidget = getProgressWidget(uploader);
            $uploadProgressWidget.removeClass('hidden');
            $uploadProgressWidget.find('.progress-track').addClass('hidden');
            $uploadProgressWidget.find('.glyphicon-ok').removeClass('hidden');

            // Populate form field values for pre-selected file
            var prePopulatedFile = $.parseJSON(decodeURIComponent($(uploader).data('prepopulate')));
            $('input[' + fieldName + '][original-size]').val(prePopulatedFile.size);
            $('input[' + fieldName + '][original-name]').val(prePopulatedFile.name);
            $('input[' + fieldName + '][temp]').val(prePopulatedFile.path);

            // Populate display name for pre-selected file
            var displayFilename = (prePopulatedFile.name.length > 35)
                ? prePopulatedFile.name.substring(0, 35) + '...'
                : prePopulatedFile.name;
            displayFilename += ' (' + formatBytes(prePopulatedFile.size) + ')';
            $uploadProgressWidget.find('.title').text(displayFilename);
        }
    });
});

/**
 * Resets upload progress widget to a pre "file selected" state
 *
 * @param {Object} uploadProgressWidget jQuery object for the upload progress widget being reset
 */
function resetProgress(uploadProgressWidget)
{
    uploadProgressWidget.addClass('hidden');
    uploadProgressWidget.find('.title').text('');
    uploadProgressWidget.find('.progress-track').removeClass('hidden');
    uploadProgressWidget.find('.glyphicon-ok').addClass('hidden');
    uploadProgressWidget.find('.progress-fill').removeClass('in-progress').css('width', '0%');
    uploadProgressWidget.attr('data-progress', '0%');
}

/**
 * Retrieves uploader widget related to given node
 *
 * @param {DOMNode} domNode DOM node to search for related widget
 * @return {Object} Returns jQuery object for the given node's upload progress widget
 * @throws Thrown if no upload width is associated with given node
 */
function getUploaderWidget(domNode)
{
    if ($(domNode).hasClass('.uploader')) {
        return $(domNode).find('.uploader');
    } else {

        var $container = $(domNode).parents('.uploader-container');

        // Throw error if no upload widget is assiciated with given node
        if ($container.length === 0) {
            throw 'CumulusClips Uploader: No upload widget associated with given node';
        }

        return $container.find('.uploader');
    }
}

/**
 * Retrieves upload progress widget related to given node
 *
 * @param {DOMNode} domNode DOM node to search for related widget
 * @return {Object} Returns jQuery object for the given node's upload progress widget
 * @throws Thrown if no upload width is associated with given node
 */
function getProgressWidget(domNode)
{
    if ($(domNode).hasClass('.uploader-container')) {
        return $(domNode).find('.upload-progress');
    } else {

        var $container = $(domNode).parents('.uploader-container');

        // Throw error if no upload widget is assiciated with given node
        if ($container.length === 0) {
            throw 'CumulusClips Uploader: No upload widget associated with given node';
        }

        return $container.find('.upload-progress');
    }
}