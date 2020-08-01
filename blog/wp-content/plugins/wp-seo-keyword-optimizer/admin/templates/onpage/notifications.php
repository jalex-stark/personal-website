<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$has_report = WSKO_Class_Onpage::has_current_report();
if ( !wp_next_scheduled( 'wsko_onpage_analysis' ) ) {
    if ( $has_report ) {
        WSKO_Class_Template::render_notification( 'error', array(
            'msg' => wsko_loc( 'notif_onpage', 'cronjob_inactive' ) . WSKO_Class_Template::render_ajax_button(
            __( 'Reset CronJobs', 'wsko' ),
            'reset_cronjobs',
            array(),
            array(),
            true
        ),
        ) );
    }
}