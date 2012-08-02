window.addEvent('load', function() {
    // start dropbox sync
    new Request.JSON({
        url: 'index.php?option=com_dropbox&view=sync',
        method: 'get',
        onSuccess: function(progress) {
            if(progress.added) {
                
            }
        }
    }).send();
});