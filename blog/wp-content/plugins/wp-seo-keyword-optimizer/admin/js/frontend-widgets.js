if (typeof(jQuery) != "undefined")
{
    window.wsko_frontend_reinit = function()
    {
        if (typeof($) === "undefined" || !$) $ = jQuery;
        $('.bst-content-table').each(function(index){
            var $this = $(this),
            table_id = $this.attr('id'),
            $source = $this.data('source') ? $($this.data('source')) : $this.parent(),
            $target = $this.data('target') ? $($this.data('target')) : $this.parent(),
            type = $this.data('type') == 'ul' ? 'ul' : 'ol',
            heading = $this.data('heading') ? $this.data('heading') : '',
            appendh1 = $this.data('appendh1'),
            $table_wrapper = $('<div id="'+table_id+'" class="bst-content-table-wrapper"><div class="bst-content-table-heading">'+heading.replace('%h1%', $('h1').text())+'</div><'+type+' class="bst-content-table-gen"></'+type+'></div>'),
            $table = $table_wrapper.find('ol'),
            level = 1,
            $cur = null;
            $source.find((appendh1?'h1,':'')+'h2,h3,h4,h5,h6').each(function(index){
                var $el = $(this),
                dl = parseInt($el.prop("tagName").toLowerCase().replace("h","")),
                $link = $('<a href="#" class="bst-content-table-link">'+$el.text()+'</a>');
                $link.data('target', $el);
                if (dl == 1)
                    dl = 2;
                if (index && dl != 1)
                {
                    if (level < dl)
                    {
                        level++;
                        dl = level;
                        $cur = $('<li class="bst-content-table-row"><'+type+' class="bst-content-table-row-subitems"></'+type+'></li>').appendTo($cur.find(type));
                        $cur.prepend($link);
                    }
                    else if (level > dl)
                    {
                        for (var i = level; i > dl; i--)
                        {
                            $cur = $cur.parent().closest('li');
                        }
                        $cur = $('<li class="bst-content-table-row"><'+type+' class="bst-content-table-row-subitems"></'+type+'></li>').appendTo($cur.closest(type));
                        $cur.prepend($link);
                    }
                    else
                    {
                        $cur = $('<li class="bst-content-table-row"><'+type+' class="bst-content-table-row-subitems"></'+type+'></li>').appendTo($cur.closest(type));
                        $cur.prepend($link);
                    }
                }
                else
                {
                    $cur = $('<li class="bst-content-table-row"><'+type+' class="bst-content-table-row-subitems"></'+type+'></li>').appendTo($table);
                    $cur.prepend($link);
                }
                level = dl;
            });
            $table_wrapper.prepend($this.html());
            $target.prepend($table_wrapper).removeClass('bst-content-table-target');
            $this.remove();
        });
        $('.bst-content-table-link:not(.wsko-init)').addClass('wsko-init').click(function(event){
            event.preventDefault();
            var $this = $(this);
            
            $(window.wsko_get_scroll_parent($this.get(0))).animate({
                scrollTop: $this.data('target').offset().top
            }, 500);
        });
    };

    jQuery(document).ready(function($) {
        window.wsko_frontend_reinit();
    });
}