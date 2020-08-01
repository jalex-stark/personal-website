<?php
/*
Plugin Name: BAVOKO SEO Tools
Plugin URI: 	http://www.bavoko.tools/
Description: 	The Most Comprehensive All-in-One WordPress SEO Plugin!
Version: 		2.1.9.12
Author: 		BAVOKO Tools
Text Domain: 	wsko
Domain Path: 	/languages
Author URI: 	http://www.bavoko.tools/
License:     	GPL2 or later
*/
/*
BAVOKO SEO Tools is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
BAVOKO SEO Tools is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/
if (!defined('ABSPATH')) exit;

if (function_exists('wsko_fs'))
{
    wsko_fs()->set_basename(true, __FILE__);
    return;
}
else
{
	define('WSKO_VERSION', '2.1.9.12');

	define('WSKO_PLUGIN_PATH', dirname(__FILE__) . '/');
	define('WSKO_PLUGIN_URL', plugins_url('', __FILE__) . '/');
	define('WSKO_PLUGIN_KEY', str_replace('\\', '/', str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, WP_PLUGIN_DIR.'/'), '', __FILE__)));

	require_once(WSKO_PLUGIN_PATH . 'classes/class.core.php');
	require_once(WSKO_PLUGIN_PATH . 'classes/class.freemius.php');

	$critical_errors = WSKO_Class_Core::get_critical_errors();
	if ($critical_errors)
	{
		global $wsko_critical_errors;
		$wsko_critical_errors = $critical_errors;
		WSKO_Class_Core::init_classes(false, true);
		require_once(WSKO_PLUGIN_PATH . 'depr.php');
		return;
	}

	WSKO_Class_Core::init_classes();
	WSKO_Class_Freemius::init_get_freemius();
		
	function wsko_install_plugin()
	{
		WSKO_Class_Core::init_classes(true);
		if (WSKO_Class_Core::is_configured())
			WSKO_Class_Core::pre_track(2);
		else
			WSKO_Class_Core::pre_track(0);
		WSKO_Class_Crons::register_cronjobs();
		WSKO_Class_Core::install();

		if ($plugins = get_option('active_plugins'))
		{
			if ($key = array_search(WSKO_PLUGIN_KEY, $plugins))
			{
				array_splice($plugins, $key, 1);
				array_unshift($plugins, WSKO_PLUGIN_KEY);
				update_option('active_plugins', $plugins);
			}
		}
		if (WSKO_Class_Core::is_premium())
			WSKO_Class_Reporting::register_report_endpoint(true);
		//update_option('wsko_do_activation_redirect', true);
	}
	register_activation_hook(__FILE__, 'wsko_install_plugin');

	function wsko_deinstall_plugin()
	{
		WSKO_Class_Core::init_classes(true);
		WSKO_Class_Core::deinstall();
	}
	register_deactivation_hook(__FILE__, 'wsko_deinstall_plugin');

	function wsko_update_plugin($upgrader_object, $options)
	{
		WSKO_Class_Core::init_classes(true);
		
		$current_plugin_path_name = plugin_basename(__FILE__);

		if ($options['action'] == 'update' && $options['type'] == 'plugin')
		{
			if ($options['plugins'])
			{
				foreach($options['plugins'] as $pl)
				{
					if ($pl == $current_plugin_path_name)
					{
						WSKO_Class_Core::update();
						$wsko_data = WSKO_Class_Core::get_data();
						if ((isset($wsko_data['version']) && $wsko_data['version'] && version_compare($wsko_data['version'], "2.0", "<")))
							update_option('wsko_do_activation_redirect', true);
					}
				}
			}
		}
	}
	add_action('upgrader_process_complete', 'wsko_update_plugin', 10, 2);

	/*function wsko_activation_redirect($plugin)
	{
		if (get_option('wsko_do_activation_redirect') && !WSKO_Class_Core::get_option('activation_page_shown'))
		{
			WSKO_Class_Core::save_option('activation_page_shown', true);
			delete_option('wsko_do_activation_redirect');
			wp_redirect(WSKO_Controller_Info::get_link());
			exit();
		}
	}
	add_action('admin_init', 'wsko_activation_redirect');*/
}
?>