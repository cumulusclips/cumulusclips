$(document).ready(function() {
    var xhr;
    var minMessage = '';
    var checkAvailability = '';
    getText (function(data){minMessage = data;}, 'username_minimum');
    getText (function(data){checkAvailability = data;}, 'checking_availability');

    $("#username").keyup(function() {

        if (typeof xhr != 'undefined') xhr.abort();
        var username = $("#username").val();
        if (username.length >= 4) {

            $("#status").text(checkAvailability).addClass('loading').removeClass('error ok');

            xhr = $.ajax({
                type: 'POST',
                url: baseUrl+'/actions/username/',
                data: {username:username},
                success: function(response, textStatus, jqXHR) {
                    response = $.parseJSON(response);
                    $('#status').text(response.msg);
                    if (response.result == 1) {
                        $('#status').addClass('ok').removeClass('error loading');
                    } else {
                        $('#status').addClass('error').removeClass('ok loading');
                    }
                 }
            });

        } else {
            $('#status').text(minMessage).addClass('error').removeClass('ok loading');
        }
    });
});