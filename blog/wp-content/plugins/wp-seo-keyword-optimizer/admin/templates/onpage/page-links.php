<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
$analysis = ( isset( $global_analysis['current_report'] ) ? $global_analysis['current_report'] : false );
$header_link_table = array(
    __( 'CS', 'wsko' ) . WSKO_Class_Template::render_infoTooltip( __( 'Content Score', 'wsko' ), 'info', true ),
    __( 'Page', 'wsko' ),
    array(
    'name'  => __( 'URL Length', 'wsko' ),
    'width' => '15%',
),
    array(
    'name'  => '',
    'width' => '5%',
)
);
$filter_link_table = array(
    'onpage_score' => array(
    'title' => __( 'Onpage Score', 'wsko' ),
    'type'  => 'number_range',
    'max'   => $analysis['max']['onpage_score'],
),
    'url'          => array(
    'title' => __( 'URL', 'wsko' ),
    'type'  => 'text',
),
    'post_type'    => array(
    'title'  => __( 'Post Type', 'wsko' ),
    'type'   => 'select',
    'values' => ( isset( $analysis['post_types'] ) ? WSKO_Class_Helper::get_post_type_labels( $analysis['post_types'] ) : array() ),
),
    'url_length'   => array(
    'title' => __( 'URL Length', 'wsko' ),
    'type'  => 'number_range',
    'max'   => $analysis['max']['url_length'],
),
);
$order = array(
    'col' => 2,
    'dir' => 'desc',
);
?>
<div class="row">
	<?php 
WSKO_Class_Template::render_panel( array(
    'type'  => 'progress',
    'title' => __( 'URL Length', 'wsko' ),
    'col'   => 'col-sm-12 col-xs-12',
    'items' => array( array(
    'title'          => __( 'OK', 'wsko' ),
    'tooltip'        => sprintf( _n(
    '%s Page',
    '%s Pages',
    $analysis['url_length_dist']['ok'],
    'wsko'
), number_format_i18n( $analysis['url_length_dist']['ok'] ) ),
    'value'          => $analysis['url_length_dist']['ok'],
    'max'            => $analysis['total_pages'],
    'progress_class' => 'success',
    'class'          => 'wsko-external-table-filter',
    'data'           => array(
    'table' => '#wsko_onpage_analysis_permalink_table',
    'val'   => 'url_length:0:' . (WSKO_ONPAGE_URL_MAX - 1),
),
), array(
    'title'          => __( 'Too Long', 'wsko' ),
    'tooltip'        => sprintf( _n(
    '%s Page',
    '%s Pages',
    $analysis['url_length_dist']['too_long'],
    'wsko'
), number_format_i18n( $analysis['url_length_dist']['too_long'] ) ),
    'value'          => $analysis['url_length_dist']['too_long'],
    'max'            => $analysis['total_pages'],
    'progress_class' => 'warning',
    'class'          => 'wsko-external-table-filter',
    'data'           => array(
    'table' => '#wsko_onpage_analysis_permalink_table',
    'val'   => 'url_length:' . (WSKO_ONPAGE_URL_MAX + 1) . ':' . $analysis['max']['url_length'],
),
) ),
) );
?>
		
	<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Pages', 'wsko' ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_table(
    $header_link_table,
    array(),
    array(
    'id'     => 'wsko_onpage_analysis_permalink_table',
    'order'  => $order,
    'ajax'   => array(
    'action' => 'wsko_table_onpage',
    'arg'    => 'links',
),
    'filter' => $filter_link_table,
),
    true
),
) );
?>
</div>