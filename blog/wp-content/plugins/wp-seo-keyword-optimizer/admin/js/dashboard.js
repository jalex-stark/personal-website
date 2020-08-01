jQuery(document).ready(function($)
{
    $('.wsko-head-expand').click(function(){
        event.preventDefault();        
        
        $(this).hide();
        $('.wsko-dashboard-head.wsko-dashboard-head-collapsed').removeClass('wsko-dashboard-head-collapsed');
        $('.wsko-dashboard-head-wrapper.collapsed').removeClass('collapsed');
    });

    //Dashboard Hero Scrolling Buttons
    var step = 125;
    var scrolling = false;
    // Wire up events for the 'scrollUp' link:
    $(".wsko-dashboard-head-wrapper .wsko-left").on("click", function (event) {
        // Animates the scrollTop property by the specified
        // step.
        $(".wsko-dashboard-head-wrapper .wsko-dashboard-head").animate({
            scrollLeft: "-=" + step + "px"
        });
        event.preventDefault();
    })
    $(".wsko-dashboard-head-wrapper .wsko-right").on("click", function (event) {
        $(".wsko-dashboard-head-wrapper .wsko-dashboard-head").animate({
            scrollLeft: "+=" + step + "px"
        });
        event.preventDefault();
    })
    function scrollContent(direction) {
        var amount = (direction === "up" ? "-=1px" : "+=1px");
        $(".wsko-dashboard-head-wrapper .wsko-dashboard-head").animate({
            scrollLeft: amount
        }, 1, function () {
            if (scrolling) {
                scrollContent(direction);
            }
        });
    }
    $(".wsko-dashboard-head-wrapper .wsko-dashboard-head").scroll( function() {
        $(".wsko-dashboard-head-wrapper .wsko-right.wsko-animate").removeClass('wsko-animate');

        //toggle-controls
        var $this = $(this);
        if ( $this.scrollLeft() == 0 ) {
            $(".wsko-dashboard-head-wrapper .wsko-left").fadeOut();
        } else {
            $(".wsko-dashboard-head-wrapper .wsko-left").fadeIn();
        }    

        if (($this.width() + $this.scrollLeft()+10) >= $this.get(0).scrollWidth) {
            $(".wsko-dashboard-head-wrapper .wsko-right").fadeOut();
        }
        else
        $(".wsko-dashboard-head-wrapper .wsko-right").fadeIn();
       // if ( $(this).scrollLeft() + $(".wsko-dashboard-head-wrapper .wsko-dashboard-head").width() == $(this).width() ) {
       // }
    });

    $('.wsko-dashboard-page-widget').each(function(index){
        var $this = $(this),
        objData = $this.data(),
        data = {widget:true},
        controller = $this.data('controller'),
        subpage = $this.data('subpage');
        $.each(objData, function(k, v) {
            if (k.startsWith('wskoPost'))
                data[k.substr(8).toLowerCase()] = v;
        });
        window.wsko_load_lazy_page_widget(controller, subpage, $this.data('subtab'), data, function(res){
            if (res.success)
            {
                $this.html(res.view).prepend(res.notifications);
                window.wsko_load_lazy_data_for_controller($this, controller, subpage, 'page_data', {widget:true}, false);
                $('#wsko_admin_view_script_wrapper').append(res.scripts);
                //Update view
                /*$('.wsko-admin-main-navbar-sub-panel').removeClass('wsko-active');
                $('.wsko-admin-main-navbar-item').removeClass('wsko-active');
                $('.wsko-admin-main-navbar-item[data-link="'+controller+'"]').addClass('wsko-active').find('.wsko-admin-main-navbar-sub-panel').addClass('wsko-active');
                
                $('.wsko-admin-main-sub-navbar-item').removeClass('wsko-active');
                $('.wsko-admin-main-sub-navbar-item[data-link="'+controller+"_"+subpage+'"]').addClass('wsko-active');
                
                $('#wsko_main_title').html(res.title);
                $('#wsko_main_breadcrumb').html(res.breadcrumb);
                $('#wsko_help_breadcrumb').html(res.breadcrumb);
                
                $('#wsko_admin_view_header_wrapper').html(res.header);
                $('#wsko_admin_view_notification_wrapper').html(res.notif);
                $('#wsko_admin_view_wrapper').html(res.view);
                $('#wsko_admin_view_content_footer_wrapper').html(res.footer);
                $('#wsko_admin_view_script_wrapper').html(res.scripts);
                
                $('#wsko_admin_ajax_notifications').html("");
                $('html').scrollTop(0);
                if (is_reload)
                {
                    $.each(tab_states, function(k, v){
                        if (v.active)
                        {
                            $('.nav-tabs a[href="#' + v.id + '"]').closest('li').addClass('active');
                            $('#' + v.id).addClass('in active');
                        }
                        else
                        {
                            $('.nav-tabs a[href="#' + v.id + '"]').closest('li').removeClass('active');
                            $('#' + v.id).removeClass('in active');
                        }
                    });
                }
                window.wsko_reload_help_content();*/
            }
            return true;
        }, function() {

        });
    });
});    