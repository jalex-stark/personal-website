<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Info extends WSKO_Controller
{
	public $is_invisible_page = true;
	public $has_main_nav_link = false;
	public $has_main_frame = false;
    public $link = "updated";

	public $styles = array('info');
    
	public $template_folder = "info";
    
	public function get_title()
	{
		return __('WSKO = BST', 'wsko');
	}
	//Singleton
	static $instance;
}
WSKO_Controller_Info::init_controller();
?>