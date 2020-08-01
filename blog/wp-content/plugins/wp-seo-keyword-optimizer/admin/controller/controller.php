<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller
{
    /*Options*/
    //string
    public  $icon = "" ;
    //string
    public  $link = "" ;
    //array
    public  $subpages = array() ;
    //array
    public  $subpages_ext = array() ;
    //array
    public  $scripts = array() ;
    //array
    public  $styles = array() ;
    //array
    public  $ajax_actions = array() ;
    //string
    public  $template_folder = "" ;
    //string
    /*public $template_main = "";
    	//string
    	public $template_header = "";
    	//string
    	public $template_notifications = "";
    	//string
    	public $template_footer = "";*/
    //bool
    public  $is_main = false ;
    //bool
    public  $show_in_setup = false ;
    //bool
    public  $admin_only = false ;
    //bool
    public  $is_real_page = true ;
    //bool
    public  $is_invisible_page = false ;
    //int
    public  $nav_prio = 0 ;
    //bool
    public  $has_main_nav_link = true ;
    //bool
    public  $has_main_frame = true ;
    //array
    public  $tour = array() ;
    /*/*/
    public  $subpage ;
    public static  $main_controller = null ;
    public static  $controllers = array() ;
    /*Controls*/
    public static function init_controller( $add = true )
    {
        $inst = static::get_instance();
        $inst->add_actions();
        if ( $add ) {
            static::$controllers[] = $inst;
        }
        if ( !static::$main_controller ) {
            static::$main_controller = $inst;
        }
    }
    
    public function add_actions()
    {
        if ( !has_action( 'wp_ajax_wsko_get_attachment_popup' ) ) {
            add_action( 'wp_ajax_wsko_get_attachment_popup', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_load_lazy_data' ) ) {
            add_action( 'wp_ajax_wsko_load_lazy_data', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_load_lazy_page' ) ) {
            add_action( 'wp_ajax_wsko_load_lazy_page', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_get_help_content' ) ) {
            add_action( 'wp_ajax_wsko_get_help_content', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_set_timespan' ) ) {
            add_action( 'wp_ajax_wsko_set_timespan', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_reset_timespan' ) ) {
            add_action( 'wp_ajax_wsko_reset_timespan', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_save_ajax_input' ) ) {
            add_action( 'wp_ajax_wsko_save_ajax_input', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_resolve_url' ) ) {
            add_action( 'wp_ajax_wsko_resolve_url', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_get_content_optimizer' ) ) {
            add_action( 'wp_ajax_wsko_get_content_optimizer', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_import_plugin' ) ) {
            add_action( 'wp_ajax_wsko_import_plugin', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_send_feedback' ) ) {
            add_action( 'wp_ajax_wsko_send_feedback', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_send_nps_feedback' ) ) {
            add_action( 'wp_ajax_wsko_send_nps_feedback', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_skip_nps_feedback' ) ) {
            add_action( 'wp_ajax_wsko_skip_nps_feedback', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_discard_notice' ) ) {
            add_action( 'wp_ajax_wsko_discard_notice', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_controller_tour_ended' ) ) {
            add_action( 'wp_ajax_wsko_controller_tour_ended', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_search_posts_selectpicker' ) ) {
            add_action( 'wp_ajax_wsko_search_posts_selectpicker', array( $this, 'handle_action' ) );
        }
        if ( !has_action( 'wp_ajax_wsko_get_ranking_deltas' ) ) {
            add_action( 'wp_ajax_wsko_get_ranking_deltas', array( $this, 'handle_action' ) );
        }
        if ( $this->ajax_actions ) {
            foreach ( $this->ajax_actions as $action ) {
                if ( !has_action( 'wp_ajax_wsko_' . $action ) ) {
                    add_action( 'wp_ajax_wsko_' . $action, array( $this, 'handle_action' ) );
                }
            }
        }
    }
    
    public function handle_action()
    {
        $action = sanitize_text_field( $_POST['action'] );
        $action = substr( $action, strlen( 'wsko_' ), strlen( $action ) );
        $func = 'action_' . $action;
        
        if ( method_exists( $this, $func ) ) {
            $res = $this->{$func}();
            
            if ( $res === true ) {
                wp_send_json( array(
                    'success' => true,
                ) );
                wp_die();
            } else {
                
                if ( !$res && !is_array( $res ) ) {
                    
                    if ( WSKO_Class_Core::is_demo() ) {
                        wp_send_json( array(
                            'success' => false,
                            'msg'     => __( 'Please note: Editing is disabled in the Demo', 'wsko' ),
                        ) );
                        wp_die();
                    } else {
                        wp_send_json( array(
                            'success' => false,
                        ) );
                        wp_die();
                    }
                
                } else {
                    wp_send_json( $res );
                    wp_die();
                }
            
            }
        
        }
        
        wp_send_json( array(
            'success' => false,
        ) );
        wp_die();
    }
    
    public function can_execute_action( $high_cap = true, $no_ajax = false )
    {
        
        if ( $no_ajax ) {
            if ( WSKO_Class_Helper::check_user_permissions( $high_cap ) ) {
                return true;
            }
        } else {
            $real_action = sanitize_text_field( $_POST['action'] );
            
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && $real_action && substr( $real_action, 0, strlen( 'wsko_' ) ) == 'wsko_' ) {
                $action = substr( $real_action, strlen( 'wsko_' ), strlen( $real_action ) );
                if ( $action && ($this->ajax_actions && in_array( $action, $this->ajax_actions ) || in_array( $action, array(
                    'get_attachment_popup',
                    'load_lazy_data',
                    'load_lazy_page',
                    'get_help_content',
                    'set_timespan',
                    'reset_timespan',
                    'save_ajax_input',
                    'resolve_url',
                    'get_content_optimizer',
                    'import_plugin',
                    'send_feedback',
                    'send_nps_feedback',
                    'skip_nps_feedback',
                    'discard_notice',
                    'controller_tour_ended',
                    'search_posts_selectpicker',
                    'get_ranking_deltas'
                ) )) ) {
                    if ( WSKO_Class_Helper::check_user_permissions( $high_cap ) ) {
                        if ( wp_verify_nonce( $_POST['nonce'], $real_action ) ) {
                            return true;
                        }
                    }
                }
            }
        
        }
        
        return false;
    }
    
    public function add_menu_page( $callback )
    {
        $is_configured = WSKO_Class_Core::is_configured();
        if ( $this->is_real_page && static::$main_controller ) {
            $hook = add_submenu_page(
                ( !$this->is_invisible_page && ($is_configured && !$this->show_in_setup || !$is_configured && $this->show_in_setup) ? static::$main_controller->get_link( false, true ) : null ),
                static::get_title_s( true, true ),
                static::get_title_s(),
                ( $this->admin_only ? 'manage_options' : 'edit_posts' ),
                static::get_link( false, true ),
                $callback
            );
        }
    }
    
    public function set_subpage( $page )
    {
        $subpages = $this->get_subpages();
        
        if ( !$page && $subpages && is_array( $subpages ) ) {
            reset( $subpages );
            $page = key( $subpages );
        }
        
        
        if ( $page && $subpages && is_array( $subpages ) && isset( $subpages[$page] ) ) {
            $this->subpage = array(
                'key' => $page,
            ) + $subpages[$page];
            return true;
        }
        
        return false;
    }
    
    public function get_current_subpage( $return_obj = false )
    {
        $subpages = $this->get_subpages();
        
        if ( $subpages && is_array( $subpages ) ) {
            $subpage = $this->subpage;
            
            if ( !$subpage ) {
                reset( $subpages );
                $key = key( $subpages );
                $subpage = $subpages[$key];
                $subpage['key'] = $key;
            }
            
            if ( $subpage ) {
                return ( $return_obj ? $subpage : $subpage['key'] );
            }
        }
        
        return false;
    }
    
    /*/*/
    /*View*/
    public function redirect()
    {
        //abstract
    }
    
    public function custom_view()
    {
        //abstract
        return false;
    }
    
    public function get_title()
    {
        //abstract
        return __( 'No Title', 'wsko' );
        //$this->title;
    }
    
    public function get_subpage_title( $subpage )
    {
        //abstract
        return __( 'No Title', 'wsko' );
    }
    
    public function get_breadcrumb()
    {
        $breadcrumb = $this->get_breadcrumb_title();
        
        if ( $breadcrumb ) {
            return $breadcrumb;
        } else {
            if ( $this->subpage && is_array( $this->subpage ) ) {
                return $this->get_title() . " <i class='fa fa-angle-right fa-fw'></i> " . $this->get_subpage_title( $this->subpage['key'] );
            }
        }
        
        return "";
    }
    
    public function get_breadcrumb_title()
    {
        return false;
    }
    
    public function render_head_scripts()
    {
        ?><script type="text/javascript"><?php 
        
        if ( WSKO_Class_Core::is_demo() && function_exists( 'wsko_add_demo_tracking' ) ) {
            wsko_add_demo_tracking();
        } else {
            
            if ( WSKO_Class_Core::is_opted_in() ) {
                ?>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
			ga('create', 'UA-64850400-8', 'auto', 'wskoTracker');
			ga('wskoTracker.set', 'appName', 'BAVOKO SEO Tools');
			ga('wskoTracker.set', 'appId', 'bst');
			ga('wskoTracker.set', 'appVersion', '<?php 
                echo  WSKO_VERSION ;
                ?>');<?php 
            }
        
        }
        
        $mail = get_option( 'admin_email' );
        $user = '';
        $fs_user = wsko_fs()->get_user();
        
        if ( $fs_user ) {
            $mail = $fs_user->email;
            $user = $fs_user->first . ' ' . $fs_user->last;
        }
        
        $loc = WSKO_Class_Core::get_plugin_language( false, true );
        ?>
		document.tidioIdentify = {
				email: '<?php 
        echo  $mail ;
        ?>',
				name: '<?php 
        echo  $user ;
        ?>',
				tags: ['Plugin User']
		};
		document.tidioChatLang = '<?php 
        echo  $loc ;
        ?>';
		</script>
		<script src="//code.tidio.co/uyhzpy3j6sfvja6jpdozapgvdv6houpb.js"></script>
		<script type="text/javascript">
			if (typeof(tidioChatApi) != 'undefined')
			{
				tidioChatApi.on('ready', function(){
					$('#tidio-chat iframe').get(0).contentWindow.document.body.onclick = function() { if ($('.wsko-help-wrapper:visible').length) $('.wsko-toggle-help').first().click(); };
				});
			}
			else
				console.log('bst chat unavailable');
		</script><?php 
    }
    
    public function enqueue_scripts()
    {
        $times = $this->get_cached_var( 'timespan', true );
        $script_data = array(
            'ajaxurl'                => admin_url( 'admin-ajax.php' ),
            'controller'             => $this->get_link( false, true ),
            'subpage'                => ( $this->subpage ? $this->subpage['key'] : '' ),
            'timespan_start'         => $times['start'],
            'timespan_end'           => $times['end'],
            'get_help_content_nonce' => wp_create_nonce( 'wsko_get_help_content' ),
            'save_ajax_input_nonce'  => wp_create_nonce( 'wsko_save_ajax_input' ),
            'ssl_enabled'            => is_ssl(),
        );
        wp_enqueue_script(
            'wsko_admin_js',
            WSKO_PLUGIN_URL . 'admin/js/admin.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array(),
            WSKO_VERSION
        );
        wp_localize_script( 'wsko_admin_js', 'wsko_admin_data', $script_data );
        wp_enqueue_script(
            'wsko_kb_js',
            WSKO_PLUGIN_URL . 'admin/js/knowledge-base.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array(),
            WSKO_VERSION
        );
    }
    
    public function enqueue_styles()
    {
        wp_enqueue_style(
            'wsko_old_admin_css',
            WSKO_PLUGIN_URL . 'admin/css/old-admin.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        //TODO: remove
        wp_enqueue_style(
            'wsko_admin_css',
            WSKO_PLUGIN_URL . 'admin/css/admin.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_kb_css',
            WSKO_PLUGIN_URL . 'admin/css/knowledge.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_responsive_css',
            WSKO_PLUGIN_URL . 'admin/css/responsive.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_normalize_css',
            WSKO_PLUGIN_URL . 'admin/css/normalize.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
    }
    
    public function notifications( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        WSKO_Class_Template::render_template( 'controller/template-global-notifications.php', array() );
        if ( $this->template_folder ) {
            WSKO_Class_Template::render_template( $this->template_folder . '/notifications.php', array(), false );
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function get_header_widget( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        WSKO_Class_Template::render_template( 'controller/template-global-header.php', array(
            'controller' => $this->link,
            'subpage'    => $this->get_current_subpage( false ),
        ), false );
        if ( $this->template_folder ) {
            WSKO_Class_Template::render_template( $this->template_folder . '/header.php', array(
                'subpage' => $this->get_current_subpage( false ),
            ), false );
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function view( $widget = false, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( $this->template_folder ) {
            $inst = static::get_instance();
            $subpage = $inst->get_current_subpage();
            
            if ( $inst->is_accessible( $subpage ) ) {
                
                if ( WSKO_Class_Template::template_exists( $this->template_folder . '/frame.php' ) ) {
                    WSKO_Class_Template::render_template( $this->template_folder . '/frame.php', array(
                        'widget' => $widget,
                    ) );
                } else {
                    $inst->subpage_view( $widget );
                }
            
            } else {
                WSKO_Class_Template::render_template( $this->template_folder . '/frame-empty.php', array(
                    'widget'  => $widget,
                    'subpage' => $subpage,
                ) );
            }
        
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function subpage_view( $widget = false, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $subpages = $this->get_subpages();
        
        if ( $subpages && is_array( $subpages ) ) {
            $subpage = $this->get_current_subpage( true );
            if ( $subpage ) {
                WSKO_Class_Template::render_template( $subpage['template'], array(
                    'controller' => $this,
                    'widget'     => $widget,
                ) );
            }
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function content_footer( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        if ( $this->template_folder ) {
            WSKO_Class_Template::render_template( $this->template_folder . '/footer.php', array(), false );
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function get_scripts( $widget = false, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( !$widget ) {
            $inst = static::get_instance();
            ?><script type="text/javascript" >
			<?php 
            if ( WSKO_Class_Core::is_opted_in() ) {
                
                if ( $inst && $inst->link ) {
                    $subpage = $inst->get_current_subpage();
                    $page = '/' . $inst->link . (( $subpage ? '/' . $subpage : '' ));
                    ?>ga('wskoTracker.set', 'page', '<?php 
                    echo  $page ;
                    ?>');
						ga('wskoTracker.set', 'screenName', '<?php 
                    echo  $page ;
                    ?>');
						ga('wskoTracker.send', 'screenview');
						ga('wskoTracker.send', 'pageview');<?php 
                }
            
            }
            ?>
			jQuery(document).ready(function($){
				wsko_admin_data.timespan_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_set_timespan' ) ;
            ?>';
				wsko_admin_data.resolve_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_resolve_url' ) ;
            ?>';
				wsko_admin_data.lazy_page_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_load_lazy_page' ) ;
            ?>';
				wsko_admin_data.get_attachment_popup_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_get_attachment_popup' ) ;
            ?>';
				wsko_admin_data.get_help_content_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_get_help_content' ) ;
            ?>';
				wsko_admin_data.save_ajax_input_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_save_ajax_input' ) ;
            ?>';
				wsko_admin_data.feedback_nonce = '<?php 
            echo  wp_create_nonce( 'wsko_send_feedback' ) ;
            ?>';
			});
			</script><?php 
        }
        
        if ( $this->scripts && is_array( $this->scripts ) ) {
            foreach ( $this->scripts as $script ) {
                ?><script type="text/javascript" src="<?php 
                echo  WSKO_PLUGIN_URL . 'admin/js/' . $script . '.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js?ver=' . WSKO_VERSION ;
                ?>"></script><?php 
            }
        }
        if ( $this->styles && is_array( $this->styles ) ) {
            foreach ( $this->styles as $style ) {
                ?><link href="<?php 
                echo  WSKO_PLUGIN_URL . 'admin/css/' . $style . '.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css?ver=' . WSKO_VERSION ;
                ?>" rel="stylesheet" type="text/css"></link><?php 
            }
        }
        
        if ( !$widget ) {
            ?><script type="text/javascript">
			jQuery(document).ready(function($){
				window.wsko_load_lazy_data($('#wsko_admin_view_content_wrapper'), 'page_data', false, false);
				<?php 
            if ( $this->uses_timespan ) {
                ?> 
				$(document).on("wsko_event_timespan_set", function(e){
					window.wsko_load_lazy_data($('#wsko_admin_view_content_wrapper'), 'page_data', false, true);
				});
				<?php 
            }
            ?>
				window.wsko_init_admin();
				<?php 
            $this->render_js_tour( false );
            ?>
			});
			</script><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public function render_js_tour( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $tour = $this->build_js_tour();
        ?>
		window.wsko_open_controller_tour = function()
		{ 
			<?php 
        /*if ($tour) { //TODO: Tour ?>
        				$('body').itour({
        					tourTitle:'<?=$tour['title']?>',
        					<?=isset($tour['intro']) && $tour['intro'] ? 'introShow:true,' : ''?>
        					<?=isset($tour['intro_cover']) && $tour['intro_cover'] ? 'introCover:"'.$tour['intro_cover'].'",' : ''?>
        					<?=isset($tour['hide_map']) && $tour['hide_map'] ? '' : 'tourMapVisible:true,'?>
        					steps:[
        						<?php foreach ($tour['steps'] as $step)
        						{
        							?>{
        								title: '<?=$step['title']?>',
        								content: '<?=$step['content']?>',
        								name: '<?=$step['data_name']?>',
        							},<?php
        						} ?>],
        					end: function(){
        						window.wsko_post_element({action:'wsko_controller_tour_ended', tour:'<?=$tour['name']?>', nonce: '<?=wp_create_nonce('wsko_controller_tour_ended')?>'}, function(res){ return true; }, function(){ return true; }, false, false);
        					}
        				});
        			<?php
        		}*/
        ?>
		
			<?php 
        
        if ( WSKO_Class_Core::is_configured() ) {
            $co_post = WSKO_Class_Helper::url_to_postid( home_url() );
            
            if ( !$co_post ) {
                $co_post = WSKO_Class_Helper::get_random_post();
                if ( $co_post ) {
                    $co_post = $co_post->ID;
                }
            }
            
            ?>
			$('body').itour({
				CSSClass:'anyClassName',				//Assign for tour a unique class name to change the display styles of the tour.
				tourID:'wsko-general-tour',						//This string allows you to save data with a unique name about  tour progress. It can be used to save information on the progress of the tour for several users. Or save the progress of each tour separately
				introShow:true,						//If set to true, before the tour you will see the introductory slide, which will offer to see a tour.
				introCover:false,						//Path to the cover of tour
				startStep:1,							//Step from which the tour begins
				tourMapEnable:true,						//Tour Map Enable
				tourMapPos:'right',						//Tour Map Position 
				tourMapJump:true,						//If set to false, then links of steps on the tour map will not be clickable
				tourTitle:'BAVOKO SEO Tools',			//Tour title
				tourMapVisible:false,					//Specifies to show or hide the map of the tour at the start of the tour
				spacing:10,								//Indent highlighting around the element
				overlayClickable:true,					//This parameter enables or disables the click event for overlying layer
				modalCancelVisible:true,				//Shows a cancel button in modal window.
				stepNumbersVisible:true,				//Shows the total number of steps and the current step number
				showAbsentElement:false,				//Shows an absent element in tour map and find this element in DOM.
				tourContinue:true,						//This parameter add the ability to continue the unfinished tour.
				textDirection:'ltr',					//The direction property specifies the text direction/writing direction. (ltr, rtl)
				lang: {									//Default language settings
					cancelText:	'<?php 
            echo  __( 'Cancel Tour', 'wsko' ) ;
            ?>',			//The text in the cancel tour button
					hideText: '<?php 
            echo  __( 'Hide Tour Map', 'wsko' ) ;
            ?>',			//The text in the hidden tour map button 
					tourMapText:'•••',					//The text in the show tour button
					tourMapTitle: '<?php 
            echo  __( 'Tour Map', 'wsko' ) ;
            ?>',			//Title of Tour map button
					nextTextDefault:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',				//The text in the Next Button
					prevTextDefault:'<?php 
            echo  __( 'Prev', 'wsko' ) ;
            ?>',				//The text in the Prev Button
					endText:'<?php 
            echo  __( 'End Tour', 'wsko' ) ;
            ?>',					//sets the text for the close button in the last step of the tour
					contDialogTitle:'<?php 
            echo  __( 'Continue the unfinished tour?', 'wsko' ) ;
            ?>',										//Title of continue dialog
					contDialogContent:'<?php 
            echo  __( 'Click "Continue" to start with step on which finished last time.', 'wsko' ) ;
            ?>',	//Content of continue dialog
					contDialogBtnBegin:'<?php 
            echo  __( 'Start from beginning', 'wsko' ) ;
            ?>',												//Text in the start button of continue dialog 
					contDialogBtnContinue:'<?php 
            echo  __( 'Continue', 'wsko' ) ;
            ?>',														//Text in the continue button of continue dialog 
					<?php 
            
            if ( WSKO_Class_Core::is_demo() ) {
                ?>
						introTitle:'<?php 
                echo  wsko_loc( 'tour', 'intro_title_demo' ) ;
                ?>', 											//Title of introduction dialog
						introContent:"<?php 
                echo  wsko_loc( 'tour', 'intro_content_demo' ) ;
                ?>",				//Content of introduction dialog
					<?php 
            } else {
                ?>
						introTitle:'<?php 
                echo  wsko_loc( 'tour', 'intro_title' ) ;
                ?>', 											//Title of introduction dialog
						introContent:"<?php 
                echo  wsko_loc( 'tour', 'intro_content' ) ;
                ?>",				//Content of introduction dialog
					<?php 
            }
            
            ?>
					introDialogBtnStart:'<?php 
            echo  __( 'Start', 'wsko' ) ;
            ?>',															//Text in the start button of introduction dialog
					introDialogBtnCancel:'<?php 
            echo  __( 'Cancel', 'wsko' ) ;
            ?>'															//Text in the cancel button of introduction dialog
				},
				steps:[
				{
					title:'<?php 
            echo  wsko_loc( 'tour', 'dashboard_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'dashboard_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_dashboard',
					event:'next',
					nextText:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',
					trigger:false
				},
				{
					title:'<?php 
            echo  wsko_loc( 'tour', 'search_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'search_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_search',
					event:'next',
					nextText:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',
					trigger:false
				},
				{
					title:'<?php 
            echo  wsko_loc( 'tour', 'onpage_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'onpage_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_onpage',
					event:'next',
					nextText:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',
					trigger:false
				},
				{
					title:'<?php 
            echo  wsko_loc( 'tour', 'backlinks_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'backlinks_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_backlinks',
					event:'next',
					nextText:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',
					trigger:false
				},
				{
					title:'<?php 
            echo  wsko_loc( 'tour', 'performance_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'performance_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_performance',
					event:'next',
					nextText:'<?php 
            echo  __( 'Next', 'wsko' ) ;
            ?>',
					trigger:false
				},
				{
					image:'',
					title:'<?php 
            echo  wsko_loc( 'tour', 'tools_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'tools_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_tools',
					event:'next',
					nextText:'<?php 
            echo  ( $co_post ? __( 'Next', 'wsko' ) : __( 'End Tour', 'wsko' ) ) ;
            ?>',					//The text in the Next Button
					trigger:false,
				},
				{
					image:'',
					title:'<?php 
            echo  wsko_loc( 'tour', 'workflow_title' ) ;
            ?>',
					content:'<?php 
            echo  wsko_loc( 'tour', 'workflow_content' ) ;
            ?>',
					contentPosition:'auto',
					name:'wsko_tools',
					event:'next',
					stepID:'wsko_tour_pre_co',	
					nextText:'<?php 
            echo  ( $co_post ? '<a href="#" class="wsko-content-optimizer-link wsko-tour-co"><small class="wsko-content-optimizer-link-text">' . __( 'CO', 'wsko' ) . '</small></a>' : __( 'End Tour', 'wsko' ) ) ;
            ?>',					//The text in the Next Button
					prevText:'',
					trigger:false,
				}
				<?php 
            
            if ( $co_post ) {
                ?>
				,{
					title:'<?php 
                echo  wsko_loc( 'tour', 'co_title' ) ;
                ?>',
					content:'<?php 
                echo  wsko_loc( 'tour', 'co_content' ) ;
                ?>',
					contentPosition:'auto',
					name:'wsko_content_optimizer',
					event:'next',
					nextText:'<?php 
                echo  __( 'Next', 'wsko' ) ;
                ?>',
					trigger:false,
					before:function(){ window.wsko_open_optimizer_modal(<?php 
                echo  $co_post ;
                ?>, 'post_id', ''); },
					after:function(){$('#wsko_content_optimizer_modal .wsko-modal-close').click(); },
				}
			<?php 
            }
            
            ?>										//Text in the continue button of continue dialog 
				<?php 
            
            if ( WSKO_Class_Core::is_demo() ) {
                ?>
				,{
					image:'',
					title:'<?php 
                echo  wsko_loc( 'tour', 'end_title_demo' ) ;
                ?>',
					content:'<?php 
                echo  wsko_loc( 'tour', 'end_content_demo', array(
                    'kb_link' => 'https://www.bavoko.tools/knowledge_base/bst-wp-seo-plugin-tutorial/',
                ) ) ;
                ?>',
					contentPosition:'auto',
					name:'wsko_logo',
					event:'next',
					nextText:'<?php 
                echo  __( 'End Tour', 'wsko' ) ;
                ?>',					//The text in the Next Button
					trigger:false,
				}
				<?php 
            } else {
                ?>
				,{
					image:'',
					title:'<?php 
                echo  wsko_loc( 'tour', 'end_title' ) ;
                ?>',
					content:'<?php 
                echo  wsko_loc( 'tour', 'end_content', array(
                    'kb_link' => 'https://www.bavoko.tools/knowledge_base/bst-wp-seo-plugin-tutorial/',
                ) ) ;
                ?>',
					contentPosition:'auto',
					name:'wsko_logo',
					event:'next',
					nextText:'<?php 
                echo  __( 'End Tour', 'wsko' ) ;
                ?>',					//The text in the Next Button
					trigger:false,
				}
				<?php 
            }
            
            /*{
            			image:'',							//Path to image file
            			title:'SEO Tools',				//Name of step
            			content:'Here you will find various SEO tools for metas, redirects, permalinks, sitemaps and much more. We recommend you to carefully review all settings in this area after this tour and take a closer look at the corresponding help texts in our knowledge base.',		//Description of step
            			contentPosition:'auto',				//Position of message
            			name:'wsko_tools',					//Unique Name (<div data-name="uniqueName"></div>) of highlighted element or .className (<div class="className"></div>) or #idValue (<div id="idValue"></div>)
            			disable:false,						//Block access to element
            			overlayOpacity:0.5,					//For each step, you can specify the different opacity values of the overlay layer.
            			event:'next',						//An event that you need to do to go to the next step
            			skip: false,						//Step can be skipped if you set parameter "skip" to true.
            			nextText:'Next',					//The text in the Next Button
            			prevText:'Prev',					//The text in the Prev Button
            			trigger:false,						//An event which is generated on the selected element, in the transition from step to step
            			stepID:'',							//Unique ID Name. This name is assigned to the "html" tag as "data-step" attribute (If not specified, the plugin generates it automatically in the form: "step-N")
            			loc:false,							//The path to the page on which the step should work
            			before:function(){},				//Triggered before the start of step
            			during:function(){},				//Triggered after the onset of step
            			after:function(){},					//Triggered After completion of the step, but before proceeding to the next
            			delayBefore:0,						//The delay before the element search, ms
            			delayAfter:0						//The delay before the transition to the next step, ms
            		}*/
            ?>],
				create: function(){},					//Triggered when the itour is created
				end: function(){
					window.wsko_post_element({action:'wsko_controller_tour_ended', tour:'global', nonce: '<?php 
            echo  wp_create_nonce( 'wsko_controller_tour_ended' ) ;
            ?>'}, function(res){ return true; }, function(){ return true; }, false, false);
				},						//Triggered when the tour ended, or was interrupted
				abort: function(){},					//Triggered when the tour aborted
				finish: function(){}					//Triggered when step sequence is over
				});<?php 
        }
        
        ?>
		}; <?php 
        
        if ( !WSKO_Class_Core::get_user_setting( 'tour_skipped_global' ) || WSKO_Class_Core::is_demo() && function_exists( 'wsko_get_tour_override' ) && wsko_get_tour_override() ) {
            //($tour && !$tour['skipped']) {
            ?>
			window.wsko_open_controller_tour();<?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    static  $current_tour_cache ;
    public function build_js_tour()
    {
        if ( static::$current_tour_cache ) {
            return static::$current_tour_cache;
        }
        $inst = static::get_instance();
        
        if ( $inst ) {
            $subpage = $inst->get_current_subpage();
            $con = $inst->_build_js_tour( $subpage );
            
            if ( $con ) {
                $con['name'] = $name = get_class( $inst ) . $subpage;
                $con['skipped'] = WSKO_Class_Core::get_user_setting( 'tour_skipped_' . $name );
                return static::$current_tour_cache = $con;
            }
        
        }
        
        return array();
    }
    
    public function _build_js_tour( $subpage )
    {
    }
    
    public function get_subpage_ext_link( $subpage )
    {
        //abstract
        return '';
    }
    
    public function get_help_content( $subpage )
    {
        $inst = static::get_instance();
        
        if ( $inst ) {
            $tags = $inst->get_knowledge_base_tags( $subpage );
            
            if ( $tags ) {
                ob_start();
                ?><p class="wsko-label-sm"><?php 
                echo  __( 'Knowledge Base', 'wsko' ) ;
                ?></h2><?php 
                $kb_articles = WSKO_Class_Knowledge::search_knowledge_base(
                    'kb',
                    false,
                    array(),
                    $tags
                );
                
                if ( $kb_articles ) {
                    ?> <ul> <?php 
                    foreach ( $kb_articles as $a ) {
                        ?><li class="panel panel-body"><a href="#" class="wsko-open-knowledge-base-article dark" data-article="<?php 
                        echo  $a->id ;
                        ?>" data-type="kb">
							<p><?php 
                        echo  $a->title ;
                        ?></p>
							<small class="text-off"><?php 
                        echo  $a->preview ;
                        ?></small>
						</a></li><?php 
                    }
                    ?> </ul> <?php 
                } else {
                    ?><ul><li><?php 
                    echo  __( 'No Articles found.', 'wsko' ) ;
                    ?></li></ul><?php 
                }
                
                ?><p class="wsko-label-sm"><?php 
                echo  __( 'Question & Answer', 'wsko' ) ;
                ?></p><?php 
                $qa_articles = WSKO_Class_Knowledge::search_knowledge_base(
                    'q_and_a',
                    false,
                    array(),
                    $tags
                );
                
                if ( $qa_articles ) {
                    ?> <ul> <?php 
                    foreach ( $qa_articles as $a ) {
                        ?><li class="panel panel-body"><a href="#" class="wsko-open-knowledge-base-article dark" data-article="<?php 
                        echo  $a->id ;
                        ?>" data-type="q_and_a">
							<p><?php 
                        echo  $a->title ;
                        ?></p>
							<small class="text-off"><?php 
                        echo  $a->preview ;
                        ?></small>
						</a></li><?php 
                    }
                    ?> </ul> <?php 
                } else {
                    ?><ul><li><?php 
                    echo  __( 'No Articles found.', 'wsko' ) ;
                    ?></li></ul><?php 
                }
                
                return ob_get_clean();
            }
        
        }
        
        return __( "No help available", 'wsko' );
    }
    
    public function get_knowledge_base_tags( $subpage )
    {
        return array();
    }
    
    static  $accessible_cache = array() ;
    public function is_accessible( $subpage = false )
    {
        $inst = static::get_instance();
        
        if ( method_exists( $inst, '_is_accessible' ) ) {
            $class = get_class( $inst ) . (( $subpage ? '_' . $subpage : '' ));
            if ( isset( static::$accessible_cache[$class] ) ) {
                return static::$accessible_cache[$class];
            }
            return static::$accessible_cache[$class] = $inst->_is_accessible( $subpage );
        }
        
        return true;
    }
    
    /*Actions*/
    public function action_load_lazy_data()
    {
        $controller = WSKO_Class_Core::get_current_controller();
        
        if ( $controller ) {
            $action = ( isset( $_POST['wsko_action'] ) ? sanitize_text_field( $_POST['wsko_action'] ) : false );
            $lazy_data = ( isset( $_POST['lazy_data'] ) ? $_POST['lazy_data'] : false );
            
            if ( $action && $action != 'page' ) {
                WSKO_Class_Helper::safe_set_time_limit( 60 );
                $func = 'load_lazy_' . $action;
                if ( method_exists( $controller, $func ) ) {
                    return $controller->{$func}( $lazy_data );
                }
            }
        
        }
        
        return false;
    }
    
    public function action_get_attachment_popup()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $attachment = ( isset( $_POST['attachment'] ) ? intval( $_POST['attachment'] ) : false );
        
        if ( $attachment ) {
            $view = get_media_item( $attachment );
            if ( $view ) {
                return array(
                    'success' => true,
                    'view'    => $view,
                );
            }
        }
    
    }
    
    public function action_load_lazy_page()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $controller = WSKO_Class_Core::get_current_controller();
        
        if ( $controller ) {
            $widget = ( isset( $_POST['widget'] ) && $_POST['widget'] ? true : false );
            $header = $controller->get_header_widget( true ) . '<div id="wsko_admin_view_timespan_wrapper" class="hidden-xs">';
            if ( $controller->uses_timespan( $controller->get_current_subpage() ) ) {
                $header .= WSKO_Class_Template::render_timespan_widget( true );
            }
            $header .= '</div>';
            return array(
                'success'    => true,
                'title'      => $controller->get_title(),
                'tab_title'  => $controller->get_title_s( true, true ),
                'breadcrumb' => $controller->get_breadcrumb(),
                'notif'      => $controller->notifications( true ),
                'view'       => $controller->view( $widget, true ),
                'scripts'    => $controller->get_scripts( $widget, true ),
                'footer'     => $controller->content_footer( true ),
                'header'     => $header,
            );
        }
    
    }
    
    public function action_get_help_content()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $view = false;
        $controller = WSKO_Class_Core::get_current_controller();
        if ( $controller ) {
            $view = $controller->get_help_content( $controller->get_current_subpage() );
        }
        return array(
            'success' => true,
            'view'    => $view,
        );
    }
    
    public function action_set_timespan()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $controller = WSKO_Class_Core::get_current_controller();
        
        if ( $controller->uses_timespan && isset( $_POST['start_time'] ) && $_POST['start_time'] && isset( $_POST['end_time'] ) && $_POST['end_time'] ) {
            $controller->set_timespan( intval( $_POST['start_time'] ), intval( $_POST['end_time'] ) );
            return true;
        }
    
    }
    
    public function action_reset_timespan()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $controller = WSKO_Class_Core::get_current_controller();
        
        if ( $controller->uses_timespan ) {
            $controller->set_timespan( 0, 0 );
            return true;
        }
    
    }
    
    public function action_save_ajax_input()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        
        if ( isset( $_POST['target'] ) && $_POST['target'] ) {
            $target = sanitize_text_field( $_POST['target'] );
            $deep = ( isset( $_POST['deep'] ) && $_POST['deep'] ? true : false );
            $val = ( isset( $_POST['val'] ) && $_POST['val'] ? ( $deep ? ( is_array( $_POST['val'] ) ? array_map( 'sanitize_text_field', $_POST['val'] ) : false ) : sanitize_text_field( $_POST['val'] ) ) : false );
            $setting = ( isset( $_POST['setting'] ) && $_POST['setting'] ? sanitize_text_field( $_POST['setting'] ) : false );
            switch ( $target ) {
                case 'settings':
                    
                    if ( $setting ) {
                        $set = ( $val && $val !== 'false' ? $val : false );
                        WSKO_Class_Core::save_setting( $setting, $set );
                        switch ( $setting ) {
                            case 'hide_category_slug':
                                if ( isset( $_POST['alert'] ) ) {
                                    
                                    if ( $_POST['alert'] && $_POST['alert'] != 'false' ) {
                                        WSKO_Class_Core::save_setting( 'hide_category_slug_redirect', true );
                                    } else {
                                        WSKO_Class_Core::save_setting( 'hide_category_slug_redirect', false );
                                    }
                                
                                }
                                break;
                        }
                        return true;
                    }
                    
                    break;
                case 'user_settings':
                    
                    if ( $setting ) {
                        $set = ( $val && $val !== 'false' ? $val : false );
                        if ( $setting == 'unstick_content_optimizer' ) {
                            $set = !$set;
                        }
                        WSKO_Class_Core::save_user_setting( $setting, $set );
                        /*switch($setting)
                        		{
                        			case 'backlinks_view_filter':
                        				$this->delete_cache();
                        			break;
                        		}*/
                        return true;
                    }
                    
                    break;
                case 'onpage_sitemap_post_exclude':
                    $post = ( $setting ? intval( $setting ) : false );
                    
                    if ( $post ) {
                        $set = ( $val && $val !== 'false' ? true : false );
                        
                        if ( $set ) {
                            $gen_params = WSKO_Class_Onpage::get_sitemap_params();
                            
                            if ( !isset( $gen_params['excluded_posts'] ) || !is_array( $gen_params['excluded_posts'] ) ) {
                                $gen_params['excluded_posts'] = array( $post );
                            } else {
                                if ( !in_array( $post, $gen_params['excluded_posts'] ) ) {
                                    $gen_params['excluded_posts'][] = $post;
                                }
                            }
                            
                            WSKO_Class_Onpage::set_sitemap_params( $gen_params );
                        } else {
                            $gen_params = WSKO_Class_Onpage::get_sitemap_params();
                            
                            if ( isset( $gen_params['excluded_posts'] ) && is_array( $gen_params['excluded_posts'] ) && ($key = array_search( $post, $gen_params['excluded_posts'] )) !== false ) {
                                unset( $gen_params['excluded_posts'][$key] );
                                WSKO_Class_Onpage::set_sitemap_params( $gen_params );
                            }
                        
                        }
                        
                        return true;
                    }
                    
                    break;
                case 'onpage_sitemap_term_exclude':
                    
                    if ( $setting ) {
                        $parts = WSKO_Class_Helper::safe_explode( ':', $setting );
                        
                        if ( count( $parts ) == 2 ) {
                            $term = get_term( $parts[1], $parts[0] );
                            
                            if ( $term ) {
                                $term = $term->taxonomy . ':' . $term->term_id;
                                $set = ( $val && $val !== 'false' ? true : false );
                                
                                if ( $set ) {
                                    $gen_params = WSKO_Class_Onpage::get_sitemap_params();
                                    
                                    if ( !isset( $gen_params['excluded_terms'] ) || !is_array( $gen_params['excluded_terms'] ) ) {
                                        $gen_params['excluded_terms'] = array( $term );
                                    } else {
                                        if ( !in_array( $term, $gen_params['excluded_terms'] ) ) {
                                            $gen_params['excluded_terms'][] = $term;
                                        }
                                    }
                                    
                                    WSKO_Class_Onpage::set_sitemap_params( $gen_params );
                                } else {
                                    $gen_params = WSKO_Class_Onpage::get_sitemap_params();
                                    
                                    if ( isset( $gen_params['excluded_terms'] ) && is_array( $gen_params['excluded_terms'] ) && ($key = array_search( $term, $gen_params['excluded_terms'] )) !== false ) {
                                        unset( $gen_params['excluded_terms'][$key] );
                                        WSKO_Class_Onpage::set_sitemap_params( $gen_params );
                                    }
                                
                                }
                                
                                return true;
                            }
                        
                        }
                    
                    }
                    
                    break;
                case 'onpage_analysis_post_exclude':
                    $post = ( $setting ? intval( $setting ) : false );
                    
                    if ( $post ) {
                        $set = ( $val && $val !== 'false' ? true : false );
                        $excluded_op = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_exclude_posts' ) );
                        
                        if ( $set ) {
                            $excluded_op[] = $post;
                            WSKO_Class_Core::save_setting( 'onpage_exclude_posts', implode( ',', $excluded_op ) );
                        } else {
                            
                            if ( ($key = array_search( $post, $excluded_op )) !== false ) {
                                unset( $excluded_op[$key] );
                                WSKO_Class_Core::save_setting( 'onpage_exclude_posts', implode( ',', $excluded_op ) );
                            }
                        
                        }
                        
                        return true;
                    }
                    
                    break;
                case 'api_search':
                    if ( $setting ) {
                        switch ( $setting ) {
                            case 'token':
                                $set = ( $val == 'false' ? false : $val );
                                WSKO_Class_Search::set_se_property( $set );
                                $this->delete_cache();
                                $ga_client_se = WSKO_Class_Search::get_ga_client_se();
                                $ga_token_se = WSKO_Class_Search::get_se_token();
                                $ga_config_se = WSKO_Class_Search::get_se_property();
                                $ga_valid_se = ( !WSKO_Class_Core::get_option( 'search_query_first_run' ) && $ga_token_se ? true : !WSKO_Class_Search::check_se_access( !WSKO_Class_Core::is_configured() ) );
                                $badge = WSKO_Class_Template::render_status_iconbar( array(
                                    array(
                                    'condition' => $ga_client_se,
                                    'text_t'    => __( 'API loaded', 'wsko' ),
                                    'text_f'    => __( 'API could not be loaded!', 'wsko' ),
                                ),
                                    array(
                                    'condition' => $ga_token_se,
                                    'text_t'    => __( 'Credentials provided', 'wsko' ),
                                    'text_f'    => __( 'No Credentials provided', 'wsko' ),
                                ),
                                    array(
                                    'condition' => $ga_config_se,
                                    'text_t'    => __( 'Profile selected', 'wsko' ),
                                    'text_f'    => __( 'No Profile selected', 'wsko' ),
                                ),
                                    array(
                                    'condition' => $ga_valid_se,
                                    'warning'   => ( $ga_config_se ? false : true ),
                                    'text_t'    => __( 'Permission granted', 'wsko' ),
                                    'text_f'    => ( $ga_config_se ? __( 'Credentials invalid or insufficient permissions', 'wsko' ) : __( 'Credentials will be verified, when a profile is set', 'wsko' ) ),
                                )
                                ), array(
                                    'class' => 'pull-right m10',
                                ), true );
                                $err_view = '';
                                $show_default_error = true;
                                if ( $set ) {
                                    
                                    if ( !$ga_valid_se ) {
                                        $is_owner_error = false;
                                        $has_property = false;
                                        $properties = WSKO_Class_Search::get_se_properties();
                                        if ( $properties ) {
                                            foreach ( $properties as $prop ) {
                                                
                                                if ( $prop['url'] === $set ) {
                                                    $has_property = true;
                                                    if ( $prop['access'] !== 'siteOwner' && $prop['access'] !== 'siteFullUser' ) {
                                                        $is_owner_error = true;
                                                    }
                                                }
                                            
                                            }
                                        }
                                        WSKO_Class_Search::set_se_token( false );
                                        
                                        if ( $has_property ) {
                                            
                                            if ( $is_owner_error ) {
                                                $err_view = WSKO_Class_Template::render_notification( 'error', array(
                                                    'msg' => wsko_loc( 'notif_search', 'no_owner_access' ),
                                                ), true );
                                                $show_default_error = false;
                                            }
                                        
                                        } else {
                                            $err_view = WSKO_Class_Template::render_notification( 'error', array(
                                                'msg' => wsko_loc( 'notif_search', 'no_site_access', array(
                                                'domain' => $set,
                                            ) ),
                                            ), true );
                                        }
                                    
                                    }
                                
                                }
                                if ( $show_default_error ) {
                                    $err_view .= WSKO_Class_Template::render_notification( 'warning', array(
                                        'msg'  => wsko_loc( 'notif_search', 'credentials_help' ),
                                        'list' => wsko_loc( 'notif_search', 'credentials_help_list', array(
                                        'home_url'     => home_url(),
                                        'host'         => WSKO_Class_Helper::get_host( true ),
                                        'howto_link'   => WSKO_Class_Search::get_external_link( 'how_to' ),
                                        'report_link'  => WSKO_Class_Search::get_external_link( 'support_report' ),
                                        'console_link' => WSKO_Class_Search::get_external_link( 'console' ),
                                    ) ),
                                    ), true );
                                }
                                return array(
                                    'success'          => true,
                                    'dynamic_elements' => array(
                                    '#wsko_search_api_badge'       => $badge,
                                    '#wsko_api_search_custom_help' => $err_view,
                                ),
                                );
                            default:
                                $set = ( $val == 'false' ? false : $val );
                                $se_data = WSKO_Class_Search::get_search_data();
                                $se_data[$setting] = $set;
                                WSKO_Class_Search::set_search_data( $se_data );
                                return true;
                        }
                    }
                    break;
                case 'api_premium':
                case 'api_analytics':
                    break;
            }
        }
    
    }
    
    public function action_resolve_url()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        
        if ( isset( $_POST['urls'] ) && $_POST['urls'] && is_array( $_POST['urls'] ) ) {
            $urls = array_map( 'sanitize_text_field', $_POST['urls'] );
            $url_res = array();
            $failed_urls = array();
            foreach ( $urls as $url ) {
                $res = WSKO_Class_Helper::url_get_title( $url );
                if ( !$res->resolved ) {
                    $failed_urls[] = $url;
                }
                $url_res[$url] = $res;
            }
            $eff_urls = WSKO_Class_Helper::get_effective_urls( $failed_urls );
            foreach ( $eff_urls as $o_url => $url ) {
                $url_res[$o_url] = WSKO_Class_Helper::url_get_title( $url );
            }
            return array(
                'success' => true,
                'urls'    => $url_res,
            );
        }
    
    }
    
    public function action_send_feedback()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $type = ( isset( $_POST['type'] ) ? intval( $_POST['type'] ) : false );
        $msg = ( isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : false );
        $title = ( isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : false );
        $email = ( isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : false );
        $name = ( isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : false );
        $append_reports = ( isset( $_POST['append_reports'] ) && $_POST['append_reports'] && $_POST['append_reports'] != 'false' ? true : false );
        
        if ( $msg && $title ) {
            $res = false;
            $title = 'BST: ' . $title;
            $msg .= '<br/>Von: ' . $name . ' (' . $email . ')';
            $headers = array( 'From: ' . (( $name ? $name : $email )) . ' <' . $email . '>', 'Content-Type: text/html; charset=UTF-8' );
            global  $wsko_mail_error_temp ;
            $wsko_mail_error_temp = false;
            add_action(
                'wp_mail_failed',
                function ( $wp_error ) {
                global  $wsko_mail_error_temp ;
                $wsko_mail_error_temp = $wp_error;
            },
                10,
                1
            );
            if ( $type == 1 ) {
                
                if ( $append_reports ) {
                    $rep = WSKO_Class_Helper::format_reports();
                    $msg .= "<br/><br/>---------------------------Reports---------------------------<br/><br/>" . $rep;
                }
            
            }
            $res = wp_mail(
                WSKO_SUPPORT_MAIL,
                $title,
                $msg,
                $headers
            );
            global  $wsko_mail_error_temp ;
            if ( $wsko_mail_error_temp ) {
                $res = false;
            }
            
            if ( $res ) {
                return true;
            } else {
                $help_notice = WSKO_Class_Template::render_notification( 'error', array(
                    'msg' => wsko_loc( 'notif', 'support_mail_fail' ),
                ), true );
                return array(
                    'success'     => false,
                    'msg'         => __( 'Mail could not be sent!', 'wsko' ),
                    'help_notice' => $help_notice,
                );
            }
        
        }
    
    }
    
    public function action_discard_notice()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $notice = ( isset( $_POST['notice'] ) ? sanitize_text_field( $_POST['notice'] ) : false );
        
        if ( $notice ) {
            $wsko_data = WSKO_Class_Core::get_data();
            if ( !isset( $wsko_data['discard_notice'] ) || !$wsko_data['discard_notice'] ) {
                $wsko_data['discard_notice'] = array();
            }
            $wsko_data['discard_notice'][] = $notice;
            WSKO_Class_Core::save_data( $wsko_data );
        }
        
        return true;
    }
    
    public function action_controller_tour_ended()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $tour = ( isset( $_POST['tour'] ) ? sanitize_text_field( $_POST['tour'] ) : false );
        
        if ( $tour ) {
            if ( $tour === 'global' ) {
                if ( WSKO_Class_Core::is_demo() && function_exists( 'wsko_set_tour_override' ) ) {
                    wsko_set_tour_override();
                }
            }
            WSKO_Class_Core::save_user_setting( 'tour_skipped_' . $tour, true );
        }
        
        return true;
    }
    
    public function action_search_posts_selectpicker()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $search = ( isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : false );
        
        if ( $search ) {
            $res = array();
            $query = new WP_Query( array(
                's'              => $search,
                'posts_per_page' => -1,
            ) );
            $posts = $query->posts;
            if ( $posts ) {
                foreach ( $posts as $p ) {
                    $res[] = array(
                        'value'   => $p->ID,
                        'text'    => '#' . $p->ID,
                        'subtext' => $p->post_title,
                    );
                }
            }
        }
        
        return array(
            'success' => true,
            'data'    => $res,
        );
    }
    
    public function action_get_ranking_deltas()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
        $res = WSKO_Class_Template::render_template( 'misc/template-ranking-deltas.php', array(
            'type' => $type,
        ), true );
        return array(
            'success' => true,
            'view'    => $res,
        );
    }
    
    public function action_skip_nps_feedback()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $temp = ( isset( $_POST['temp'] ) && $_POST['temp'] && $_POST['temp'] !== 'false' ? true : false );
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( $temp ) {
            $wsko_data['skipped_feedback_today'] = time();
        } else {
            $wsko_data['skipped_feedback'] = time();
        }
        
        WSKO_Class_Core::save_data( $wsko_data );
        return true;
    }
    
    public function action_send_nps_feedback()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $msg = ( isset( $_POST['msg'] ) ? sanitize_text_field( $_POST['msg'] ) : false );
        $rating = ( isset( $_POST['rating'] ) ? intval( $_POST['rating'] ) : false );
        
        if ( $rating ) {
            $data = array(
                'action' => 'nps_feedback',
                'msg'    => $msg,
                'rating' => $rating,
            );
            
            if ( !wsko_fs()->is_activation_mode() && !wsko_fs()->is_pending_activation() ) {
                $user = wsko_fs()->get_user();
                if ( $user && is_object( $user ) ) {
                    $data['mail'] = $user->email;
                }
            }
            
            $res = WSKO_Class_Helper::post_to_url( WSKO_FEEDBACK_API, $data, array( "charset=utf-8" ) );
            
            if ( $res === 'true' ) {
                $wsko_data = WSKO_Class_Core::get_data();
                $wsko_data['feedback_sent'] = true;
                WSKO_Class_Core::save_data( $wsko_data );
                return true;
            }
        
        }
    
    }
    
    public function action_get_content_optimizer()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $post_id = ( isset( $_POST['post'] ) ? intval( $_POST['post'] ) : false );
        $widget = ( isset( $_POST['widget'] ) && $_POST['widget'] && $_POST['widget'] != 'false' ? true : false );
        $preview = ( isset( $_POST['preview'] ) && $_POST['preview'] && $_POST['preview'] != 'false' ? true : false );
        $open_tab = ( isset( $_POST['open_tab'] ) ? sanitize_text_field( $_POST['open_tab'] ) : false );
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
        
        if ( WSKO_Class_Core::is_demo() ) {
            $post_id = WSKO_Class_Helper::url_to_postid( home_url() );
            // WSKO_Class_Helper::get_home_id();
        } else {
            if ( $type === 'post_url' && isset( $_POST['post'] ) ) {
                $post_id = WSKO_Class_Helper::url_to_postid( esc_url( $_POST['post'] ) );
            }
        }
        
        
        if ( $post_id ) {
            $preview_data = false;
            if ( $preview ) {
                $preview_data = array(
                    'post_title'   => ( isset( $_POST['post_title'] ) ? sanitize_post_field( 'post_title', $_POST['post_title'], $post_id ) : '' ),
                    'post_content' => ( isset( $_POST['post_content'] ) ? sanitize_post_field( 'post_content', $_POST['post_content'], $post_id ) : '' ),
                    'post_slug'    => ( isset( $_POST['post_slug'] ) ? sanitize_title_with_dashes( $_POST['post_slug'] ) : '' ),
                );
            }
            $view = WSKO_Controller_Optimizer::render(
                $post_id,
                $widget,
                $preview_data,
                $open_tab
            );
            if ( $view ) {
                return array(
                    'success' => true,
                    'view'    => $view,
                );
            }
        }
    
    }
    
    public function action_import_plugin()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $plugin = ( isset( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : false );
        $options = ( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? array_map( 'sanitize_text_field', $_POST['options'] ) : false );
        
        if ( $plugin && $options ) {
            $stats = WSKO_Class_Compatibility::import_plugin( $plugin, $options );
            $view = WSKO_Class_Template::render_template( 'misc/template-import-report.php', array(
                'stats' => $stats,
            ), true );
            return array(
                'success'      => true,
                'report_title' => __( 'Plugin Import Report', 'wsko' ),
                'report'       => $view,
            );
        }
    
    }
    
    /*/*/
    public function get_table_data( $data, $params = array(), $is_formated = false )
    {
        $order = ( isset( $_POST['order'] ) ? $_POST['order'] : false );
        $offset = ( isset( $_POST['start'] ) ? intval( $_POST['start'] ) : 0 );
        $count = ( isset( $_POST['length'] ) ? intval( $_POST['length'] ) : 10 );
        $custom_filter = ( isset( $_POST['custom_filter'] ) ? $_POST['custom_filter'] : false );
        if ( $offset < 0 ) {
            $offset = 0;
        }
        if ( $count < 1 ) {
            $count = 1;
        }
        
        if ( $order && $order[0] ) {
            
            if ( $order[0]['dir'] == "asc" ) {
                $orderdir = 1;
            } else {
                $orderdir = 0;
            }
            
            $order = $order[0]['column'];
        } else {
            $order = 0;
            $orderdir = 1;
        }
        
        $order = intval( $order );
        $search = ( isset( $_POST['search']['value'] ) ? $_POST['search']['value'] : false );
        if ( !isset( $params['search'] ) ) {
            $params['search'] = $search;
        }
        return WSKO_Class_Helper::prepare_data_table(
            $data,
            array(
            'offset' => $offset,
            'count'  => $count,
        ),
            $custom_filter,
            array(
            'key' => $order,
            'dir' => $orderdir,
        ),
            $params,
            $is_formated
        );
    }
    
    /*Timespan*/
    public  $uses_timespan = false ;
    public  $uses_fixed_timespan = false ;
    public  $timespan_start = 0 ;
    public  $timespan_end = 0 ;
    public  $is_default_timespan = false ;
    public function set_timespan( $start_time, $end_time )
    {
        if ( $this->uses_fixed_timespan ) {
            return;
        }
        $this->delete_cache();
        $this->timespan_start = intval( $start_time );
        $this->timespan_end = intval( $end_time );
        //Update Cache
        $this->get_cached_var(
            'timespan',
            true,
            array(),
            true
        );
    }
    
    public function uses_timespan()
    {
        return $this->uses_timespan;
    }
    
    /*Cache*/
    private static  $cache = array() ;
    public function build_cache()
    {
        
        if ( WSKO_Class_Helper::is_wsko_page() || WSKO_Class_Helper::is_wsko_ajax() ) {
            WSKO_Class_Cache::prepare_session_cache();
            //Global Cache
            
            if ( $this->uses_timespan ) {
                $today = WSKO_Class_Helper::get_midnight();
                
                if ( $this->uses_fixed_timespan ) {
                    $this->timespan_start = $today - 60 * 60 * 24 * 30;
                    $this->timespan_end = time();
                    //$today - (60 * 60 * 24 * 3);
                    $this->is_default_timespan = true;
                } else {
                    $times = $this->get_cached_var( 'timespan', true );
                    //, array(), true);
                    $this->timespan_start = $times['start'];
                    $this->timespan_end = $times['end'];
                    $this->is_default_timespan = false;
                    if ( $this->timespan_start == $today - 60 * 60 * 24 * 30 && $this->timespan_end == $today - 60 * 60 * 24 * 3 ) {
                        $this->is_default_timespan = true;
                    }
                }
            
            }
        
        }
    
    }
    
    public function get_cached_var(
        $param_key,
        $parent = false,
        $args = array(),
        $recache = false,
        $expire_time = false
    )
    {
        if ( !$expire_time ) {
            $expire_time = WSKO_Class_Helper::get_midnight( time() + 60 * 60 * 24 ) - time();
        }
        
        if ( $param_key && $param_key != 'var' ) {
            $int = static::get_instance();
            
            if ( $int ) {
                $arg_k = "";
                if ( $args ) {
                    foreach ( $args as $k => $v ) {
                        $arg_k .= $k . '_' . $v;
                    }
                }
                $key = (( $parent ? 'global' : get_class( $int ) )) . '_' . $param_key . '_' . $arg_k;
                //if (!$recache && isset(self::$cache[$key]))
                //return self::$cache[$key];
                $val = WSKO_Class_Cache::get_session_cache( $key );
                
                if ( $recache || !$val ) {
                    $func = 'get_cached_' . $param_key;
                    $val = false;
                    if ( method_exists( $int, $func ) ) {
                        $val = $int->{$func}( $args );
                    }
                    if ( $val ) {
                        WSKO_Class_Cache::set_session_cache( $key, $val, $expire_time );
                    }
                }
                
                //if ($val)
                //self::$cache[$key] = $val;
                return $val;
            }
        
        }
        
        return false;
    }
    
    public function get_cached_var_for_time(
        $param_key,
        $args = array(),
        $recache = false,
        $expire_time = false
    )
    {
        if ( !$expire_time ) {
            $expire_time = WSKO_Class_Helper::get_midnight( time() + 60 * 60 * 24 ) - time();
        }
        
        if ( $param_key && $param_key != 'var' ) {
            $int = static::get_instance();
            
            if ( $int && $int->uses_timespan && $int->timespan_start && $int->timespan_end ) {
                $arg_k = "";
                if ( $args ) {
                    foreach ( $args as $k => $v ) {
                        $arg_k .= $k . '_' . $v;
                    }
                }
                $key = get_class( $int ) . '_' . $param_key . '_' . $int->timespan_start . '_' . $int->timespan_end . '_' . $arg_k;
                //if (!$recache && isset(self::$cache[$key]))
                //return self::$cache[$key];
                $val = WSKO_Class_Cache::get_session_cache( $key );
                
                if ( $recache || !$val ) {
                    $func = 'get_cached_' . $param_key;
                    $val = false;
                    if ( method_exists( $int, $func ) ) {
                        $val = $int->{$func}( $args );
                    }
                    if ( $val ) {
                        WSKO_Class_Cache::set_session_cache( $key, $val, $expire_time );
                    }
                }
                
                //if ($val)
                //self::$cache[$key] = $val;
                return $val;
            }
        
        }
        
        return false;
    }
    
    public function delete_cache( $key = false )
    {
        WSKO_Class_Cache::clear_session_cache( $key );
    }
    
    //Global Cache
    public function get_cached_timespan( $args )
    {
        $res = array();
        $today = WSKO_Class_Helper::get_midnight();
        
        if ( $this->timespan_start ) {
            $res['start'] = $this->timespan_start;
        } else {
            $res['start'] = $today - 60 * 60 * 24 * 30;
        }
        
        
        if ( $this->timespan_end ) {
            $res['end'] = $this->timespan_end;
        } else {
            $res['end'] = $today - 60 * 60 * 24 * 3;
        }
        
        return $res;
    }
    
    /*/*/
    /*Static Helpers*/
    public static function add_wp_nav_links( $callback )
    {
        foreach ( static::$controllers as $c ) {
            $c->add_menu_page( $callback );
        }
    }
    
    public static function get_title_s( $with_prefix = false, $short_prefix = false )
    {
        
        if ( $short_prefix ) {
            $prefix = 'BST';
        } else {
            $prefix = 'BAVOKO SEO Tools';
        }
        
        $int = static::get_instance();
        if ( $int ) {
            return (( $with_prefix ? $prefix . ' - ' : "" )) . $int->get_title();
        }
        return ( $with_prefix ? $prefix : "" );
    }
    
    public static function get_link( $subpage = false, $simple = false, $prefix = true )
    {
        $int = static::get_instance();
        
        if ( $int && $int->link ) {
            $subpages = $int->get_subpages();
            if ( $subpage && $subpages && is_array( $subpages ) && isset( $subpages[$subpage] ) ) {
                return ( $simple ? (( $prefix ? 'wsko_' : '' )) . $subpage : admin_url( 'admin.php?page=wsko_' . $int->link . '&subpage=' . $subpage ) );
            }
            return ( $simple ? (( $prefix ? 'wsko_' : '' )) . $int->link : admin_url( 'admin.php?page=wsko_' . $int->link ) );
        }
        
        return ( $simple ? "" : admin_url( 'admin.php' ) );
    }
    
    public static function get_subpages( $real_pages = true )
    {
        $int = static::get_instance();
        
        if ( $int->link ) {
            
            if ( $int->subpages && is_array( $int->subpages ) ) {
                $subpages = $int->subpages;
                foreach ( $subpages as $k => $p ) {
                    if ( isset( $p['is_ext'] ) && !$p['link'] ) {
                        $subpages[$k]['link'] = $int->get_subpage_ext_link( $k );
                    }
                    
                    if ( isset( $p['is_premium'] ) && (!wsko_fs()->can_use_premium_code() || !wsko_fs()->is_premium()) || !$real_pages && !isset( $p['is_ext'] ) || $real_pages && isset( $p['is_ext'] ) || isset( $p['is_beta'] ) && !WSKO_Class_Core::is_beta() ) {
                        unset( $subpages[$k] );
                    } else {
                        $subpages[$k]['title'] = $int->get_subpage_title( $k );
                    }
                
                }
                return $subpages;
            }
            
            return false;
        }
    
    }
    
    public static function call_redirect( $subpage = false )
    {
        wp_redirect( static::get_link( $subpage ) );
        exit;
    }
    
    public static function call_redirect_raw( $url )
    {
        wp_redirect( $url );
        exit;
    }
    
    public static function get_registered_controller( $name, $switched = false )
    {
        $is_configured = WSKO_Class_Core::is_configured();
        foreach ( static::$controllers as $c ) {
            if ( ($is_configured && (( $switched ? $c->show_in_setup : !$c->show_in_setup )) || !$is_configured && (( $switched ? !$c->show_in_setup : $c->show_in_setup ))) && $c::get_link( false, true, false ) === $name ) {
                return $c;
            }
        }
        return null;
    }
    
    public static function get_main_nav_controllers()
    {
        $res = array();
        foreach ( static::$controllers as $c ) {
            if ( $c->has_main_nav_link ) {
                $res[] = $c;
            }
        }
        return $res;
    }
    
    public static function get_lazy_page_widget_beacon( $subpage = false, $showtab = false, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $inst = static::get_instance();
        
        if ( $inst ) {
            ?><div class="wsko-dashboard-page-widget" data-controller="<?php 
            echo  $inst->get_link( false, true, true ) ;
            ?>" data-subpage="<?php 
            echo  $subpage ;
            ?>" data-subtab="<?php 
            echo  $showtab ;
            ?>">
			<?php 
            WSKO_Class_Template::render_preloader( array(
                'size' => 'big',
            ) );
            ?>
			</div><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    /*/*/
    /*Singleton*/
    public static  $instance ;
    public static function get_instance()
    {
        
        if ( !isset( static::$instance ) ) {
            static::$instance = new static();
            static::$instance->build_cache();
        }
        
        return static::$instance;
    }

}