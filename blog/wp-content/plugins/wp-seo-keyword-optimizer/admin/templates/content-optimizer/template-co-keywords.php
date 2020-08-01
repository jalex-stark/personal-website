<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$post_id = ( isset( $template_args['post_id'] ) ? $template_args['post_id'] : false );
$keywords = ( isset( $template_args['keyword_data'] ) ? $template_args['keyword_data'] : false );

if ( $post_id ) {
    $keywords_available = WSKO_Controller_Search::get_instance()->is_accessible( 'overview' );
    $table_keywords = array();
    $max_t_kw_clicks = 0;
    $max_t_kw_pos = 0;
    $max_t_kw_impr = 0;
    $max_t_kw_ctr = 0;
    $max_t_kw_sv = 0;
    $max_t_kw_cpc = 0;
    if ( $keywords ) {
        foreach ( $keywords as $kw ) {
            $kw_data = array(
                'clicks' => '-',
                'pos'    => '-',
                'kw_den' => '-',
            );
            $data_arr = array(
                array(
                'order' => $kw->keyval,
                'value' => WSKO_Class_Template::render_keyword_field( $kw->keyval, true ),
            ),
                array(
                'order' => $kw->position,
                'value' => WSKO_Class_Helper::format_number( $kw->position ) . (( $kw->position_ref != null ? WSKO_Class_Template::render_progress_icon(
                round( abs( $kw->position_ref - $kw->position ), 2 ),
                WSKO_Class_Helper::get_ref_value( $kw->position, $kw->position_ref ),
                array(
                'absolute' => true,
                'tooltip'  => $kw->position_ref,
            ),
                true
            ) : '' )),
            ),
                array(
                'order' => $kw->clicks,
                'value' => WSKO_Class_Helper::format_number( $kw->clicks ) . (( $kw->clicks_ref != null ? WSKO_Class_Template::render_progress_icon(
                $kw->clicks_ref,
                WSKO_Class_Helper::get_ref_value( $kw->clicks, $kw->clicks_ref ),
                array(),
                true
            ) : '' )),
            ),
                array(
                'order' => $kw->impressions,
                'value' => WSKO_Class_Helper::format_number( $kw->impressions ) . (( $kw->impressions_ref != null ? WSKO_Class_Template::render_progress_icon(
                $kw->impressions_ref,
                WSKO_Class_Helper::get_ref_value( $kw->impressions, $kw->impressions_ref ),
                array(),
                true
            ) : '' )),
            ),
                array(
                'order' => $kw->ctr,
                'value' => round( $kw->ctr, 2 ) . '%' . (( $kw->ctr_ref != null ? WSKO_Class_Template::render_progress_icon(
                $kw->ctr_ref,
                WSKO_Class_Helper::get_ref_value( round( $kw->ctr, 2 ), $kw->ctr_ref ),
                array(),
                true
            ) : '' )),
            )
            );
            if ( $kw->position > $max_t_kw_pos ) {
                $max_t_kw_pos = $kw->position;
            }
            if ( $kw->clicks > $max_t_kw_clicks ) {
                $max_t_kw_clicks = $kw->clicks;
            }
            if ( $kw->impressions > $max_t_kw_impr ) {
                $max_t_kw_impr = $kw->impressions;
            }
            if ( $kw->ctr > $max_t_kw_ctr ) {
                $max_t_kw_ctr = $kw->ctr;
            }
            $data_arr[] = array(
                'value' => '<a href="#" class="wsko-co-add-priority-keyword-inline dark" data-toggle="tooltip" data-title="' . __( 'Add to SEO Keywords', 'wsko' ) . '" data-post="' . $post_id . '" data-keyword="' . $kw->keyval . '" data-prio="" data-nonce="' . wp_create_nonce( 'wsko_co_add_priority_keyword' ) . '"><i class="fa fa-plus fa-fw"></i></a>',
            );
            $table_keywords[] = $data_arr;
        }
    }
    $keyword_filters = array(
        '1' => array(
        'title' => __( 'Position', 'wsko' ),
        'type'  => 'number_range',
        'max'   => $max_t_kw_pos,
    ),
        '2' => array(
        'title' => __( 'Clicks', 'wsko' ),
        'type'  => 'number_range',
        'max'   => $max_t_kw_clicks,
    ),
        '3' => array(
        'title' => __( 'Impressions', 'wsko' ),
        'type'  => 'number_range',
        'max'   => $max_t_kw_impr,
    ),
    );
    
    if ( $keywords_available ) {
        ?>
    <ul class="wsko-nav bsu-tabs bsu-tabs-sm">
        <li><a href="#wsko_co_keywords_rankings" class="wsko-nav-link wsko-nav-link-active"><?php 
        echo  __( 'Ranking Keywords', 'wsko' ) ;
        ?></a></li>
        <li><a href="#wsko_co_keywords_histories" class="wsko-nav-link"><?php 
        echo  __( 'Ranking Histories', 'wsko' ) ;
        ?></a></li>
        <?php 
        ?>	
    </ul>
    <div class="wsko-tab-content">
        <div id="wsko_co_keywords_rankings" class="wsko-tab wsko-tab-active">
            <?php 
        
        if ( !WSKO_Class_Core::get_option( 'search_query_first_run' ) ) {
            WSKO_Class_Template::render_notification( 'warning', array(
                'msg' => wsko_loc( 'notif_co', 'search_first_run' ),
            ) );
        } else {
            
            if ( $keywords ) {
                $keyword_headers = array(
                    __( 'Keyword', 'wsko' ),
                    __( 'Pos', 'wsko' ),
                    __( 'Clicks', 'wsko' ),
                    __( 'Impr', 'wsko' ),
                    __( 'CTR', 'wsko' )
                );
                $keyword_headers[] = '';
                WSKO_Class_Template::render_table( $keyword_headers, $table_keywords, array(
                    'order'  => array(
                    'col' => '2',
                    'dir' => 'desc',
                ),
                    'filter' => $keyword_filters,
                ) );
            } else {
                WSKO_Class_Template::render_template( 'misc/template-no-data.php', array() );
            }
        
        }
        
        ?>
        </div>
        <div id="wsko_co_keywords_histories" class="wsko-tab">
                <?php 
        $clicks_hist = WSKO_Class_Core::get_post_data_history( $post_id, 'search_clicks' );
        $pos_hist = WSKO_Class_Core::get_post_data_history( $post_id, 'search_pos' );
        $imp_hist = WSKO_Class_Core::get_post_data_history( $post_id, 'search_imp' );
        $ctr_hist = WSKO_Class_Core::get_post_data_history( $post_id, 'search_ctr' );
        if ( $clicks_hist ) {
            foreach ( $clicks_hist as $k => $hist ) {
                $k_v = date( 'M. d, Y', $k );
                $clicks_hist[$k] = array( array(
                    'v' => $k_v,
                    'f' => $k_v,
                ), array(
                    'v' => $hist,
                    'f' => WSKO_Class_Helper::format_number( $hist ),
                ) );
            }
        }
        if ( $pos_hist ) {
            foreach ( $pos_hist as $k => $hist ) {
                $k_v = date( 'M. d, Y', $k );
                $pos_hist[$k] = array( array(
                    'v' => $k_v,
                    'f' => $k_v,
                ), array(
                    'v' => $hist,
                    'f' => WSKO_Class_Helper::format_number( $hist ),
                ) );
            }
        }
        if ( $imp_hist ) {
            foreach ( $imp_hist as $k => $hist ) {
                $k_v = date( 'M. d, Y', $k );
                $imp_hist[$k] = array( array(
                    'v' => $k_v,
                    'f' => $k_v,
                ), array(
                    'v' => $hist,
                    'f' => WSKO_Class_Helper::format_number( $hist ),
                ) );
            }
        }
        if ( $ctr_hist ) {
            foreach ( $ctr_hist as $k => $hist ) {
                $k_v = date( 'M. d, Y', $k );
                $ctr_hist[$k] = array( array(
                    'v' => $k_v,
                    'f' => $k_v,
                ), array(
                    'v' => $hist,
                    'f' => WSKO_Class_Helper::format_number( $hist ),
                ) );
            }
        }
        ?>
                    <ul class="wsko-nav bsu-tabs bsu-tabs-sm">
                        <li><a href="#ext_search_tab_clicks" class="wsko-nav-link wsko-nav-link-active"><?php 
        echo  __( 'Clicks History', 'wsko' ) ;
        ?></a></li>
                        <li><a href="#ext_search_tab_pos" class="wsko-nav-link"><?php 
        echo  __( 'Position History', 'wsko' ) ;
        ?></a></li>
                        <li><a href="#ext_search_tab_imp" class="wsko-nav-link"><?php 
        echo  __( 'Impressions History', 'wsko' ) ;
        ?></a></li>
                        <li><a href="#ext_search_tab_ctr" class="wsko-nav-link"><?php 
        echo  __( 'CTR History', 'wsko' ) ;
        ?></a></li>
                    </ul>    
                    <div class="wsko-tab-content">
                        <div id="ext_search_tab_clicks" class="wsko-tab wsko-tab-active">
                            <?php 
        WSKO_Class_Template::render_chart(
            'area',
            array( __( 'Date', 'wsko' ), __( 'Clicks', 'wsko' ) ),
            $clicks_hist,
            array(
            'axisTitleY'  => __( 'Clicks', 'wsko' ),
            'chart_left'  => '15',
            'chart_width' => '70',
        ),
            false
        );
        ?>
                        </div>
                        <div id="ext_search_tab_pos" class="wsko-tab">
                            <?php 
        WSKO_Class_Template::render_chart(
            'area',
            array( __( 'Date', 'wsko' ), __( 'Position', 'wsko' ) ),
            $pos_hist,
            array(
            'axisTitleY'  => __( 'Position', 'wsko' ),
            'chart_left'  => '15',
            'chart_width' => '70',
        ),
            false
        );
        ?>
                        </div>
                        <div id="ext_search_tab_imp" class="wsko-tab">
                            <?php 
        WSKO_Class_Template::render_chart(
            'area',
            array( __( 'Date', 'wsko' ), __( 'Impressions', 'wsko' ) ),
            $imp_hist,
            array(
            'axisTitleY'  => __( 'Impressions', 'wsko' ),
            'chart_left'  => '15',
            'chart_width' => '70',
        ),
            false
        );
        ?>
                        </div>
                        <div id="ext_search_tab_ctr" class="wsko-tab">
                            <?php 
        WSKO_Class_Template::render_chart(
            'area',
            array( __( 'Date', 'wsko' ), __( 'CTR', 'wsko' ) ),
            $ctr_hist,
            array(
            'axisTitleY'  => __( 'CTR', 'wsko' ),
            'chart_left'  => '15',
            'chart_width' => '70',
        ),
            false
        );
        ?>
                        </div>
                    </div>
                    <?php 
        ?>
        </div>
        <?php 
        ?>	
    </div><?php 
    } else {
        WSKO_Class_Template::render_notification( 'info', array(
            'msg' => wsko_loc( 'notif_search', 'api_error' ) . WSKO_Class_Template::render_page_link(
            WSKO_Controller_Search::get_instance(),
            '',
            __( 'See more', 'wsko' ),
            array(
            'button' => false,
        ),
            true
        ),
        ) );
    }

}
