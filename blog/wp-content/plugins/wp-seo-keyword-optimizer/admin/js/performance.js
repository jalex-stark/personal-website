jQuery(document).ready(function($){
	$(document).on('wsko_init_core', function(e) {
        $('.wsko-replace-asset:not(.wsko-init)').addClass('wsko-init').change(function(event){
            event.preventDefault();
            var $this = $(this),
            file = $this[0].files[0];
            $this.val('');
            window.wsko_post_file(file, {action: 'wsko_replace_asset', path: $this.data('path'), nonce: $this.data('nonce')}, false, function(res){
                if (res.success)
                {
                    d = new Date();
                    $this.closest('.wsko-asset-img-replace-wrapper').find('.wsko-asset-img-preview').attr('src', res.url+'?'+d.getTime());
                    return true;
                }
                return false;
            }, function(){
            }, $this, false);
        });
    });
});