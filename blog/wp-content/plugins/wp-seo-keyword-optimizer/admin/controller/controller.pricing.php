<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Pricing extends WSKO_Controller
{
    public $is_real_page = false;
	public $has_main_nav_link = false;
	public $icon = "dollar";
    public $link = "dashboard-pricing";
    
	public function get_title()
	{
		return __('Pricing', 'wsko');
	}

	//Singleton
	static $instance;
}
WSKO_Controller_Pricing::init_controller();
?>