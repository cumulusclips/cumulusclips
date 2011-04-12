$(document).ready(function() {

    $("#username").change(function() {

        var usr = $("#username").val();
        if (usr.length >= 3) {

            $("#status").html('&nbsp;<img src="/images/loading.gif" align="absmiddle">&nbsp;<strong>Checking availability...</strong>');
            $.ajax({
                type: "POST",
                url: "/includes/username_validation.php",
                data: "username=" + usr,
                success: function(msg) {

                    $("#status").ajaxComplete(function(event, request, settings) {

                        if (msg == 'TRUE') {
                            $("#username_label").removeClass('Errors');
                            $(this).html('&nbsp;<img id="icon" src="/images/silk_accept.gif">&nbsp; <span class="available">Username is available!</span>');
                        } else {
                            $("#username_label").addClass('Errors');
                            $(this).html('&nbsp;<img id="icon" src="/images/silk_exclamation.gif">&nbsp; <span class="Errors">That username is unavailable</span>');
                        }

                    });

                 }

            });

        } else {

            $("#status").html('&nbsp;<img id="icon" src="/images/silk_exclamation.gif">&nbsp; <span class="Errors">At least 4 characters are required.</span>');
            $("#username").removeClass('object_ok'); // if necessary
            //$("#username").addClass("object_error");

        }

    });

});