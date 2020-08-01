<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Setup extends WSKO_Controller
{
	public $admin_only = true;
	public $show_in_setup = true;
	public $has_main_nav_link = false;
	public $link = "setup";
	public $styles = array('setup');
	public $scripts = array('setup');
	
	public $template_folder = "setup";
	
	public $ajax_actions = array('finish_setup');

	public function get_title()
	{
		return __('Setup', 'wsko');
	}
	
	public function redirect()
	{
		WSKO_Class_Core::pre_track(1);
	}
	
	public function load_lazy_page_data()
	{
		return true;
	}
	
	public function action_finish_setup()
	{
		if (!$this->can_execute_action())
			return false;

		if (WSKO_Class_Core::is_configured())
		{
			return array(
					'success' => false,
					'msg' => __('Setup allready finished', 'wsko'),
					'redirect' => WSKO_Controller_Dashboard::get_link()
				);
		}
		if (WSKO_Class_Core::set_configured())
		{
			WSKO_Class_Core::setup();
			return array(
					'success' => true,
					'redirect' => WSKO_Controller_Dashboard::get_link()
				);
		}
		return array(
				'success' => false,
				'msg' => __('Setup could not be finished', 'wsko')
			);
	}
	
	//Singleton
	static $instance;
}
WSKO_Controller_Setup::init_controller();
?>