<?php
if (!defined('ABSPATH')) exit;

if (!wp_next_scheduled('wsko_daily_maintenance'))
{
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_settings', 'cronjob_dm_inactive').WSKO_Class_Template::render_ajax_button(__('Reset CronJobs', 'wsko'), 'reset_cronjobs', array(), array(), true)));
}
?>