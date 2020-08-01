<?php
if (!defined('ABSPATH')) exit;
class WSKO_Controller_Account extends WSKO_Controller
{
    public $is_real_page = false;
	public $has_main_nav_link = false;
	public $icon = "user";
    public $link = "dashboard-account";
    
	public function get_title()
	{
		return __('Account', 'wsko');
	}

	public function redirect()
	{
		if (!WSKO_Class_Core::has_account())
		{
			//$this::call_redirect_raw(WSKO_Controller_Dashboard::get_link());
		}
	}

	//Singleton
	static $instance;
}
WSKO_Controller_Account::init_controller(WSKO_Class_Core::has_account()); 
?>