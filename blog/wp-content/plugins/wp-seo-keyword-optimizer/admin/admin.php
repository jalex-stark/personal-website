<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
use  Google\AdsApi\AdWords\AdWordsServices ;
use  Google\AdsApi\AdWords\AdWordsSessionBuilder ;
use  Google\AdsApi\Common\OAuth2TokenBuilder ;
class WSKO_AdminMenu
{
    static  $instance ;
    private  $wp_screen_prefix_nolevel = "admin_page_" ;
    private  $wp_screen_prefix_toplevel = "toplevel_page_" ;
    private  $wp_screen_prefix_sublevel = "bavoko-seo-tools_page_" ;
    public  $controller = null ;
    private  $controller_name = null ;
    private  $subpage_name = null ;
    //private $has_critical_errors = false;
    public function __construct()
    {
        global  $pagenow ;
        if ( is_admin() ) {
            if ( WSKO_Class_Helper::check_user_permissions( false ) ) {
                
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    //AJAX Controller
                    
                    if ( isset( $_POST['wsko_controller'] ) && $_POST['wsko_controller'] ) {
                        $this->get_controller( sanitize_text_field( $_POST['wsko_controller'] ) );
                        
                        if ( $this->controller != null ) {
                            //Check for errors
                            //$this->has_critical_errors = WSKO_Class_Core::get_critical_errors();
                            //if (!$this->has_critical_errors)
                            //{
                            //Init Controller
                            $this->controller->set_subpage( ( isset( $_POST['wsko_controller_sub'] ) ? sanitize_text_field( $_POST['wsko_controller_sub'] ) : '' ) );
                            //}
                        }
                    
                    }
                
                } else {
                    //Backend Controllers
                    if ( $pagenow == 'admin.php' ) {
                        add_action( 'current_screen', array( $this, 'load_controller' ) );
                    }
                    add_action( 'admin_menu', array( $this, 'menu_items' ) );
                    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
                    add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 0 );
                    add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_third_party' ), 999999 );
                    add_action( 'admin_head', array( $this, 'admin_head' ), 1 );
                    add_action( 'admin_footer', array( $this, 'admin_footer' ) );
                    add_action( 'admin_bar_menu', array( $this, 'admin_loaded' ), 1 );
                    add_filter(
                        'post_row_actions',
                        array( $this, 'add_content_optimizer_to_post_types' ),
                        10,
                        2
                    );
                    add_filter(
                        'page_row_actions',
                        array( $this, 'add_content_optimizer_to_post_types' ),
                        10,
                        2
                    );
                    $taxonomies = WSKO_Class_Helper::get_public_taxonomies( 'objects' );
                    if ( $taxonomies ) {
                        foreach ( $taxonomies as $tax ) {
                            add_action(
                                $tax->name . '_edit_form_fields',
                                array( $this, 'add_taxonomy_view' ),
                                500,
                                2
                            );
                        }
                    }
                    wsko_fs()->add_filter(
                        'templates/account.php',
                        array( $this, 'render_controller' ),
                        10,
                        1
                    );
                }
            
            }
        }
        add_filter( 'redirect_post_location', function ( $location ) {
            //append "bst_iframe" again after post save
            if ( isset( $_REQUEST['bst_iframe'] ) || isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], '&bst_iframe' ) !== false ) {
                $location = add_query_arg( 'bst_iframe', 'true', $location );
            }
            return $location;
        } );
    }
    
    public function add_taxonomy_view( $term )
    {
        //$term = get_term($term->term_id);
        
        if ( $term ) {
            ?><tr class="form-field">
				<th scope="row" valign="top"><label>BAVOKO SEO Tools</label><p style="opacity:.7;font-weight: normal;margin: 5px 0px;"><?php 
            echo  __( 'Taxonomy SEO Settings', 'wsko' ) ;
            ?></p></th>
				<td>
					<div id="wsko_tax_meta_view" style="position:relative">
						<?php 
            echo  WSKO_Controller_Iframe::get_iframe( 'tax_meta', $term->term_id, array(
                'width'    => '100%',
                'height'   => '100%',
                'url_data' => array(
                'taxonomy' => $term->taxonomy,
            ),
            ) ) ;
            ?>
						<div id="wsko_tax_meta_view_loading_view" style="position:absolute;left:0px;top:0px;width:100%;height:100%;background-color:white;">
							<?php 
            echo  WSKO_Class_Template::render_bst_preloader() ;
            ?>
						</div>
						<script type="text/javascript">jQuery(document).ready(function($){
							var $iframe = $('#wsko_tax_meta_view iframe');
							if ($iframe.length)
							{
								if ($iframe.load)
								{
									$iframe.load(function(){
										$('#wsko_tax_meta_view_loading_view').hide();
										$('#wsko_tax_meta_view').height($iframe.contents().find('body')[0].scrollHeight);
									});
								}
								else
								{
									setTimeout(function(){
										$('#wsko_tax_meta_view_loading_view').hide();
										$('#wsko_tax_meta_view').height($iframe.contents().find('body')[0].scrollHeight);
									}, 4000);
								}
							}
						});</script>
					</div>
				</td>
			</tr><?php 
        }
    
    }
    
    public function add_content_optimizer_to_post_types( $actions, $post )
    {
        //if (!in_array($post->post_type, WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_optimizer_post_types_exclude'))))
        //$actions['wsko_co'] = WSKO_Class_Template::render_content_optimizer_link($post->ID, array('iframe' => true), true);
        WSKO_Class_Template::render_content_optimizer_link( $post->ID, array(
            'iframe' => true,
            'class'  => 'wsko-content-optimizer-table-link',
        ) );
        return $actions;
    }
    
    public function load_controller()
    {
        
        if ( is_admin() ) {
            $screen = get_current_screen();
            
            if ( $screen ) {
                $this->get_controller( $screen->id );
                
                if ( $this->controller != null ) {
                    //Check for errors
                    //$this->has_critical_errors = WSKO_Class_Core::get_critical_errors();
                    //if (!$this->has_critical_errors)
                    //{
                    //Init Controller Rendering
                    $this->controller_name = $this->controller->get_link( false, true );
                    if ( $this->controller->set_subpage( ( isset( $_GET['subpage'] ) ? sanitize_text_field( $_GET['subpage'] ) : '' ) ) ) {
                        $this->subpage_name = $this->controller->get_current_subpage();
                    }
                    $this->controller->redirect();
                    //}
                } else {
                    if ( $this->get_controller( $screen->id, true, false ) ) {
                        
                        if ( WSKO_Class_Core::is_configured() ) {
                            wp_redirect( WSKO_Controller_Dashboard::get_link() );
                            exit;
                        } else {
                            wp_redirect( WSKO_Controller_Setup::get_link() );
                            exit;
                        }
                    
                    }
                }
            
            }
        
        }
    
    }
    
    public function menu_items()
    {
        $is_configured = WSKO_Class_Core::is_configured();
        $is_admin = current_user_can( 'manage_options' );
        
        if ( $is_configured ) {
            $hook = add_menu_page(
                WSKO_Controller_Dashboard::get_title_s( true, true ),
                'BAVOKO SEO Tools',
                //Dont change!!!
                'edit_posts',
                WSKO_Controller_Dashboard::get_link( false, true ),
                array( $this, 'render_controller' ),
                '',
                2
            );
            WSKO_Controller::add_wp_nav_links( array( $this, 'render_controller' ) );
        } else {
            
            if ( $is_admin ) {
                $hook = add_menu_page(
                    WSKO_Controller_Dashboard::get_title_s( true, false ),
                    'BAVOKO SEO Tools',
                    //Dont change!!!
                    'manage_options',
                    WSKO_Controller_Setup::get_link( false, true ),
                    array( $this, 'render_controller' ),
                    '',
                    2
                );
                WSKO_Controller::add_wp_nav_links( array( $this, 'render_controller' ) );
            }
        
        }
        
        $hook = add_submenu_page(
            null,
            WSKO_Controller_Download::get_title_s( true, true ),
            WSKO_Controller_Download::get_title_s(),
            'manage_options',
            WSKO_Controller_Download::get_link( false, true ),
            array( $this, 'render_controller' )
        );
        //TODO: remove
        $hook = add_submenu_page(
            null,
            'BAVOKO SEO Tools - Update',
            'Update',
            'edit_posts',
            'wsko_updated',
            array( $this, 'render_updated_page' )
        );
    }
    
    public function load_scripts( $hook )
    {
        if ( !in_array( $hook, array( 'edit.php', 'post.php', 'term.php' ) ) && !$this->controller ) {
            return;
        }
        if ( $hook == 'post.php' ) {
            
            if ( isset( $_GET['bst_iframe'] ) && $_GET['bst_iframe'] ) {
                wp_enqueue_style(
                    'wsko_post_iframe_css',
                    WSKO_PLUGIN_URL . 'admin/css/post-iframe.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
                    array(),
                    WSKO_VERSION
                );
                wp_enqueue_script(
                    'wsko_post_iframe_js',
                    WSKO_PLUGIN_URL . 'admin/js/post-iframe.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
                    array(),
                    WSKO_VERSION
                );
            }
        
        }
        //WSKO JS
        wp_enqueue_script(
            'wsko_global_js',
            WSKO_PLUGIN_URL . 'admin/js/global.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_global_css',
            WSKO_PLUGIN_URL . 'admin/css/global.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        //Local
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-fade' );
        wp_enqueue_script( 'jquery-effects-slide' );
        if ( !$this->controller ) {
            return;
        }
        $script_data = array(
            'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
            'mapsApiKey'                      => WSKO_GOOGLE_MAPS_API_KEY,
            'lazy_data_nonce'                 => wp_create_nonce( 'wsko_load_lazy_data' ),
            'add_monitoring_keyword_nonce'    => wp_create_nonce( 'wsko_add_monitoring_keyword' ),
            'remove_monitoring_keyword_nonce' => wp_create_nonce( 'wsko_remove_monitoring_keyword' ),
            'disavow_backlink_nonce'          => wp_create_nonce( 'wsko_disavow_backlink' ),
            'remove_disavowed_backlink_nonce' => wp_create_nonce( 'wsko_remove_disavowed_backlink' ),
            'knowledge_base_nonce'            => wp_create_nonce( 'wsko_get_knowledge_base_article' ),
            'is_configured'                   => WSKO_Class_Core::is_configured(),
            'template_no_data'                => WSKO_Class_Template::render_template( 'misc/template-no-data.php', array(), true ),
            'save_ajax_input_nonce'           => wp_create_nonce( 'wsko_save_ajax_input' ),
            'texts'                           => array(
            'import_invalid'         => __( 'Please select something to import', 'wsko' ),
            'delete_cfg_confirm'     => __( 'Are you sure you want to delete your configuration?', 'wsko' ),
            'resolve_error'          => __( 'Resolving Error', 'wsko' ),
            'undefined_error'        => __( 'Undefined Error', 'wsko' ),
            'server_error'           => __( 'Server Error', 'wsko' ),
            'clear_log_confirm'      => __( 'You are about to delete every log report. Are you sure you want to continue?', 'wsko' ),
            'co_saving'              => __( 'Saving', 'wsko' ),
            'co_saving_success'      => __( 'All changes saved', 'wsko' ),
            'co_saving_fail'         => __( 'Saving failed!', 'wsko' ),
            'auto_redirect_confirm'  => __( "You have changed the permalink structure. Do you wan't BST to automatically create redirect rules for the old links?", 'wsko' ),
            'keyword_empty'          => __( 'You need to enter a keyword!', 'wsko' ),
            'similar_kw_enter'       => __( 'Press Enter to Submit', 'wsko' ),
            'similar_kw_add'         => __( 'Add similar keyword', 'wsko' ),
            'snippet_cfg_error'      => __( 'Snippet config could not be loaded. Please reload the page and try again.', 'wsko' ),
            'core_ajax_saved'        => __( 'Saved', 'wsko' ),
            'core_ajax_error'        => __( 'An undefined error occurred. Maybe your session has expired, refresh the page and try again.', 'wsko' ),
            'core_ajax_server_error' => __( 'Server Error', 'wsko' ),
        ),
        );
        $co_script_data = array(
            'content_optimizer_nonce' => wp_create_nonce( 'wsko_get_content_optimizer' ),
            'set_link_nonce'          => wp_create_nonce( 'wsko_co_set_link' ),
            'is_premium'              => WSKO_Class_Core::is_premium(),
            'meta_view_autosave'      => WSKO_Class_Core::get_user_setting( 'metas_view_autosave' ),
        );
        //if ($this->controller)
        //{
        wp_enqueue_media();
        //wp_enqueue_script( 'bloody_tinymce_js_main', includes_url() . 'js/tinymce/tinymce.min.js' );
        //}
        wp_enqueue_script(
            'wsko_frontend_js',
            WSKO_PLUGIN_URL . 'admin/js/frontend-widgets.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array( 'jquery' ),
            WSKO_VERSION
        );
        wp_enqueue_script(
            'wsko_core_js',
            WSKO_PLUGIN_URL . 'admin/js/core.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array( 'jquery' ),
            WSKO_VERSION
        );
        wp_localize_script( 'wsko_core_js', 'wsko_data', $script_data );
        wp_enqueue_script(
            'wsko_co_js',
            WSKO_PLUGIN_URL . 'admin/js/post-widget.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
            array( 'jquery' ),
            WSKO_VERSION
        );
        wp_localize_script( 'wsko_co_js', 'wsko_co_data', $co_script_data );
        wp_enqueue_style( 'font_awesome', WSKO_PLUGIN_URL . 'includes/font-awesome/css/font-awesome.min.css' );
        wp_enqueue_style(
            'wsko_roboto',
            WSKO_PLUGIN_URL . 'admin/css/fonts.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        //Color Picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery_datatables', WSKO_PLUGIN_URL . 'includes/datatables/datatables.min.js' );
        //wp_enqueue_style('jquery_datatables_css', WSKO_PLUGIN_URL . 'includes/datatables/datatables.min.css');
        wp_enqueue_style( 'jquery_datatables_bootstrap', WSKO_PLUGIN_URL . 'includes/datatables/datatables.bootstrap.min.css' );
        wp_enqueue_script( 'jquery_datatables_bootstrap', WSKO_PLUGIN_URL . 'includes/datatables/datatables.bootstrap.min.js' );
        //Materialize
        //wp_enqueue_script('wsko_materialize', WSKO_PLUGIN_URL . 'includes/materialize/js/materialize.js', array(), WSKO_VERSION);
        wp_enqueue_style(
            'wsko_materialize',
            WSKO_PLUGIN_URL . 'includes/materialize/css/materialize.css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_script(
            'wsko_materialize_nouislider',
            WSKO_PLUGIN_URL . 'includes/materialize/extras/noUiSlider/nouislider.js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_materialize_nouislider',
            WSKO_PLUGIN_URL . 'includes/materialize/extras/noUiSlider/nouislider.css',
            array(),
            WSKO_VERSION
        );
        // WP Media Upload
        wp_enqueue_media();
        //if ($this->controller)
        //{
        //iTour
        wp_enqueue_script(
            'wsko_itour',
            WSKO_PLUGIN_URL . 'includes/iTour/js/jquery.itour.js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_itour',
            WSKO_PLUGIN_URL . 'includes/iTour/css/itour.css',
            array(),
            WSKO_VERSION
        );
        //Sigma.js
        wp_enqueue_script(
            'wsko_sigma',
            WSKO_PLUGIN_URL . 'includes/sigma.js/sigma.min.js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_script(
            'wsko_sigma_atlas',
            WSKO_PLUGIN_URL . 'includes/sigma.js/plugins/sigma.layout.forceAtlas2.min.js',
            array(),
            WSKO_VERSION
        );
        //wp_enqueue_style('wsko_sigma', WSKO_PLUGIN_URL . 'includes/sigma.js/src/sigma.css', array(), WSKO_VERSION);
        $this->controller->enqueue_scripts();
        //}
    }
    
    public function load_scripts_third_party( $hook )
    {
        //if (!in_array($hook, array('edit.php', 'post.php', 'term.php')) && !$this->controller)
        //return;
        if ( !$this->controller ) {
            return;
        }
        $has_g_charts = false;
        //$has_materialize = false;
        $has_bootstrap_js = false;
        $has_bootstrap_css = false;
        $has_moment = false;
        $has_b_datepicker = false;
        $has_roboto = false;
        $wp_scripts = wp_scripts();
        $wp_styles = wp_styles();
        $bootstrap_override = false;
        if ( $this->controller ) {
            $bootstrap_override = true;
        }
        foreach ( $wp_scripts->registered as $handle => $data ) {
            if ( stripos( $data->src, 'plugins/wp-seo-keyword-optimizer/' ) !== false || stripos( $data->src, 'plugins/wp-seo-keyword-optimizer-premium/' ) !== false || stripos( $data->src, 'plugins/wsko/' ) !== false ) {
                continue;
            }
            $is_enqueued = WSKO_Class_Helper::is_enqueued( $data->handle, 1 );
            if ( stripos( $data->src, 'gstatic.com/charts/loader.js' ) !== false && $is_enqueued ) {
                $has_g_charts = true;
            }
            //if (stripos($data->src, '/materialize.js') !== false || stripos($data->src, '/materialize.min.js') !== false)
            //$has_materialize = true;
            if ( (stripos( $data->src, '/bootstrap.js' ) !== false || stripos( $data->src, '/bootstrap.min.js' ) !== false) && $is_enqueued ) {
                
                if ( $bootstrap_override ) {
                    wp_deregister_script( $handle );
                } else {
                    $has_bootstrap_js = true;
                }
            
            }
            if ( (stripos( $data->src, '/moment.js' ) !== false || stripos( $data->src, '/moment.min.js' ) !== false) && $is_enqueued ) {
                $has_moment = true;
            }
            if ( stripos( $data->src, '/bootstrap-daterangepicker/' ) !== false && $is_enqueued ) {
                $has_b_datepicker = true;
            }
            //if (stripos($data->src, WSKO_PLUGIN_URL . 'admin/css/fonts.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css') !== false)
            //$has_roboto = true;
        }
        foreach ( $wp_styles->registered as $handle => $data ) {
            if ( stripos( $data->src, 'plugins/wp-seo-keyword-optimizer/' ) !== false || stripos( $data->src, 'plugins/wsko/' ) !== false ) {
                continue;
            }
            $is_enqueued = WSKO_Class_Helper::is_enqueued( $data->handle, 0 );
            if ( (stripos( $data->src, '/bootstrap.css' ) !== false || stripos( $data->src, '/bootstrap.min.css' ) !== false) && $is_enqueued ) {
                
                if ( $bootstrap_override ) {
                    wp_deregister_style( $handle );
                } else {
                    $has_bootstrap_css = true;
                }
            
            }
            if ( stripos( $data->src, '/bootstrap-daterangepicker/' ) !== false && $is_enqueued ) {
                $has_b_datepicker = true;
            }
            //if (stripos($data->src, WSKO_PLUGIN_URL . 'admin/css/fonts.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css') !== false)
            //$has_roboto = true;
        }
        //WSKO CSS
        wp_enqueue_style(
            'wsko_core_css',
            WSKO_PLUGIN_URL . 'admin/css/core.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_dashboard_css',
            WSKO_PLUGIN_URL . 'admin/css/dashboard.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_freemius_css',
            WSKO_PLUGIN_URL . 'admin/css/freemius-style.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_post_wdiget_css',
            WSKO_PLUGIN_URL . 'admin/css/post-widget.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_misc_css',
            WSKO_PLUGIN_URL . 'admin/css/misc.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_frontend_css',
            WSKO_PLUGIN_URL . 'admin/css/frontend-widgets.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
            array(),
            WSKO_VERSION
        );
        //CDN
        if ( !$has_g_charts ) {
            wp_enqueue_script( 'wsko_google_charts', 'https://www.gstatic.com/charts/loader.js' );
        }
        //3rd Party
        if ( !$has_bootstrap_js ) {
            wp_enqueue_script( 'wsko_bootstrap', WSKO_PLUGIN_URL . 'includes/bootstrap/js/bootstrap.min.js' );
        }
        if ( !$has_bootstrap_css ) {
            wp_enqueue_style( 'wsko_bootstrap', WSKO_PLUGIN_URL . 'includes/bootstrap/css/bootstrap.min.css' );
        }
        //if ($this->controller)
        //{
        if ( !$has_moment ) {
            wp_enqueue_script( 'wsko_moment', WSKO_PLUGIN_URL . 'includes/moment/moment.js' );
        }
        
        if ( !$has_b_datepicker ) {
            wp_enqueue_script( 'wsko_bootstrap_datepicker', WSKO_PLUGIN_URL . 'includes/bootstrap-daterangepicker/daterangepicker.js' );
            wp_enqueue_style( 'wsko_bootstrap_datepicker', WSKO_PLUGIN_URL . 'includes/bootstrap-daterangepicker/daterangepicker.css' );
        }
        
        //Roboto Font
        if ( !$has_roboto ) {
            wp_enqueue_style(
                'wsko_roboto',
                WSKO_PLUGIN_URL . 'admin/css/fonts.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
                array(),
                WSKO_VERSION
            );
        }
        $this->controller->enqueue_styles();
        //}
        wp_enqueue_script(
            'wsko_bootstrap_select',
            WSKO_PLUGIN_URL . 'includes/bootstrap-select/js/bootstrap-select.min.js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_bootstrap_select',
            WSKO_PLUGIN_URL . 'includes/bootstrap-select/css/bootstrap-select.min.css',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_script(
            'wsko_bootstrap_ajax_select',
            WSKO_PLUGIN_URL . 'includes/AJAX-Autocomplete-Bootstrap-Select/dist/js/ajax-bootstrap-select.min.js',
            array(),
            WSKO_VERSION
        );
        wp_enqueue_style(
            'wsko_bootstrap_ajax_select',
            WSKO_PLUGIN_URL . 'includes/AJAX-Autocomplete-Bootstrap-Select/dist/css/ajax-bootstrap-select.min.css',
            array(),
            WSKO_VERSION
        );
    }
    
    static  $render_modals_flag ;
    public function render_controller( $ext = false )
    {
        if ( $ext ) {
            ob_start();
        }
        
        if ( $this->controller || $ext ) {
            
            if ( !WSKO_Class_Core::is_configured() ) {
                ?><div class="wsko-display-none-fix"><?php 
                
                if ( current_user_can( 'manage_options' ) ) {
                    
                    if ( !$ext ) {
                        $this->controller->view( false );
                        $this->controller->get_scripts( false );
                    } else {
                        echo  $ext ;
                    }
                    
                    static::$render_modals_flag = true;
                } else {
                    WSKO_Class_Template::render_notification( 'error', array(
                        'msg' => wsko_loc( 'notif', 'setup_non_admin' ),
                    ) );
                }
                
                ?></div><?php 
            } else {
                
                if ( $this->controller->has_main_frame ) {
                    static::$render_modals_flag = true;
                    $breadcrumb = ( !$ext ? $this->controller->get_breadcrumb() : '' );
                    $plan = wsko_fs()->get_plan();
                    ?>
					<div id="wsko_admin_view_body" class="wsko_wrapper wsko-controller-view-<?php 
                    echo  $this->controller->link ;
                    ?> wsko-mobile-nav">
						<div id="wsko_admin_view_topbar">
							<div id="wsko_admin_view_topbar_icon" class="wsko_important_nav">
								<div class="wsko_logo" style="top: -5px;position: relative;" data-name="wsko_logo"><img class="" src="<?php 
                    echo  WSKO_PLUGIN_URL . 'admin/img/logo.png' ;
                    ?>" /></div> 
								<div style="display:inline-block;top:5px;position:relative;">
									<span>BAVOKO SEO Tools <?php 
                    if ( WSKO_Class_Core::is_demo() ) {
                        ?><small class="text-off" style="color:#fff;">Demo</small><?php 
                    }
                    ?></span>
									<br/>
									<span class="wsko-text-off"><?php 
                    echo  ( $plan ? $plan->title : 'Free' ) ;
                    
                    if ( !WSKO_Class_Core::is_premium() ) {
                        ?> • <a class="wsko-white wsko-upgrade-now" href="<?php 
                        echo  wsko_fs()->pricing_url() ;
                        ?>"><?php 
                        echo  __( 'Upgrade', 'wsko' ) ;
                        ?></a><?php 
                    }
                    
                    ?></span>
								</div>	
							</div>
							<div id="wsko_admin_view_topbar_header">
								<div class="row">
									<div class="col-sm-5 col-xs-6">
										<span class="mobile-nav-toggle" style="display:none;"><a href="#" class="menu"><i class="fa fa-bars fa-fw fa-2x dark"></i></a></span>
										<div class="wsko-header-title-wrapper">
											<h2 id="wsko_main_title"><?php 
                    echo  $this->controller->get_title() ;
                    ?></h2>
											<span id="wsko_main_breadcrumb"><?php 
                    echo  $breadcrumb ;
                    ?></span>
										</div>
									</div>
									<div class="col-sm-7 col-xs-6">	
										<div id="wsko_admin_view_header_wrapper" class="wsko_header_meta_wrapper hidden-xs" style="padding:10px;">
											<?php 
                    $this->controller->get_header_widget();
                    ?>
											<div id="wsko_admin_view_timespan_wrapper">
												<?php 
                    if ( !$ext && $this->controller->uses_timespan( $this->controller->get_current_subpage() ) ) {
                        WSKO_Class_Template::render_timespan_widget();
                    }
                    ?>
											</div>
										</div>
									</div>
								</div>		
							</div>
						</div>
						<div id="wsko_admin_view_sidebar" class="wsko_important_nav">
							<div id="wsko_admin_view_sidebar_overlay" class="wsko-admin-main-navbar">
								<?php 
                    $controllers = WSKO_Controller::get_main_nav_controllers();
                    WSKO_Class_Template::render_main_navigation( $controllers, ( !$ext ? $this->controller_name : '' ), ( !$ext ? $this->subpage_name : '' ) );
                    ?>	

								<div class="wsko-sidebar-info-links">
									<ul>
										<?php 
                    
                    if ( current_user_can( 'manage_options' ) ) {
                        ?>
											<?php 
                        
                        if ( WSKO_Class_Core::has_account() ) {
                            ?>
												<li><a class="wsko-white" href="<?php 
                            echo  wsko_fs()->get_account_url() ;
                            ?>"><i class="fa fa-user fa-fw mr3"></i><?php 
                            echo  __( 'Account', 'wsko' ) ;
                            ?></a></li>
											<?php 
                        } else {
                            ?>
												<li><a class="wsko-white" href="<?php 
                            echo  WSKO_Class_Freemius::get_optin_url() ;
                            ?>"><i class="fa fa-user fa-fw mr3"></i><?php 
                            echo  __( 'Opt In', 'wsko' ) ;
                            ?></a></li>
											<?php 
                        }
                        
                        ?>
										<?php 
                    }
                    
                    ?>
										<li class="hidden-xs"><a class="wsko-white wsko-toggle-help"><i class="fa fa-question fa-fw mr3"></i><?php 
                    echo  __( 'Help', 'wsko' ) ;
                    ?></a></li>
										<li class="hidden-xs"><a class="wsko-white" target="_blank" href="https://www.bavoko.tools/affiliate-program/"><i class="fa fa-share-alt fa-fw mr3"></i><?php 
                    echo  __( 'Affiliate', 'wsko' ) ;
                    ?></a></li>
									</ul>	
								</div>					
							</div>						
							
						</div>
						<div id="wsko_admin_view_content">
							<div id="wsko_admin_view_content_wrapper">
								<div id="wsko_notification_wrapper">
									<div id="wsko_admin_view_notification_wrapper" style="margin: auto 20px;">
										<?php 
                    if ( !$ext ) {
                        $this->controller->notifications();
                    }
                    ?>
									</div>
									<div class="wsko_notification" id="wsko_admin_ajax_notifications"></div>
								</div>	
								
								<div id="wsko_admin_view_wrapper">
									<?php 
                    
                    if ( !$ext ) {
                        $this->controller->view( false );
                    } else {
                        echo  $ext ;
                    }
                    
                    ?>
								</div>
								
								<div id="wsko_admin_view_loading">
									<div id="wsko_admin_view_loading_background"></div>
									<?php 
                    echo  WSKO_Class_Template::render_preloader( array(
                        'size' => 'big',
                    ) ) ;
                    ?> 
								</div>
							</div>

							<?php 
                    WSKO_Class_Template::render_template( 'misc/modal-help.php', array(
                        'breadcrumb' => $breadcrumb,
                    ) );
                    ?>

							<p class="hidden-xs wsko-made-in"><?php 
                    echo  __( 'Made with <i class="fa fa-heart fa-fw"></i> in Berlin, Germany', 'wsko' ) ;
                    ?></p>			
							<?php 
                    //WSKO_Class_Template::render_template('misc/template-nps-score.php', array());
                    ?>
						</div>
					</div>
					<div id="wsko_admin_view_ajax_notification"></div>
					
					<div id="wsko_admin_view_script_wrapper">
						<?php 
                    if ( !$ext ) {
                        $this->controller->get_scripts( false );
                    }
                    ?>
					</div><?php 
                } else {
                    $this->controller->view( false );
                    $this->controller->get_scripts( false );
                }
            
            }
        
        } else {
            ?><div class="wsko-display-none-fix"><?php 
            WSKO_Class_Template::render_notification( 'error', array(
                'msg' => wsko_loc( 'notif', 'admin_no_controller' ),
            ) );
            ?></div><?php 
        }
        
        if ( $ext ) {
            return ob_get_clean();
        }
    }
    
    public function render_updated_page()
    {
        WSKO_Class_Template::render_template( 'misc/frame-info.php', array() );
    }
    
    public function add_meta_boxes()
    {
        //if (WSKO_Class_Core::get_setting('activate_content_optimizer'))
        //{
        
        if ( !isset( $_GET['bst_iframe'] ) || !$_GET['bst_iframe'] ) {
            $wsko_post_types = get_post_types( array(), 'names' );
            //$wsko_post_types_ex = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_optimizer_post_types_exclude'));
            $wsko_post_types_in = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'content_optimizer_post_types_include' ) );
            foreach ( $wsko_post_types as $type ) {
                //if (!$wsko_post_types_ex || !in_array($type, $wsko_post_types_ex))
                if ( $wsko_post_types_in && in_array( $type, $wsko_post_types_in ) ) {
                    add_meta_box(
                        'wsko_post_metabox',
                        'BAVOKO SEO Tools',
                        array( &$this, 'post_meta_box_view' ),
                        $type,
                        'normal',
                        'high'
                    );
                }
            }
        } else {
            add_action( 'edit_form_top', function () {
                ?><input type="hidden" name="bst_iframe" value="true"><?php 
            } );
        }
        
        //}
    }
    
    public function post_meta_box_view()
    {
        global  $pagenow ;
        
        if ( $pagenow != 'post.php' ) {
            ?><p><?php 
            echo  __( 'Content Optimizer is available after first save.', 'wsko' ) ;
            ?></p><?php 
            return;
        }
        
        global  $post ;
        
        if ( $post ) {
            global  $wp_version ;
            $post_builder = WSKO_Class_Compatibility::has_post_builder( $post );
            ?><script type="text/javascript">
					jQuery(document).ready(function($){
						<?php 
            
            if ( WSKO_Class_Core::get_user_setting( 'unstick_content_optimizer' ) || $post_builder && $post_builder != 'gutenberg' ) {
                
                if ( $post_builder === 'beaver' || $post_builder === 'visual_composer' ) {
                    ?>$('#wsko_post_metabox').addClass('wsko-metabox-vc').appendTo('#post-body-content');<?php 
                } else {
                    ?>$('#wsko_post_metabox').addClass('wsko-metabox-vc').insertAfter('#titlediv');<?php 
                }
            
            } else {
                ?>$('#wsko_post_metabox').insertBefore('#wp-content-media-buttons');<?php 
            }
            
            ?>
						//$('#wsko_post_metabox h2.hndle span').prepend('<div class="wsko_mini_logo"><img src="<?php 
            echo  WSKO_PLUGIN_URL . 'admin/img/logo.png' ;
            ?>"></div>');
						var changeTimout,
						post_title,
						post_content,
						post_slug;
						window.wsko_reload_content_optimizer_post_widget = function()
						{
							if (changeTimout)
								clearTimeout(changeTimout);
							
							changeTimout = setTimeout(function(){
								$('#wsko_content_optimizer_ajax_reload_overlay').show();

								var $global_frame_form = $('#wsko_global_frame_form');
								if ($global_frame_form.length <= 0)
								{
									$global_frame_form = $('<div id="wsko_global_frame_form" style="display:none"></div>').appendTo('body');
									$global_frame_form.html($('#wsko_content_optimizer_ajax_reload_wrapper').data('iframe-form'));
								}

								var $iframe = $('#wsko_content_optimizer_ajax_reload_wrapper').html($('#wsko_content_optimizer_ajax_reload_wrapper').data('iframe')).find('iframe'),
								$form = $global_frame_form.find('form'),
								height_timeout = $('#wsko_content_optimizer_ajax_reload_overlay').data('height-interval');

								if (height_timeout)
									clearInterval(height_timeout);

								if ($form.length > 0)
								{
									$form.find('input[name="post_title"]').val(post_title);
									$form.find('input[name="post_content"]').val(post_content);
									$form.find('input[name="post_slug"]').val(post_slug);
									$form.find('input[name="preview"]').val((post_title||post_content||post_slug) ? true : false);
									$form.submit();
								}
								if ($iframe.load)
								{
									$iframe.load(function(){
										$resize_wrapper = $iframe.contents().find('.wsko-resizable-wrapper');
										$('#wsko_content_optimizer_ajax_reload_overlay').hide().data('height-interval', setInterval(() => {
											$('#wsko_content_optimizer_ajax_reload_wrapper').height($resize_wrapper.height()+10+"px");
											//var iframe = $iframe.get(0);
											//$('#wsko_content_optimizer_ajax_reload_wrapper').height(iframe.contentWindow.document.body.scrollHeight + 1 + "px");
										}, 10));
									});
								}
								else
								{
									setTimeout(function(){
										$resize_wrapper = $iframe.contents().find('.wsko-resizable-wrapper');
										$('#wsko_content_optimizer_ajax_reload_overlay').hide().data('height-interval', setInterval(() => {
											$('#wsko_content_optimizer_ajax_reload_wrapper').height($resize_wrapper.height()+10+"px");
											//var iframe = $iframe.get(0);
											//$('#wsko_content_optimizer_ajax_reload_wrapper').height(iframe.contentWindow.document.body.scrollHeight + 1 + "px");
										}, 10));
									}, 4000);
								}
								/*var was_open = $('.wsko-co-widget').length && !$('.wsko-co-widget').hasClass('wsko-short-view-active');
								window.wsko_post_element({action: 'wsko_get_content_optimizer', post: <?php 
            echo  $post->ID ;
            ?>, widget:true, preview:((post_title||post_content||post_slug) ? true : false), post_title: post_title, post_content:post_content, post_slug:post_slug ,nonce: wsko_co_data.content_optimizer_nonce}, 
									function(res){
										$('#wsko_content_optimizer_ajax_reload_overlay').hide();
										if (res.success)
										{
											$('#wsko_content_optimizer_ajax_reload_wrapper').html(res.view);
											if (was_open)
											{
												$('.wsko-co-widget').removeClass('wsko-short-view-active');
											}
											return true;
										}
										else
										{
											$('#wsko_content_optimizer_ajax_reload_wrapper').html("Content Optimizer could not be generated. Please try again");
										}
									},
									function()
									{
										$('#wsko_content_optimizer_ajax_reload_wrapper').html("A Server Error occured. Please try again.");
										$('#wsko_content_optimizer_ajax_reload_overlay').hide();
									}, false, false);*/
							}, 1000);
						}
						$('#titlewrap input[name="post_title"]').change(function(){
							post_title = $(this).val();
							window.wsko_reload_content_optimizer_post_widget();
						});
						$('#wp-content-editor-container .wp-editor-area').change(function(){
							post_content = $(this).val();
							window.wsko_reload_content_optimizer_post_widget();
						});
						$('#edit-slug-buttons .save.button').click(function(event){
							post_slug = $('#editable-post-name').text();
							window.wsko_reload_content_optimizer_post_widget();
						});
						$(window).on("message", function(e) {
							var data = e.originalEvent.data;  // Should work.
							if (data == 'wsko_co_saving') {
								$('#wsko_co_saving_text_post').html('<i class="fa fa-spinner fa-spin"></i> <?php 
            echo  __( 'Saving', 'wsko' ) ;
            ?>');
							} else if (data == 'wsko_co_saving_success') {
								$('#wsko_co_saving_text_post').text("<?php 
            echo  __( 'All changes saved', 'wsko' ) ;
            ?>");
							} else if (data == 'wsko_co_saving_failed') {
								$('#wsko_co_saving_text_post').html('<i class="fa fa-times"></i> <?php 
            echo  __( 'Saving failed!', 'wsko' ) ;
            ?>');
							}
						});
						<?php 
            
            if ( $post_builder == 'gutenberg' ) {
                ?>
							if (typeof(wp.element) != 'undefined')
							{
								var el = wp.element.createElement;
								var Fragment = wp.element.Fragment;
								var PluginSidebar = wp.editPost.PluginSidebar;
								var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
								var registerPlugin = wp.plugins.registerPlugin;

								var sidebarEl = el('div', { id: "wsko_content_optimizer_ajax_reload_overlay", style: "position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:white;text-align:center;padding-top:10px;z-index:99;display:none;" },
									el('i', { className: "fa fa-spinner fa-pulse" })
								);
								function wsko_to_widget_link_event(event)
								{
									event.preventDefault();
									$([document.documentElement, document.body, $('.edit-post-layout__content').get(0)]).animate({
										scrollTop: $("#wsko_post_metabox").offset().top
									}, 2000);
								}
								function Component() {
									return el(
										Fragment,
										{},
										el(
											PluginSidebarMoreMenuItem,
											{
												target: 'wsko-co-gutenberg-sidebar',
											},
											'BAVOKO SEO Tools'
										),
										el(
											PluginSidebar,
											{
												name: 'wsko-co-gutenberg-sidebar',
												title: 'BAVOKO SEO Tools',
											},
											el('div', {},
												'<?php 
                echo  __( 'Gutenberg integration coming soon...', 'wsko' ) ;
                ?>',
												el('br', {}),
												el('a', {href: "#", onClick: wsko_to_widget_link_event}, '<?php 
                echo  __( 'To Widget', 'wsko' ) ;
                ?>')
											)
										)
									);
								}
								var iconEl = el('img', { src: "<?php 
                echo  WSKO_PLUGIN_URL ;
                ?>admin/img/logo-bl-sm.png" });
								registerPlugin('wsko-co', {
									icon: iconEl,
									render: Component,
								});
							}
							window.wsko_reload_content_optimizer_post_widget();
						<?php 
            } else {
                ?>
							setTimeout(function(){ //late bind tinymce
								if (typeof(tinymce) != "undefined")
								{
									for (var i = 0; i < tinymce.editors.length; i++) {
										tinymce.editors[i].onChange.add(function (ed, e) {
											post_content = ed.getContent();
											window.wsko_reload_content_optimizer_post_widget();
										});
									}
								}
								window.wsko_reload_content_optimizer_post_widget();
							}, 1000);
						<?php 
            }
            
            ?>
					});
				</script>
				
				<a target="_blank" class="button wsko-fetch-as-google" href="<?php 
            echo  WSKO_Class_Search::get_external_link( 'tool_fetch', WSKO_Class_Helper::get_host_base( true ), ltrim( WSKO_Class_Helper::get_relative_url( get_permalink( $post->ID ) ), '/' ) ) ;
            ?>"><?php 
            echo  __( 'Fetch as Google', 'wsko' ) ;
            ?></a>
				<span id="wsko_co_saving_text_post" style="position: absolute;top: -34px;font-size: 85%;opacity: .7;right: 150px;"></span>
				<?php 
            $co_iframe = WSKO_Controller_Iframe::get_iframe( 'co', $post->ID, array(
                'width'            => '100%',
                'height'           => '100%',
                'url_data'         => array(
                'widget' => true,
            ),
                'form_frame'       => array(
                'preview'      => '',
                'post_title'   => '',
                'post_content' => '',
                'post_slug'    => '',
            ),
                'form_frame_split' => true,
            ) );
            ?>
				<div id="wsko_content_optimizer_ajax_reload_wrapper" data-iframe="<?php 
            echo  htmlentities( $co_iframe['iframe'] ) ;
            ?>" data-iframe-form="<?php 
            echo  htmlentities( $co_iframe['form'] ) ;
            ?>" style="height:100%">
					<?php 
            //echo WSKO_Controller_Optimizer::render($post->ID, true);
            ?>
				</div>

				<div id="wsko_content_optimizer_ajax_reload_overlay" style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:white;text-align:center;padding-top:10px;z-index:99;display:none;">
					<?php 
            echo  WSKO_Class_Template::render_bst_preloader() ;
            ?> <?php 
            echo  __( 'Loading', 'wsko' ) ;
            ?>
				</div>
				<?php 
        }
    
    }
    
    public function admin_head()
    {
        if ( $this->controller ) {
            $this->controller->render_head_scripts();
        }
    }
    
    public function admin_footer()
    {
        /*global $post, $pagenow;
        		if ($pagenow != 'post.php')
        			return;*/
        global  $pagenow ;
        if ( !in_array( $pagenow, array( 'edit.php', 'post.php', 'term.php' ) ) && !$this->controller ) {
            return;
        }
        
        if ( !isset( $_GET['bst_iframe'] ) || !$_GET['bst_iframe'] ) {
            WSKO_Class_Template::render_template( 'misc/modal-co-iframe.php', array() );
            WSKO_Class_Template::render_template( 'misc/modal-content-optimizer.php', array() );
            WSKO_Class_Template::render_template( 'misc/modal-knowledge-base-article.php', array() );
            
            if ( static::$render_modals_flag ) {
                //is plugin page
                WSKO_Class_Template::render_template( 'misc/modal-general.php', array() );
                WSKO_Class_Template::render_template( 'misc/modal-feedback.php', array() );
                WSKO_Class_Template::render_template( 'misc/modal-pro.php', array() );
            }
        
        }
    
    }
    
    public function admin_loaded()
    {
        
        if ( $this->controller ) {
            global  $wsko_double_load_fix ;
            if ( $wsko_double_load_fix ) {
                return;
            }
            $wsko_double_load_fix = true;
            $res = $this->controller->custom_view();
            
            if ( $res ) {
                ?></head>
				<body>
					<?php 
                echo  $res ;
                ?>
					<div style="display:none;"><?php 
                wp_footer();
                ob_flush();
                //print remaining buffer(s) if any
                ?></div>
				</body>
				</html><?php 
                exit;
            }
        
        }
    
    }
    
    public function get_controller( $name, $switched = false, $set = true )
    {
        $contr = null;
        $name_p = WSKO_Class_Helper::safe_explode( 'wsko_', $name );
        $contr_n = end( $name_p );
        if ( $res = WSKO_Controller::get_registered_controller( $contr_n, $switched ) ) {
            $contr = $res;
        }
        if ( $set ) {
            $this->controller = $contr;
        }
        return $contr;
    }
    
    /*Singleton*/
    public static function get_instance()
    {
        if ( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}