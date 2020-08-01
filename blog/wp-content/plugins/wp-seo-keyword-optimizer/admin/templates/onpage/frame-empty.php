<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$subpage = ( isset( $template_args['subpage'] ) ? $template_args['subpage'] : false );
$is_disabled = false;
/*if ($subpage == 'titles' || $subpage == 'descriptions')
{
	if (!WSKO_Class_Onpage::seo_plugins_disabled())
	{
		WSKO_Class_Template::render_template('misc/template-seo-plugins-disable.php', array());
		$is_disabled = true;
	}
}*/

if ( !$is_disabled ) {
    $is_fetching = false;
    
    if ( WSKO_Class_Core::get_option( 'onpage_analysis_running' ) ) {
        $last_onpage_segment = WSKO_Class_Core::get_option( 'last_onpage_offset' );
        $last_onpage_segment_count = WSKO_Class_Core::get_option( 'last_onpage_segment_count' );
        WSKO_Class_Template::render_notification( 'info', array(
            'msg'     => wsko_loc( 'notif_onpage', 'updating' ),
            'subnote' => ( $last_onpage_segment_count ? wsko_loc( 'notif_onpage', 'updating_sub', array(
            'curr'  => $last_onpage_segment + 1,
            'count' => $last_onpage_segment_count,
        ) ) : '' ),
        ) );
        $is_fetching = true;
    }
    
    if ( !$is_fetching ) {
        
        if ( !WSKO_Class_Onpage::has_current_report() ) {
            WSKO_Class_Template::render_no_data_view( array(
                'elem' => 'onpage',
            ) );
        } else {
            if ( !$is_fetching ) {
                WSKO_Class_Template::render_notification( 'info', array(
                    'msg' => wsko_loc( 'notif_onpage', 'pending' ),
                ) );
            }
        }
    
    }
}
