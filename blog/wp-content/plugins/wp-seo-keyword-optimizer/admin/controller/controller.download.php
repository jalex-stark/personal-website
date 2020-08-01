<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller_Download extends WSKO_Controller
{
    public  $is_invisible_page = true ;
    public  $has_main_nav_link = false ;
    public  $link = "safe_download" ;
    public  $template_folder = "download" ;
    public function get_title()
    {
        return __( 'Safe Download', 'wsko' );
    }
    
    public function redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo(), true ) ) {
            return;
        }
        $type = ( isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : false );
        $arg = ( isset( $_GET['arg'] ) ? sanitize_text_field( $_GET['arg'] ) : false );
        switch ( $type ) {
            case 'backup':
                if ( !$this->can_execute_action( true, true ) ) {
                    return;
                }
                $backup = WSKO_Class_Backup::get_configuration_backup( $arg );
                
                if ( $backup ) {
                    $backup['key'] = $arg;
                    $data = json_encode( $backup );
                    WSKO_Class_Helper::set_file_download_headers( 'bst_backup_' . (( $backup['auto'] ? 'auto_' : '' )) . $backup['time'] . '_' . date( 'd.m.Y-H:i', $backup['time'] ) . '.bst_backup', strlen( $data ) );
                    echo  $data ;
                    exit;
                }
                
                break;
        }
    }
    
    public static function load_lazy_page_data()
    {
        return true;
    }
    
    public static function get_download_link( $type, $arg = false )
    {
        switch ( $type ) {
            case 'backup':
                return static::get_link() . '&type=' . $type . '&arg=' . $arg;
                break;
        }
    }
    
    //Singleton
    static  $instance ;
}
WSKO_Controller_Download::init_controller();