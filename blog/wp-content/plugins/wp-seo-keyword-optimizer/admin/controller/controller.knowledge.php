<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Knowledge extends WSKO_Controller
{
	//Options
	public $icon = "info";
	public $link = "knowledge";
	
	public $has_main_nav_link = false;
	public $is_invisible_page = true;
	
	public $ajax_actions = array('get_knowledge_base_article', 'search_knowledge_base', 'rate_knowledge_base_article');
	
	public $template_folder = "knowledge";
	
	public function get_title()
	{
		return __('Knowledge Base', 'wsko');
	}
	
	public function load_lazy_page_data()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$notif = "";
		$data = array();
		
		$articles = WSKO_Class_Knowledge::search_knowledge_base('kb', '');
		$qa_articles = WSKO_Class_Knowledge::search_knowledge_base('q_and_a', '');
		$data['kb_articles'] = WSKO_Class_Template::render_template('knowledge/view-articles.php', array('articles' => $articles, 'qa_articles' => $qa_articles), true);

		return array(
				'success' => true,
				'data' => $data,
				'notif' => $notif
			);
	}
	
	public function action_get_knowledge_base_article()
	{
		if (!$this->can_execute_action(false))
			return false;
		
			$article_id = isset($_POST['article']) ? intval($_POST['article']) : false;
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : false;
		if ($article_id)
		{
			$info = WSKO_Class_Knowledge::get_knowledge_base_article_info($type, $article_id);
			if ($info)
			{
				$view = WSKO_Class_Template::render_template('knowledge/template-article-modal.php', array('article' => $info, 'type' => $type), true);
				return array(
						'success' => true,
						'view' => $view
					);
			}
		}
	}
	
	public function action_search_knowledge_base()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
		$cats = isset($_POST['cats']) ? array_map('sanitize_text_field', $_POST['cats']) : array();
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$articles = WSKO_Class_Knowledge::search_knowledge_base('kb', $search, $cats);
		$qa_articles = WSKO_Class_Knowledge::search_knowledge_base('q_and_a', $search, $cats);
		$view = WSKO_Class_Template::render_template('knowledge/view-articles.php', array('articles' => $articles, 'qa_articles' => $qa_articles, 'search' => $search), true);
		
		return array(
				'success' => true,
				'view' => $view
			);
	}
	public function action_rate_knowledge_base_article()
	{
		if (!$this->can_execute_action(WSKO_Class_Core::is_demo()))
			return false;
		
		$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
		$post = isset($_POST['post']) ? intval($_POST['post']) : false;
		
		WSKO_Class_Knowledge::rate_article($post, $type);
		
		return array(
				'success' => true
			);
	}
	//Singleton
	static $instance;
}
WSKO_Controller_Knowledge::init_controller();
?>