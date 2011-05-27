    var settings = {};
    function retrieveSettings(){
        // Get from cookie
        var stringSettings = $.cookie('test_cookie');
//        var stringSettings = 'm9=c&m3=c&m4=c&m6=c&m5=o&m1=c&m2=c&editor=tinymce&m10=c&m0=c&m7=c&urlbutton=file&m8=c';
        var preSettings = stringSettings.split('&');
        $.each (preSettings,function(index,value){
            var placeHolder = value.split('=');
            settings[placeHolder[0]] = placeHolder[1];
        });
//        console.log(settings);
    }
    function storeSettings(){
        var stringSettings = $.param(settings);
        // Save to cookie
        $.cookie('test_cookie',stringSettings);
        console.log(stringSettings);
    }
retrieveSettings();
settings.dashboard = 1;
storeSettings();