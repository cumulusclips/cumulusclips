$(function(){
    // Initialize & attach TinyMCE editor to specified field
    $('.tinymce').tinymce({

        // General options
        theme : "modern",
        plugins : "charmap,code,textcolor,image,link,lists,pagebreak,table,insertdatetime,media,searchreplace,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking",
        height : "300",
        toolbar: "styleselect | fontsizeselect forecolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        menu : { // this is the complete default configuration
            edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall | searchreplace'},
            insert : {title : 'Insert', items : 'media link image | charmap pagebreak insertdatetime nonbreaking'},
            view   : {title : 'View'  , items : 'visualchars visualaid | code fullscreen'},
            format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat  | formatselect fontselect fontsizeselect'},
            table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'}
        }
    });
});