if (typeof(window.jQuery) != 'undefined')
{
    window.wsko_set_co_links = function()
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        $('.bst-co-iframe-link:not(.bst-co-iframe-init)').addClass('bst-co-iframe-init').each(function(){
            
        });
    };
    window.wsko_get_scroll_parent = function(element, includeHidden)
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        var style = getComputedStyle(element);
        var excludeStaticParent = style.position === "absolute";
        var overflowRegex = includeHidden ? /(auto|scroll|hidden)/ : /(auto|scroll)/;

        if (style.position === "fixed") return document.body;
        for (var parent = element; (parent = parent.parentElement);) {
            style = getComputedStyle(parent);
            if (excludeStaticParent && style.position === "static") {
                continue;
            }
            if (overflowRegex.test(style.overflow + style.overflowY + style.overflowX)) return parent;
        }

        return document.body;
    }

    window.wsko_scroll_element = function($element)
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        jQuery(window.wsko_get_scroll_parent($element.get(0))).animate({
            scrollTop: $element.offset().top
        }, 500);
    };
    
    var scrollTopHtml = 0,
    scrollTopBody = 0;
    
    window.bst_toggle_co_iframe = function(iframe_str)
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        var $body = $('body'),
        $html = $('html'),
        $modal = $('#bst_co_iframe');
        if ($modal.hasClass('bst-co-iframe-active'))
        {
            $modal.removeClass('bst-co-iframe-active');
            $body.removeClass('bst-co-iframe-open');
            $html.scrollTop(scrollTopHtml);
            $body.scrollTop(scrollTopBody);
            $html.css('top', false);
            $body.css('top', false);
        }
        else
        {
            $modal.find('.bst-co-iframe-modal-loading').show();
            if ($modal.data('post-frame'))
                iframe_str = $modal.data('post-frame');
            scrollTopHtml = $html.scrollTop();
            scrollTopBody = $body.scrollTop();
            $html.css('top', -scrollTopHtml + 'px');
            $body.css('top', -scrollTopHtml + 'px');
            if (iframe_str)
            {
                var $iframe = $modal.find('.bst-co-iframe-modal-content').html(iframe_str).find('iframe');
                if ($iframe.load)
                {
                    $iframe.load(function(){
                        $modal.find('.bst-co-iframe-modal-loading').hide();
                    });
                }
                else
                {
                    setTimeout(function(){
                        $modal.find('.bst-co-iframe-modal-loading').hide();
                    }, 4000);
                }
            }
            $modal.addClass('bst-co-iframe-active');
            $body.addClass('bst-co-iframe-open');
        }
    }
    
    window.wsko_co_iframe_reinit = function()
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        $('.bst-co-iframe-link:not(.wsko-init)').addClass('wsko-init').click(function(event){
            event.preventDefault();
            var $this = $(this);
            window.bst_toggle_co_iframe($this.data('iframe'));
        });
        $('.bst-co-iframe-modal-backdrop:not(.wsko-init),.bst-co-iframe-modal-close:not(.wsko-init)').addClass('wsko-init').click(function(event){
            window.bst_toggle_co_iframe(false);
        });
    };

    jQuery(document).ready(function($) {
        window.wsko_set_co_links();
        window.wsko_co_iframe_reinit();
        
		$('.wsko-content-optimizer-table-link').each(function(index){
			$(this).prependTo($(this).closest('.page-title'));
		});
    });
}