<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Freemius
{
    public static function init_get_freemius()
    {
        global  $wsko_fs ;
        
        if ( !isset( $wsko_fs ) ) {
            WSKO_Class_Core::include_lib( 'freemius' );
            
            if ( WSKO_Class_Core::is_demo() && function_exists( 'wsko_get_demo_fs' ) ) {
                $wsko_fs = wsko_get_demo_fs();
            } else {
                $wsko_fs = fs_dynamic_init( array(
                    'id'             => '1608',
                    'slug'           => 'wp-seo-keyword-optimizer',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_44907806b1b63319b99263a8ca3a4',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'premium_suffix' => '(Premium)',
                    'menu'           => array(
                    'slug'    => 'wsko_dashboard',
                    'contact' => false,
                    'support' => false,
                ),
                    'is_live'        => true,
                ) );
                do_action( 'wsko_fs_loaded' );
                fs_override_i18n( array(
                    'upgrade' => __( "Upgrade", 'wsko' ),
                ), 'wp-seo-keyword-optimizer' );
                $wsko_fs->add_filter( 'permission_list', function ( $permissions ) {
                    $permissions['analytics'] = array(
                        'icon-class' => 'dashicons dashicons-chart-bar',
                        'label'      => wsko_fs()->get_text_inline( 'Usage Tracking with Google Analytics', 'permissions-analytics' ),
                        'desc'       => wsko_fs()->get_text_inline( 'Collecting plugin usage data with Google Analytics', 'permissions-analytics_desc' ),
                        'priority'   => 15,
                    );
                    return $permissions;
                } );
            }
        
        }
        
        return $wsko_fs;
    }
    
    public static function get_optin_url()
    {
        $params = array(
            'nonce'     => wp_create_nonce( wsko_fs()->get_unique_affix() . '_reconnect' ),
            'fs_action' => wsko_fs()->get_unique_affix() . '_reconnect',
        );
        return wsko_fs()->get_activation_url( $params );
    }

}
function wsko_fs()
{
    return WSKO_Class_Freemius::init_get_freemius();
}
