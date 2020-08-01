<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$elem = ( isset( $template_args['elem'] ) ? $template_args['elem'] : 'search,analytics,bavoko,onpage' );
$array_elem = explode( ',', $elem );
$collapsed = ( isset( $template_args['collapsed'] ) ? $template_args['collapsed'] : false );
$ga_token_se = WSKO_Class_Search::get_se_token();
?>

<div class="setup-widget-wrapper">
    <div class="<?php 
echo  ( $collapsed ? 'panel-group' : '' ) ;
?>" id="wsko_setup_accordion">
        <?php 
// BAVOKO API
if ( in_array( 'bavoko', $array_elem ) ) {
}
// Google Search Console API
if ( in_array( 'search', $array_elem ) ) {
    echo  WSKO_Class_Template::render_template( 'settings/view-api-search.php', array(
        'collapsed' => $collapsed,
    ) ) ;
}
// Google Analytics API
if ( in_array( 'analytics', $array_elem ) ) {
}
// Onpage Analysis
if ( in_array( 'onpage', $array_elem ) ) {
    
    if ( !wp_next_scheduled( 'wsko_onpage_analysis' ) ) {
        WSKO_Class_Template::render_template( 'onpage/template-analysis-config.php', array(
            'collapsed' => $collapsed,
        ) );
    } else {
        WSKO_Class_Template::render_notification( 'success', array(
            'msg' => wsko_loc( 'setup', 'onpage_report_running' ),
        ) );
    }

}
?>
    </div>
</div>