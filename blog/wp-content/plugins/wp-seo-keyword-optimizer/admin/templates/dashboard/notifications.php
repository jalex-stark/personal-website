<?php
if (!defined('ABSPATH')) exit;

if (/*!wp_next_scheduled('wsko_cache_social') || !wp_next_scheduled('wsko_check_timeout') ||*/ !wp_next_scheduled('wsko_daily_maintenance'))
{
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'system_cronjobs').WSKO_Class_Template::render_ajax_button(__('Reset CronJobs', 'wsko'), 'reset_cronjobs', array(), array(), true)));
}
?>
