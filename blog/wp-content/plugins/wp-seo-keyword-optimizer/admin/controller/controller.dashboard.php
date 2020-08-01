<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Dashboard extends WSKO_Controller
{
	//Options
	public $icon = "pie-chart";
	public $link = "dashboard";
	public $uses_timespan = true;
	 
	public $scripts = array('dashboard');

	public $template_folder = "dashboard";

	public function get_title()
	{
		return __('Dashboard', 'wsko');
	}

	public function get_knowledge_base_tags($subpage)
	{
		return array("general");
	}
	
	public function load_lazy_page_data($lazy_data)
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$notif = "";
		$data = array();
		
		
		return array(
				'success' => true,
				'data' => $data,
				'notif' => $notif
			);
	}
	
	public function get_breadcrumb_title()
	{
		$current_user = wp_get_current_user();
		return sprintf(__('Welcome back %s!', 'wsko'), ($current_user?", ".$current_user->user_login:''));
	}
	
	//Singleton
	static $instance;
}
WSKO_Controller_Dashboard::init_controller();
?>