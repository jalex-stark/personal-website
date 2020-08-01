<?php
/*
 * Deprecated or critical error routine
 */
add_action('admin_menu', function(){
    $hook = add_menu_page(
        'BAVOKO SEO Tools',
        'BAVOKO SEO Tools', //Dont change!!!
        'edit_posts',
        'wsko_error',
        function(){
            global $wsko_critical_errors, $wp_version;
            
            ?><div id="wsko_admin_critical_error_wrapper" class="wsko-display-none-fix">
                <h2>BAVOKO SEO Tools is blocked due to critical errors!</h2>
                <?php
                if (isset($wsko_critical_errors['incompatible_php']) && $wsko_critical_errors['incompatible_php'])
                    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'ce_php', array('php' => PHP_VERSION ? PHP_VERSION : '(unversioned)')), 'subnote' => wsko_loc('notif', 'ce_php_sub')));

                if (isset($wsko_critical_errors['incompatible_wp']) && $wsko_critical_errors['incompatible_wp'])
                    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'ce_wp', array('wp' => $wp_version ? $wp_version : '(unversioned)'))));
                    
                if (isset($wsko_critical_errors['content_dir_access']) && $wsko_critical_errors['content_dir_access'])
                    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'ce_dir', array('dir' => WP_CONTENT_DIR)), 'subnote' => wsko_loc('notif_tools', 'ce_dir_sub')));

                if (isset($wsko_critical_errors['bst_dir_access']) && $wsko_critical_errors['bst_dir_access'])
                    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'ce_dir_access', array('dir' => WP_CONTENT_DIR)), 'subnote' => wsko_loc('notif_tools', 'ce_dir_access_sub')));

                WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'cr_main')));
            ?></div><?php
        },
        '',
        2
    );
    add_action('admin_enqueue_scripts', function($hook){
        if ($hook == 'toplevel_page_wsko_error')
            wp_enqueue_style('wsko_core_css', WSKO_PLUGIN_URL . 'admin/css/core.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css', array(), WSKO_VERSION);
    }, 0);
});
?>