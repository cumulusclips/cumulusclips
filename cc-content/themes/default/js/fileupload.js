
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
 * data-upload-button: string Optional - Default behavior is to automatically start uploading file once it is selected.
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

    // Attach upload plugin to form field
    $('.uploader').fileupload(cumulusClips.uploaderOptions);

    // Attach close event to remove button
    $('body').on('click', '.upload-progress .remove', function(event){

        var $uploadWidget = getUploadWidget(this);

        // Disable start upload button
        $uploadWidget.find('.button-upload').prop('disabled', true);

        // Abort upload
        stopUpload($uploadWidget);

        // Reset progress widget
        resetProgress($uploadWidget);

        event.preventDefault();
    });

    // Attach upload event to start upload button
    $('body').on('click', '.button-upload', function(event){
        var $uploadWidget = getUploadWidget(this);
        startUpload($uploadWidget);
        event.preventDefault();
    });

    // Initialize uploader widget on file upload fields
    $('.uploader').each(function(index, element){
        initUploader(element);
    });
});


/**
 * Options to pass to uploader plugin
 */
cumulusClips.uploaderOptions = {
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
        var $uploadWidget = getUploadWidget(this);
        var selectedFile = data.files[0];
        var filesizeLimit = Number($(this).data('limit'));
        var autoStart = $(this).data('upload-button') ? false : true;

        // Abort any uploads in progress
        stopUpload($uploadWidget);
        resetProgress($uploadWidget);

        // Validate file type
        if ($(this).data('extensions')) {
            var allowedExtensions = $.parseJSON(decodeURIComponent($(this).data('extensions')));
            var matches = selectedFile.name.match(/\.[a-z0-9]+$/i);
            if (!matches || $.inArray(matches[0].substr(1).toLowerCase(), allowedExtensions) === -1) {
                displayMessage(false, cumulusClips.errorFormat);
                window.scrollTo(0, 0);
                return false;
            }
        }

        // Validate filesize
        if (selectedFile.size > filesizeLimit) {
            displayMessage(false, cumulusClips.errorSize);
            window.scrollTo(0, 0);
            return false;
        }

        // Prepare upload progress box
        $uploadWidget.find('.upload-progress').removeClass('hidden');
        $uploadWidget.find('.progress-fill').css('width', '0%');

        // Set upload filename
        var displayFilename = (selectedFile.name.length > 35)
            ? selectedFile.name.substring(0, 35) + '...'
            : selectedFile.name;
        displayFilename += ' (' + formatBytes(selectedFile.size, 0) + ')';
        $uploadWidget.find('.title').text(displayFilename);

        // Store selected file data
        $(this).data('selectedFileData', data);

        // Start upload if set to automatically start when file is selected
        if (autoStart) {
            // Start Upload
            startUpload($uploadWidget);
        } else {
            // Enable start upload button
            $uploadWidget.find('.button-upload').prop('disabled', false);
        }
    },
    progress: function(event, data)
    {
        var $uploadWidget = getUploadWidget(this);
        var progress = parseInt(data.loaded / data.total * 100, 10);

        // Update progress bar
        $uploadWidget.find('.progress-fill').css('width', progress + '%');
    },
    fail: function(event, data)
    {
        $uploadWidget = getUploadWidget(this);

        // Disable start upload button
        $uploadWidget.find('.button-upload').prop('disabled', true);

        // Determine reason for failure
        if (data.errorThrown === 'abort') {

            // Upload was cancelled (either via API or by user)
            return false;
        } else {

            // HTTP upload failed/handler rejected upload
            $(this).trigger('uploadfailed');
            resetProgress($uploadWidget);
            displayMessage(false, cumulusClips.errorGeneral);
            window.scrollTo(0, 0);
        }
    },
    done: function(event, data)
    {
        var selectedFileData = $(this).data('selectedFileData');
        var fieldName = $(this).attr('name');
        var $uploadWidget = getUploadWidget(this);

        // Disable start upload button
        $uploadWidget.find('.button-upload').prop('disabled', true);

        // Determine result from server validation
        if (data.result.result === true) {

            // Update form with temp path to uploaded file
            $uploadWidget.find('.temp').val(data.result.other.temp);
            $uploadWidget.find('.size').val(selectedFileData.files[0].size);
            $uploadWidget.find('.name').val(selectedFileData.files[0].name);

            // Mark progress widget as complete
            $uploadWidget.find('.progress-track').addClass('hidden');
            $uploadWidget.find('.glyphicon-ok').removeClass('hidden');
            $(this).data('selectedFileData', null).data('jqXHR', null);

            // Submit parent form if auto-submit is turned on
            if ($(this).data('auto-submit')) {
                $(this).parents('form').submit();
            }

        } else {
            resetProgress($uploadWidget);
            displayMessage(false, data.result.message);
            window.scrollTo(0, 0);
        }

        $(this).trigger('uploadcomplete');
    }
};

/**
 * Initializes uploader widget for given file upload form field
 *
 * @param {DOMNode} domNode File upload form field to initialize uploader on
 */
