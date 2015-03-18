$(function(){

    // Initialize & attach TinyMCE editor to specified field
    $('.tinymce').tinymce({

        // General options
        theme : "advanced",
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,formatselect,forecolor,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,|,table,image,charmap,|,code,fullscreen",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        height : "300",
        init_instance_callback : "skipToolbar"

    });

});




/**
 * Allow tabbing directly to content by skipping the toolbar
 * @return void mce toolbar is skipped during tabs
 */
function skipToolbar() {
    $('.mceToolbar *').attr('tabindex',-1);
}