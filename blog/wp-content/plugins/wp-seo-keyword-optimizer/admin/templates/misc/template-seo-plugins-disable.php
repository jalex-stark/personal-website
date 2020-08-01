<?php
if (!defined('ABSPATH'))
	exit;

$preview = isset($template_args['preview']) ? $template_args['preview'] : false;
//if ($preview)
	WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_onpage', 'seo_plugins_active'), 'list' => WSKO_Class_Compatibility::get_active_seo_plugins(), 'subnote' =>  wsko_loc('notif_onpage', 'seo_plugins_active_sub')));
//else
	//WSKO_Class_Template::render_notification('warning', array('msg' =>  wsko_loc('notif_onpage', 'seo_plugins_active'), 'list' => WSKO_Class_Compatibility::get_active_seo_plugins(), 'subnote' => wsko_loc('notif_onpage', 'seo_plugins_active_sub')));
?>