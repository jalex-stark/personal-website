<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !WSKO_Class_Helper::is_php_func_enabled( 'set_time_limit' ) ) {
    WSKO_Class_Template::render_notification( 'error', array(
        'msg'     => wsko_loc( 'notif', 'e_set_time_limit' ),
        'subnote' => wsko_loc( 'notif', 'e_set_time_limit_sub' ),
    ) );
}

if ( !WSKO_Class_Core::is_demo() ) {
    $user = wsko_fs()->get_user();
    if ( $user && !$user->is_verified() ) {
        WSKO_Class_Template::render_notification( 'warning', array(
            'msg' => wsko_loc( 'notif', 'e_license_user_verify', array(
            'account_link' => WSKO_Controller_Account::get_link(),
        ) ),
        ) );
    }
    
    if ( wsko_fs()->is_premium() ) {
        if ( !wsko_fs()->can_use_premium_code() ) {
            WSKO_Class_Template::render_notification( 'warning', array(
                'msg' => wsko_loc( 'notif', 'e_license_timeout', array(
                'account_link' => WSKO_Controller_Account::get_link(),
            ) ),
            ) );
        }
    } else {
        
        if ( wsko_fs()->can_use_premium_code() ) {
            $user = wsko_fs()->get_user();
            WSKO_Class_Template::render_notification( 'warning', array(
                'msg'     => wsko_loc( 'notif', 'e_license_prem', array(
                'account_link' => WSKO_Controller_Account::get_link(),
            ) ),
                'subnote' => wsko_loc( 'notif', 'e_license_prem_sub', array(
                'mail' => $user->email,
            ) ),
            ) );
        }
    
    }
    
    if ( wsko_fs()->is_premium() ) {
        if ( wsko_fs()->_get_license() && !wsko_fs()->get_plan() ) {
            WSKO_Class_Template::render_notification( 'warning', array(
                'msg' => wsko_loc( 'notif', 'e_license_plan', array(
                'account_link' => WSKO_Controller_Account::get_link(),
            ) ),
            ) );
        }
    }
}

//Update Notifications
$wsko_data = WSKO_Class_Core::get_data();
if ( isset( $wsko_data['version_pre'] ) && $wsko_data['version_pre'] && version_compare( $wsko_data['version_pre'], '2.1', '<' ) ) {
    WSKO_Class_Template::render_notification( 'warning', array(
        'msg'     => wsko_loc( 'update', 'un_core_update' ) . WSKO_Class_Template::render_ajax_button(
        __( 'Reset', 'wsko' ),
        'reset_configuration',
        array(
        'delete_metas'     => false,
        'delete_cache'     => false,
        'delete_backups'   => false,
        'delete_redirects' => false,
    ),
        array(),
        true
    ),
        'subnote' => wsko_loc( 'notif', 'un_core_update_sub' ),
    ) );
}
//WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('update', 'un_onpage_meta_desc'), 'discardable' => 'onpage_meta_desc_length_16_05_2018'));
//WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('update', 'un_onpage_settings').WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), false, __('Settings', 'wsko'), array(), true), 'discardable' => 'onpage_settings_02_11_2018'));
if ( WSKO_Class_Core::get_option( 'se_has_v1' ) ) {
    WSKO_Class_Template::render_notification( 'warning', array(
        'msg'         => wsko_loc( 'update', 'un_search_api', array(
        'test' => 'true',
    ) ) . WSKO_Class_Template::render_page_link(
        WSKO_Controller_Settings::get_instance(),
        false,
        __( 'Settings', 'wsko' ),
        array(),
        true
    ),
        'discardable' => 'search_api_14_11_2018',
    ) );
}
WSKO_Class_Compatibility::render_compatibility_notices();