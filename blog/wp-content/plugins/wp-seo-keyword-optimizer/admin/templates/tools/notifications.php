
<?php
if (!defined('ABSPATH')) exit;

if (!wp_next_scheduled('wsko_sitemap_generation') && WSKO_Class_Core::get_setting('automatic_sitemap'))
{
    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'cronjob_sitemap_inactive').WSKO_Class_Template::render_ajax_button(__('Reset', 'wsko'), 'reset_cronjobs', array(), array(), true)));
}
?>