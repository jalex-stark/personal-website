<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Core
{
    static  $init_cache ;
    public static function install()
    {
        if ( !WSKO_Class_Cache::get_wsko_option( 'install_time' ) ) {
            WSKO_Class_Cache::save_wsko_option( 'install_time', time() );
        }
        //WSKO_Class_Crons::register_cronjobs();
        WSKO_Class_Cache::check_database();
    }
    
    public static function update()
    {
        WSKO_Class_Cache::check_database();
    }
    
    public static function deinstall()
    {
        WSKO_Class_Crons::deregister_cronjobs();
    }
    
    public static function setup()
    {
        WSKO_Class_Cache::check_database();
        WSKO_Class_Core::set_default_settings();
        WSKO_Class_Crons::deregister_cronjobs();
        WSKO_Class_Crons::register_cronjobs();
        WSKO_Class_Core::pre_track( 2 );
    }
    
    public static function set_default_settings()
    {
        //if (WSKO_Class_Core::get_setting('defaults_loaded'))
        //return;
        WSKO_Class_Onpage::set_sitemap_params( array(
            'types' => array(
            'post' => array(
            'freq' => 'monthly',
        ),
            'page' => array(
            'freq' => 'monthly',
        ),
        ),
            'stati' => array( 'publish' ),
        ) );
        if ( !WSKO_Class_Core::has_setting( 'auto_canonical' ) ) {
            WSKO_Class_Core::save_setting( 'auto_canonical', true );
        }
        //WSKO_Class_Core::save_setting('auto_social_snippet', true);
        if ( !WSKO_Class_Core::has_setting( 'auto_social_thumbnail' ) ) {
            WSKO_Class_Core::save_setting( 'auto_social_thumbnail', true );
        }
        //if (!WSKO_Class_Core::has_setting('activate_content_optimizer'))
        //	WSKO_Class_Core::save_setting('activate_content_optimizer', true);
        //if (!WSKO_Class_Core::has_setting('auto_post_slug_redirects'))
        //	WSKO_Class_Core::save_setting('auto_post_slug_redirects', true);
        if ( !WSKO_Class_Core::has_setting( 'automatic_sitemap_ping' ) ) {
            WSKO_Class_Core::save_setting( 'automatic_sitemap_ping', true );
        }
        $post_types = WSKO_Class_Helper::get_public_post_types( 'names' );
        foreach ( $post_types as $pt ) {
            $res_o = WSKO_Class_Onpage::get_meta_object( $pt, 'post_type' );
            
            if ( !$res_o ) {
                $res = array(
                    'title' => '%post:post_title% | %site:blog_name%',
                );
                WSKO_Class_Onpage::set_meta_object( $pt, $res, 'post_type' );
            }
        
        }
        if ( !WSKO_Class_Core::has_setting( 'onpage_include_post_types' ) ) {
            WSKO_Class_Core::save_setting( 'onpage_include_post_types', 'post,page' );
        }
        if ( !WSKO_Class_Core::has_setting( 'content_optimizer_post_types_include' ) ) {
            WSKO_Class_Core::save_setting( 'content_optimizer_post_types_include', implode( ',', $post_types ) );
        }
        //WSKO_Class_Core::save_setting('defaults_loaded', true);
    }
    
    public static function init_classes( $force = false, $core_only = false )
    {
        require_once WSKO_PLUGIN_PATH . 'includes/internals.php';
        require_once WSKO_PLUGIN_PATH . 'const.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.helper.php';
        if ( static::$init_cache ) {
            return;
        }
        static::$init_cache = true;
        //if (WSKO_Class_Helper::is_wsko_ajax() || WSKO_Class_Helper::is_wsko_page() || WSKO_Class_Helper::is_wsko_cron())
        //WSKO_Class_Helper::activate_cloud_logging();
        require_once WSKO_PLUGIN_PATH . 'classes/class.template.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.localization.php';
        if ( $core_only ) {
            return;
        }
        require_once WSKO_PLUGIN_PATH . 'classes/class.backup.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.update.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.cache.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.onpage.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.search.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.crons.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.compatibility.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.knowledge.php';
        require_once WSKO_PLUGIN_PATH . 'classes/class.shortcodes.php';
        if ( WSKO_Class_Helper::is_wsko_ajax() || WSKO_Class_Helper::is_wsko_page() || WSKO_Class_Helper::is_wsko_cron() ) {
            //duplicator pro fix
            //TODO: do better
            //WSKO_Class_Core::include_lib('google');
        }
        //WSKO_Class_Update::check_version();
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.php';
        
        if ( WSKO_Class_Core::is_configured() ) {
            require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.dashboard.php';
            require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.setup.php';
        } else {
            require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.setup.php';
            require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.dashboard.php';
        }
        
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.search.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.onpage.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.tools.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.settings.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.knowledge.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.account.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.pricing.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.optimizer.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.download.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.info.php';
        require_once WSKO_PLUGIN_PATH . 'admin/controller/controller.iframe.php';
        //init necessaries
        add_action( 'wp_loaded', function () {
            WSKO_Class_Update::check_version();
        }, 999999 );
        
        if ( WSKO_Class_Core::is_configured() ) {
            WSKO_Class_Crons::add_cronjobs();
            WSKO_Class_Shortcodes::register_shortcodes();
            WSKO_Class_Onpage::register_hooks();
            WSKO_Class_Search::register_hooks();
            add_action( 'wp_enqueue_scripts', function () {
                wp_enqueue_script( 'jquery' );
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
                wp_enqueue_style(
                    'wsko_frontend_css',
                    WSKO_PLUGIN_URL . 'admin/css/frontend-widgets.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'css',
                    array(),
                    WSKO_VERSION
                );
                wp_enqueue_script(
                    'wsko_frontend_js',
                    WSKO_PLUGIN_URL . 'admin/js/frontend-widgets.' . (( WSKO_Class_Helper::is_dev() ? '' : 'min.' )) . 'js',
                    array(),
                    WSKO_VERSION
                );
            } );
            add_action( 'wp_footer', function () {
                global  $post ;
                if ( !is_admin() && is_singular() && $post ) {
                    // && WSKO_Class_Core::get_setting('activate_content_optimizer'))
                    
                    if ( WSKO_Class_Helper::check_user_permissions( false ) ) {
                        //$wsko_post_types_ex = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_optimizer_post_types_exclude'));
                        $wsko_post_types_in = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'content_optimizer_post_types_include' ) );
                        //if (!$wsko_post_types_ex || !in_array(get_post_type($post->ID), $wsko_post_types_ex))
                        
                        if ( $wsko_post_types_in && in_array( get_post_type( $post->ID ), $wsko_post_types_in ) ) {
                            add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
                                $args = array(
                                    'id'    => 'bst_frontend_co_wp',
                                    'title' => 'CO',
                                    'href'  => '#',
                                    'meta'  => array(
                                    'class' => 'bst-co-iframe-link bst-frontend-co-admin-button',
                                ),
                                );
                                $wp_admin_bar->add_node( $args );
                            }, 999 );
                            WSKO_Class_Template::render_template( 'misc/modal-co-iframe.php', array(
                                'post_frame' => htmlspecialchars( WSKO_Controller_Iframe::get_iframe( 'co', $post->ID, array(
                                'width'  => '100%',
                                'height' => '100%',
                            ) ) ),
                            ) );
                        }
                    
                    }
                
                }
            } );
        }
        
        
        if ( is_admin() ) {
            //if (!defined('DOING_AJAX') || !DOING_AJAX)
            //{
            require_once WSKO_PLUGIN_PATH . 'admin/admin.php';
            //}
            //Load Admin
            add_action( 'plugins_loaded', function () {
                $inst = new WSKO_Class_Core();
                add_filter( 'plugin_locale', array( $inst, 'modify_plugin_locale' ) );
                load_plugin_textdomain( 'wsko', false, basename( dirname( dirname( __FILE__ ) ) ) . '/languages/' );
                remove_filter( 'plugin_locale', array( $inst, 'modify_plugin_locale' ) );
                WSKO_AdminMenu::get_instance();
            } );
            if ( WSKO_Class_Core::get_option( 'refresh_permalinks' ) ) {
                add_action( 'wp_loaded', function () {
                    flush_rewrite_rules();
                }, 9999999 );
            }
            add_action( 'init', function () {
                WSKO_Class_Helper::add_error_post_type();
                WSKO_Class_Knowledge::add_post_types();
            } );
            //automatic fast reoccurring cronjobs
            
            if ( WSKO_Class_Core::is_configured() ) {
                if ( WSKO_Class_Search::get_se_token() && WSKO_Class_Search::get_se_property() && !WSKO_Class_Core::get_option( 'search_query_first_run' ) && !WSKO_Class_Core::get_option( 'search_query_running' ) && wp_next_scheduled( 'wsko_cache_keywords' ) > time() + 60 && WSKO_Class_Core::get_option( 'search_query_first_run_timeouts' ) < 5 ) {
                    WSKO_Class_Crons::bind_keyword_update( true );
                }
                if ( WSKO_Class_Core::get_option( 'onpage_analysis_running' ) && WSKO_Class_Core::get_option( 'last_onpage_segment_fetch' ) < time() - (WSKO_LRS_TIMEOUT + 10) && wp_next_scheduled( 'wsko_onpage_analysis' ) > time() + 60 ) {
                    WSKO_Class_Crons::bind_onpage_analysis( true );
                }
                if ( !WSKO_Class_Core::is_premium() ) {
                    add_action(
                        'after_plugin_row_' . WSKO_PLUGIN_KEY,
                        function ( $file, $plugin_data ) {
                        $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
                        echo  '<style>#wp-seo-keyword-optimizer-update-wsko .update-message p:before{content:"";margin: 0px;}.wsko-plugins-features-list li {display: inline-block;padding: 3px 8px;background-color: rgba(0, 0, 0, 0.05);margin-right: 5px;border-radius: 3px;</style>' ;
                        echo  '<tr class="plugin-update-tr active" id="' . esc_attr( $plugin_data['slug'] . '-update-wsko' ) . '" data-slug="' . esc_attr( $plugin_data['slug'] ) . '" data-plugin="' . esc_attr( WSKO_PLUGIN_KEY ) . '"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt" style="background-color: transparent;border-left: 0px;padding: 10px 15px;margin-top: 15px;">' ;
                        echo  '<div style="width: 40%;display:inline-block;vertical-align: top;">' ;
                        echo  '<p style="font-size: 14px;font-weight: 600;">' . wsko_loc( 'general', 'plugins_widget_head' ) . '</p>' ;
                        echo  '<p style="margin-bottom: 10px;">' . wsko_loc( 'general', 'plugins_widget_content' ) . '</p>' ;
                        echo  '<a href="https://www.bavoko.tools/pricing/" target="_blank" class="button button-primary wsko-pro-modal-link">' . __( 'Learn More!', 'wsko' ) . '</a>' ;
                        echo  '</div><div style="width: 60%;display:inline-block;">' ;
                        WSKO_Class_Template::render_ajax_beacon( 'wsko_get_ranking_deltas', array(
                            'post'     => array(
                            'type' => 'plugin_notice',
                        ),
                            'plain_js' => true,
                        ) );
                        echo  '</div></div></td></tr>' ;
                    },
                        10,
                        2
                    );
                }
                if ( WSKO_Class_Core::is_beta() ) {
                    /*
                    add_action('wp_dashboard_setup', function() {
                    	wp_add_dashboard_widget('wsko_dashboard_widget', 'BAVOKO SEO Tools', function($post, $callback_args){
                    		WSKO_Class_Template::render_template('misc/template-wp-dashboard-widget.php', array());
                    	});
                    });
                    */
                }
            }
        
        }
    
    }
    
    public function modify_plugin_locale( $locale )
    {
        $lang = WSKO_Class_Core::get_setting( 'plugin_lang' );
        
        if ( !$lang || $lang === 'auto' ) {
            return $locale;
        } else {
            return $lang;
        }
        
        //return $locale;
    }
    
    public static function pre_track( $step )
    {
        $last_step = WSKO_Class_Core::get_option( 'tracking_last_step' );
        if ( $last_step != 2 && $step > $last_step ) {
            switch ( $step ) {
                case 0:
                    WSKO_Class_Helper::post_to_url( WSKO_FEEDBACK_API, array(
                        'action' => 'pre_track',
                        'step'   => 0,
                        'site'   => home_url(),
                    ) );
                    WSKO_Class_Core::save_option( 'tracking_last_step', 0 );
                    break;
                case 1:
                    WSKO_Class_Helper::post_to_url( WSKO_FEEDBACK_API, array(
                        'action' => 'pre_track',
                        'step'   => 1,
                        'site'   => home_url(),
                    ) );
                    WSKO_Class_Core::save_option( 'tracking_last_step', 1 );
                    break;
                case 2:
                    WSKO_Class_Helper::post_to_url( WSKO_FEEDBACK_API, array(
                        'action' => 'pre_track',
                        'step'   => 2,
                        'site'   => home_url(),
                    ) );
                    WSKO_Class_Core::save_option( 'tracking_last_step', 2 );
                    break;
            }
        }
    }
    
    public static function get_install_time()
    {
        $install_time = WSKO_Class_Cache::get_wsko_option( 'install_time' );
        if ( !$install_time ) {
            WSKO_Class_Cache::save_wsko_option( 'install_time', $install_time = time() );
        }
        return $install_time;
    }
    
    public static function is_configured( $spec = false )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( $spec ) {
            
            if ( $spec === 'core' ) {
                return isset( $wsko_data['configured'] ) && $wsko_data['configured'];
            } else {
                if ( $spec === 'premium' ) {
                    return isset( $wsko_data['configured_prem'] ) && $wsko_data['configured_prem'];
                }
            }
        
        } else {
            $res = isset( $wsko_data['configured'] ) && $wsko_data['configured'];
            if ( $res ) {
            }
            return $res;
        }
    
    }
    
    public static function set_configured( $to = true )
    {
        
        if ( current_user_can( 'manage_options' ) ) {
            $wsko_data = WSKO_Class_Core::get_data();
            
            if ( $to ) {
                $wsko_data['configured'] = true;
            } else {
                unset( $wsko_data['configured'] );
            }
            
            WSKO_Class_Core::save_data( $wsko_data );
            return true;
        }
        
        return false;
    }
    
    public static function include_lib( $lib )
    {
        switch ( $lib ) {
            case 'dompdf':
                if ( !class_exists( '\\Dompdf\\Dompdf' ) ) {
                    require_once WSKO_PLUGIN_PATH . 'includes/DOMPDF/vendor/autoload.php';
                }
                break;
            case 'forceutf8':
                if ( !class_exists( '\\ForceUTF8\\Encoding' ) ) {
                    require_once WSKO_PLUGIN_PATH . 'includes/forceutf8/src/ForceUTF8/Encoding.php';
                }
                break;
            case 'iso_lang':
                if ( !class_exists( '\\League\\ISO3166\\ISO3166' ) ) {
                    require_once WSKO_PLUGIN_PATH . 'includes/iso3166/vendor/autoload.php';
                }
                break;
            case 'google':
                
                if ( !defined( 'WSKO_GOOGLE_INCLUDE_FAILED' ) ) {
                    $val = false;
                    
                    if ( !class_exists( 'Google_Client' ) ) {
                        //require_once(WSKO_PLUGIN_PATH . 'includes/google-api-php-client-2.1.0/vendor/autoload.php');
                        //require_once(WSKO_PLUGIN_PATH . 'includes/google-api-php-client-2.2.1/vendor/autoload.php');
                        require_once WSKO_PLUGIN_PATH . 'includes/google-api-php-client-2.2.2/vendor/autoload.php';
                        
                        if ( class_exists( 'Google_Client' ) ) {
                            $val = false;
                        } else {
                            $val = true;
                        }
                    
                    } else {
                        $val = true;
                    }
                    
                    define( 'WSKO_GOOGLE_INCLUDE_FAILED', $val );
                    if ( !$val ) {
                        WSKO_Class_Search::check_token();
                    }
                }
                
                break;
            case 'facebook':
                
                if ( !defined( 'WSKO_FACEBOOK_INCLUDE_FAILED' ) ) {
                    $val = false;
                    
                    if ( !class_exists( '\\Facebook\\Facebook' ) ) {
                        require_once WSKO_PLUGIN_PATH . 'includes/php-graph-sdk-5.x/src/Facebook/autoload.php';
                        
                        if ( class_exists( '\\Facebook\\Facebook' ) ) {
                            $val = false;
                        } else {
                            $val = true;
                        }
                    
                    } else {
                        $val = true;
                    }
                    
                    define( 'WSKO_FACEBOOK_INCLUDE_FAILED', $val );
                }
                
                break;
            case 'twitter':
                
                if ( !defined( 'WSKO_TWITTER_INCLUDE_FAILED' ) ) {
                    $val = false;
                    
                    if ( !class_exists( 'TwitterAPIExchange' ) ) {
                        require_once WSKO_PLUGIN_PATH . 'includes/twitter-exchange-api/TwitterAPIExchange.php';
                        
                        if ( class_exists( 'TwitterAPIExchange' ) ) {
                            $val = false;
                        } else {
                            $val = true;
                        }
                    
                    } else {
                        $val = true;
                    }
                    
                    define( 'WSKO_TWITTER_INCLUDE_FAILED', $val );
                }
                
                break;
            case 'freemius':
                if ( !function_exists( 'fs_dynamic_init' ) ) {
                    require_once WSKO_PLUGIN_PATH . '/includes/freemius/start.php';
                }
                break;
        }
    }
    
    public static function get_current_controller()
    {
        $inst = WSKO_AdminMenu::get_instance();
        if ( $inst ) {
            return $inst->controller;
        }
        return false;
    }
    
    public static function get_critical_errors()
    {
        $res = array();
        global  $wp_version ;
        if ( version_compare( $wp_version, '4.4', '<' ) ) {
            $res['incompatible_wp'] = true;
        }
        if ( version_compare( PHP_VERSION, '5.5', '<' ) ) {
            $res['incompatible_php'] = true;
        }
        
        if ( !file_exists( WP_CONTENT_DIR . '/bst/' ) && !is_writable( WP_CONTENT_DIR ) ) {
            $res['content_dir_access'] = true;
        } else {
            if ( !file_exists( WP_CONTENT_DIR . '/bst/' ) ) {
                mkdir( WP_CONTENT_DIR . '/bst/', 0755, true );
            }
            if ( !is_writable( WP_CONTENT_DIR . '/bst/' ) || !is_readable( WP_CONTENT_DIR . '/bst/' ) ) {
                $res['bst_dir_access'] = true;
            }
        }
        
        return ( empty($res) ? false : $res );
    }
    
    public static function get_data( $refetch = false )
    {
        return WSKO_Class_Cache::get_wp_option( 'wsko_init', function ( $wsko_data ) {
            
            if ( !$wsko_data || !is_array( $wsko_data ) ) {
                $wsko_data_t = WSKO_Class_Core::get_data_default();
                WSKO_Class_Core::save_data( $wsko_data_t );
                $wsko_data = $wsko_data_t;
            }
            
            return $wsko_data;
        }, $refetch );
    }
    
    public static function save_data( $wsko_data )
    {
        WSKO_Class_Cache::save_wp_option( 'wsko_init', $wsko_data );
    }
    
    public static function has_setting( $key )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['settings'] ) && is_array( $wsko_data['settings'] ) && isset( $wsko_data['settings'][$key] ) ) {
            return true;
        }
        return false;
    }
    
    public static function get_setting( $key )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['settings'] ) && is_array( $wsko_data['settings'] ) && isset( $wsko_data['settings'][$key] ) && $wsko_data['settings'][$key] ) {
            return $wsko_data['settings'][$key];
        }
        return false;
    }
    
    public static function save_setting( $key, $val )
    {
        
        if ( $key ) {
            $wsko_data = WSKO_Class_Core::get_data();
            
            if ( isset( $wsko_data['settings'] ) && is_array( $wsko_data['settings'] ) ) {
                $wsko_data['settings'][$key] = $val;
            } else {
                $wsko_data['settings'] = array(
                    $key => $val,
                );
            }
            
            WSKO_Class_Core::save_data( $wsko_data );
        }
    
    }
    
    public static function get_user_setting( $key, $user = false )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( $user === false ) {
            $user = get_current_user_id();
        }
        if ( $user ) {
            if ( isset( $wsko_data['user_settings'] ) && is_array( $wsko_data['user_settings'] ) && isset( $wsko_data['user_settings'][$user][$key] ) && $wsko_data['user_settings'][$user][$key] ) {
                return $wsko_data['user_settings'][$user][$key];
            }
        }
        return false;
    }
    
    public static function save_user_setting( $key, $val, $user = false )
    {
        
        if ( $key ) {
            if ( $user === false ) {
                $user = get_current_user_id();
            }
            
            if ( $user ) {
                $wsko_data = WSKO_Class_Core::get_data();
                
                if ( isset( $wsko_data['user_settings'][$user] ) && is_array( $wsko_data['user_settings'][$user] ) ) {
                    $wsko_data['user_settings'][$user][$key] = $val;
                } else {
                    
                    if ( !isset( $wsko_data['user_settings'] ) ) {
                        $wsko_data['user_settings'] = array(
                            $user => array(
                            $key => $val,
                        ),
                        );
                    } else {
                        $wsko_data['user_settings'][$user] = array(
                            $key => $val,
                        );
                    }
                
                }
                
                WSKO_Class_Core::save_data( $wsko_data );
            }
        
        }
    
    }
    
    public static function get_options( $refetch = false )
    {
        return WSKO_Class_Cache::get_wsko_option( 'wsko_options', false, $refetch );
    }
    
    public static function save_options( $wsko_data )
    {
        WSKO_Class_Cache::save_wsko_option( 'wsko_options', $wsko_data );
    }
    
    public static function get_option( $key, $refetch = false )
    {
        $data_options = WSKO_Class_Core::get_options( $refetch );
        if ( $data_options && is_array( $data_options ) && isset( $data_options[$key] ) && $data_options[$key] ) {
            return $data_options[$key];
        }
        return false;
    }
    
    public static function save_option( $key, $val, $refetch = true )
    {
        
        if ( $key ) {
            $data_options = WSKO_Class_Core::get_options( $refetch );
            
            if ( isset( $data_options ) && is_array( $data_options ) ) {
                $data_options[$key] = $val;
            } else {
                $data_options = array(
                    $key => $val,
                );
            }
            
            WSKO_Class_Core::save_options( $data_options );
        }
    
    }
    
    public static function get_data_default()
    {
        return array(
            'version' => WSKO_VERSION,
        );
    }
    
    public static function get_plugin_language( $real = false, $alpha2 = false )
    {
        $lang = WSKO_Class_Core::get_setting( 'plugin_lang' );
        if ( $real ) {
            return $lang;
        }
        
        if ( !$lang || $lang == 'auto' ) {
            global  $wp_version ;
            $lang = ( version_compare( $wp_version, '4.7', '>=' ) ? get_user_locale() : false );
            if ( !$lang ) {
                $lang = get_locale();
            }
        }
        
        switch ( $lang ) {
            case 'en_EN':
            case 'de_DE':
                break;
            default:
                $lang = 'en_EN';
                break;
        }
        
        if ( $alpha2 ) {
            $lang_parts = WSKO_Class_Helper::safe_explode( '_', $lang );
            $lang = reset( $lang_parts );
        }
        
        return $lang;
    }
    
    public static function set_post_data( $post_id, $data, $key = 'post_data' )
    {
        update_post_meta( $post_id, '_wsko_' . $key, $data );
    }
    
    public static function get_post_data( $post_id, $key = 'post_data' )
    {
        $res = get_post_meta( $post_id, '_wsko_' . $key, true );
        if ( !is_array( $res ) ) {
            $res = array();
        }
        return $res;
    }
    
    public static function delete_post_data( $post_id = false, $key = 'post_data' )
    {
        
        if ( $post_id ) {
            delete_post_meta( $post_id, '_wsko_' . $key );
        } else {
            global  $wpdb ;
            $table = $wpdb->prefix . 'postmeta';
            $wpdb->delete( $table, array(
                'meta_key' => '_wsko_' . $key,
            ) );
        }
    
    }
    
    public static function set_term_data( $term_id, $data )
    {
        update_term_meta( $term_id, '_wsko_term_data', $data );
    }
    
    public static function get_term_data( $term_id )
    {
        $res = get_term_meta( $term_id, '_wsko_term_data', true );
        if ( !is_array( $res ) ) {
            $res = array();
        }
        return $res;
    }
    
    public static function delete_term_data()
    {
        global  $wpdb ;
        $table = $wpdb->prefix . 'termmeta';
        $wpdb->delete( $table, array(
            'meta_key' => '_wsko_post_data',
        ) );
    }
    
    public static function add_post_data_history(
        $post_id,
        $key,
        $time,
        $data_v
    )
    {
        $data = WSKO_Class_Core::get_post_data( $post_id );
        if ( !isset( $data['history'] ) ) {
            $data['history'] = array();
        }
        if ( !isset( $data['history'][$key] ) ) {
            $data['history'][$key] = array();
        }
        $data['history'][$key][$time] = $data_v;
        $history_timeout = time() - 60 * 60 * 24 * WSKO_HISTORY_LIMIT;
        foreach ( $data['history'][$key] as $k => $d ) {
            if ( $k < $history_timeout ) {
                unset( $data['history'][$key][$k] );
            }
        }
        ksort( $data['history'][$key] );
        WSKO_Class_Core::set_post_data( $post_id, $data );
    }
    
    public static function get_post_data_history(
        $post_id,
        $key = false,
        $sort = true,
        $trim_after = -1
    )
    {
        if ( $trim_after <= 0 ) {
            $trim_after = 60 * 60 * 24 * 30;
        }
        $data = WSKO_Class_Core::get_post_data( $post_id );
        
        if ( $key ) {
            $curr_t = time();
            
            if ( isset( $data['history'][$key] ) ) {
                if ( $sort ) {
                    ksort( $data['history'][$key] );
                }
                foreach ( $data['history'][$key] as $dk => $dv ) {
                    if ( $dk < $curr_t - $trim_after ) {
                        unset( $data['history'][$key][$dk] );
                    }
                }
                if ( $trim_after ) {
                }
                return $data['history'][$key];
            }
        
        } else {
            if ( isset( $data['history'] ) ) {
                return $data['history'];
            }
        }
        
        return false;
    }
    
    public static function is_anonymous()
    {
        return wsko_fs()->is_anonymous();
    }
    
    public static function has_account()
    {
        return wsko_fs()->is_registered();
    }
    
    public static function is_opted_in()
    {
        return wsko_fs()->is_registered() && wsko_fs()->is_tracking_allowed();
    }
    
    public static function is_premium()
    {
        return wsko_fs()->is_premium() && wsko_fs()->can_use_premium_code();
    }
    
    public static function is_demo()
    {
        /*global $wsko_is_demo;
        		if (!$wsko_is_demo)
        			$wsko_is_demo = ((defined('WSKO_DEMO_SWITCH') && WSKO_DEMO_SWITCH)?1:-1);
        		return $wsko_is_demo == 1 ? true : false;*/
        return defined( 'WSKO_DEMO_SWITCH' ) && WSKO_DEMO_SWITCH;
    }
    
    public static function is_beta()
    {
        return defined( 'WSKO_BETA_SWITCH' ) && WSKO_BETA_SWITCH;
    }
    
    public static function get_white_label()
    {
        $white_label = WSKO_Class_Core::get_setting( 'white_label' );
        if ( !$white_label || !wsko_fs()->is_plan( 'advanced' ) ) {
            $white_label = WSKO_PLUGIN_URL . 'admin/img/logo.png';
        }
        return $white_label;
    }

}