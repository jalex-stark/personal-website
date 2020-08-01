<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Search
{
    public static  $ga_client_se ;
    public static  $ga_client_an ;
    public static  $ga_webmaster ;
    public static  $ga_analytics ;
    public static function register_hooks()
    {
        $inst = WSKO_Class_Search::get_instance();
        if ( WSKO_Class_Core::is_configured() ) {
            add_action( 'wp_head', array( $inst, 'get_search_head' ) );
        }
    }
    
    public function get_search_head()
    {
        $site_verification = WSKO_Class_Search::get_site_verification();
        if ( $site_verification ) {
            echo  '<meta name="google-site-verification" content="' . $site_verification . '">' ;
        }
    }
    
    public static function get_site_verification()
    {
        $search_data = WSKO_Class_Search::get_search_data();
        if ( isset( $search_data['site_verification'] ) && $search_data['site_verification'] ) {
            return $search_data['site_verification'];
        }
        return false;
    }
    
    public static function set_site_verification( $code )
    {
        $search_data = WSKO_Class_Search::get_search_data();
        $search_data['site_verification'] = $code;
        WSKO_Class_Search::set_search_data( $search_data );
    }
    
    public static function get_new_ga_client( $clear = false )
    {
        WSKO_Class_Core::include_lib( 'google' );
        try {
            WSKO_Class_Search::check_token();
            $client = new Google_Client();
            
            if ( $clear == false ) {
                $client->setAccessType( 'offline' );
                $client->setApprovalPrompt( 'force' );
                $client->setApplicationName( 'BAVOKO SEO Tools' );
                $client->setAuthConfig( WSKO_PLUGIN_PATH . 'includes/cred.json' );
                $client->setRedirectUri( 'urn:ietf:wg:oauth:2.0:oob' );
                //'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
            }
            
            //$client->addScope("https://www.googleapis.com/auth/webmasters.readonly"); //search data
            //$client->addScope("https://www.googleapis.com/auth/analytics.readonly"); //analytics data
            return $client;
        } catch ( \Exception $error ) {
            return false;
        }
        return false;
    }
    
    public static function get_ga_client_se()
    {
        if ( self::$ga_client_se ) {
            return self::$ga_client_se;
        }
        $client = WSKO_Class_Search::get_new_ga_client();
        
        if ( $client ) {
            $client->addScope( "https://www.googleapis.com/auth/webmasters.readonly" );
            //search data
            $token = WSKO_Class_Search::get_se_token();
            
            if ( $token ) {
                $client->setAccessToken( $token );
                $token = $client->getAccessToken();
            }
            
            return self::$ga_client_se = $client;
        }
        
        return false;
    }
    
    public static function get_ga_client_an()
    {
        if ( self::$ga_client_an ) {
            return self::$ga_client_an;
        }
        $client = WSKO_Class_Search::get_new_ga_client();
        
        if ( $client ) {
            $client->addScope( "https://www.googleapis.com/auth/analytics.readonly" );
            //analytics data
            $token = WSKO_Class_Search::get_an_token();
            
            if ( $token ) {
                $client->setAccessToken( $token );
                $token = $client->getAccessToken();
            }
            
            return self::$ga_client_an = $client;
        }
        
        return false;
    }
    
    public static function get_se_auth_url()
    {
        $client = WSKO_Class_Search::get_ga_client_se();
        if ( $client ) {
            return $client->createAuthUrl();
        }
        return false;
    }
    
    public static function get_an_auth_url()
    {
        $client = WSKO_Class_Search::get_ga_client_an();
        if ( $client ) {
            return $client->createAuthUrl();
        }
        return false;
    }
    
    public static function get_ga_webmaster()
    {
        if ( self::$ga_webmaster ) {
            return self::$ga_webmaster;
        }
        $client = WSKO_Class_Search::get_ga_client_se();
        
        if ( $client ) {
            $webmaster = new Google_Service_Webmasters( $client );
        } else {
            return false;
        }
        
        self::$ga_webmaster = $webmaster;
        return $webmaster;
    }
    
    public static function get_ga_analytics()
    {
        if ( self::$ga_analytics ) {
            return self::$ga_analytics;
        }
        $client = WSKO_Class_Search::get_ga_client_an();
        
        if ( $client ) {
            $analytics = new Google_Service_Analytics( $client );
        } else {
            return false;
        }
        
        self::$ga_analytics = $analytics;
        return $analytics;
    }
    
    public static function get_search_data( $key = false )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) ) {
            
            if ( $key ) {
                if ( isset( $wsko_data['search'][$key] ) ) {
                    return $wsko_data['search'][$key];
                }
                return false;
            }
            
            return $wsko_data['search'];
        }
        
        return false;
    }
    
    public static function set_search_data( $data )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        $wsko_data['search'] = $data;
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function get_se_token()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['se_token'] ) && $wsko_data['search']['se_token'] ) {
            return $wsko_data['search']['se_token'];
        }
        return false;
    }
    
    public static function set_se_token( $token )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) ) {
            $wsko_data['search']['se_token'] = $token;
        } else {
            $wsko_data['search'] = array(
                'se_token' => $token,
            );
        }
        
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function get_se_property()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['se_profile'] ) && $wsko_data['search']['se_profile'] ) {
            return esc_url( $wsko_data['search']['se_profile'] );
        }
        return false;
    }
    
    public static function set_se_property( $profile )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) ) {
            $wsko_data['search']['se_profile'] = $profile;
        } else {
            $wsko_data['search'] = array(
                'se_profile' => $profile,
            );
        }
        
        WSKO_Class_Core::save_data( $wsko_data );
        if ( WSKO_Class_Core::is_configured() ) {
            WSKO_Class_Crons::bind_keyword_update( true );
        }
        WSKO_Class_Core::save_option( 'search_query_first_run', false );
        //reset first report flag
        WSKO_Class_Core::save_option( 'search_query_first_run_step', false );
        //reset first report steps
        WSKO_Class_Core::save_option( 'search_query_first_run_timeouts', false );
        //reset first report timeouts
        WSKO_Class_Core::save_option( 'search_query_last_start', false );
        //allow instant refetch
        WSKO_Class_Core::save_option( 'last_se_check', false );
        //reset access check
        WSKO_Class_Core::save_option( 'se_has_v1', false );
        //v2 creds are currently active
    }
    
    public static function get_an_token()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['an_token'] ) && $wsko_data['search']['an_token'] ) {
            return $wsko_data['search']['an_token'];
        }
        return false;
    }
    
    public static function set_an_token( $token )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) ) {
            $wsko_data['search']['an_token'] = $token;
        } else {
            $wsko_data['search'] = array(
                'an_token' => $token,
            );
        }
        
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function get_an_profile()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['an_profile'] ) && $wsko_data['search']['an_profile'] ) {
            return $wsko_data['search']['an_profile'];
        }
        return false;
    }
    
    public static function set_an_profile( $profile )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) ) {
            $wsko_data['search']['an_profile'] = $profile;
        } else {
            $wsko_data['search'] = array(
                'an_profile' => $profile,
            );
        }
        
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function get_monitored_keywords()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['monitored_keywords'] ) && $wsko_data['search']['monitored_keywords'] ) {
            return $wsko_data['search']['monitored_keywords'];
        }
        return array();
    }
    
    public static function add_monitored_keyword( $keyword )
    {
        
        if ( $keyword ) {
            $wsko_data = WSKO_Class_Core::get_data();
            
            if ( !isset( $wsko_data['search'] ) ) {
                $wsko_data['search'] = array(
                    'monitored_keywords' => array( $keyword ),
                );
            } else {
                
                if ( isset( $wsko_data['search']['monitored_keywords'] ) ) {
                    if ( !in_array( $keyword, $wsko_data['search']['monitored_keywords'] ) ) {
                        $wsko_data['search']['monitored_keywords'][] = $keyword;
                    }
                } else {
                    $wsko_data['search']['monitored_keywords'] = array( $keyword );
                }
            
            }
            
            WSKO_Class_Core::save_data( $wsko_data );
        }
    
    }
    
    public static function remove_monitored_keyword( $keyword )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( isset( $wsko_data['search']['monitored_keywords'] ) ) {
            $key = array_search( $keyword, $wsko_data['search']['monitored_keywords'] );
            
            if ( $key !== false ) {
                unset( $wsko_data['search']['monitored_keywords'][$key] );
                WSKO_Class_Core::save_data( $wsko_data );
            }
        
        }
    
    }
    
    public static function get_disavowed_backlinks()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        if ( isset( $wsko_data['search'] ) && is_array( $wsko_data['search'] ) && isset( $wsko_data['search']['disavowed_backlinks'] ) && $wsko_data['search']['disavowed_backlinks'] ) {
            return $wsko_data['search']['disavowed_backlinks'];
        }
        return array(
            'urls'    => array(),
            'domains' => array(),
        );
    }
    
    public static function disavow_backlink( $url, $domain = false )
    {
        $disavowed = WSKO_Class_Search::get_disavowed_backlinks();
        $disavowed[( $domain ? 'domains' : 'urls' )][$url] = true;
        $wsko_data = WSKO_Class_Core::get_data();
        $wsko_data['search']['disavowed_backlinks'] = $disavowed;
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function remove_disavowed_backlink( $url, $domain = false )
    {
        $disavowed = WSKO_Class_Search::get_disavowed_backlinks();
        if ( isset( $disavowed[( $domain ? 'domains' : 'urls' )][$url] ) ) {
            unset( $disavowed[( $domain ? 'domains' : 'urls' )][$url] );
        }
        $wsko_data = WSKO_Class_Core::get_data();
        $wsko_data['search']['disavowed_backlinks'] = $disavowed;
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function check_token( $force_check = false )
    {
        global  $wsko_ga_token_checked ;
        
        if ( !$wsko_ga_token_checked || $force_check ) {
            $wsko_ga_token_checked = true;
            $token = WSKO_Class_Search::get_se_token();
            
            if ( $token ) {
                $client = WSKO_Class_Search::get_ga_client_se();
                
                if ( $client ) {
                    $client->setAccessToken( $token );
                    
                    if ( $client->isAccessTokenExpired() ) {
                        $client->refreshToken( $token['refresh_token'] );
                        $token_c = $client->getAccessToken();
                        if ( !isset( $token_c['refresh_token'] ) ) {
                            $token_c['refresh_token'] = $token['refresh_token'];
                        }
                        $client->setAccessToken( $token_c );
                        WSKO_Class_Search::set_se_token( $token_c );
                        self::$ga_client_se = $client;
                    }
                
                }
            
            }
        
        }
    
    }
    
    public static function check_se_connected()
    {
        $client = WSKO_Class_Search::get_ga_client_se();
        $token = WSKO_Class_Search::get_se_token();
        $profile = WSKO_Class_Search::get_se_property();
        return $client && $token && $profile;
    }
    
    public static function check_se_access( $force = false, $login = false )
    {
        global  $wsko_se_checked ;
        if ( !$force && isset( $wsko_se_checked ) ) {
            return $wsko_se_checked;
        }
        $last_check = WSKO_Class_Core::get_option( 'last_se_check' );
        if ( !$force && $last_check && $last_check > time() - 60 * 60 * 1 ) {
            return ( WSKO_Class_Core::get_option( 'last_se_result' ) ? true : false );
        }
        $wsko_se_checked = $res = true;
        WSKO_Class_Core::get_option( 'last_se_check', time() );
        WSKO_Class_Core::save_option( 'last_se_result', ( $wsko_se_checked ? true : false ) );
        try {
            $client = WSKO_Class_Search::get_ga_client_se();
            $token = WSKO_Class_Search::get_se_token();
            
            if ( $login ) {
                if ( WSKO_Class_Search::get_se_properties( true, true ) !== -1 ) {
                    $res = false;
                }
            } else {
                
                if ( $client && $token ) {
                    // && ($force || !WSKO_Class_Core::is_configured() || WSKO_Class_Core::get_option('search_query_first_run')))
                    $profile = WSKO_Class_Search::get_se_property();
                    
                    if ( $profile ) {
                        $webmaster = WSKO_Class_Search::get_ga_webmaster();
                        
                        if ( $webmaster ) {
                            $q = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
                            $q->setStartDate( date( "Y-m-d", time() - 60 * 60 * 24 ) );
                            $q->setEndDate( date( "Y-m-d", time() ) );
                            $q->setDimensions( array( 'query' ) );
                            $q->setRowLimit( '1' );
                            $q->setSearchType( 'web' );
                            //$data = $webmaster->searchanalytics->query(WSKO_Class_Search::get_search_base(true), $q);
                            $data = $webmaster->searchanalytics->query( $profile, $q );
                            $rows = $data->getRows();
                            $wsko_se_checked = $res = false;
                            WSKO_Class_Core::save_option( 'last_se_result', ( $wsko_se_checked ? true : false ) );
                        }
                    
                    }
                
                }
            
            }
        
        } catch ( \Exception $error ) {
            $res = $error;
            WSKO_Class_Helper::report_error( 'exception', 'E10: ' . __( 'Search - Access Check', 'wsko' ), $error );
        }
        return $res;
    }
    
    public static function check_an_connected()
    {
        $client = WSKO_Class_Search::get_ga_client_an();
        $token = WSKO_Class_Search::get_an_token();
        $profile = WSKO_Class_Search::get_an_profile();
        return $client && $token && $profile;
    }
    
    public static function check_an_access( $force = false, $login = false )
    {
        global  $wsko_an_checked ;
        if ( !$force && isset( $wsko_an_checked ) && !$login ) {
            return $wsko_an_checked;
        }
        $last_check = WSKO_Class_Core::get_option( 'last_an_check' );
        if ( !$force && $last_check && $last_check > time() - 60 * 60 * 1 ) {
            return ( WSKO_Class_Core::get_option( 'last_an_result' ) ? true : false );
        }
        if ( !$force && !$login ) {
            $wsko_an_checked = true;
        }
        WSKO_Class_Core::get_option( 'last_an_check', time() );
        WSKO_Class_Core::save_option( 'last_an_result', ( $wsko_an_checked ? true : false ) );
        $res = true;
        return $res;
    }
    
    public static function get_empty_se_row( $keyword )
    {
        $obj = new stdClass();
        $obj->keyval = $keyword;
        $obj->clicks = 0;
        $obj->impressions = 0;
        $obj->position = 0;
        $obj->ctr = 0;
        $obj->clicks_ref = null;
        $obj->impressions_ref = null;
        $obj->position_ref = null;
        $obj->ctr_ref = null;
        $rows = array( $obj );
        return $rows[0];
    }
    
    public static function get_se_properties( $login = false, $force_reload = false )
    {
        $se_profiles = WSKO_Class_Core::get_option( 'se_profiles' );
        $token = WSKO_Class_Search::get_se_token();
        
        if ( !$force_reload && ($se_profiles && WSKO_Class_Core::get_option( 'last_se_profile_check' ) > time() - 60 * 60 * 24 && WSKO_Class_Core::get_option( 'last_se_profile_token' ) == $token) ) {
            return $se_profiles;
        } else {
            //fetch daily and on new token
            $res = array();
            
            if ( $token ) {
                $webmaster = WSKO_Class_Search::get_ga_webmaster();
                if ( $webmaster ) {
                    try {
                        $sites = $webmaster->sites->listSites();
                        foreach ( $sites as $item ) {
                            $res[] = array(
                                'url'    => $item->getSiteUrl(),
                                'access' => $item->getPermissionLevel(),
                            );
                        }
                        WSKO_Class_Core::save_option( 'last_se_profile_check', time() );
                        WSKO_Class_Core::save_option( 'last_se_profile_token', $token );
                        WSKO_Class_Core::save_option( 'se_profiles', $res );
                        return $res;
                    } catch ( \Exception $e ) {
                        
                        if ( $login ) {
                            WSKO_Class_Helper::report_error( 'exception', 'E12: ' . __( 'Search - Access Check', 'wsko' ), $e );
                            return -1;
                        }
                    
                    }
                }
            }
        
        }
        
        return false;
    }
    
    public static function get_an_profiles( $login = false, $force_reload = false )
    {
        return false;
    }
    
    public static function get_an_data( $dimension, $options = array(), &$stats_out = false )
    {
        $limit = ( isset( $options['limit'] ) ? intval( $options['limit'] ) : false );
        $offset = ( isset( $options['offset'] ) ? intval( $options['offset'] ) : false );
        $orderby = ( isset( $options['orderby'] ) ? $options['orderby'] : false );
        $where = ( isset( $options['where'] ) ? $options['where'] : false );
        $having = ( isset( $options['having'] ) ? $options['having'] : false );
        $rows = array();
        return $rows;
    }
    
    public static function get_an_query_data( $dimension, $for_url = false )
    {
        return false;
    }
    
    public static function get_se_cache_keword_history( $start, $end, $keywords )
    {
        $field_sets = array(
            'search' => array(
            'type'   => array(
            'join' => true,
            'eval' => '=0',
        ),
            'keyval' => array(
            'join' => true,
            'eval' => ' IN ("' . implode( '","', array_map( 'esc_sql', $keywords ) ) . '")',
        ),
        ),
        );
        $args = array(
            'fieldset'        => 'search',
            'group'           => 'time',
            'vals'            => 'cast(time as date) as time,SUM(clicks) as clicks,SUM(impressions) as impressions,AVG(position) as position',
            'from_cache_days' => true,
        );
        $rows = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            $field_sets,
            $args,
            false
        );
        $args['with_ref'] = false;
        $keys = $keywords;
        $i = 0;
        $step = 500;
        $args['for_keys'] = array_map( 'esc_sql', array_slice( $keys, $i * $step, $step ) );
        do {
            $ref_rows = WSKO_Class_Cache::get_cache_rows(
                $start - ($end - $start),
                $start,
                $field_sets,
                $args,
                false
            );
            foreach ( $rows as $k => $row ) {
                $rows[$k]->position_ref = null;
                $rows[$k]->clicks_ref = null;
                $rows[$k]->impressions_ref = null;
                //$rows[$k]->ctr_ref = null;
                
                if ( isset( $ref_rows[$k] ) ) {
                    $rows[$k]->position_ref = round( $ref_rows[$k]->position, 0 );
                    $rows[$k]->clicks_ref = $ref_rows[$k]->clicks;
                    $rows[$k]->impressions_ref = $ref_rows[$k]->impressions;
                    //$rows[$k]->ctr_ref = round($ref_rows[$k]->ctr, 2);
                }
            
            }
            $i++;
            $args['for_keys'] = array_map( 'esc_sql', array_slice( $keys, $i * $step, $step ) );
        } while ($args['for_keys']);
        return $rows;
    }
    
    public static function get_se_cache_history_count( $start, $end )
    {
        $rows = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => '=2',
        ),
        ),
        ),
            false,
            false
        );
        if ( $rows ) {
            return count( $rows );
        }
        return 0;
    }
    
    public static function get_se_cache_history_position( $start_time, $end_time )
    {
        $bl_table = WSKO_Class_Cache::get_table_prefix( 'bl_domains' );
        $day = 60 * 60 * 24;
        //1 day
        //$end_time = WSKO_Class_Helper::get_midnight()-$day;
        //$start_time = $end_time - ($day*30);
        $step = 1;
        $step = $day * $step;
        $history = array();
        for ( $i = $start_time ;  $i < $end_time ;  $i += $step ) {
            $step_v = $i + $step;
            $cache_rows = WSKO_Class_Cache::get_cache_rows(
                $step_v - ($end_time - $start_time),
                $step_v,
                array(
                'search' => array(
                'type' => array(
                'join' => true,
                'eval' => "=0",
            ),
            ),
            ),
                array(
                'fieldset'        => 'search',
                'group'           => 'keyval',
                'vals'            => 'cast(time as date) as time, keyval, AVG(position) as position',
                'prefix'          => 'SELECT SUM(case when position between 0 and 10 then 1 else 0 end) as pos1,SUM(case when position between 10 and 20 then 1 else 0 end) as pos2,SUM(case when position between 20 and 30 then 1 else 0 end) as pos3,SUM(case when position between 30 and 40 then 1 else 0 end) as pos4
				,SUM(case when position between 40 and 50 then 1 else 0 end) as pos5,SUM(case when position between 50 and 60 then 1 else 0 end) as pos6,SUM(case when position between 60 and 70 then 1 else 0 end) as pos7,SUM(case when position between 70 and 80 then 1 else 0 end) as pos8
				,SUM(case when position between 80 and 90 then 1 else 0 end) as pos9,SUM(case when position between 90 and 100 then 1 else 0 end) as pos10 FROM(',
                'suffix'          => ') temp',
                'from_cache_days' => true,
            ),
                false
            );
            if ( $cache_rows ) {
                $history[$step_v] = array(
                    $step_v,
                    $cache_rows[0]->pos1,
                    $cache_rows[0]->pos2,
                    $cache_rows[0]->pos3,
                    $cache_rows[0]->pos4,
                    $cache_rows[0]->pos5,
                    $cache_rows[0]->pos6,
                    $cache_rows[0]->pos7,
                    $cache_rows[0]->pos8,
                    $cache_rows[0]->pos9,
                    $cache_rows[0]->pos10
                );
            }
        }
        return $history;
    }
    
    public static function get_se_cache_history( $start_time, $end_time, $dimension )
    {
        $type = -1;
        switch ( $dimension ) {
            case 'query':
                $type = 0;
                break;
            case 'page':
                $type = 1;
                break;
        }
        $cache_rows = WSKO_Class_Cache::get_cache_rows(
            $start_time,
            $end_time,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=" . $type,
        ),
        ),
        ),
            array(
            'from_cache_days' => true,
            'group'           => 'time',
            'vals'            => 'cast(time as date) as time,SUM(case when cache_id is null then 0 else 1 end) as count',
        ),
            false
        );
        return $cache_rows;
    }
    
    public static function get_page_backlinks( $post_id )
    {
        $res = array();
        return $res;
    }
    
    public static function get_analytics_performance()
    {
        return false;
    }
    
    public static function get_se_cache_stats( $start, $end )
    {
        $res = WSKO_Class_Cache::get_cache_rows_stats( $start, $end, array(
            array(
            'table_key'  => 'search',
            'result_key' => 'kw_sum',
            'filter'     => array(
            'type' => array(
            'eval' => '=0',
        ),
        ),
        ),
            array(
            'table_key'  => 'search',
            'result_key' => 'page_sum',
            'filter'     => array(
            'type' => array(
            'eval' => '=1',
        ),
        ),
        ),
            array(
            'table_key'  => 'search',
            'result_key' => 'date_sum',
            'filter'     => array(
            'type' => array(
            'eval' => '=2',
        ),
        ),
        ),
            array(
            'table_key'  => 'search',
            'result_key' => 'device_sum',
            'filter'     => array(
            'type' => array(
            'eval' => '=3',
        ),
        ),
        ),
            array(
            'table_key'  => 'search',
            'result_key' => 'country_sum',
            'filter'     => array(
            'type' => array(
            'eval' => '=4',
        ),
        ),
        )
        ) );
        if ( isset( $res[0] ) ) {
            return $res[0];
        }
        return false;
    }
    
    public static function get_se_cache_overview( $start, $end )
    {
        $start_ref = $start - ($end - $start);
        $res = array(
            'kw_count'            => 0,
            'kw_dist_count'       => 0,
            'page_count'          => 0,
            'page_dist_count'     => 0,
            'kw_clicks'           => 0,
            'kw_imp'              => 0,
            'kw_count_ref'        => 0,
            'kw_dist_count_ref'   => 0,
            'page_count_ref'      => 0,
            'page_dist_count_ref' => 0,
            'kw_clicks_ref'       => 0,
            'kw_imp_ref'          => 0,
            'max_page_clicks'     => 0,
            'max_page_imp'        => 0,
            'max_page_position'   => 0,
            'max_kw_clicks'       => 0,
            'max_kw_imp'          => 0,
            'max_kw_position'     => 0,
        );
        $res['max_cpc'] = 0;
        $res['max_sv'] = 0;
        $kw_stats = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=0",
        ),
        ),
        ),
            array(
            'prefix' => 'SELECT COUNT(*) as keywords, MAX(clicks) as max_clicks, MAX(impressions) as max_impressions, MAX(position) as max_position FROM(',
            'vals'   => 'keyval, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position',
            'suffix' => ') as temp',
            'group'  => 'keyval',
        ),
            false
        );
        if ( $kw_stats && isset( $kw_stats[0]->keywords ) ) {
            $res = array(
                'kw_count'        => $kw_stats[0]->keywords,
                'max_kw_clicks'   => $kw_stats[0]->max_clicks,
                'max_kw_imp'      => $kw_stats[0]->max_impressions,
                'max_kw_position' => $kw_stats[0]->max_position,
            );
        }
        $kw_dist = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=0",
        ),
        ),
        ),
            array(
            'vals'   => 'keyval, AVG(position) as position',
            'group'  => 'keyval',
            'prefix' => 'SELECT SUM(CASE when position between 0 and 10 then 1 else 0 end) as keywords_top, SUM(CASE when position between 10 and 20 then 1 else 0 end) as keywords_serp2, SUM(CASE when position between 20 and 30 then 1 else 0 end) as keywords_serp3, SUM(CASE when position between 30 and 40 then 1 else 0 end) as keywords_serp4, SUM(CASE when position between 40 and 50 then 1 else 0 end) as keywords_serp5, SUM(CASE when position between 50 and 60 then 1 else 0 end) as keywords_serp6, SUM(CASE when position between 60 and 70 then 1 else 0 end) as keywords_serp7, SUM(CASE when position between 70 and 80 then 1 else 0 end) as keywords_serp8, SUM(CASE when position between 80 and 90 then 1 else 0 end) as keywords_serp9, SUM(CASE when position between 90 and 100 then 1 else 0 end) as keywords_serp10 FROM(',
            'suffix' => ') as temp',
        ),
            false
        );
        
        if ( $kw_dist && isset( $kw_dist[0]->keywords_top ) ) {
            $res['kw_dist_count'] = $kw_dist[0]->keywords_top;
            $res['kw_dist'] = array(
                '1'  => $kw_dist[0]->keywords_top,
                '2'  => $kw_dist[0]->keywords_serp2,
                '3'  => $kw_dist[0]->keywords_serp3,
                '4'  => $kw_dist[0]->keywords_serp4,
                '5'  => $kw_dist[0]->keywords_serp5,
                '6'  => $kw_dist[0]->keywords_serp6,
                '7'  => $kw_dist[0]->keywords_serp7,
                '8'  => $kw_dist[0]->keywords_serp8,
                '9'  => $kw_dist[0]->keywords_serp9,
                '10' => $kw_dist[0]->keywords_serp10,
            );
        }
        
        $page_stats = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=1",
        ),
        ),
        ),
            array(
            'prefix' => 'SELECT COUNT(*) as pages, MAX(clicks) as max_clicks, MAX(impressions) as max_impressions, MAX(position) as max_position FROM(',
            'vals'   => 'keyval, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position',
            'suffix' => ') as temp',
            'group'  => 'keyval',
        ),
            false
        );
        
        if ( $page_stats && isset( $page_stats[0]->pages ) ) {
            $res['page_count'] = $page_stats[0]->pages;
            $res['max_page_clicks'] = $page_stats[0]->max_clicks;
            $res['max_page_imp'] = $page_stats[0]->max_impressions;
            $res['max_page_position'] = $page_stats[0]->max_position;
        }
        
        $page_dist = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=1",
        ),
        ),
        ),
            array(
            'vals'   => 'keyval, AVG(position) as position',
            'group'  => 'keyval',
            'prefix' => 'SELECT SUM(CASE when position between 0 and 10 then 1 else 0 end) as pages_top FROM(',
            'suffix' => ') as temp',
        ),
            false
        );
        if ( $page_dist && isset( $page_dist[0]->pages_top ) ) {
            $res['page_dist_count'] = $page_dist[0]->pages_top;
        }
        $date_stats = WSKO_Class_Cache::get_cache_rows(
            $start,
            $end,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=2",
        ),
        ),
        ),
            array(
            'vals' => 'SUM(clicks) as clicks, SUM(impressions) as impressions',
        ),
            false
        );
        
        if ( $date_stats && isset( $date_stats[0]->clicks ) ) {
            $res['kw_clicks'] = $date_stats[0]->clicks;
            $res['kw_imp'] = $date_stats[0]->impressions;
        }
        
        //ref
        $res['has_ref'] = false;
        $kw_stats = WSKO_Class_Cache::get_cache_rows(
            $start_ref,
            $start,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=0",
        ),
        ),
        ),
            array(
            'vals'   => 'keyval, AVG(position) as position',
            'group'  => 'keyval',
            'prefix' => 'SELECT COUNT(distinct keyval) as keywords, COUNT(distinct (CASE when position between 0 and 10 then keyval end)) as keywords_top FROM(',
            'suffix' => ') as temp',
        ),
            false
        );
        
        if ( $kw_stats && isset( $kw_stats[0]->keywords ) ) {
            $res['kw_count_ref'] = $kw_stats[0]->keywords;
            $res['kw_dist_count_ref'] = $kw_stats[0]->keywords_top;
        }
        
        $page_stats = WSKO_Class_Cache::get_cache_rows(
            $start_ref,
            $start,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=1",
        ),
        ),
        ),
            array(
            'vals'   => 'keyval, AVG(position) as position',
            'group'  => 'keyval',
            'prefix' => 'SELECT COUNT(distinct keyval) as pages, COUNT(distinct (CASE when position between 0 and 10 then keyval end)) as pages_top FROM(',
            'suffix' => ') as temp',
        ),
            false
        );
        
        if ( $page_stats && isset( $page_stats[0]->pages ) ) {
            $res['page_count_ref'] = $page_stats[0]->pages;
            $res['page_dist_count_ref'] = $page_stats[0]->pages_top;
        }
        
        $date_stats = WSKO_Class_Cache::get_cache_rows(
            $start_ref,
            $start,
            array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=2",
        ),
        ),
        ),
            array(
            'vals' => 'SUM(clicks) as clicks, SUM(impressions) as impressions',
        ),
            false
        );
        
        if ( $date_stats && isset( $date_stats[0]->clicks ) ) {
            $res['kw_clicks_ref'] = $date_stats[0]->clicks;
            $res['kw_imp_ref'] = $date_stats[0]->impressions;
        }
        
        return $res;
    }
    
    public static function get_an_cache_overview( $start, $end )
    {
        $res = array();
        return $res;
    }
    
    public static function get_an_backlink_history( $start_time, $end_time, $for_target = false )
    {
        $bl_table = WSKO_Class_Cache::get_table_prefix( 'bl_domains' );
        $day = 60 * 60 * 24;
        //1 day
        //$end_time = WSKO_Class_Helper::get_midnight()-$day;
        //$start_time = $end_time - ($day*30);
        $step = 1;
        $step = $day * $step;
        $history = array();
        $fieldsets = array(
            'bl_domains' => array(),
        );
        $override_join = false;
        $group = false;
        $prefix = false;
        $suffix = false;
        
        if ( $for_target ) {
            $fieldsets['analytics'] = array(
                'target' => array(
                'join' => false,
                'eval' => ' ="' . esc_sql( $for_target ) . '"',
            ),
            );
            $override_join = array(
                'analytics' => array(
                'join_left' => true,
                'key'       => 'domain',
                'table_r'   => 'bl_domains',
                'key_r'     => 'domain',
            ),
            );
            $prefix = "SELECT SUM(count_link) as count_link, COUNT(*) as count_domains FROM(";
            //$vals = $bl_table.'.backlinks_num as count_link, COUNT(DISTINCT '.$bl_table.'.domain) as count_domains';
            $vals = 'COUNT(*) as count_link, COUNT(DISTINCT ' . $bl_table . '.domain) as count_domains';
            $suffix = ") temp";
            $group = $bl_table . ".domain";
        } else {
            $vals = 'SUM(' . $bl_table . '.backlinks_num) as count_link, COUNT(DISTINCT ' . $bl_table . '.domain) as count_domains';
        }
        
        for ( $i = $start_time ;  $i < $end_time ;  $i += $step ) {
            $step_v = $i + $step;
            $hist = WSKO_Class_Cache::get_cache_rows(
                0,
                0,
                $fieldsets,
                array(
                'vals'          => $vals,
                'override_join' => $override_join,
                'where'         => array(
                $bl_table . '.last_seen' => '>' . ($step_v - 60 * 60 * 24 * WSKO_BACKLINK_LOST_TRESHOLD),
            ),
                'group'         => $group,
                'prefix'        => $prefix,
                'suffix'        => $suffix,
            ),
                false
            );
            /*$hist = WSKO_Class_Cache::get_cache_rows(0, 0, $fieldsets, array('vals'=>
            		'SUM('.$bl_table.'.backlinks_num) as count_link, COUNT(DISTINCT '.$bl_table.'.domain) as count_domains', 'override_join' => $override_join,
            		'where' => array($bl_table.'.last_seen' => '>'.$step_v), 'group' => $group, 'prefix' => $prefix, 'suffix' => $suffix), false);*/
            $history[$step_v] = array( date( "M. d, Y", $step_v ), $hist[0]->count_link, $hist[0]->count_domains );
        }
        return $history;
    }
    
    public static function update_se_cache()
    {
        WSKO_Class_Helper::prepare_heavy_operation( 'search_cache' );
        WSKO_Class_Helper::refresh_wp_cache( 'cron', true );
        $token = WSKO_Class_Search::get_se_token();
        
        if ( $token ) {
            $client = WSKO_Class_Search::get_ga_client_se();
            $profile = WSKO_Class_Search::get_se_property();
            
            if ( $client && $profile ) {
                //if (WSKO_Class_Core::get_option('search_query_first_run') && WSKO_Class_Core::get_option('search_query_last_start') > (time()-(60*60*12))) //allow refetch only half daily
                //return;
                $invalid = WSKO_Class_Search::check_se_access( true );
                
                if ( !$invalid ) {
                    try {
                        $start_i = 0;
                        $end_i = WSKO_SEARCH_REPORT_SIZE - 3;
                        //subtract 3 days as they are not fetchable
                        $start = WSKO_Class_Helper::get_midnight() - 60 * 60 * 24 * 3;
                        $timeout = 5;
                        WSKO_Class_Core::save_option( 'search_query_running', true );
                        WSKO_Class_Core::save_option( 'search_query_last_start', time() );
                        $step = false;
                        
                        if ( !WSKO_Class_Core::get_option( 'search_query_first_run' ) ) {
                            $step = intval( WSKO_Class_Core::get_option( 'search_query_first_run_step' ) );
                            
                            if ( !$step ) {
                                WSKO_Class_Core::save_option( 'search_query_first_run_step', 1 );
                                $step = 1;
                            } else {
                                if ( $step < 1 && $step > WSKO_SEARCH_INITIAL_REPORTS ) {
                                    $step = false;
                                }
                            }
                            
                            
                            if ( $step !== false ) {
                                $start_i = WSKO_SEARCH_INITIAL_REPORT_SIZE * ($step - 1);
                                $end_i = $start_i + WSKO_SEARCH_INITIAL_REPORT_SIZE;
                                if ( $step == WSKO_SEARCH_INITIAL_REPORTS ) {
                                    $end_i -= 3;
                                }
                            }
                        
                        }
                        
                        $error_report = '';
                        for ( $i = $start_i ;  $i < $end_i ;  $i++ ) {
                            WSKO_Class_Helper::safe_set_time_limit( 30 );
                            // reset timer for ongoing process
                            WSKO_Class_Search::check_token( true );
                            $curr = $start - 60 * 60 * 24 * $i;
                            
                            if ( $curr ) {
                                $curr2 = $curr + 60 * 60 * 23 + 60 * 59 + 59;
                                $has_cache_row = true;
                                //($temp && !empty($temp));
                                $stats = WSKO_Class_Search::get_se_cache_stats( $curr, $curr2 );
                                
                                if ( !$stats || !$stats->date_sum || $stats->kw_sum == 0 && $stats->page_sum == 0 && $stats->device_sum == 0 && $stats->country_sum == 0 ) {
                                    WSKO_Class_Cache::delete_cache_row( $curr, array( 'search' ) );
                                    $has_cache_row = false;
                                }
                                
                                
                                if ( !$has_cache_row ) {
                                    //Clean and refetch
                                    WSKO_Class_Cache::delete_cache_row( $curr, array( 'search' ) );
                                    //$curr2 = $curr - (60 * 6-0 * 24);
                                    sleep( 1 );
                                    //Sleep to not hit 5 QPS Limit
                                    $date_rows = WSKO_Class_Search::get_se_query_data(
                                        $curr,
                                        $curr2,
                                        'date',
                                        false
                                    );
                                    $page_rows = WSKO_Class_Search::get_se_query_data(
                                        $curr,
                                        $curr2,
                                        'page',
                                        false
                                    );
                                    $kw_rows = WSKO_Class_Search::get_se_query_data(
                                        $curr,
                                        $curr2,
                                        'query',
                                        false
                                    );
                                    $device_rows = array();
                                    $country_rows = array();
                                    
                                    if ( $date_rows === -1 || $page_rows === -1 || $kw_rows === -1 || $device_rows === -1 || $country_rows === -1 ) {
                                        //A Query failed
                                        $error_report .= "\r\n" . date( 'd.m.Y', $curr ) . ' - ' . (( $timeout > 0 ? 'Query Warning' : 'Query Error' )) . ' ' . '|KW: ' . (( is_array( $kw_rows ) ? ( empty($kw_rows) ? 'Set (Empty)' : 'Set' ) : $kw_rows )) . '|PA: ' . (( is_array( $page_rows ) ? ( empty($page_rows) ? 'Set (Empty)' : 'Set' ) : $page_rows )) . '|DA: ' . (( is_array( $date_rows ) ? ( empty($date_rows) ? 'Set (Empty)' : 'Set' ) : $date_rows ));
                                        $timeout--;
                                        /*if ($timeout == 0)
                                        		{
                                        			WSKO_Class_Helper::report_error('error', 'Search - Query Error', 'The query for "' . date('Y-m-d', $curr) . '" failed during the cache update, but all timeouts are used.', $add);
                                        		}
                                        		else if ($timeout > 0)
                                        		{
                                        			WSKO_Class_Helper::report_error('warning', 'Search - Query Error', 'The query for "' . date('Y-m-d', $curr) . '" failed during the cache update and will be pulled again.', $add);
                                        			$i--;
                                        		}*/
                                        continue;
                                    } else {
                                        
                                        if ( !$date_rows || empty($date_rows) ) {
                                            // || !$page_rows || !$kw_rows || !$device_rows || !$country_rows)
                                            //empty data
                                            WSKO_Class_Cache::set_cache_row( $curr, array( array(
                                                'set'   => 'search',
                                                'where' => array(
                                                'type' => '2',
                                            ),
                                                'rows'  => array( array(
                                                'keyval'      => date( 'd.m.Y 00:00:00', $curr ),
                                                'clicks'      => 0,
                                                'position'    => 0,
                                                'impressions' => 0,
                                            ) ),
                                            ) ) );
                                            $error_report .= "\r\n" . date( 'd.m.Y', $curr ) . ' - Empty Dataset';
                                        } else {
                                            $key_errors = 0;
                                            $kw_rows_c = array();
                                            foreach ( $kw_rows as $kw_row ) {
                                                
                                                if ( $kw_row->keys ) {
                                                    $kw_rows_c[] = array(
                                                        'keyval'      => $kw_row->keys[0],
                                                        'clicks'      => $kw_row->clicks,
                                                        'position'    => $kw_row->position,
                                                        'impressions' => $kw_row->impressions,
                                                    );
                                                } else {
                                                    $key_errors++;
                                                }
                                            
                                            }
                                            $page_rows_c = array();
                                            foreach ( $page_rows as $page_row ) {
                                                
                                                if ( $page_row->keys ) {
                                                    $post_id = WSKO_Class_Helper::url_to_postid( $page_row->keys[0] );
                                                    
                                                    if ( $post_id ) {
                                                        WSKO_Class_Core::add_post_data_history(
                                                            $post_id,
                                                            'search_clicks',
                                                            $curr,
                                                            $page_row->clicks
                                                        );
                                                        WSKO_Class_Core::add_post_data_history(
                                                            $post_id,
                                                            'search_pos',
                                                            $curr,
                                                            $page_row->position
                                                        );
                                                        WSKO_Class_Core::add_post_data_history(
                                                            $post_id,
                                                            'search_imp',
                                                            $curr,
                                                            $page_row->impressions
                                                        );
                                                        WSKO_Class_Core::add_post_data_history(
                                                            $post_id,
                                                            'search_ctr',
                                                            $curr,
                                                            $page_row->ctr
                                                        );
                                                    }
                                                    
                                                    $page_rows_c[] = array(
                                                        'keyval'      => $page_row->keys[0],
                                                        'clicks'      => $page_row->clicks,
                                                        'position'    => $page_row->position,
                                                        'impressions' => $page_row->impressions,
                                                    );
                                                } else {
                                                    $key_errors++;
                                                }
                                            
                                            }
                                            $date_rows_c = array();
                                            foreach ( $date_rows as $date_row ) {
                                                
                                                if ( $date_row->keys ) {
                                                    $date_rows_c[] = array(
                                                        'keyval'      => $date_row->keys[0],
                                                        'clicks'      => $date_row->clicks,
                                                        'position'    => $date_row->position,
                                                        'impressions' => $date_row->impressions,
                                                    );
                                                } else {
                                                    $key_errors++;
                                                }
                                            
                                            }
                                            
                                            if ( !$key_errors ) {
                                                $data = array( array(
                                                    'set'   => 'search',
                                                    'where' => array(
                                                    'type' => '0',
                                                ),
                                                    'rows'  => $kw_rows_c,
                                                ), array(
                                                    'set'   => 'search',
                                                    'where' => array(
                                                    'type' => '1',
                                                ),
                                                    'rows'  => $page_rows_c,
                                                ), array(
                                                    'set'   => 'search',
                                                    'where' => array(
                                                    'type' => '2',
                                                ),
                                                    'rows'  => $date_rows_c,
                                                ) );
                                                WSKO_Class_Cache::set_cache_row( $curr, $data );
                                            } else {
                                                $error_report .= "\r\n" . date( 'd.m.Y', $curr ) . ' - Skipped due to ' . $key_errors . ' key errors';
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                        
                        }
                        if ( $error_report ) {
                            WSKO_Class_Helper::report_error(
                                'error',
                                'E14: ' . __( 'Search - Query Error Report', 'wsko' ),
                                'The cache update has reported some problems:',
                                $error_report
                            );
                        }
                        
                        if ( $step !== false && $step < WSKO_SEARCH_INITIAL_REPORTS ) {
                            WSKO_Class_Helper::refresh_wp_cache( 'cron', true );
                            WSKO_Class_Core::save_option( 'search_query_first_run_step', $step + 1 );
                            //WSKO_Class_Crons::bind_keyword_update(true); //continue as soon as possible
                        } else {
                            WSKO_Class_Helper::refresh_wp_cache( 'cron', true );
                            WSKO_Class_Core::save_option( 'search_query_first_run_step', false );
                            WSKO_Class_Core::save_option( 'search_query_first_run', true );
                        }
                        
                        WSKO_Class_Core::save_option( 'search_query_first_run_timeouts', false );
                        WSKO_Class_Core::save_option( 'search_query_running', false );
                    } catch ( \Exception $error ) {
                        WSKO_Class_Core::save_option( 'search_query_running', false );
                        $last_error_id = WSKO_Class_Helper::report_error( 'exception', 'E15: ' . __( 'Search - Cache Error', 'wsko' ), $error );
                        WSKO_Class_Core::save_option( 'search_query_first_run_timeouts', intval( WSKO_Class_Core::get_option( 'search_query_first_run_timeouts' ) ) + 1 );
                        WSKO_Class_Core::save_option( 'search_query_first_run_timeout_error', $error->getMessage() );
                    }
                } else {
                    WSKO_Class_Core::save_option( 'search_query_first_run_timeouts', intval( WSKO_Class_Core::get_option( 'search_query_first_run_timeouts' ) ) + 1 );
                    WSKO_Class_Core::save_option( 'search_query_first_run_timeout_error', ( $invalid instanceof \Exception ? $invalid->getMessage() : 'no exception' ) );
                }
                
                /*else
                		{
                			WSKO_Class_Helper::report_error('error_dump', 'Search - Cache Authentication Error', $invalid);
                		}*/
            }
        
        }
        
        WSKO_Class_Helper::finish_heavy_operation( 'search_cache' );
    }
    
    public static function update_se_delta()
    {
        WSKO_Class_Helper::prepare_heavy_operation( 'search_delta' );
        $cache_id = WSKO_Class_Cache::get_cache_id( 0, true );
        
        if ( $cache_id ) {
            global  $wpdb ;
            $table_se_delta = WSKO_Class_Cache::get_table( 'search_delta' );
            $end_time = WSKO_Class_Helper::get_midnight();
            $start_time = $end_time - 60 * 60 * 24 * 30;
            $start_time_t = $start_time - ($end_time - $start_time);
            $end_time_t = $start_time;
            $args_kw = array(
                'search' => array(
                'type' => array(
                'join' => true,
                'eval' => "=0",
            ),
            ),
            );
            $args_page = array(
                'search' => array(
                'type' => array(
                'join' => true,
                'eval' => "=1",
            ),
            ),
            );
            $vals_kw_new = 'DISTINCT keyval, "' . $cache_id . '" as cache_id, "0" as type, "1" as new, "0" as lost';
            $vals_page_new = 'DISTINCT keyval, "' . $cache_id . '" as cache_id, "1" as type, "1" as new, "0" as lost';
            $ref_query_new = array(
                'key'   => 'keyval',
                'start' => $start_time_t,
                'end'   => $end_time_t,
            );
            $vals_kw_lost = 'DISTINCT keyval, "' . $cache_id . '" as cache_id, "0" as type, "0" as new, "1" as lost';
            $vals_page_lost = 'DISTINCT keyval, "' . $cache_id . '" as cache_id, "1" as type, "0" as new, "1" as lost';
            $ref_query_lost = array(
                'key'   => 'keyval',
                'start' => $start_time,
                'end'   => $end_time,
            );
            WSKO_Class_Cache::delete_cache_rows( array( 'search_delta' ), true );
            $kws_new = WSKO_Class_Cache::get_cache_rows(
                $start_time,
                $end_time,
                $args_kw,
                array(
                'has_no_ref' => $ref_query_new,
                'vals'       => $vals_kw_new,
            ),
                false,
                true
            );
            $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $table_se_delta . '(keyval, cache_id, type, new, lost) ' . $kws_new );
            $kws_old = WSKO_Class_Cache::get_cache_rows(
                $start_time_t,
                $end_time_t,
                $args_kw,
                array(
                'has_no_ref' => $ref_query_lost,
                'vals'       => $vals_kw_lost,
            ),
                false,
                true
            );
            $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $table_se_delta . '(keyval, cache_id, type, new, lost) ' . $kws_old );
            $pages_new = WSKO_Class_Cache::get_cache_rows(
                $start_time,
                $end_time,
                $args_page,
                array(
                'has_no_ref' => $ref_query_new,
                'vals'       => $vals_page_new,
            ),
                false,
                true
            );
            $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $table_se_delta . '(keyval, cache_id, type, new, lost) ' . $pages_new );
            $pages_old = WSKO_Class_Cache::get_cache_rows(
                $start_time_t,
                $end_time_t,
                $args_page,
                array(
                'has_no_ref' => $ref_query_lost,
                'vals'       => $vals_page_lost,
            ),
                false,
                true
            );
            $wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $table_se_delta . '(keyval, cache_id, type, new, lost) ' . $pages_old );
        }
        
        WSKO_Class_Helper::finish_heavy_operation( 'search_delta' );
    }
    
    public static function update_an_cache()
    {
        WSKO_Class_Helper::prepare_heavy_operation( 'analytics_cache' );
        WSKO_Class_Helper::finish_heavy_operation( 'analytics_cache' );
    }
    
    public static function get_se_custom_query(
        $start_time,
        $end_time,
        $dimension,
        $for_url = false,
        $search = false
    )
    {
        $query_rows = WSKO_Class_Search::get_se_query_data(
            $start_time,
            $end_time,
            $dimension,
            $for_url,
            $search
        );
        $rows = array();
        if ( $query_rows && $query_rows !== -1 ) {
            foreach ( $query_rows as $row ) {
                $row->position = round( $row->position, 0 );
                $row->keyval = $row->keys[0];
                unset( $row->keys );
                $rows[$row->keyval] = $row;
            }
        }
        return $rows;
    }
    
    public static function get_se_data_for_url(
        $start_time,
        $end_time,
        $dimension,
        $for_url
    )
    {
        $query_rows = WSKO_Class_Search::get_se_query_data(
            $start_time,
            $end_time,
            $dimension,
            $for_url
        );
        $rows = array();
        if ( $query_rows && $query_rows !== -1 ) {
            foreach ( $query_rows as $row ) {
                $row->position = round( $row->position, 0 );
                $row->keyval = $row->keys[0];
                unset( $row->keys );
                $rows[$row->keyval] = $row;
            }
        }
        return $rows;
    }
    
    public static function get_se_data(
        $start_time,
        $end_time,
        $dimension,
        $options = array(),
        &$stats_out = false
    )
    {
        $limit = ( isset( $options['limit'] ) ? intval( $options['limit'] ) : false );
        $offset = ( isset( $options['offset'] ) ? intval( $options['offset'] ) : false );
        $where = ( isset( $options['where'] ) ? $options['where'] : false );
        $having = ( isset( $options['having'] ) ? $options['having'] : false );
        $orderby = ( isset( $options['orderby'] ) ? $options['orderby'] : false );
        $with_ref = ( isset( $options['with_ref'] ) ? $options['with_ref'] : true );
        $is_new = ( isset( $options['is_new'] ) ? $options['is_new'] : false );
        $is_lost = ( isset( $options['is_lost'] ) ? $options['is_lost'] : false );
        $for_keys = ( isset( $options['for_keys'] ) ? $options['for_keys'] : false );
        $type = -1;
        switch ( $dimension ) {
            case 'query':
                $type = 0;
                break;
            case 'page':
                $type = 1;
                break;
            case 'date':
                $type = 2;
                if ( !$orderby ) {
                    $orderby = "keyval ASC";
                }
                break;
            case 'device':
                $type = 3;
                break;
            case 'country':
                $type = 4;
                break;
        }
        if ( !$orderby ) {
            $orderby = "clicks DESC";
        }
        $table_search = WSKO_Class_Cache::get_table_prefix( 'search' );
        //if ($order)
        //	$orderby = $order;
        $args = array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=" . $type,
        ),
        ),
        );
        $override_join = array();
        //if ($search)
        //{
        //$filters['keyval'] =  array('join' => false, 'eval' => "LIKE '%" . $search . "%'");
        //}
        if ( $for_keys ) {
            $args['search']['keyval'] = array(
                'join' => false,
                'eval' => ' IN ("' . implode( '","', array_map( 'esc_sql', $for_keys ) ) . '")',
            );
        }
        $vals = $table_search . '.keyval,AVG(position) as position,SUM(clicks) as clicks,SUM(impressions) as impressions,SUM(clicks)/SUM(impressions)*100 as ctr';
        $ref_query = false;
        
        if ( $is_new ) {
            
            if ( $type == 0 || $type == 1 ) {
                $args['search_delta'] = array(
                    'type' => array(
                    'join' => true,
                    'eval' => '=' . $type,
                ),
                    'new'  => array(
                    'join' => true,
                    'eval' => '=1',
                ),
                );
                $override_join['search_delta'] = array(
                    'key'     => 'keyval',
                    'table_r' => 'search',
                    'key_r'   => 'keyval',
                );
            } else {
                $ref_query = array(
                    'key'   => 'keyval',
                    'start' => $start_time - ($end_time - $start_time),
                    'end'   => $start_time,
                );
            }
        
        } else {
            
            if ( $is_lost ) {
                $start_time_t = $start_time - ($end_time - $start_time);
                $end_time_t = $start_time;
                
                if ( $type == 0 || $type == 1 ) {
                    $args['search_delta'] = array(
                        'type' => array(
                        'join' => true,
                        'eval' => '=' . $type,
                    ),
                        'lost' => array(
                        'join' => true,
                        'eval' => '=1',
                    ),
                    );
                    $override_join['search_delta'] = array(
                        'key'     => 'keyval',
                        'table_r' => 'search',
                        'key_r'   => 'keyval',
                    );
                } else {
                    $ref_query = array(
                        'key'   => 'keyval',
                        'start' => $start_time,
                        'end'   => $end_time,
                    );
                }
                
                $start_time = $start_time_t;
                $end_time = $end_time_t;
            }
        
        }
        
        $cache_rows = WSKO_Class_Cache::get_cache_rows(
            $start_time,
            $end_time,
            $args,
            array(
            'has_no_ref'    => $ref_query,
            'suffix'        => (( $limit ? 'LIMIT ' . $limit . ' ' : '' )) . (( $offset ? 'OFFSET ' . $offset . ' ' : '' )),
            'where'         => $where,
            'having'        => $having,
            'group'         => $table_search . '.keyval',
            'vals'          => $vals,
            'override_join' => $override_join,
        ),
            $orderby
        );
        $rows = array();
        foreach ( $cache_rows as $row ) {
            //if ($stats_out === true)
            //$stats_out = array('filtered' => $row->r_filtered_c, 'total' => $row->r_total_c);
            $row->position = round( $row->position, 0 );
            $row->ctr = round( $row->ctr, 2 );
            if ( !isset( $row->search_volume ) || $row->search_volume === null ) {
                $row->search_volume = -1;
            }
            if ( !isset( $row->cpc ) || $row->cpc === null ) {
                $row->cpc = -1;
            }
            if ( $dimension === 'date' ) {
                $row->temp_keyval = strtotime( $row->keyval );
            }
            $rows[$row->keyval] = $row;
        }
        
        if ( $stats_out ) {
            $stats_out = array(
                'filtered' => 0,
                'total'    => 0,
            );
            //$vals = 'keyval,AVG(position) as position,SUM(clicks) as clicks,SUM(impressions) as impressions,SUM(clicks)/SUM(impressions)*100 as ctr';
            //$args_c = array('search' => array('type' => array('join' => true, 'eval' => "=".$type)));
            $cache_stats = WSKO_Class_Cache::get_cache_rows(
                $start_time,
                $end_time,
                $args,
                array(
                'has_no_ref'    => $ref_query,
                'prefix'        => "SELECT COUNT(*) as num_filtered FROM(",
                'suffix'        => ') temp',
                'where'         => $where,
                'having'        => $having,
                'group'         => $table_search . '.keyval',
                'vals'          => $vals,
                'override_join' => $override_join,
            ),
                false
            );
            
            if ( $cache_stats ) {
                $stats_out['filtered'] = $cache_stats[0]->num_filtered;
                $stats_out['total'] = $cache_stats[0]->num_filtered;
            }
        
        }
        
        //' IN ("'.implode('","',array_map('esc_sql', array_keys($rows))).'")';
        
        if ( $with_ref ) {
            $args['with_ref'] = false;
            $keys = array_keys( $rows );
            $i = 0;
            $step = 500;
            $args['for_keys'] = array_map( 'esc_sql', array_slice( $keys, $i * $step, $step ) );
            do {
                $ref_rows = WSKO_Class_Search::get_se_data(
                    $start_time - ($end_time - $start_time),
                    $start_time,
                    $dimension,
                    $args,
                    $null
                );
                foreach ( $rows as $k => $row ) {
                    $rows[$k]->position_ref = null;
                    $rows[$k]->clicks_ref = null;
                    $rows[$k]->impressions_ref = null;
                    $rows[$k]->ctr_ref = null;
                    
                    if ( isset( $ref_rows[$k] ) ) {
                        $rows[$k]->position_ref = round( $ref_rows[$k]->position, 0 );
                        $rows[$k]->clicks_ref = $ref_rows[$k]->clicks;
                        $rows[$k]->impressions_ref = $ref_rows[$k]->impressions;
                        $rows[$k]->ctr_ref = round( $ref_rows[$k]->ctr, 2 );
                    }
                
                }
                $i++;
                $args['for_keys'] = array_map( 'esc_sql', array_slice( $keys, $i * $step, $step ) );
            } while ($args['for_keys']);
        }
        
        if ( $dimension === 'date' ) {
            uasort( $rows, function ( $a, $b ) {
                if ( $a->temp_keyval === $b->temp_keyval ) {
                    return 0;
                }
                return ( $a->temp_keyval > $b->temp_keyval ? 1 : -1 );
            } );
        }
        return $rows;
    }
    
    public static function get_se_query_data(
        $start_time,
        $end_time,
        $dimension,
        $for_url = false,
        $search = false
    )
    {
        $webmaster = WSKO_Class_Search::get_ga_webmaster();
        
        if ( $webmaster ) {
            $profile = WSKO_Class_Search::get_se_property();
            
            if ( $profile ) {
                $q = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
                $q->setStartDate( date( "Y-m-d", $start_time ) );
                $q->setEndDate( date( "Y-m-d", $end_time ) );
                $q->setDimensions( array( $dimension ) );
                $q->setRowLimit( WSKO_SEARCH_ROW_LIMIT );
                $q->setSearchType( 'web' );
                
                if ( $for_url || $search ) {
                    $filters = array();
                    
                    if ( $for_url ) {
                        $filter = new Google_Service_Webmasters_ApiDimensionFilter();
                        $filter->setDimension( "page" );
                        $filter->setOperator( "equals" );
                        $filter->setExpression( $for_url );
                        array_push( $filters, $filter );
                    }
                    
                    
                    if ( $search ) {
                        $filter = new Google_Service_Webmasters_ApiDimensionFilter();
                        $filter->setDimension( ( is_array( $search ) ? $search['key'] : $dimension ) );
                        $filter->setOperator( "contains" );
                        $filter->setExpression( ( is_array( $search ) ? $search['val'] : $search ) );
                        array_push( $filters, $filter );
                    }
                    
                    $filter_group = new Google_Service_Webmasters_ApiDimensionFilterGroup();
                    $filter_group->setGroupType( 'and' );
                    $filter_group->setFilters( $filters );
                    $q->setDimensionFilterGroups( array( $filter_group ) );
                }
                
                try {
                    //$data = $webmaster->searchanalytics->query(WSKO_Class_Search::get_search_base(), $q);
                    $data = $webmaster->searchanalytics->query( $profile, $q );
                    
                    if ( $data ) {
                        $rows = $data->getRows();
                        $res = array();
                        foreach ( $rows as $key => $row ) {
                            $res_o = new stdClass();
                            $res_o->keys = $row->getKeys();
                            $res_o->clicks = $row->getClicks();
                            $res_o->position = round( $row->getPosition() );
                            $res_o->impressions = $row->getImpressions();
                            $res_o->ctr = round( $res_o->clicks / $res_o->impressions * 100, 2 );
                            $res[$key] = $res_o;
                        }
                        return $res;
                    }
                
                } catch ( \Exception $e ) {
                }
            }
        
        }
        
        return -1;
    }
    
    public static function get_search_base( $append_slash = true )
    {
        $search_data = WSKO_Class_Search::get_search_data();
        
        if ( isset( $search_data['override_domain'] ) && $search_data['override_domain'] ) {
            return rtrim( $search_data['override_domain'], '/' ) . (( $append_slash ? '/' : '' ));
        } else {
            return WSKO_Class_Helper::get_host_base( $append_slash );
        }
    
    }
    
    public static function import_search_backlinks_list( $content )
    {
        return false;
    }
    
    public static function get_ranking_data( $time )
    {
        $res = false;
        $args = array(
            'search' => array(
            'type' => array(
            'join' => true,
            'eval' => "=0",
        ),
        ),
        );
        $table_search = WSKO_Class_Cache::get_table_prefix( 'search' );
        $kw_cache_stats = WSKO_Class_Cache::get_cache_rows(
            0,
            time(),
            $args,
            array(
            'having' => array(
            'kws' => '>500',
        ),
            'suffix' => 'LIMIT 1',
            'group'  => 'cache_id',
            'vals'   => 'cache_id, COUNT(DISTINCT keyval) as kws',
        ),
            'kws ASC'
        );
        $time2 = false;
        if ( $kw_cache_stats ) {
            $time2 = strtotime( WSKO_Class_Cache::get_cache_time( $kw_cache_stats[0]->cache_id ) );
        }
        if ( $time < $time2 ) {
            $time = $time2;
        }
        
        if ( $time2 && $time < time() - 60 * 60 * 24 * 30 ) {
            $res = array(
                'old' => array(
                'kws'         => 0,
                'clicks'      => 0,
                'impressions' => 0,
                'position'    => 0,
            ),
                'new' => array(
                'kws'         => 0,
                'clicks'      => 0,
                'impressions' => 0,
                'position'    => 0,
            ),
            );
            $args = array(
                'search' => array(
                'type' => array(
                'join' => true,
                'eval' => "=2",
            ),
            ),
            );
            $cache_stats = WSKO_Class_Cache::get_cache_rows(
                $time - 60 * 60 * 24 * 30,
                $time,
                $args,
                array(
                'prefix' => "SELECT SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position FROM(",
                'suffix' => ') temp',
            ),
                false
            );
            $cache_stats_new = WSKO_Class_Cache::get_cache_rows(
                time() - 60 * 60 * 24 * 33,
                time() - 60 * 60 * 24 * 3,
                $args,
                array(
                'prefix' => "SELECT SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(position) as position FROM(",
                'suffix' => ') temp',
            ),
                false
            );
            
            if ( $cache_stats ) {
                $res['old']['clicks'] = $cache_stats[0]->clicks;
                $res['old']['impressions'] = $cache_stats[0]->impressions;
                $res['old']['position'] = $cache_stats[0]->position;
            }
            
            
            if ( $cache_stats_new ) {
                $res['new']['clicks'] = $cache_stats_new[0]->clicks;
                $res['new']['impressions'] = $cache_stats_new[0]->impressions;
                $res['new']['position'] = $cache_stats_new[0]->position;
            }
            
            $args = array(
                'search' => array(
                'type' => array(
                'join' => true,
                'eval' => "=0",
            ),
            ),
            );
            $kw_cache_stats = WSKO_Class_Cache::get_cache_rows(
                $time - 60 * 60 * 24 * 30,
                $time,
                $args,
                array(
                'vals' => 'COUNT(DISTINCT keyval) as kws',
            ),
                false
            );
            $kw_cache_stats_new = WSKO_Class_Cache::get_cache_rows(
                time() - 60 * 60 * 24 * 33,
                time() - 60 * 60 * 24 * 3,
                $args,
                array(
                'vals' => 'COUNT(DISTINCT keyval) as kws',
            ),
                false
            );
            if ( $kw_cache_stats ) {
                $res['old']['kws'] = $kw_cache_stats[0]->kws;
            }
            if ( $kw_cache_stats_new ) {
                $res['new']['kws'] = $kw_cache_stats_new[0]->kws;
            }
        }
        
        return $res;
    }
    
    public static function get_external_link( $type, $arg = false, $arg2 = false )
    {
        switch ( $type ) {
            case 'console':
                return 'https://www.google.com/webmasters/tools/home';
            case 'how_to':
                return 'https://support.google.com/webmasters/answer/34592';
            case 'support_report':
                return 'https://support.google.com/webmasters/answer/6155685';
            case 'property_guide':
                return 'https://support.google.com/webmasters/answer/35179';
            case 'sitemap_guide':
                return 'https://support.google.com/webmasters/answer/183668';
            case 'disavow':
                return 'https://www.google.com/webmasters/tools/disavow-links-main';
            case 'favicon':
                return 'https://www.google.com/s2/favicons?domain=' . urlencode( $arg );
            case 'ping_google':
                return 'http://www.google.com/webmasters/tools/ping?' . http_build_query( array(
                    'sitemap' => $arg,
                ) );
            case 'ping_bing':
                return 'http://www.bing.com/webmaster/ping.aspx?' . http_build_query( array(
                    'sitemap' => $arg,
                ) );
            case 'tool_backlinks':
                return 'https://www.google.com/webmasters/tools/external-links-domain?siteUrl=' . urlencode( $arg );
                //case 'tool_fetch': return 'https://www.google.com/webmasters/tools/googlebot-fetch?siteUrl='.urlencode($arg).'&path='.urlencode($arg2);
            //case 'tool_fetch': return 'https://www.google.com/webmasters/tools/googlebot-fetch?siteUrl='.urlencode($arg).'&path='.urlencode($arg2);
            case 'tool_fetch':
                return 'https://search.google.com/search-console?resource_id=' . urlencode( $arg );
        }
        return '#';
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