<?php
session_start();
$_SESSION['test'] = 'yes';
?><!DOCTYPE HTML>
<html lang="en">
<head>
<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
<meta charset="utf-8">
<title>jQuery File Upload Demo</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">
.button {
    text-decoration: none;
    font-family: arial;
    line-height: 35px;
    height: 35px;
    color: #FFF;
    padding: 0 15px;
    border-radius: 5px;
    border: none;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
/*    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJod…EiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);*/
    background: #023e7b;
}

a.button {
    display: inline-block;
}

#status {
    font-family: arial;
    background-color: #F0F0F0;
    border: 3px solid #E1E1E1;
    margin: 10px 0;
    padding: 10px;
    width: 300px;
    display: none;
    font-size: 12px;
}

#message {
    margin: 0 0 10px;
}

#meter {
    width: 300px;
    height: 10px;
    background-color: #E1E1E1;
}

#progress {
    background-color: #023e7b;
    width: 0%;
    height: 10px;
}
</style>
</head>
<body>
    
<form action="server.php" method="POST" enctype="multipart/form-data">
    <a href="" id="browse" class="button">Browse</a>
    <input style="display:none;" id="fileupload" type="file" name="files[]" />
    <input id="button" type="button" value="Upload" class="button" />
</form>

<div id="status">
    <div id="message"></div>
    <div id="meter">
        <div id="progress"></div>
    </div>
</div>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script>
$(function() {
    
    $('#browse').click(function(){
        $('#fileupload').click();
        return false;
    });
    
    // Change this to the location of your server-side upload handler:
    $('#fileupload').fileupload({
        autoUpload: false,
        dataType: 'html',
        add: function(e, data) {
//            var file = data.files[0];
            console.log(data);
            
            $('#status').show();
            
//            if (!file.type.match(/^video\/mp4$/i)) {
//                console.log(file.type);
//                $('#message').text('Invalid file type');
//                return false;
//            }
            
//            if (!file.name.match(/\.mp4$/i)) {
//                console.log(file.name);
//                $('#message').text('Invalid file extension');
//                return false;
//            }
            
            
//            if (file.size > 102000000) {
//                console.log(file.size);
//                $('#message').text('File too large');
//                return false;
//            }
            
            
            
//            $('#message').text(file.name);
            $('#button').click(function () {
                console.log('submitted');
                data.submit();
            });
        },
        done: function (e, data) {
            console.log(data);
            $('#message').text('Upload Complete');
            $('#meter').hide();
            // data.result
            // data.textStatus;
            // data.jqXHR;
        },
        fail: function (e, data) {
            console.log('error ocurred');
            // data.errorThrown
            // data.textStatus;
            // data.jqXHR;
        },
        progress: function (e, data) {
//            var progress = parseInt(data.loaded / data.total * 100, 10);
//            $('#message').text('Uploading... ' + progress + '%');
//            $('#progress').css('width', progress + '%');
        }
    });
});
</script>
</body> 
</html>