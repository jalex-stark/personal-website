jQuery(document).ready(function($){
    var is_switching = false;
    $('.wsko-setup-control:not(.wsko_init)').addClass('wsko_init').click(function(event){
        event.preventDefault();
        var $this = $(this);
        if (!$this.attr('disabled'))
        {
            var action = $this.data('action'),
            $current = $('.wsko-setup-slide.wsko-setup-slide-active'),
            curr = parseInt($current.data('slide'));
            if (action == 'next' || action == 'prev')
            {
                if (!is_switching)
                {
                    var right = false;
                    if (action == 'next')
                    {
                        right = true;
                        $("#progressbar li").eq(curr).addClass("active");
                        curr++;
                    }
                    else
                    {
                        curr--;
                        $("#progressbar li").eq(curr).removeClass("active");
                    }

                    var $next = $('.wsko-setup-slide[data-slide="'+curr+'"]');
                    if ($next.length)
                    {
                        is_switching = true;
                        $current.hide('slide',{direction:right?'left':'right'},250,function(){
                            $current.removeClass('wsko-setup-slide-active');
                            $next.show('slide',{direction:right?'right':'left'},250,function(){
                                $next.addClass('wsko-setup-slide-active');
                                //wsko_update_controls();
                                is_switching = false;
                            });
                        });
                    }
                }
            }
        }
    });

    /*
    function wsko_update_controls()
    {
        var $active = $('.wsko-setup-slide.wsko-setup-slide-active');
        var curr = parseInt($active.data('slide'));
        console.log($('.wsko-setup-slide[data-slide="'+(curr-1)+'"]').length);
        if ($('.wsko-setup-slide[data-slide="'+(curr-1)+'"]').length == 0)
        {
            $('.wsko-setup-control-prev').hide();
            $('.wsko-setup-control-next').show();
            $('.wsko-setup-control-finish').hide();            
        }
        else if ($('.wsko-setup-slide[data-slide="'+(curr+1)+'"]').length == 0)
        {
            $('.wsko-setup-control-prev').show();
            $('.wsko-setup-control-next').hide();
            $('.wsko-setup-control-finish').show();            
        }
        else
        {
            $('.wsko-setup-control-prev').attr('disabled', false);
            $('.wsko-setup-control-next').attr('disabled', false);
            $('.wsko-setup-control-finish').hide();
        }
    }
    wsko_update_controls(); */
  
});