$('document').ready(function() {

    var host = $('[name="register:host"]').attr('content');
    var theme = $('[name="register:theme"]').attr('content');

    $("#username").keydown(function() {

        var username = $("#username").val();
        if (username.length >= 4) {

            $("#status").html('&nbsp;<img src="'+theme+'/images/loading.gif" align="absmiddle">&nbsp;<strong>Checking availability...</strong>');
            $.ajax({
                type: 'POST',
                url: host+'/username/validate/',
                data: 'username=' + username,
                success: function(response, textStatus, jqXHR) {

                    if (response == 'TRUE') {
                        $("#username_label").removeClass('errors');
                        $(this).html('&nbsp;<img id="icon" src="'+theme+'/images/silk_accept.gif">&nbsp; <span class="available">Username is available!</span>');
                    } else {
                        $("#username_label").addClass('errors');
                        $(this).html('&nbsp;<img id="icon" src="'+theme+'/images/silk_exclamation.gif">&nbsp; <span class="errors">That username is unavailable</span>');
                    }

                 }

            }); // END AJAX Call

        } else {
            $("#status").html('&nbsp;<img id="icon" src="'+theme+'/images/silk_exclamation.gif">&nbsp; <span class="errors">At least 4 characters are required.</span>');
            $("#username").removeClass('object_ok'); // if necessary
        }

    });

});