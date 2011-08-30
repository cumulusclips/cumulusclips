$('document').ready(function() {

    var host = $('[name="register:host"]').attr('content');
    var xhr;
    var minMessage = '';
    var checkAvailability = '';
    GetText (function(data){minMessage = data;}, 'username_minimum');
    GetText (function(data){checkAvailability = data;}, 'checking_availability');

    $("#username").keyup(function() {

        if (typeof xhr != 'undefined') xhr.abort();
        var username = $("#username").val();
        if (username.length >= 4) {

            $("#status").html('<span class="loading">'+checkAvailability+'...</span>');

            xhr = $.ajax({
                type: 'POST',
                url: host+'/actions/username/',
                data: {username:username},
                success: function(response, textStatus, jqXHR) {
                    response = $.parseJSON(response);
                    if (response.result == 1) {
                        $('#status').html('<span class="ok">'+response.msg+'</span>');
                    } else {
                        $('#status').html('<span class="errors">'+response.msg+'</span>');
                    }
                 }

            }); // END AJAX Call

        } else {
            $('#status').html('<span class="errors">'+minMessage+'</span>');
        }

    });

});