function initUploader(domNode)
{
    var buttonText = $(domNode).data('text');
    var fieldName = $(domNode).attr('name');
    var startUploadButtonText = $(domNode).data('upload-button');

    // Build uploader and progress widgets
    $(domNode).wrap('<div class="uploader-container uploader-' + fieldName + '"><div class="button button-browse"></div></div>')
        .before('<span>' + buttonText + '</span>')
        .parents('.uploader-container')
        .append(

            // Append start upload button if the button's text is provied
            (startUploadButtonText ? '<input type="button" class="button button-upload" disabled value="' + startUploadButtonText + '" />' : '')

            // Append uploader settings
            + '<input type="hidden" class="size" name="' + fieldName + '[original-size]" value="" />'
            + '<input type="hidden" class="temp" name="' + fieldName + '[temp]" value="" />'
            + '<input type="hidden" class="name" name="' + fieldName + '[original-name]" value="" />'

            // Append progress bar template
            + '<div class="upload-progress hidden">'
                + '<a class="remove" href=""><span class="glyphicon glyphicon-remove"></span></a>'
                + '<span class="title"></span>'
                + '<div class="progress-track">'
                    + '<div class="progress-fill"></div>'
                + '</div>'
                + '<span class="hidden pull-right glyphicon glyphicon-ok"></span>'
            + '</div>'
        );

    // Display upload widget with file pre-selected if applicable
    if ($(domNode).data('prepopulate')) {

        // Hide progress track and icons
        var $uploadWidget = getUploadWidget(domNode);
        $uploadWidget.find('.upload-progress').removeClass('hidden');
        $uploadWidget.find('.progress-track').addClass('hidden');
        $uploadWidget.find('.glyphicon-ok').removeClass('hidden');

        // Populate form field values for pre-selected file
        var prePopulatedFile = $.parseJSON(decodeURIComponent($(domNode).data('prepopulate')));
        $uploadWidget.find('.size').val(prePopulatedFile.size);
        $uploadWidget.find('.name').val(prePopulatedFile.name);
        $uploadWidget.find('.temp').val(prePopulatedFile.path);

        // Populate display name for pre-selected file
        var displayFilename = (prePopulatedFile.name.length > 35)
            ? prePopulatedFile.name.substring(0, 35) + '...'
            : prePopulatedFile.name;
        displayFilename += ' (' + formatBytes(prePopulatedFile.size) + ')';
        $uploadWidget.find('.title').text(displayFilename);
    }
}

/**
 * Stops upload process for an upload widget
 *
 * @param {jQuery} $uploadWidget jQuery object for the upload widget being updated
 */
function stopUpload($uploadWidget)
{
    $uploader = $uploadWidget.find('.uploader');

    // Verify upload has started
    if ($uploader.data('jqXHR')) {

        // Abort upload in progress
        $uploader.data('jqXHR').abort();
        $uploader.data('jqXHR', null);
        $uploader.data('selectedFileData', null);
        $uploader.trigger('uploadaborted');

    } else {

        // Determine if upload was cancelled or discarded
        if ($uploader.data('selectedFileData')) {
            console.log($uploader.data('selectedFileData'));
            $uploader.trigger('uploadcancelled');
        } else if ($uploadWidget.find('.temp').val()){
            $uploader.trigger('uploaddiscarded');
        }
    }
}

/**
 * Starts upload process for an upload widget
 *
 * @param {jQuery} $uploadWidget jQuery object for the upload widget being updated
 */
function startUpload($uploadWidget)
{
    $uploader = $uploadWidget.find('.uploader');

    // Determine if upload has already started
    if (!$uploader.data('jqXHR')) {

        // Update progress widget
        $uploadWidget.find('.progress-fill').addClass('in-progress');

        // Start HTTP upload
        var selectedFileData = $uploader.data('selectedFileData');
        $uploader.data('jqXHR', selectedFileData.submit());
    }
}

/**
 * Resets upload widget to a pre "file selected" state
 *
 * @param {jQuery} $uploadWidget jQuery object for the upload progress widget being updated
 */
function resetProgress($uploadWidget)
{
    $uploadWidget.find('.upload-progress').addClass('hidden');
    $uploadWidget.find('.title').text('');
    $uploadWidget.find('.progress-track').removeClass('hidden');
    $uploadWidget.find('.glyphicon-ok').addClass('hidden');
    $uploadWidget.find('.progress-fill')
        .removeClass('in-progress')
        .css('width', '0%');

    // Reset form values
    $uploadWidget.find('input[type="hidden"]').val('');
    $uploadWidget.find('.uploader')
        .data('selectedFileData', null)
        .data('jqXHR', null);
}

/**
 * Retrieves upload widget related to given node
 *
 * @param {DOMNode} domNode DOM node to search for related upload widget
 * @return {jQuery} Returns jQuery object for the upload widget
 * @throws Thrown if no upload widget is associated with given node
 */
function getUploadWidget(domNode)
{
    if ($(domNode).hasClass('.uploader-container')) {
        return $(domNode);
    } else {

        var $container = $(domNode).parents('.uploader-container');

        // Throw error if no upload widget is assiciated with given node
        if ($container.length === 0) {
            throw 'CumulusClips Uploader: No upload container associated with given node';
        }

        return $container;
    }
}
