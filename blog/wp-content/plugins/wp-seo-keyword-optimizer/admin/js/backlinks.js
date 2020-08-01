
jQuery(document).ready(function($)
{
	$(document).on('wsko_init_page', function(e) {
        $('.wsko-show-all-list-elements:not(.wsko_init)').addClass('wsko_init').click(function(event){
            event.preventDefault();
            var $this = $(this);
            $this.closest('ul').find('li.wsko-list-hidden').show();
            $this.remove();
        });
        $('.wsko-set-backlink-disavow:not(.wsko_init)').addClass('wsko_init').click(function(event){
            event.preventDefault();
            var $this = $(this),
            set = $this.data('set') && $this.data('set') != 'false',
            domain = $this.data('domain') && $this.data('domain') != 'false';
            window.wsko_post_element({action: set ? 'wsko_disavow_backlink' : 'wsko_remove_disavowed_backlink', backlink: $this.data('backlink'), domain: domain, nonce: set ? wsko_data.disavow_backlink_nonce : wsko_data.remove_disavowed_backlink_nonce}, function(res){
                    if (res.success)
                    {
                        if ($this.data('reload-table'))
                        {
                            $this.closest('.wsko-tables').trigger('wsko_data_source_updated');
                        }
                        else
                        {
                            $this.data('set', !set);
                            if (set) {
                                $this.find('i').removeClass('fa-trash').addClass('fa-undo');
                            } else {
                                $this.find('i').removeClass('fa-undo').addClass('fa-trash');
                            }
                            var title = $this.attr('title');
                            $this.attr('title', $this.attr('data-original-title'));
                            $this.attr('data-original-title', title);
                        }
                        return true;
                    }
                }, false, $this, false);
        });
        $('.wsko-backlink-import-search-field:not(.wsko_init)').addClass('wsko_init').change(function(event){
            var $this = $(this);
            if ($this.val())
            {
                window.wsko_post_file($this[0].files[0], {action: 'wsko_import_search_backlinks_list', nonce: $this.data('nonce')}, false, false, false, $this, false);
                $this.val('');
            }
        });
    });
});