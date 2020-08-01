<?php
if (!defined('ABSPATH')) exit;
$client = WSKO_Class_Search::get_ga_client_se();
if (defined('WSKO_GOOGLE_INCLUDE_FAILED') && WSKO_GOOGLE_INCLUDE_FAILED)
{
	$old_ver = Google_Client::LIBVER;
	
	if (!$client)
	{
		WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'google_api_error', array('old_ver' => $old_ver)), 'subnote' => wsko_loc('notif', 'google_api_error_sub')));
	}
	else
	{
		WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif', 'google_api_warning', array('old_ver' => $old_ver)), 'subnote' => wsko_loc('notif', 'google_api_error_sub')));
	}
}
else
{
	if (WSKO_Class_Search::get_se_token())
	{
		if (!wp_next_scheduled('wsko_cache_keywords'))
		{
			WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_search', 'cronjob_inactive').WSKO_Class_Template::render_ajax_button(__('Reset CronJobs', 'wsko'), 'reset_cronjobs', array(), array(), true)));
		}
		else if (!WSKO_Class_Core::get_option('search_query_first_run') && wp_next_scheduled('wsko_cache_keywords'))
		{
			if (WSKO_Class_Core::get_option('search_query_first_run_timeouts') >= 5)
			{
				$last_error = WSKO_Class_Core::get_option('search_query_first_run_timeout_error');
				$last_error_text = '';
				if ($last_error)
				{
					ob_start();
					print_r($last_error);
					$last_error_text = __('Last error:', 'wsko').' '.ob_get_clean();
				}
				WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_search', 'cronjob_timeout').WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#apis', __('API Settings', 'wsko'), array('button' => true), true), 'subnote' => $last_error_text));
			}
		}
		else
		{
			if (WSKO_Class_Search::get_se_property() && WSKO_Class_Search::check_se_access())
			{
				WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_search', 'api_error').WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#apis', __('Settings', 'wsko'), array('button' => false), true)));
			}
		}
	}
}
?>