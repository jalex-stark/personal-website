<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller_Search extends WSKO_Controller
{
    //Options
    public  $icon = "search" ;
    public  $link = "search" ;
    public  $uses_timespan = true ;
    public  $scripts = array( 'search' ) ;
    public  $styles = array( 'search' ) ;
    public  $ajax_actions = array(
        'get_search_serp_data',
        'do_keyword_research',
        'get_keyword_research',
        'delete_keyword_research',
        'table_search',
        'add_monitoring_keyword',
        'remove_monitoring_keyword',
        'query_search_custom',
        'get_report_suggestions'
    ) ;
    public  $template_folder = "search" ;
    public  $subpages = array(
        'overview'         => array(
        'template' => 'search/page-overview.php',
    ),
        'keywords'         => array(
        'template' => 'search/page-keywords.php',
    ),
        'pages'            => array(
        'template' => 'search/page-pages.php',
    ),
        'dev_coun'         => array(
        'template'   => 'search/page-devices-countries.php',
        'is_premium' => true,
    ),
        'keyword_research' => array(
        'template'   => 'search/page-research.php',
        'is_premium' => true,
    ),
        'monitoring'       => array(
        'template' => 'search/page-monitoring.php',
    ),
    ) ;
    public function get_title()
    {
        return 'Search';
    }
    
    public function get_subpage_title( $subpage )
    {
        switch ( $subpage ) {
            case 'overview':
                return __( 'Overview', 'wsko' );
            case 'keywords':
                return __( 'Keywords', 'wsko' );
            case 'pages':
                return __( 'Pages', 'wsko' );
            case 'dev_coun':
                return __( 'Countries & Devices', 'wsko' );
            case 'competitors':
                return __( 'Competitors', 'wsko' );
            case 'keyword_research':
                return __( 'Keyword Research', 'wsko' );
            case 'monitoring':
                return __( 'Keyword Monitoring', 'wsko' );
        }
        return '';
    }
    
    public function get_knowledge_base_tags( $subpage )
    {
        $res = array( 'search' );
        switch ( $subpage ) {
            case 'overview':
                $res[] = "search_overview";
                break;
            case 'keywords':
                $res[] = "search_keywords";
                break;
            case 'pages':
                $res[] = "search_pages";
                break;
            case 'dev_coun':
                $res[] = "search_countries_devices";
                break;
            case 'competitors':
                $res[] = "search_competitors";
                break;
            case 'keyword_research':
                $res[] = "search_keyword_research";
                break;
            case 'monitoring':
                $res[] = "search_keyword_monitoring";
                break;
        }
        return $res;
    }
    
    /*
    public function _build_js_tour($subpage)
    {
    	switch ($subpage)
    	{
    		case 'overview': return array(
    			'title' => __('Search Tour', 'wsko'),
    			'steps' => array(
    				array(
    					'title' => __('Step 1 Search', 'wsko'),
    					'content' => __('Step 1 Search Content', 'wsko'),
    					'data_name' => 'test_name_logo',
    				)
    			)
    		);
    		case 'keywords': return array(
    			'title' => __('Keywords Tour', 'wsko'),
    			'steps' => array(
    				array(
    					'title' => __('Step 1 Keywords', 'wsko'),
    					'content' => __('Step 1 Keywords Content', 'wsko'),
    					'data_name' => 'test_name_logo',
    				)
    			)
    		);
    	}
    	return array();
    }
    */
    public function load_lazy_page_data( $lazy_data )
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $notif = "";
        $data = array();
        $dynamic_elements = false;
        $is_widget = false;
        if ( isset( $lazy_data['widget'] ) && $lazy_data['widget'] ) {
            $is_widget = true;
        }
        $page = $this->get_current_subpage();
        
        if ( $page ) {
            global  $wpdb ;
            $accessible = $this->is_accessible( $page );
            $client = WSKO_Class_Search::get_ga_client_se();
            switch ( $page ) {
                case 'overview':
                    
                    if ( $accessible ) {
                        $snippet_data = $this->get_cached_var_for_time( 'snippet_data' );
                        $table_data = $this->get_cached_var_for_time( 'table_data' );
                        
                        if ( $is_widget ) {
                            $graph_data = $this->get_cached_var_for_time( 'overview_graph_data', array(
                                'widget' => $is_widget,
                            ) );
                        } else {
                            $graph_data = $this->get_cached_var_for_time( 'overview_graph_data' );
                        }
                        
                        $nextCron = wp_next_scheduled( 'wsko_cache_keywords' );
                        $is_admin = current_user_can( 'manage_options' );
                        //Snippets
                        $data['total_keywords'] = '<span class="wsko-label">' . (( (( $snippet_data['kw_count'] == WSKO_SEARCH_ROW_LIMIT ? '<i data-toggle="tooltip" title="' . sprintf( __( 'Google Search API only allows %d rows per query. This limit will be removed in a further version.', 'wsko' ), WSKO_SEARCH_ROW_LIMIT ) . '">></i>' : '' )) . $snippet_data['kw_count'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_count'] ) : 0 )) . '</span>';
                        $data['total_keywords'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_count_ref'],
                            $snippet_data['kw_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_keyword_dist'] = '<span class="wsko-label">' . (( $snippet_data['kw_dist_count'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_dist_count'] ) : 0 )) . '</span>';
                        $data['total_keyword_dist'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_dist_count_ref'],
                            $snippet_data['kw_dist_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_clicks'] = '<span class="wsko-label">' . (( $snippet_data['kw_clicks'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_clicks'] ) : 0 )) . '</span>';
                        $data['total_clicks'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_clicks_ref'],
                            $snippet_data['kw_clicks_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_impressions'] = '<span class="wsko-label">' . (( $snippet_data['kw_imp'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_imp'] ) : 0 )) . '</span>';
                        $data['total_impressions'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_imp_ref'],
                            $snippet_data['kw_imp_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $dynamic_elements = array(
                            '#wsko_db_search_keywords' => $data['total_keywords'],
                            '#wsko_db_search_clicks'   => $data['total_clicks'],
                        );
                        //Charts
                        $data['chart_history_keywords'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'Keywords', 'wsko' ), __( 'Keywords (Last Period)', 'wsko' ) ),
                            $graph_data['history_keywords'],
                            array(
                            'axisTitleY'  => __( 'Keywords', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        $data['chart_history_pages'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'Pages', 'wsko' ), __( 'Pages (Last Period)', 'wsko' ) ),
                            $graph_data['history_pages'],
                            array(
                            'axisTitleY'  => __( 'Pages', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        $data['chart_history_clicks'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'Clicks', 'wsko' ), __( 'Clicks (Last Period)', 'wsko' ) ),
                            $graph_data['history_clicks'],
                            array(
                            'axisTitleY'  => __( 'Clicks', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        $data['chart_history_position'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'Avg. Position', 'wsko' ), __( 'Avg. Position (Last period)', 'wsko' ) ),
                            $graph_data['history_position'],
                            array(
                            'axisTitleY'  => __( 'Avg. Position', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        $data['chart_history_impressions'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'Impressions', 'wsko' ), __( 'Impressions (Last Period)', 'wsko' ) ),
                            $graph_data['history_impressions'],
                            array(
                            'axisTitleY'  => __( 'Impressions', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        $data['chart_history_ctr'] = WSKO_Class_Template::render_chart(
                            'area',
                            array( __( 'Date', 'wsko' ), __( 'CTR', 'wsko' ), __( 'CTR (Last Period)', 'wsko' ) ),
                            $graph_data['history_ctr'],
                            array(
                            'axisTitleY'  => __( 'CTR', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        //$data['chart_history_serp'] = WSKO_Class_Template::render_chart('line', array('Date', 'SERP 1', 'SERP 2', 'SERP 3', 'SERP 4', 'SERP 5', 'SERP 6', 'SERP 7', 'SERP 8', 'SERP 9', 'SERP 10'), $graph_data['history_serp'], array('axisTitleY' => 'Keywords', 'chart_left' => '15', 'chart_width' => '70', 'legend_pos' => 'top', 'colors' => array('#4ea04e', '#5CB85C', '#88C859', '#C5D955', '#d9ca55', '#EAC050', '#EFAC4D', '#EB984E', '#E6854E', '#dc6b3e', '#d26839'), 'pixel_height' => 400, 'format' => array('1' => __('{0} Keywords', 'wsko'), '2' => __('{0} Keywords', 'wsko'), '3' => __('{0} Keywords', 'wsko'), '4' => __('{0} Keywords', 'wsko'), '5' => __('{0} Keywords', 'wsko'), '6' => __('{0} Keywords', 'wsko'), '7' => __('{0} Keywords', 'wsko'), '8' => __('{0} Keywords', 'wsko'), '9' => __('{0} Keywords', 'wsko'), '10' => __('{0} Keywords', 'wsko'))), true);
                        $data['chart_history_serp'] = WSKO_Class_Template::render_ajax_beacon( 'wsko_get_search_serp_data', array(
                            'size' => 'big',
                        ), true );
                        $data['chart_history_rankings'] = WSKO_Class_Template::render_chart(
                            'column',
                            array( __( 'Position', 'wsko' ), __( 'Keywords', 'wsko' ) ),
                            $graph_data['history_rankings'],
                            array(
                            'axisTitle'   => __( 'SERPs', 'wsko' ),
                            'axisTitleY'  => __( 'Keywords', 'wsko' ),
                            'chart_left'  => '15',
                            'chart_width' => '70',
                        ),
                            true
                        );
                        //Tables
                        foreach ( $table_data['top_keywords'] as $k => $kw ) {
                            $table_data['top_keywords'][$k][0]['value'] = WSKO_Class_Template::render_keyword_field( $kw[0]['value'], true );
                        }
                        foreach ( $table_data['top_pages'] as $k => $p ) {
                            $table_data['top_pages'][$k][0]['value'] = WSKO_Class_Template::render_url_post_field_s( $p[0]['value'], array(
                                'open_tab' => 'keywords',
                            ), true );
                            $post_id = WSKO_Class_Helper::url_to_postid( $p[5]['value'] );
                            $table_data['top_pages'][$k][5]['value'] = ( $post_id ? WSKO_Class_Template::render_content_optimizer_link( $post_id, array(
                                'open_tab' => 'keywords',
                            ), true ) : '' );
                        }
                        $keyword_headers = array(
                            __( 'Keyword', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' )
                        );
                        $data['table_top_keywords'] = WSKO_Class_Template::render_table(
                            $keyword_headers,
                            $table_data['top_keywords'],
                            array(
                            'no_pages' => true,
                            'order'    => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                        ),
                            true
                        );
                        $data['table_top_pages'] = WSKO_Class_Template::render_table(
                            array(
                            __( 'URL', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' ),
                            ''
                        ),
                            $table_data['top_pages'],
                            array(
                            'no_pages' => true,
                            'order'    => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                        ),
                            true
                        );
                        //Notifications
                        $today = WSKO_Class_Helper::get_midnight();
                        $start = $today - 60 * 60 * 24 * 3;
                        $end = $today - 60 * 60 * 24 * WSKO_SEARCH_REPORT_SIZE;
                        $num_cache_rows = WSKO_Class_Search::get_se_cache_history_count( $end, $start );
                        $recacheable = WSKO_SEARCH_REPORT_SIZE - 3;
                        
                        if ( $num_cache_rows < $recacheable ) {
                            $missing = $recacheable - $num_cache_rows;
                            $missing_time = date( 'i:s', $missing * 2.35 );
                            //avg process time (1.35) + counter-query-limit-sleep between calls(1.0)
                            $notif_text = wsko_loc( 'notif_search', 'incomplete_cache', array(
                                'days'    => WSKO_SEARCH_REPORT_SIZE,
                                'missing' => $missing,
                                'cron'    => ( $nextCron ? date( 'd.m.Y H:i', $nextCron ) : 'disabled' ),
                            ) ) . '<br/>' . WSKO_Class_Template::render_recache_api_button( 'ga_search', array(), true );
                            $notif .= WSKO_Class_Template::render_notification( 'error', array(
                                'msg'     => $notif_text,
                                'subnote' => ( $is_admin ? wsko_loc( 'notif_search', 'incomplete_cache_sub', array(
                                'days' => WSKO_SEARCH_REPORT_SIZE,
                            ) ) : false ),
                            ), true );
                        }
                    
                    } else {
                        $dynamic_elements = array(
                            '#wsko_db_search_keywords' => '-',
                            '#wsko_db_search_clicks'   => '-',
                        );
                    }
                    
                    break;
                case 'keywords':
                    
                    if ( $accessible ) {
                        $snippet_data = $this->get_cached_var_for_time( 'snippet_data' );
                        $data['total_keywords'] = '<span class="wsko-label">' . (( (( $snippet_data['kw_count'] == WSKO_SEARCH_ROW_LIMIT ? '<i data-toggle="tooltip" title="' . sprintf( __( 'You may have hit the limit of %d rows per query.', 'wsko' ), WSKO_SEARCH_ROW_LIMIT ) . '"></i>' : '' )) . $snippet_data['kw_count'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_count'] ) : 0 )) . '</span>';
                        $data['total_keywords'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_count_ref'],
                            $snippet_data['kw_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_keyword_dist'] = '<span class="wsko-label">' . (( $snippet_data['kw_dist_count'] ? WSKO_Class_Helper::format_number( $snippet_data['kw_dist_count'] ) : 0 )) . '</span>';
                        $data['total_keyword_dist'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['kw_dist_count_ref'],
                            $snippet_data['kw_dist_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_new_keywords'] = '<span id="wsko_new_keywords" class="wsko-label">' . WSKO_Class_Template::render_preloader( array(
                            'size' => 'small',
                        ), true ) . '</span>';
                        $data['total_lost_keywords'] = '<span id="wsko_lost_keywords" class="wsko-label">' . WSKO_Class_Template::render_preloader( array(
                            'size' => 'small',
                        ), true ) . '</span>';
                        $keyword_headers = array(
                            __( 'Keyword', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' )
                        );
                        $keyword_filter = array(
                            '0' => array(
                            'title' => __( 'Keyword', 'wsko' ),
                            'type'  => 'text',
                        ),
                            '1' => array(
                            'title' => __( 'Position', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_kw_position'],
                        ),
                            '2' => array(
                            'title' => __( 'Clicks', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_kw_clicks'],
                        ),
                            '3' => array(
                            'title' => __( 'Impressions', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_kw_imp'],
                        ),
                            '4' => array(
                            'title' => __( 'CTR', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => 100,
                        ),
                        );
                        $data['table_keywords'] = WSKO_Class_Template::render_table(
                            $keyword_headers,
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'keywords',
                        ),
                            'filter' => $keyword_filter,
                        ),
                            true
                        );
                        $data['table_new_keywords'] = WSKO_Class_Template::render_table(
                            $keyword_headers,
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'keywords_new',
                        ),
                            'filter' => $keyword_filter,
                        ),
                            true
                        );
                        $data['table_lost_keywords'] = WSKO_Class_Template::render_table(
                            $keyword_headers,
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'keywords_lost',
                        ),
                            'filter' => $keyword_filter,
                        ),
                            true
                        );
                    }
                    
                    break;
                case 'pages':
                    
                    if ( $accessible ) {
                        $snippet_data = $this->get_cached_var_for_time( 'snippet_data' );
                        $data['total_pages'] = '<span class="wsko-label">' . (( (( $snippet_data['page_count'] == WSKO_SEARCH_ROW_LIMIT ? '<i data-toggle="tooltip" title="' . sprintf( __( 'You may have hit the limit of %d rows per query.', 'wsko' ), WSKO_SEARCH_ROW_LIMIT ) . '"></i>' : '' )) . $snippet_data['page_count'] ? WSKO_Class_Helper::format_number( $snippet_data['page_count'] ) : 0 )) . '</span>';
                        $data['total_pages'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['page_count_ref'],
                            $snippet_data['page_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_page_dist'] = '<span class="wsko-label">' . (( $snippet_data['page_dist_count'] ? WSKO_Class_Helper::format_number( $snippet_data['page_dist_count'] ) : 0 )) . '</span>';
                        $data['total_page_dist'] .= WSKO_Class_Template::render_progress_icon(
                            $snippet_data['page_dist_count_ref'],
                            $snippet_data['page_dist_count_ref_perc'],
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_new_pages'] = '<span id="wsko_new_pages" class="wsko-label">' . WSKO_Class_Template::render_preloader( array(
                            'size' => 'small',
                        ), true ) . '</span>';
                        $data['total_lost_pages'] = '<span id="wsko_lost_pages" class="wsko-label">' . WSKO_Class_Template::render_preloader( array(
                            'size' => 'small',
                        ), true ) . '</span>';
                        $data['table_pages'] = WSKO_Class_Template::render_table(
                            array(
                            __( 'URL', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' ),
                            ''
                        ),
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'pages',
                        ),
                            'filter' => array(
                            '0' => array(
                            'title' => __( 'URL', 'wsko' ),
                            'type'  => 'text',
                        ),
                            '1' => array(
                            'title' => __( 'Position', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_position'],
                        ),
                            '2' => array(
                            'title' => __( 'Clicks', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_clicks'],
                        ),
                            '3' => array(
                            'title' => __( 'Impressions', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_imp'],
                        ),
                            '4' => array(
                            'title' => __( 'CTR', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => 100,
                        ),
                        ),
                        ),
                            true
                        );
                        $data['table_new_pages'] = WSKO_Class_Template::render_table(
                            array(
                            __( 'URL', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' ),
                            ''
                        ),
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'pages_new',
                        ),
                            'filter' => array(
                            '0' => array(
                            'title' => __( 'URL', 'wsko' ),
                            'type'  => 'text',
                        ),
                            '1' => array(
                            'title' => __( 'Position', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_position'],
                        ),
                            '2' => array(
                            'title' => __( 'Clicks', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_clicks'],
                        ),
                            '3' => array(
                            'title' => __( 'Impressions', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_imp'],
                        ),
                            '4' => array(
                            'title' => __( 'CTR', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => 100,
                        ),
                        ),
                        ),
                            true
                        );
                        $data['table_lost_pages'] = WSKO_Class_Template::render_table(
                            array(
                            __( 'URL', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' ),
                            ''
                        ),
                            array(),
                            array(
                            'order'  => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                            'ajax'   => array(
                            'action' => 'wsko_table_search',
                            'arg'    => 'pages_lost',
                        ),
                            'filter' => array(
                            '0' => array(
                            'title' => __( 'URL', 'wsko' ),
                            'type'  => 'text',
                        ),
                            '1' => array(
                            'title' => __( 'Position', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_position'],
                        ),
                            '2' => array(
                            'title' => __( 'Clicks', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_clicks'],
                        ),
                            '3' => array(
                            'title' => __( 'Impressions', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => $snippet_data['max_page_imp'],
                        ),
                            '4' => array(
                            'title' => __( 'CTR', 'wsko' ),
                            'type'  => 'number_range',
                            'min'   => 0,
                            'max'   => 100,
                        ),
                        ),
                        ),
                            true
                        );
                    }
                    
                    break;
                case 'dev_coun':
                    if ( $accessible ) {
                    }
                    break;
                case 'competitors':
                    break;
                case 'monitoring':
                    $monitored_keywords = WSKO_Class_Search::get_monitored_keywords();
                    
                    if ( $monitored_keywords && !empty($monitored_keywords) ) {
                        $monitoring_data = $this->get_cached_var_for_time( 'monitoring_data' );
                        $data['total_keywords'] = WSKO_Class_Helper::format_number( $monitoring_data['total_keywords'] );
                        //$data['total_keywords'] .= WSKO_Class_Template::render_progress_icon($monitoring_data['total_keywords_ref'], WSKO_Class_Helper::get_ref_value($monitoring_data['total_keywords'], $monitoring_data['total_keywords_ref']), array('decimals' => 2), true);
                        $data['total_clicks'] = WSKO_Class_Helper::format_number( $monitoring_data['total_clicks'] );
                        $data['total_clicks'] .= WSKO_Class_Template::render_progress_icon(
                            $monitoring_data['total_clicks_ref'],
                            WSKO_Class_Helper::get_ref_value( $monitoring_data['total_clicks'], $monitoring_data['total_clicks_ref'] ),
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['total_impressions'] = WSKO_Class_Helper::format_number( $monitoring_data['total_impressions'] );
                        $data['total_impressions'] .= WSKO_Class_Template::render_progress_icon(
                            $monitoring_data['total_impressions_ref'],
                            WSKO_Class_Helper::get_ref_value( $monitoring_data['total_impressions'], $monitoring_data['total_impressions_ref'] ),
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['avg_position'] = WSKO_Class_Helper::format_number( $monitoring_data['avg_position'] );
                        $data['avg_position'] .= WSKO_Class_Template::render_progress_icon(
                            $monitoring_data['avg_position_ref'],
                            WSKO_Class_Helper::get_ref_value( $monitoring_data['avg_position'], $monitoring_data['avg_position_ref'] ),
                            array(
                            'decimals' => 2,
                        ),
                            true
                        );
                        $data['chart_histories'] = WSKO_Class_Template::render_template( 'search/view-monitoring-histories.php', array(
                            'clicks'      => $monitoring_data['history_clicks'],
                            'impressions' => $monitoring_data['history_impressions'],
                            'position'    => $monitoring_data['history_position'],
                            'ctr'         => $monitoring_data['history_ctr'],
                        ), true );
                        $data['table_monitored_keywords'] = WSKO_Class_Template::render_table(
                            array(
                            __( 'Keyword', 'wsko' ),
                            __( 'Position', 'wsko' ),
                            __( 'Clicks', 'wsko' ),
                            __( 'Impressions', 'wsko' ),
                            __( 'CTR', 'wsko' ),
                            ''
                        ),
                            $monitoring_data['table_monitored_keywords'],
                            array(
                            'order' => array(
                            'col' => 2,
                            'dir' => 'desc',
                        ),
                        ),
                            true
                        );
                    } else {
                        $data['total_keywords'] = "-";
                        $data['total_clicks'] = "-";
                        $data['total_impressions'] = "-";
                        $data['avg_position'] = "-";
                        $is_fetching = !WSKO_Class_Core::get_option( 'search_query_first_run' );
                        if ( !WSKO_Class_Search::get_se_token() ) {
                            $is_fetching = false;
                        }
                        
                        if ( $is_fetching ) {
                            $tmp_fail = WSKO_Class_Template::render_template( 'misc/template-no-cache-fetching.php', array(), true );
                            $data['chart_histories'] = $tmp_fail;
                            $data['table_monitored_keywords'] = $tmp_fail;
                        }
                    
                    }
                    
                    break;
            }
            return array(
                'success'          => true,
                'data'             => $data,
                'notif'            => $notif,
                'dynamic_elements' => $dynamic_elements,
            );
        }
    
    }
    
    public function action_get_search_serp_data()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $data = $this->get_cached_var( 'serp_data' );
        
        if ( $data && isset( $data['history_serp'] ) ) {
            $view = WSKO_Class_Template::render_chart(
                'line',
                array(
                __( 'Date', 'wsko' ),
                __( 'SERP 1', 'wsko' ),
                __( 'SERP 2', 'wsko' ),
                __( 'SERP 3', 'wsko' ),
                __( 'SERP 4', 'wsko' ),
                __( 'SERP 5', 'wsko' ),
                __( 'SERP 6', 'wsko' ),
                __( 'SERP 7', 'wsko' ),
                __( 'SERP 8', 'wsko' ),
                __( 'SERP 9', 'wsko' ),
                __( 'SERP 10', 'wsko' )
            ),
                $data['history_serp'],
                array(
                'toggle_columns' => true,
                'axisTitleY'     => __( 'Keywords', 'wsko' ),
                'chart_left'     => '15',
                'chart_width'    => '70',
                'legend_pos'     => 'top',
                'colors'         => array(
                '#4ea04e',
                '#5CB85C',
                '#88C859',
                '#C5D955',
                '#d9ca55',
                '#EAC050',
                '#EFAC4D',
                '#EB984E',
                '#E6854E',
                '#dc6b3e',
                '#d26839'
            ),
                'pixel_height'   => 400,
                'format'         => array(
                '1'  => __( '{0} Keywords', 'wsko' ),
                '2'  => __( '{0} Keywords', 'wsko' ),
                '3'  => __( '{0} Keywords', 'wsko' ),
                '4'  => __( '{0} Keywords', 'wsko' ),
                '5'  => __( '{0} Keywords', 'wsko' ),
                '6'  => __( '{0} Keywords', 'wsko' ),
                '7'  => __( '{0} Keywords', 'wsko' ),
                '8'  => __( '{0} Keywords', 'wsko' ),
                '9'  => __( '{0} Keywords', 'wsko' ),
                '10' => __( '{0} Keywords', 'wsko' ),
            ),
            ),
                true
            );
            return array(
                'success' => true,
                'view'    => $view,
            );
        }
    
    }
    
    public function action_do_keyword_research()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
    }
    
    public function action_get_keyword_research()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
    }
    
    public function action_delete_keyword_research()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
    }
    
    public function action_table_search()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
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
        $dynamic_elements = false;
        switch ( $arg ) {
            case 'keywords':
                $orderby = "";
                $keys = array(
                    'keyval',
                    array( 'position', 'position_ref' ),
                    array( 'clicks', 'clicks_ref' ),
                    array( 'impressions', 'impressions_ref' ),
                    array( 'ctr', 'ctr_ref' )
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'query',
                    array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'keyword',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        round( abs( $arg[1] - $arg[0] ), 0 ),
                        -WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(
                        'absolute' => true,
                        'tooltip'  => $arg[1],
                    ),
                        true
                    );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0], 2 ) . ' %' . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'keywords_new':
                $orderby = "";
                $keys = array(
                    'keyval',
                    'position',
                    'clicks',
                    'impressions',
                    'ctr'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'query',
                    array(
                    'is_new'  => true,
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'keyword',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg, 2 ) . ' %';
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                if ( !$custom_filter ) {
                    $dynamic_elements = array(
                        '#wsko_new_keywords'   => $total_data,
                        '#wsko_new_keywords_t' => '(' . $total_data . ')',
                    );
                }
                break;
            case 'keywords_lost':
                $orderby = "";
                $keys = array(
                    'keyval',
                    'position',
                    'clicks',
                    'impressions',
                    'ctr'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'query',
                    array(
                    'is_lost' => true,
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'keyword',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg, 2 ) . ' %';
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                if ( !$custom_filter ) {
                    $dynamic_elements = array(
                        '#wsko_lost_keywords'   => $total_data,
                        '#wsko_lost_keywords_t' => '(' . $total_data . ')',
                    );
                }
                break;
            case 'pages':
                $orderby = "";
                $keys = array(
                    'keyval',
                    array( 'position', 'position_ref' ),
                    array( 'clicks', 'clicks_ref' ),
                    array( 'impressions', 'impressions_ref' ),
                    array( 'ctr', 'ctr_ref' ),
                    'keyval'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'page',
                    array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_url_post_field_s( $arg, array(
                        'open_tab' => 'keywords',
                    ), true );
                },
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        round( abs( $arg[1] - $arg[0] ), 0 ),
                        -WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(
                        'absolute' => true,
                        'tooltip'  => $arg[1],
                    ),
                        true
                    );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0] ) . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg[0], 2 ) . ' %' . WSKO_Class_Template::render_progress_icon(
                        $arg[1],
                        WSKO_Class_Helper::get_ref_value( $arg[0], $arg[1] ),
                        array(),
                        true
                    );
                },
                    '5' => function ( $arg ) {
                    $arg = WSKO_Class_Helper::url_to_postid( $arg );
                    if ( !$arg && WSKO_Class_Core::is_demo() ) {
                        $arg = WSKO_Class_Helper::get_home_id();
                    }
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'pages_new':
                $orderby = "";
                $keys = array(
                    'keyval',
                    'position',
                    'clicks',
                    'impressions',
                    'ctr',
                    'keyval'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'page',
                    array(
                    'is_new'  => true,
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_url_post_field_s( $arg, array(
                        'open_tab' => 'keywords',
                    ), true );
                },
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg, 2 ) . ' %';
                },
                    '5' => function ( $arg ) {
                    $arg = WSKO_Class_Helper::url_to_postid( $arg );
                    if ( !$arg && WSKO_Class_Core::is_demo() ) {
                        $arg = WSKO_Class_Helper::get_home_id();
                    }
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                if ( !$custom_filter ) {
                    $dynamic_elements = array(
                        '#wsko_new_pages'   => $total_data,
                        '#wsko_new_pages_t' => '(' . $total_data . ')',
                    );
                }
                break;
            case 'pages_lost':
                $orderby = "";
                $keys = array(
                    'keyval',
                    'position',
                    'clicks',
                    'impressions',
                    'ctr',
                    'keyval'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true, array(
                    'keys' => $keys,
                ) );
                if ( $search ) {
                    $custom_filter['keyval'] = " LIKE '%" . esc_sql( $search ) . "%'";
                }
                $stats = true;
                $data = WSKO_Class_Search::get_se_data(
                    $this->timespan_start,
                    $this->timespan_end,
                    'page',
                    array(
                    'is_lost' => true,
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ),
                    $stats
                );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_url_post_field_s( $arg, array(
                        'open_tab' => 'keywords',
                    ), true );
                },
                    '1' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '2' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg );
                },
                    '4' => function ( $arg ) {
                    return WSKO_Class_Helper::format_number( $arg, 2 ) . ' %';
                },
                    '5' => function ( $arg ) {
                    $arg = WSKO_Class_Helper::url_to_postid( $arg );
                    if ( !$arg && WSKO_Class_Core::is_demo() ) {
                        $arg = WSKO_Class_Helper::get_home_id();
                    }
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                if ( !$custom_filter ) {
                    $dynamic_elements = array(
                        '#wsko_lost_pages'   => $total_data,
                        '#wsko_lost_pages_t' => '(' . $total_data . ')',
                    );
                }
                break;
            case 'countries':
                break;
            case 'devices':
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
            'success'          => true,
            'data'             => ( $data ? $data['data'] : array() ),
            'recordsFiltered'  => $filtered_data,
            'recordsTotal'     => $total_data,
            'dynamic_elements' => $dynamic_elements,
        );
    }
    
    public function action_add_monitoring_keyword()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $keyword = ( isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : false );
        $multi = ( isset( $_POST['multi'] ) && $_POST['multi'] && $_POST['multi'] != 'false' ? true : false );
        
        if ( $keyword ) {
            
            if ( $multi ) {
                $keywords = WSKO_Class_Helper::safe_explode( ',', $keyword );
                foreach ( $keywords as $kw ) {
                    WSKO_Class_Search::add_monitored_keyword( trim( strtolower( $kw ) ) );
                }
            } else {
                WSKO_Class_Search::add_monitored_keyword( trim( strtolower( $keyword ) ) );
            }
            
            $this->delete_cache();
            return true;
        }
    
    }
    
    public function action_remove_monitoring_keyword()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $this->delete_cache();
        $keyword = ( isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : false );
        
        if ( $keyword ) {
            WSKO_Class_Search::remove_monitored_keyword( $keyword );
            return true;
        }
    
    }
    
    public function action_query_search_custom()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $dimension = ( isset( $_POST['dimension'] ) ? sanitize_text_field( $_POST['dimension'] ) : false );
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        
        if ( $dimension ) {
            $header_name = "";
            $rows = WSKO_Class_Search::get_se_custom_query(
                $this->timespan_start,
                $this->timespan_end,
                'query',
                false,
                array(
                'key' => $dimension,
                'val' => $arg,
            )
            );
            $table_data = array();
            $kw_headers = array(
                __( 'Keyword', 'wsko' ),
                __( 'Position', 'wsko' ),
                __( 'Clicks', 'wsko' ),
                __( 'Impressions', 'wsko' ),
                __( 'CTR', 'wsko' )
            );
            foreach ( $rows as $row ) {
                $kw = $row->keyval;
                $table_data[$kw] = array(
                    array(
                    'class' => 'wsko_table_col1',
                    'value' => $kw,
                ),
                    array(
                    'order' => $row->position,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->position ) . '</span> ',
                ),
                    array(
                    'order' => $row->clicks,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->clicks ) . '</span> ',
                ),
                    array(
                    'order' => $row->impressions,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->impressions ) . '</span> ',
                ),
                    array(
                    'order' => $row->ctr,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->ctr, 2 ) . ' %</span> ',
                )
                );
            }
            $view = (( $header_name ? '<p class="panel-heading m0">' . sprintf( __( 'Keywords for %s', 'wsko' ), $header_name ) . '</p>' : '' )) . WSKO_Class_Template::render_table(
                $kw_headers,
                $table_data,
                array(
                'order' => array(
                'col' => '2',
                dir   => 'desc',
            ),
            ),
                true
            );
            return array(
                'success' => true,
                'view'    => $view,
            );
        }
    
    }
    
    public function action_get_report_suggestions()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $keyword = ( isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : false );
        $all = ( isset( $_POST['all'] ) && $_POST['all'] && $_POST['all'] != "false" ? true : false );
        $data = "";
        
        if ( $keyword ) {
            $reports = WSKO_Class_Premium::get_saved_keyword_researchs( array(
                'search' => $keyword,
                'limit'  => ( $all ? false : 10 ),
            ) );
            foreach ( $reports as $report ) {
                
                if ( stripos( $report->keyword, $keyword ) !== false ) {
                    $location = $report->source;
                    $data .= '<li><a class="wsko-open-keyword-research dark" data-research="' . $report->id . '" data-nonce="' . wp_create_nonce( 'wsko_get_keyword_research' ) . '" href="#"><i class="fa fa-clock-o fa-fw wsko-mr5"></i> <img class="mr5" src="' . WSKO_PLUGIN_URL . 'includes/famfamfam_flags/' . strtolower( $location ) . '.png"> ' . $report->lang . ' | ' . $report->keyword . ' <small class="pull-right text-off">' . date( 'd.m.Y', $report->time ) . '</small></a></li>';
                }
            
            }
            if ( count( $reports ) == 10 && !$all ) {
                $data .= '<li><a class="wsko-research-get-all-suggestions dark" href="#">' . __( 'Show all', 'wsko' ) . '</a></li>';
            }
        }
        
        if ( !$data ) {
            $data .= '<li>' . __( 'You have no previous reports for this keyword', 'wsko' ) . '</li>';
        }
        return array(
            'success' => true,
            'view'    => $data,
        );
    }
    
    //Cache methods
    public function get_cached_snippet_data( $args )
    {
        $res = WSKO_Class_Search::get_se_cache_overview( $this->timespan_start, $this->timespan_end );
        
        if ( $res ) {
            $res['kw_count_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['kw_count'], $res['kw_count_ref'] );
            $res['kw_dist_count_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['kw_dist_count'], $res['kw_dist_count_ref'] );
            $res['kw_clicks_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['kw_clicks'], $res['kw_clicks_ref'] );
            $res['kw_imp_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['kw_imp'], $res['kw_imp_ref'] );
            $res['page_count_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['page_count'], $res['page_count_ref'] );
            $res['page_dist_count_ref_perc'] = WSKO_Class_Helper::get_ref_value( $res['page_dist_count'], $res['page_dist_count_ref'] );
        }
        
        return $res;
    }
    
    public function get_cached_overview_graph_data( $args )
    {
        $is_widget = false;
        if ( isset( $args['widget'] ) && $args['widget'] ) {
            $is_widget = true;
        }
        $res = array();
        $snippet_data = $this->get_cached_var_for_time( 'snippet_data' );
        $diff = $this->timespan_end - $this->timespan_start;
        $time3 = $this->timespan_start - $diff;
        $keywords = array();
        $keywords_d = WSKO_Class_Search::get_se_cache_history( $this->timespan_start, $this->timespan_end, 'query' );
        $keywords_d_ref = WSKO_Class_Search::get_se_cache_history( $time3, $this->timespan_start, 'query' );
        foreach ( $keywords_d as $kw ) {
            $daydiff = round( ($this->timespan_end - strtotime( $kw->time )) / (60 * 60 * 24) );
            $keywords[$daydiff] = array( date( "M. d, Y", strtotime( $kw->time ) ), $kw->count, 0 );
        }
        foreach ( $keywords_d_ref as $kw2 ) {
            $daydiff = round( ($this->timespan_start - strtotime( $kw2->time )) / (60 * 60 * 24) );
            if ( isset( $keywords[$daydiff] ) ) {
                $keywords[$daydiff][2] = $kw2->count;
            }
        }
        $pages = array();
        $pages_d = WSKO_Class_Search::get_se_cache_history( $this->timespan_start, $this->timespan_end, 'page' );
        $pages_d_ref = WSKO_Class_Search::get_se_cache_history( $time3, $this->timespan_start, 'page' );
        foreach ( $pages_d as $p ) {
            $daydiff = round( ($this->timespan_end - strtotime( $p->time )) / (60 * 60 * 24) );
            $pages[$daydiff] = array( date( "M. d, Y", strtotime( $p->time ) ), $p->count, 0 );
        }
        foreach ( $pages_d_ref as $p2 ) {
            $daydiff = round( ($this->timespan_start - strtotime( $p2->time )) / (60 * 60 * 24) );
            if ( isset( $pages[$daydiff] ) ) {
                $pages[$daydiff][2] = $p2->count;
            }
        }
        $clicks = array();
        $position = array();
        $impressions = array();
        $ctr = array();
        $date_rows = WSKO_Class_Search::get_se_data(
            $this->timespan_start,
            $this->timespan_end,
            'date',
            array(
            'with_ref' => false,
        )
        );
        $date_rows_ref = WSKO_Class_Search::get_se_data(
            $time3,
            $this->timespan_start,
            'date',
            array(
            'with_ref' => false,
        )
        );
        foreach ( $date_rows as $row ) {
            $date = date( "M. d, Y", strtotime( $row->keyval ) );
            $daydiff = round( ($this->timespan_end - strtotime( $row->keyval )) / (60 * 60 * 24) );
            $clicks[$daydiff] = array( $date, $row->clicks, 0 );
            $position[$daydiff] = array( $date, $row->position, 0 );
            $impressions[$daydiff] = array( $date, $row->impressions, 0 );
            $ctr[$daydiff] = array( $date, $row->ctr, 0 );
        }
        foreach ( $date_rows_ref as $i => $row ) {
            $daydiff = round( ($this->timespan_start - strtotime( $row->keyval )) / (60 * 60 * 24) );
            if ( isset( $clicks[$daydiff] ) ) {
                $clicks[$daydiff][2] = $row->clicks;
            }
            if ( isset( $position[$daydiff] ) ) {
                $position[$daydiff][2] = $row->position;
            }
            if ( isset( $impressions[$daydiff] ) ) {
                $impressions[$daydiff][2] = $row->impressions;
            }
            if ( isset( $ctr[$daydiff] ) ) {
                $ctr[$daydiff][2] = $row->ctr;
            }
        }
        
        if ( !$is_widget ) {
            $devices = array();
            $countries = array();
            $device_rows = WSKO_Class_Search::get_se_data(
                $this->timespan_start,
                $this->timespan_end,
                'device',
                array(
                'with_ref' => false,
            )
            );
            foreach ( $device_rows as $row ) {
                $devices[] = array( $row->keyval, $row->clicks );
            }
            $res['countries_matrix'] = array();
            $country_rows = WSKO_Class_Search::get_se_data(
                $this->timespan_start,
                $this->timespan_end,
                'country',
                array(
                'with_ref' => false,
            )
            );
            foreach ( $country_rows as $row ) {
                $country = WSKO_Class_Helper::get_country_name( strtoupper( $row->keyval ) );
                
                if ( $country ) {
                    $countries[] = array( $country, $row->clicks );
                    $res['countries_matrix'][0][] = array( '.wsko-search-query-link-' . $row->keyval );
                }
            
            }
            $graph_data = array();
            if ( isset( $snippet_data['kw_dist'] ) ) {
                foreach ( $snippet_data['kw_dist'] as $pos => $count ) {
                    $graph_data[] = array( $pos, array(
                        'v' => $count,
                        'f' => WSKO_Class_Helper::format_number( $count ),
                    ) );
                }
            }
        }
        
        $res['history_keywords'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ),
                );
            }
            return $a;
        }, $keywords );
        $res['history_pages'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ),
                );
            }
            return $a;
        }, $pages );
        $res['history_clicks'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ),
                );
            }
            return $a;
        }, $clicks );
        $res['history_position'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ),
                );
            }
            return $a;
        }, $position );
        $res['history_impressions'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ),
                );
            }
            return $a;
        }, $impressions );
        $res['history_ctr'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            if ( isset( $a[2] ) ) {
                $a[2] = array(
                    'v' => $a[2],
                    'f' => WSKO_Class_Helper::format_number( $a[2] ) . '%',
                );
            }
            return $a;
        }, $ctr );
        if ( !$is_widget ) {
            $res['history_rankings'] = $graph_data;
        }
        return $res;
    }
    
    public function get_cached_serp_data( $args )
    {
        WSKO_Class_Helper::safe_set_time_limit( 60 );
        //allow 1 minute generation time
        $res = array();
        $rankings = WSKO_Class_Search::get_se_cache_history_position( $this->timespan_start, $this->timespan_end );
        foreach ( $rankings as $k => $r ) {
            $rankings[$k][0] = date( "M. d, Y", $r[0] );
        }
        $res['history_serp'] = array_map( function ( $a ) {
            $a[1] = array(
                'v' => $a[1],
                'f' => WSKO_Class_Helper::format_number( $a[1] ),
            );
            $a[2] = array(
                'v' => $a[2],
                'f' => WSKO_Class_Helper::format_number( $a[2] ),
            );
            $a[3] = array(
                'v' => $a[3],
                'f' => WSKO_Class_Helper::format_number( $a[3] ),
            );
            $a[4] = array(
                'v' => $a[4],
                'f' => WSKO_Class_Helper::format_number( $a[4] ),
            );
            $a[5] = array(
                'v' => $a[5],
                'f' => WSKO_Class_Helper::format_number( $a[5] ),
            );
            $a[6] = array(
                'v' => $a[6],
                'f' => WSKO_Class_Helper::format_number( $a[6] ),
            );
            $a[7] = array(
                'v' => $a[7],
                'f' => WSKO_Class_Helper::format_number( $a[7] ),
            );
            $a[8] = array(
                'v' => $a[8],
                'f' => WSKO_Class_Helper::format_number( $a[8] ),
            );
            $a[9] = array(
                'v' => $a[9],
                'f' => WSKO_Class_Helper::format_number( $a[9] ),
            );
            $a[10] = array(
                'v' => $a[10],
                'f' => WSKO_Class_Helper::format_number( $a[10] ),
            );
            return $a;
        }, $rankings );
        return $res;
    }
    
    public function get_cached_table_data( $args )
    {
        $res = array(
            'top_keywords' => array(),
            'top_pages'    => array(),
        );
        $keyword_rows = WSKO_Class_Search::get_se_data(
            $this->timespan_start,
            $this->timespan_end,
            'query',
            array(
            'limit' => 5,
        )
        );
        foreach ( $keyword_rows as $key => $row ) {
            $ref_row = $row->clicks_ref !== null;
            $res['top_keywords'][$key] = array(
                array(
                    'class' => 'wsko_table_col1',
                    'value' => $row->keyval,
                ),
                array(
                    'order' => $row->position,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->position ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    round( abs( $row->position_ref - $row->position ), 0 ),
                    -WSKO_Class_Helper::get_ref_value( $row->position, $row->position_ref ),
                    array(
                    'absolute' => true,
                    'tooltip'  => $row->position_ref,
                ),
                    true
                ) : '' )),
                ),
                //'<span class="wsko_single_progress '.($ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_position > 0)) ? '+' : '').($ref_row ? $ref_position : '-').' %</span>'),
                array(
                    'order' => $row->clicks,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->clicks ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    $row->clicks_ref,
                    WSKO_Class_Helper::get_ref_value( $row->clicks, $row->clicks_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                //<span class="wsko_single_progress '.($ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_clicks > 0)) ? '+' : '').($ref_row ? $ref_clicks : '-').' %</span>'),
                array(
                    'order' => $row->impressions,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->impressions ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    $row->impressions_ref,
                    WSKO_Class_Helper::get_ref_value( $row->impressions, $row->impressions_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                //<span class="wsko_single_progress '.($ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_impressions > 0)) ? '+' : '').($ref_row ? $ref_impressions : '-').' %</span>'),
                array(
                    'order' => $row->ctr,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->ctr, 2 ) . ' %</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    round( $row->ctr_ref, 2 ),
                    WSKO_Class_Helper::get_ref_value( $row->ctr, $row->ctr_ref ),
                    array(),
                    true
                ) : '' )),
                ),
            );
        }
        $page_rows = WSKO_Class_Search::get_se_data(
            $this->timespan_start,
            $this->timespan_end,
            'page',
            array(
            'limit' => 5,
        )
        );
        foreach ( $page_rows as $row ) {
            $ref_row = $row->clicks_ref !== null;
            $res['top_pages'][] = array(
                array(
                    'class' => 'wsko_table_col1',
                    'value' => $row->keyval,
                ),
                array(
                    'order' => $row->position,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->position ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    round( abs( $row->position_ref - $row->position ), 0 ),
                    -WSKO_Class_Helper::get_ref_value( $row->position, $row->position_ref ),
                    array(
                    'absolute' => true,
                    'tooltip'  => $row->position_ref,
                ),
                    true
                ) : '' )),
                ),
                //'<span class="wsko_single_progress '.($ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_position > 0)) ? '+' : '').($ref_row ? $ref_position : '-').' %</span>'),
                array(
                    'order' => $row->clicks,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->clicks ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    $row->clicks_ref,
                    WSKO_Class_Helper::get_ref_value( $row->clicks, $row->clicks_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                //<span class="wsko_single_progress '.($ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_clicks > 0)) ? '+' : '').($ref_row ? $ref_clicks : '-').' %</span>'),
                array(
                    'order' => $row->impressions,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->impressions ) . '</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    $row->impressions_ref,
                    WSKO_Class_Helper::get_ref_value( $row->impressions, $row->impressions_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                //<span class="wsko_single_progress '.($ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_impressions > 0)) ? '+' : '').($ref_row ? $ref_impressions : '-').' %</span>'),
                array(
                    'order' => $row->ctr,
                    'value' => '<span style="float:left;">' . WSKO_Class_Helper::format_number( $row->ctr, 2 ) . ' %</span> ' . (( $ref_row ? WSKO_Class_Template::render_progress_icon(
                    round( $row->ctr_ref, 2 ),
                    WSKO_Class_Helper::get_ref_value( $row->ctr, $row->ctr_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                //<span class="wsko_single_progress '.($ref_row && $ref_ctr != 0 ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font').'">'.(($ref_row && ($ref_ctr > 0)) ? '+' : '').($ref_row ? $ref_ctr : '-').' %</span>')
                array(
                    'value' => $row->keyval,
                ),
            );
        }
        return $res;
    }
    
    public function get_cached_monitoring_data( $args )
    {
        $res = array();
        $total_keywords = 0;
        $total_keywords_ref = 0;
        $total_clicks = 0;
        $total_clicks_ref = 0;
        $total_impressions = 0;
        $total_impressions_ref = 0;
        $avg_postion = 0;
        $avg_postion_ref = 0;
        $monitored_keywords = WSKO_Class_Search::get_monitored_keywords();
        $table_kw = array();
        
        if ( $monitored_keywords ) {
            $kw_rows = WSKO_Class_Search::get_se_data(
                $this->timespan_start,
                $this->timespan_end,
                'query',
                array(
                'for_keys' => $monitored_keywords,
            )
            );
            foreach ( $monitored_keywords as $mkw ) {
                
                if ( isset( $kw_rows[$mkw] ) ) {
                    $kw_obj = $kw_rows[$mkw];
                } else {
                    $kw_obj = WSKO_Class_Search::get_empty_se_row( $mkw );
                }
                
                $total_keywords++;
                $table_kw[] = array(
                    array(
                    'value' => $kw_obj->keyval,
                ),
                    array(
                    'order' => $kw_obj->position,
                    'value' => WSKO_Class_Helper::format_number( $kw_obj->position ) . (( $kw_obj->position_ref ? WSKO_Class_Template::render_progress_icon(
                    round( abs( $kw_obj->position_ref - $kw_obj->position ), 0 ),
                    -WSKO_Class_Helper::get_ref_value( $kw_obj->position, $kw_obj->position_ref ),
                    array(
                    'absolute' => true,
                    'tooltip'  => $kw_obj->position_ref,
                ),
                    true
                ) : '' )),
                ),
                    array(
                    'order' => $kw_obj->clicks,
                    'value' => WSKO_Class_Helper::format_number( $kw_obj->clicks ) . (( $kw_obj->position_ref ? WSKO_Class_Template::render_progress_icon(
                    $kw_obj->clicks_ref,
                    WSKO_Class_Helper::get_ref_value( $kw_obj->clicks, $kw_obj->clicks_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                    array(
                    'order' => $kw_obj->impressions,
                    'value' => WSKO_Class_Helper::format_number( $kw_obj->impressions ) . (( $kw_obj->position_ref ? WSKO_Class_Template::render_progress_icon(
                    $kw_obj->impressions_ref,
                    WSKO_Class_Helper::get_ref_value( $kw_obj->impressions, $kw_obj->impressions_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                    array(
                    'order' => $kw_obj->ctr,
                    'value' => WSKO_Class_Helper::format_number( $kw_obj->ctr, 2 ) . ' %' . (( $kw_obj->ctr_ref ? WSKO_Class_Template::render_progress_icon(
                    round( $kw_obj->ctr_ref, 2 ),
                    WSKO_Class_Helper::get_ref_value( $kw_obj->ctr, $kw_obj->ctr_ref ),
                    array(),
                    true
                ) : '' )),
                ),
                    array(
                    'value' => '<a class="wsko-remove-monitoring-keyword" data-keyword="' . $kw_obj->keyval . '" href=""><i class="fa fa-times"></i></a>',
                )
                );
            }
        }
        
        $history_clicks = array();
        $history_position = array();
        $history_impressions = array();
        $history_ctr = array();
        $histories = WSKO_Class_Search::get_se_cache_keword_history( $this->timespan_start, $this->timespan_end, $monitored_keywords );
        $hist_count = count( $histories );
        foreach ( $histories as $history ) {
            $total_clicks += $history->clicks;
            $total_impressions += $history->impressions;
            $avg_postion += $history->position;
            $total_clicks_ref += $history->clicks_ref;
            $total_impressions_ref += $history->impressions_ref;
            $avg_postion_ref += $history->position_ref;
            $time = date( "M. d, Y", strtotime( $history->time ) );
            $ctr = ( $history->clicks && $history->impressions ? $history->clicks / $history->impressions * 100 : 0 );
            $ctr_ref = ( $history->clicks_ref && $history->impressions_ref ? $history->clicks_ref / $history->impressions_ref * 100 : 0 );
            $history_clicks[] = array( $time, array(
                'v' => $history->clicks,
                'f' => WSKO_Class_Helper::format_number( $history->clicks ),
            ), array(
                'v' => $history->clicks_ref,
                'f' => WSKO_Class_Helper::format_number( $history->clicks_ref ),
            ) );
            $history_position[] = array( $time, array(
                'v' => $history->position,
                'f' => WSKO_Class_Helper::format_number( $history->position, 2 ),
            ), array(
                'v' => $history->position_ref,
                'f' => WSKO_Class_Helper::format_number( $history->position_ref, 2 ),
            ) );
            $history_impressions[] = array( $time, array(
                'v' => $history->impressions,
                'f' => WSKO_Class_Helper::format_number( $history->impressions ),
            ), array(
                'v' => $history->impressions_ref,
                'f' => WSKO_Class_Helper::format_number( $history->impressions_ref ),
            ) );
            $history_ctr[] = array( $time, array(
                'v' => $ctr,
                'f' => WSKO_Class_Helper::format_number( $ctr, 2 ),
            ), array(
                'v' => $ctr_ref,
                'f' => WSKO_Class_Helper::format_number( $ctr_ref, 2 ),
            ) );
        }
        $res['total_keywords'] = $total_keywords;
        $res['total_clicks'] = $total_clicks;
        $res['total_impressions'] = $total_impressions;
        
        if ( $hist_count > 0 ) {
            $res['avg_position'] = round( $avg_postion / $hist_count, 2 );
        } else {
            $res['avg_position'] = 0;
        }
        
        $res['total_clicks_ref'] = $total_clicks_ref;
        $res['total_impressions_ref'] = $total_impressions_ref;
        
        if ( $hist_count > 0 ) {
            $res['avg_position_ref'] = round( $avg_postion_ref / $hist_count, 2 );
        } else {
            $res['avg_position_ref'] = 0;
        }
        
        $res['history_clicks'] = $history_clicks;
        $res['history_position'] = $history_position;
        $res['history_impressions'] = $history_impressions;
        $res['history_ctr'] = $history_ctr;
        $res['table_monitored_keywords'] = $table_kw;
        return $res;
    }
    
    /*/*/
    public function uses_timespan( $subpage = false )
    {
        if ( $subpage === 'competitors' || $subpage === 'keyword_research' ) {
            return false;
        }
        return $this->uses_timespan;
    }
    
    public function _is_accessible( $subpage = false )
    {
        if ( $subpage === 'monitoring' || $subpage === 'keyword_research' ) {
            return true;
        }
        $is_fetching = !WSKO_Class_Core::get_option( 'search_query_first_run' ) || WSKO_Class_Core::get_option( 'search_query_running' );
        $invalid = ( $is_fetching ? true : !WSKO_Class_Search::check_se_connected() );
        return !$invalid;
    }
    
    //Singleton
    static  $instance ;
}
WSKO_Controller_Search::init_controller();