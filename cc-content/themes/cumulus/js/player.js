// Global vars
var theme = $('meta[name="theme"]').attr('content');
var videoURL = $('[meta[name="videoURL"]').attr('content');

jwplayer("container").setup({
    flashplayer: theme+"/flash/player.swf",
    file: videoURL,
    controlbar: 'bottom',
    height: 450,
    width: 600
});