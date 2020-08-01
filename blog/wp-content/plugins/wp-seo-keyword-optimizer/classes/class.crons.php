<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Crons
{
    public static function register_cronjobs()
    {
        /*if (!wp_next_scheduled('wsko_check_timeout'))
        		{
        			wp_schedule_event(WSKO_Class_Helper::get_midnight() + (60*60*12), 'daily', 'wsko_check_timeout'); //12:00
        		}*/
        
        if ( WSKO_Class_Core::is_configured() ) {
            WSKO_Class_Crons::bind_redirect_check( time() + 60 * 60 );
            WSKO_Class_Crons::bind_daily_maintenance();
            WSKO_Class_Crons::bind_sitemap_generation();
            if ( WSKO_Class_Onpage::get_onpage_analysis() || WSKO_Class_Core::get_option( 'start_with_onpage_crawl' ) ) {
                //WSKO_Class_Core::save_option('start_with_onpage_crawl', false);
                WSKO_Class_Crons::bind_onpage_analysis();
            }
            WSKO_Class_Crons::bind_keyword_update();
        }
    
    }
    
    public static function bind_keyword_info_update( $rebind = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_backlink_update();
        }
        
        if ( !wp_next_scheduled( 'wsko_keyword_info' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 60 * 16, 'daily', 'wsko_keyword_info' );
            //16:00
        }
    
    }
    
    public static function unbind_keyword_info_update()
    {
        wp_clear_scheduled_hook( 'wsko_keyword_info' );
    }
    
    public static function bind_backlink_update( $rebind = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_backlink_update();
        }
        
        if ( !wp_next_scheduled( 'wsko_cache_backlinks' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 60 * 6, 'daily', 'wsko_cache_backlinks' );
            //06:00
        }
        
        WSKO_Class_Core::save_option( 'backlink_crawl_started', true );
    }
    
    public static function unbind_backlink_update()
    {
        wp_clear_scheduled_hook( 'wsko_cache_backlinks' );
        WSKO_Class_Core::save_option( 'backlink_crawl_started', false );
    }
    
    public static function bind_daily_maintenance()
    {
        
        if ( !wp_next_scheduled( 'wsko_daily_maintenance' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 60 * 12, 'daily', 'wsko_daily_maintenance' );
            //12:00
        }
    
    }
    
    public static function bind_sitemap_generation()
    {
        
        if ( !wp_next_scheduled( 'wsko_sitemap_generation' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 45, 'hourly', 'wsko_sitemap_generation' );
            //**:45
        }
    
    }
    
    public static function unbind_sitemap_generation()
    {
        wp_clear_scheduled_hook( 'wsko_sitemap_generation' );
    }
    
    public static function bind_onpage_analysis( $rebind = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_onpage_analysis();
        }
        
        if ( !wp_next_scheduled( 'wsko_onpage_analysis' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 15, 'hourly', 'wsko_onpage_analysis' );
            //**:15
        }
    
    }
    
    public static function unbind_onpage_analysis()
    {
        wp_clear_scheduled_hook( 'wsko_onpage_analysis' );
    }
    
    public static function bind_prem_onpage_analysis( $rebind = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_prem_onpage_analysis();
        }
        
        if ( !wp_next_scheduled( 'wsko_prem_onpage_analysis' ) ) {
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + 60 * 20, 'hourly', 'wsko_prem_onpage_analysis' );
            //**:15
        }
    
    }
    
    public static function unbind_prem_onpage_analysis()
    {
        wp_clear_scheduled_hook( 'wsko_prem_onpage_analysis' );
    }
    
    public static function bind_keyword_update( $rebind = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_keyword_update();
        }
        
        if ( !wp_next_scheduled( 'wsko_cache_keywords' ) ) {
            $rand_offset = mt_rand( 0, 60 * 60 );
            wp_schedule_event( WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 + $rand_offset, 'daily', 'wsko_cache_keywords' );
            //**+~1:00
        }
    
    }
    
    public static function unbind_keyword_update()
    {
        wp_clear_scheduled_hook( 'wsko_cache_keywords' );
    }
    
    public static function bind_redirect_check( $rebind = false, $time = false )
    {
        if ( $rebind ) {
            WSKO_Class_Crons::unbind_redirect_check();
        }
        if ( !wp_next_scheduled( 'wsko_redirect_check' ) ) {
            wp_schedule_event( ( $time ? $time : time() ), 'daily', 'wsko_redirect_check' );
        }
    }
    
    public static function unbind_redirect_check()
    {
        wp_clear_scheduled_hook( 'wsko_redirect_check' );
    }
    
    public static function deregister_cronjobs()
    {
        wp_clear_scheduled_hook( 'wsko_cache_backlinks' );
        //wp_clear_scheduled_hook('wsko_check_timeout');
        wp_clear_scheduled_hook( 'wsko_daily_maintenance' );
        WSKO_Class_Crons::unbind_keyword_info_update();
        WSKO_Class_Crons::unbind_onpage_analysis();
        WSKO_Class_Crons::unbind_prem_onpage_analysis();
        WSKO_Class_Crons::unbind_sitemap_generation();
        WSKO_Class_Crons::unbind_keyword_update();
        WSKO_Class_Crons::unbind_redirect_check();
    }
    
    public static function add_cronjobs()
    {
        
        if ( WSKO_Class_Core::is_configured() ) {
            $inst = WSKO_Class_Crons::get_instance();
            add_action( 'wsko_cache_keywords', array( $inst, 'cronjob_cache_keywords' ) );
            add_action( 'wsko_cache_backlinks', array( $inst, 'cronjob_cache_backlinks' ) );
            //add_action('wsko_check_timeout', array($inst, 'cronjob_check_timeout'));
            add_action( 'wsko_onpage_analysis', array( $inst, 'cronjob_onpage_analysis' ) );
            add_action( 'wsko_prem_onpage_analysis', array( $inst, 'cronjob_prem_onpage_analysis' ) );
            add_action( 'wsko_sitemap_generation', array( $inst, 'cronjob_sitemap_generation' ) );
            add_action( 'wsko_daily_maintenance', array( $inst, 'conjob_daily_maintenance' ) );
            add_action( 'wsko_redirect_check', array( $inst, 'conjob_redirect_check' ) );
        }
    
    }
    
    function cronjob_cache_keywords()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'cache_keywords' );
        }
        //if ((WSKO_Class_Helper::get_current_time() - WSKO_Class_Helper::get_midnight()) > (60*60)) //skip first hour
        //{
        $start_time = time();
        WSKO_Class_Search::update_se_cache();
        if ( WSKO_Class_Core::get_option( 'search_query_first_run' ) && time() - $start_time < WSKO_LRS_TIMEOUT / 2 ) {
            WSKO_Class_Search::update_se_delta();
        }
        //}
    }
    
    function cronjob_cache_backlinks()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'cache_backlinks' );
        }
    }
    
    function cronjob_check_timeout()
    {
        /*if (!WSKO_Class_Crons::execute_limited_cron())
        			return;
        		/*$wsko_data = WSKO_Class_Core::get_data();
        		$wsko_data['timeout_check'] = time();
        		WSKO_Class_Core::save_data($wsko_data);
        		
        		WSKO_Class_Helper::safe_set_time_limit(WSKO_LRS_TIMEOUT);
        		sleep(WSKO_LRS_TIMEOUT - 10);
        		
        		/*$wsko_data = WSKO_Class_Core::get_data();
        		unset($wsko_data['timeout_check']);
        		WSKO_Class_Core::save_data($wsko_data);*/
    }
    
    function cronjob_premium_keyword_info()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'cache_keyword_info' );
        }
    }
    
    function cronjob_onpage_analysis()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'onpage_analysis' );
        }
        WSKO_Class_Onpage::generate_onpage_analysis();
    }
    
    function cronjob_prem_onpage_analysis()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'prem_onpage_analysis' );
        }
    }
    
    function cronjob_sitemap_generation()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'sitemap_generation' );
        }
        
        if ( WSKO_Class_Core::get_setting( 'automatic_sitemap' ) ) {
            WSKO_Class_Onpage::generate_sitemap();
            
            if ( WSKO_Class_Core::get_option( 'sitemap_dirty' ) ) {
                WSKO_Class_Onpage::upload_sitemap();
                WSKO_Class_Core::save_option( 'sitemap_dirty', false );
            }
        
        }
    
    }
    
    function conjob_daily_maintenance()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'daily_maintenance' );
        }
        try {
            //expired search cache
            
            if ( WSKO_Class_Core::get_setting( 'cache_time_limit' ) && WSKO_Class_Core::get_setting( 'cache_time_limit' ) > 90 ) {
                $start2 = strtotime( 'today' ) - 60 * 60 * 24 * WSKO_Class_Core::get_setting( 'cache_time_limit' );
                WSKO_Class_Cache::delete_cache_rows_before( $start2 );
            }
            
            //expired logs
            WSKO_Class_Helper::clear_logs();
            //auto backup
            WSKO_Class_Backup::update_backups();
            //clear session cache
            WSKO_Class_Cache::clear_session_cache();
        } catch ( \Exception $ex ) {
            WSKO_Class_Helper::report_error( 'exception', 'E0: ' . __( 'Maintenance Error', 'wsko' ), $ex );
        }
    }
    
    function conjob_redirect_check()
    {
        if ( !WSKO_Class_Crons::execute_limited_cron() ) {
            return;
        }
        if ( WSKO_Class_Helper::is_dev() ) {
            WSKO_Class_Helper::report_error( 'info', 'I0: ' . __( 'CronJob started', 'wsko' ), 'redirect_check' );
        }
        //remove invalid redirects
        WSKO_Class_Onpage::clean_auto_redirects();
    }
    
    public static function execute_limited_cron( $reset_timeout = true )
    {
        //FIXME: temp disabled (recheck this functionality)
        /*global $wsko_limit_cron_flag;
        		if ($wsko_limit_cron_flag)
        		{
        			//if ($reset_timeout)
        			//	WSKO_Class_Helper::safe_set_time_limit(WSKO_LRS_TIMEOUT);
        			return false;
        		}
        		$wsko_limit_cron_flag = true;*/
        return true;
    }
    
    //Singleton
    static  $instance ;
    public static function get_instance()
    {
        if ( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}