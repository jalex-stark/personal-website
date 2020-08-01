<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Iframe extends WSKO_Controller
{
	public $is_invisible_page = true;
	public $has_main_nav_link = false;
	public $link = "iframe";
	
	public $template_folder = "iframe";
	
	public function get_title()
	{
		return __('Iframe', 'wsko');
	}
	
	public function custom_view()
	{
        if (WSKO_Class_Helper::check_user_permissions(false))
        {
            $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : false;
            $arg = isset($_GET['arg']) ? sanitize_text_field($_GET['arg']) : false;
            switch ($type)
            {
                case 'co':
                $arg = intval($arg);
                if ($arg)
                {
                    $preview = isset($_POST['preview']) && $_POST['preview'] && $_POST['preview'] != 'false' ? true : false;
                    $open_tab = isset($_POST['open_tab']) ? sanitize_text_field($_POST['open_tab']) : false;
                    $preview_data = false;
                    if ($preview)
                    {
                        $preview_data = array(
                            'post_title' => isset($_POST['post_title']) ? sanitize_post_field('post_title', $_POST['post_title'], $arg) : '',
                            'post_content' => isset($_POST['post_content']) ? sanitize_post_field('post_content', $_POST['post_content'], $arg) : '',
                            'post_slug' => isset($_POST['post_slug']) ? sanitize_title_with_dashes($_POST['post_slug']) : ''
                        );
                    }
                    ob_start();
                    ?><script type="text/javascript">document.documentElement.classList.add('wsko-co-iframe')</script>
					<style>#adminmenumain{display:none;!important}html,body,#wpcontent{margin:0px!important;padding:0px!important;}html,body,#wpwrap{background-color:white!important;}</style><?php
                    $widget = isset($_GET['widget']) && $_GET['widget'] && $_GET['widget'] != 'false' ? true: false;
                    return ob_get_clean().WSKO_Controller_Optimizer::render($arg, $widget, $preview_data, $open_tab, true);
                }
                break;
                case 'tax_meta':
                $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : false;
                $term_id = intval($arg);
                if ($term_id && $taxonomy)
                {
                    ob_start();
                    ?><script type="text/javascript">document.documentElement.classList.add('wsko-co-iframe')</script>
					<style>#adminmenumain{display:none;!important}html,body,#wpcontent{margin:0px!important;padding:0px!important;}html,body,#wpwrap{background-color:#f1f1f1!important;}</style><?php
                    return ob_get_clean().WSKO_Class_Template::render_template('tools/template-metas-view-tax.php', array('term_key' => $taxonomy.':'.$term_id), true);
                }
                break;
            }
            WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'iframe_incomplete')));
        }
        else
        {
            WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'no_permission')));
        }
        return false;
	}
	
	public static function load_lazy_page_data()
	{
		return true;
    }
    
    public static function get_iframe($type, $arg = false, $params = array())
    {
        $width = isset($params['width']) ? $params['width'] : 500;
        $height = isset($params['height']) ? $params['height'] : 300;
        $url_data = isset($params['url_data']) ? $params['url_data'] : array();
        $src = WSKO_Controller_Iframe::get_link().'&'.http_build_query(array('type' => $type, 'arg' => $arg) + (is_array($url_data)?$url_data:array()));
        $form_frame = isset($params['form_frame']) ? $params['form_frame'] : array();
        $form_frame_split = isset($params['form_frame_split']) ? $params['form_frame_split'] : false;
        if ($form_frame)
        {
            $uniq_frame = WSKO_Class_Helper::get_unique_id('wsko_unique_iframe');
            $fields = '';
            foreach($form_frame as $k => $v)
            {
                $fields .= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
            }
            if ($form_frame_split)
            {
                return array('form' => '<form action="'.$src.'" method="post" target="'.$uniq_frame.'">'.$fields.'</form>', 'iframe' => '<iframe id="'.$uniq_frame.'" name="'.$uniq_frame.'" src="'.$src.'" width="'.$width.'" height="'.$height.'"></iframe>');
            }
            else
            {
                return '<form action="'.$src.'" method="post" target="'.$uniq_frame.'">'.$fields.'</form><iframe id="'.$uniq_frame.'" width="'.$width.'" height="'.$height.'"></iframe>';
            }
        }
        else
        {
            return '<iframe src="'.$src.'" width="'.$width.'" height="'.$height.'"></iframe>';
        }
    }

    public function render_head_scripts()
    {
        //inherit empty header
    }
	
	//Singleton
	static $instance;
}
WSKO_Controller_Iframe::init_controller();
?>