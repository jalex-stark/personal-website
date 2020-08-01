<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller_Settings extends WSKO_Controller
{
    //Options
    public  $admin_only = true ;
    public  $icon = "cog" ;
    public  $link = "settings" ;
    public  $styles = array( 'settings' ) ;
    public  $scripts = array( 'settings' ) ;
    public  $ajax_actions = array(
        'table_settings',
        'reporting_add_mail',
        'reporting_change_mail',
        'reporting_remove_mail',
        'reporting_update_data',
        'import_white_label',
        'regen_onpage_analysis',
        'reset_keyword_update',
        'request_api_access',
        'revoke_api_access',
        'update_api_cache',
        'delete_api_cache',
        /*'set_ga_an_profile',*/
        'clear_session_cache',
        'reset_configuration',
        'reset_cronjobs',
        'clear_error_reports',
        'backup_configuration',
        'delete_conf_backups',
        'load_configuration_backup',
        'delete_configuration_backup',
        'import_configuration_backup',
        'create_plugin_report',
        'delete_plugin_report',
    ) ;
    public  $template_folder = "settings" ;
    public function get_title()
    {
        return __( 'Settings', 'wsko' );
    }
    
    /*
    public function _build_js_tour($subpage)
    {
    	return array(
    		'title' => __('Settings Tour', 'wsko'),
    		'steps' => array(
    			array(
    				'title' => __('Step 1 Settings', 'wsko'),
    				'content' => __('Step 1 Settings Content', 'wsko'),
    				'data_name' => 'test_name_logo',
    			)
    		)
    	);
    }
    */
    public function redirect()
    {
    }
    
    public function load_lazy_page_data()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        if ( !current_user_can( 'manage_options' ) ) {
            return true;
        }
        $notif = "";
        $data = array();
        $data['api_search'] = WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(), true );
        return array(
            'success' => true,
            'data'    => $data,
            'notif'   => $notif,
        );
    }
    
    public function action_table_settings()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $params = array();
        $data = array();
        $is_custom = false;
        $is_formated = false;
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        $order = ( isset( $_POST['order'] ) ? $_POST['order'] : false );
        $offset = ( isset( $_POST['start'] ) ? intval( $_POST['start'] ) : 0 );
        $count = ( isset( $_POST['length'] ) ? intval( $_POST['length'] ) : 10 );
        $custom_filter = ( isset( $_POST['custom_filter'] ) ? $_POST['custom_filter'] : false );
        $search = ( isset( $_POST['search']['value'] ) ? sanitize_text_field( $_POST['search']['value'] ) : false );
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
        $filtered_data = 0;
        $total_data = 0;
        switch ( $arg ) {
            case 'error_logs':
                $is_custom = true;
                $post_type = WSKO_POST_TYPE_ERROR;
                $orderby = "";
                $keys = array(
                    'post_status',
                    'post_title',
                    'post_content',
                    'date'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = ( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] );
                }
                $orderdir = ( $orderdir ? 'ASC' : 'DESC' );
                $query = new WP_Query( array(
                    's'                => $search,
                    'post_type'        => $post_type,
                    'posts_per_page'   => $count,
                    'offset'           => $offset,
                    'orderby'          => $orderby,
                    'order'            => $orderdir,
                    'post_status'      => 'any',
                    'suppress_filters' => true,
                ) );
                $res = array();
                foreach ( $query->posts as $p ) {
                    $type = '';
                    switch ( $p->post_status ) {
                        case 'error':
                            $type = '<i class="fa fa-times-circle" style="color:red"></i>';
                            break;
                        case 'warning':
                            $type = '<i class="fa fa-exclamation-triangle" style="color:Gold"></i>';
                            break;
                        case 'info':
                            $type = '<i class="fa fa-info-circle" style="color:LightSkyBlue"></i>';
                            break;
                    }
                    $content = '<a href="#" class="button pull-right" data-toggle="collapse" data-target="#wsko_error_report_backtrace_' . $p->ID . '">' . __( 'Stacktrace', 'wsko' ) . '</a>' . htmlentities( $p->post_content );
                    /*if (strlen($content) > 500)
                    					{
                    						$content = substr($content, 0, 500).'... <button class="button" data-toggle="collapse" data-target="#wsko_error_report_details_'.$p->ID.'">More</button>
                    
                    						<div id="wsko_error_report_details_'.$p->ID.'" class="collapse">
                    							'.$content.'
                    						</div>';
                    					}*/
                    $content .= '<div id="wsko_error_report_backtrace_' . $p->ID . '" class="collapse">
						' . get_post_meta( $p->ID, '_wsko_backtrace', true ) . '
					</div>';
                    $res[] = array(
                        $type,
                        $p->post_title,
                        $content,
                        WSKO_Class_Helper::get_time_elapsed_string( get_the_date( 'd.m.Y H:i:s', $p->ID ), false, date( 'd.m.Y H:i:s', WSKO_Class_Helper::get_current_time() ) ) . '<br/><small class="text-off">' . get_the_date( 'd.m.Y', $p->ID ) . '<br/>' . get_the_date( 'H:i:s', $p->ID ) . '</small>'
                    );
                }
                $data = array(
                    'data'     => $res,
                    'filtered' => $query->found_posts,
                    'total'    => $query->found_posts,
                );
                break;
        }
        
        if ( $data ) {
            if ( !$is_custom ) {
                $data = $this->get_table_data( $data, $params, $is_formated );
            }
            if ( !$filtered_data && isset( $data['filtered'] ) ) {
                $filtered_data = $data['filtered'];
            }
            if ( !$total_data && isset( $data['total'] ) ) {
                $total_data = $data['total'];
            }
        }
        
        return array(
            'success'         => true,
            'data'            => ( $data ? $data['data'] : array() ),
            'recordsFiltered' => $filtered_data,
            'recordsTotal'    => $total_data,
        );
    }
    
    function action_regen_onpage_analysis()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Onpage::clear_onpage_analysis();
        WSKO_Class_Crons::bind_onpage_analysis( true );
        return true;
    }
    
    function action_reset_keyword_update()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Crons::bind_keyword_update( true );
        return true;
    }
    
    function action_clear_session_cache()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $this->delete_cache();
        return true;
    }
    
    function action_update_api_cache()
    {
        WSKO_Class_Helper::safe_set_time_limit( WSKO_LRS_TIMEOUT );
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $view = '';
        
        if ( isset( $_POST['api'] ) ) {
            switch ( $_POST['api'] ) {
                case 'ga_search':
                    WSKO_Class_Core::save_option( 'search_query_last_start', false );
                    //allow instant refetch
                    WSKO_Class_Search::update_se_cache();
                    WSKO_Class_Core::get_data( true );
                    $view = WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(), true );
                    break;
            }
            $this->delete_cache();
            return array(
                'success' => true,
                'view'    => $view,
            );
        }
    
    }
    
    function action_request_api_access()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $this->delete_cache();
        $view = '';
        if ( isset( $_POST['code'] ) && $_POST['code'] && isset( $_POST['type'] ) && $_POST['type'] ) {
            switch ( $_POST['type'] ) {
                case 'ga_search':
                    $show_default_error = true;
                    $err_view = "";
                    $client = WSKO_Class_Search::get_ga_client_se();
                    
                    if ( $client ) {
                        $client->authenticate( $_POST['code'] );
                        $token = $client->getAccessToken();
                        
                        if ( $token ) {
                            WSKO_Class_Search::set_se_token( $token );
                            $invalid = WSKO_Class_Search::check_se_access( true, true );
                            
                            if ( !$invalid ) {
                                $profiles = WSKO_Class_Search::get_se_properties( false, true );
                                if ( is_array( $profiles ) ) {
                                    foreach ( $profiles as $profile ) {
                                        if ( rtrim( $profile['url'], '/' ) == WSKO_Class_Search::get_search_base( false ) ) {
                                            WSKO_Class_Search::set_se_property( $profile['url'] );
                                        }
                                    }
                                }
                                WSKO_Class_Core::save_option( 'last_se_check', false );
                                //reset access check
                                WSKO_Class_Core::get_data( true );
                                $view = WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(), true );
                                return array(
                                    'success' => true,
                                    'view'    => $view,
                                    'msg'     => 'Login successfull',
                                );
                            } else {
                                $is_rate_error = false;
                                
                                if ( $invalid && $invalid instanceof \Exception ) {
                                    $errors = $invalid->getErrors();
                                    if ( $errors ) {
                                        foreach ( $errors as $err ) {
                                            
                                            if ( isset( $err['reason'] ) && in_array( $err['reason'], array( 'limitExceeded', 'rateLimitExceeded', 'dailyLimitExceeded' ) ) ) {
                                                $is_rate_error = true;
                                                $show_default_error = false;
                                                $err_view = WSKO_Class_Template::render_notification( 'error', array(
                                                    'msg' => wsko_loc( 'notif', 'api_unavailable' ),
                                                ), true );
                                            }
                                        
                                        }
                                    }
                                }
                            
                            }
                            
                            //WSKO_Class_Search::update_se_cache(true);
                        } else {
                            $err_view = WSKO_Class_Template::render_notification( 'error', array(
                                'msg' => wsko_loc( 'notif_search', 'invalid_token' ),
                            ), true );
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
                            'success'  => false,
                            'err_view' => $err_view,
                            'msg'      => __( 'Your credentials are invalid!', 'wsko' ),
                        );
                    }
                    
                    break;
                case 'ga_analytics':
                    break;
            }
        }
        return array(
            'success' => false,
        );
    }
    
    function action_revoke_api_access()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $this->delete_cache();
        $view = '';
        if ( isset( $_POST['api'] ) && $_POST['api'] ) {
            switch ( $_POST['api'] ) {
                case 'ga_search':
                    $token = WSKO_Class_Search::get_se_token();
                    
                    if ( $token ) {
                        $client = WSKO_Class_Search::get_ga_client_se();
                        if ( $client ) {
                            $client->revokeToken( $token );
                        }
                        WSKO_Class_Search::set_se_token( false );
                        WSKO_Class_Search::set_se_property( false );
                        WSKO_Class_Crons::unbind_keyword_update();
                        WSKO_Class_Core::get_data( true );
                        $view = WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(), true );
                        return array(
                            'success' => true,
                            'view'    => $view,
                            'msg'     => __( 'Logout successfull', 'wsko' ),
                        );
                    }
                    
                    break;
                case 'ga_analytics':
                    break;
            }
        }
    }
    
    function action_delete_api_cache()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $this->delete_cache();
        if ( isset( $_POST['api'] ) && $_POST['api'] ) {
            switch ( $_POST['api'] ) {
                case 'ga_search':
                    WSKO_Class_Cache::delete_cache_rows( array( 'search' ) );
                    WSKO_Class_Core::save_option( 'search_query_first_run', false );
                    WSKO_Class_Crons::bind_keyword_update( true );
                    WSKO_Class_Core::get_data( true );
                    $view = WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(), true );
                    return array(
                        'success' => true,
                        'view'    => $view,
                        'msg'     => __( 'Cache cleaned', 'wsko' ),
                    );
                    break;
                case 'ga_analytics':
                    break;
                case 'premium_keyword_infos':
                    break;
            }
        }
    }
    
    function action_reset_configuration()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        
        if ( isset( $_POST['delete_backups'] ) && $_POST['delete_backups'] && $_POST['delete_backups'] != 'false' ) {
            WSKO_Class_Backup::clear_backups();
        } else {
            WSKO_Class_Backup::backup_configuration();
        }
        
        if ( isset( $_POST['delete_redirects'] ) && $_POST['delete_redirects'] && $_POST['delete_redirects'] != 'false' ) {
            delete_option( 'wsko_redirects' );
        }
        
        if ( isset( $_POST['delete_metas'] ) && $_POST['delete_metas'] && $_POST['delete_metas'] != 'false' ) {
            WSKO_Class_Core::delete_post_data();
            WSKO_Class_Core::delete_term_data();
        }
        
        
        if ( isset( $_POST['delete_cache'] ) && $_POST['delete_cache'] && $_POST['delete_cache'] != 'false' ) {
            WSKO_Class_Cache::delete_cache();
            //WSKO_Class_Core::save_option('search_query_first_run', false);
        }
        
        WSKO_Class_Knowledge::clear_knowledge_base();
        WSKO_Class_Knowledge::clear_question_and_answer();
        WSKO_Class_Helper::clear_logs( true );
        WSKO_Class_Crons::deregister_cronjobs();
        $token = WSKO_Class_Search::get_se_token();
        
        if ( $token ) {
            $client = WSKO_Class_Search::get_ga_client_se();
            if ( $client ) {
                $client->revokeToken( $token );
            }
        }
        
        WSKO_Class_Cache::remove_wsko_option( 'wsko_options' );
        WSKO_Class_Cache::remove_wp_option( 'wsko_init' );
        WSKO_Class_Cache::remove_wp_option( 'wsko_bl_analysis' );
        WSKO_Class_Cache::remove_wp_option( 'wsko_op_analysis' );
        WSKO_Class_Cache::remove_wp_option( 'wsko_op_analysis_prem' );
        $this->delete_cache();
        WSKO_Class_Helper::refresh_permalinks();
        $new_data = WSKO_Class_Core::get_data( true );
        WSKO_Class_Core::set_default_settings();
        return array(
            'success'  => true,
            'redirect' => WSKO_Controller_Setup::get_link(),
            'msg'      => __( 'Plugin reset!', 'wsko' ),
        );
    }
    
    function action_reset_cronjobs()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Crons::deregister_cronjobs();
        WSKO_Class_Crons::register_cronjobs();
        return true;
    }
    
    function action_clear_error_reports()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Helper::clear_logs( true );
        return true;
    }
    
    function action_backup_configuration()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $targets = ( isset( $_POST['targets'] ) && is_array( $_POST['targets'] ) ? array_map( 'sanitize_text_field', $_POST['targets'] ) : array() );
        if ( $targets ) {
            if ( !in_array( 'general', $targets ) ) {
                $targets[] = 'general';
            }
        }
        WSKO_Class_Backup::backup_configuration( false, $targets );
        return true;
    }
    
    function action_delete_conf_backups()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Backup::clear_backups();
        return true;
    }
    
    function action_load_configuration_backup()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $key = ( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : false );
        $res = WSKO_Class_Backup::load_existing_configuration_backup( $key );
        if ( $res ) {
            return true;
        }
    }
    
    function action_delete_configuration_backup()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $key = ( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : false );
        WSKO_Class_Backup::delete_configuration_backup( $key );
        return true;
    }
    
    function action_import_configuration_backup()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $fileName = $_FILES['file']['name'];
        $fileType = $_FILES['file']['type'];
        $fileError = $_FILES['file']['error'];
        
        if ( $fileError == UPLOAD_ERR_OK ) {
            $fileContent = file_get_contents( $_FILES['file']['tmp_name'] );
            
            if ( $fileContent ) {
                $res = WSKO_Class_Backup::import_configuration_backup( $fileContent );
                if ( $res ) {
                    return true;
                }
            }
        
        }
    
    }
    
    function action_create_plugin_report()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        WSKO_Class_Reporting::create_report( true );
        return true;
    }
    
    function action_delete_plugin_report()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $report = ( isset( $_POST['report'] ) ? sanitize_text_field( $_POST['report'] ) : false );
        $res = WSKO_Class_Reporting::delete_report( $report );
        
        if ( $res ) {
            return true;
        } else {
            return array(
                'success' => true,
                'msg'     => __( 'Report could not be deleted!', 'wsko' ),
            );
        }
    
    }
    
    function action_reporting_add_mail()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
    }
    
    function action_reporting_change_mail()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
    }
    
    function action_reporting_remove_mail()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
    }
    
    function action_reporting_update_data()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
    }
    
    function action_import_white_label()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
    }
    
    //Singleton
    static  $instance ;
}
WSKO_Controller_Settings::init_controller();