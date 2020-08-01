<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Knowledge
{
	private static $kb_endpoint = 'https://www.bavoko.tools/ext/v1/kb-api.php';
	private static $post_type_kb = 'wsko_c_kb';
	private static $post_type_qanda = 'wsko_c_qanda';

	public static function add_post_types()
	{
		register_post_type(static::$post_type_kb,
			array(
			'labels' => array( //just for completeness
				'name' => 'WSKO Knowledge Base',
				'singular_name' => 'WSKO Knowledge Base',
				/*'add_new' => 'Add a New WSKO Knowledge Base',
				'add_new_item' => 'Add a New WSKO Knowledge Base',
				'edit_item' => 'Edit WSKO Knowledge Base',
				'new_item' => 'New WSKO Knowledge Base',
				'view_item' => 'View WSKO Knowledge Base',
				'search_items' => 'Search WSKO Knowledge Bases',
				'not_found' => 'Nothing Found',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''*/
			),
			'description' => 'WSKO Knowledge Bases',
			'public' => false,
			'query_var' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'has_archive' => false,
			'rewrite' => false, //array('slug' => 'p'),
			
			/*'query_var'          => true,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author')*/
		));
		
		register_post_type(static::$post_type_qanda,
			array(
			'labels' => array( //just for completeness
				'name' => 'WSKO Question and Answer',
				'singular_name' => 'WSKO Question and Answer',
				/*'add_new' => 'Add a New WSKO Question and Answer',
				'add_new_item' => 'Add a New WSKO Question and Answer',
				'edit_item' => 'Edit WSKO Question and Answer',
				'new_item' => 'New WSKO Question and Answer',
				'view_item' => 'View WSKO Question and Answer',
				'search_items' => 'Search WSKO Question and Answers',
				'not_found' => 'Nothing Found',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''*/
			),
			'description' => 'WSKO Question and Answers',
			'public' => false,
			'query_var' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'has_archive' => false,
			'rewrite' => false, //array('slug' => 'p'),
			
			/*'query_var'          => true,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author')*/
		));
	}

	public static function rate_article($article, $type)
	{
		if ($article)
		{
			$article = WSKO_Class_Knowledge::get_knowledge_base_article($article);
			if ($article)
			{
				$data = WSKO_Class_Helper::post_to_url(static::$kb_endpoint, array('action' => 'rate_article', 'post' => $article->real_id, 'type' => $type), array("charset=utf-8"));
				if ($data)
				{
					$data_ref = (array)WSKO_Class_Helper::safe_json_decode($data);
					if ($data_ref && is_array($data_ref) && isset($data_ref['success']) && $data_ref['success'])
					{
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public static function search_knowledge_base($type, $search, $categories = array(), $tags = array())
	{
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'will-not-be-found',
			'post_status'      => 'any',
			'suppress_filters' => true,
			'fields' => 'ids'
		);
		switch ($type)
		{
			case 'kb': $args['post_type'] = static::$post_type_kb; WSKO_Class_Knowledge::get_knowledge_base_articles(); break;
			case 'q_and_a': $args['post_type'] = static::$post_type_qanda; WSKO_Class_Knowledge::get_q_and_a_articles(); break;
		}
		if ($search)
		{
			$args['s'] = $search;
		}
		if ($categories)
		{
			$args['meta_query'] = array(
				array(
					'key'     => 'wsko_c_cats',
					'value'   => $categories,
					'compare' => 'IN',
				),
			);
		}
		if ($tags)
		{
			if (isset($args['meta_query']))
			{
				$args['meta_query']['relation'] = 'OR';
				$args['meta_query'][] = array(
					array(
						'key'     => 'wsko_c_tags',
						'value'   => $tags,
						'compare' => 'IN',
					),
				);
			}
			else
			{
				$args['meta_query'] = array(
					array(
						'key'     => 'wsko_c_tags',
						'value'   => $tags,
						'compare' => 'IN',
					),
				);
			}
		}
		$query = new WP_Query($args);
		$articles = $query->posts;
		if ($articles)
		{
			foreach ($articles as $k => $a)
			{
				switch ($type)
				{
					case 'kb': $articles[$k] = WSKO_Class_Knowledge::get_knowledge_base_article($a); break;
					case 'q_and_a': $articles[$k] = WSKO_Class_Knowledge::get_q_and_a_article($a); break;
					default: unset($articles[$k]); break;
				}
			}
		}
		return $articles;
	}
	
	public static function clear_knowledge_base()
	{
		if (!static::$post_type_kb || !WSKO_Class_Helper::starts_with(static::$post_type_kb, 'wsko_')) //security
			return;
			
		$args = array('post_type' => static::$post_type_kb, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids');
		$msgs = get_posts($args);
		if ($msgs)
		{
			foreach ($msgs as $p)
			{
				wp_delete_post($p, true);
			}
		}
	}

	public static function get_knowledge_base_articles()
	{
		$wsko_data = WSKO_Class_Core::get_data();
		$lang = WSKO_Class_Core::get_plugin_language();
		if (WSKO_Class_Core::get_option('knowledge_base_lang') != $lang || WSKO_Class_Core::get_option('last_knowledge_base_update') < (WSKO_Class_Helper::get_current_time()-(60*60*24)))// || !isset($wsko_data['knowledge_base_categories']) || !$wsko_data['knowledge_base_categories'])
		{
			$data = WSKO_Class_Helper::post_to_url(static::$kb_endpoint, array('action' => 'list_kb_articles', 'lang' => $lang), array("charset=utf-8"));
			if ($data)
			{
				WSKO_Class_Knowledge::clear_knowledge_base();
				$data_ref = (array)WSKO_Class_Helper::safe_json_decode($data);
				if ($data_ref && is_array($data_ref) && isset($data_ref['success']) && $data_ref['success'] && isset($data_ref['articles']) && $data_ref['articles'] && isset($data_ref['categories']) && $data_ref['categories'])
				{
					foreach ($data_ref['articles'] as $d)
					{
						$d->title = htmlspecialchars_decode($d->title);
						$d->content = htmlspecialchars_decode($d->content);
						$d->preview = htmlspecialchars_decode($d->preview);
						$post_id = wp_insert_post(array(
							'post_type' => static::$post_type_kb,
							'post_status' => 'private',
							'post_title' => $d->title,
							'post_content' => $d->content,
						));
						if ($post_id)
						{
							add_post_meta($post_id, 'wsko_c_id', $d->id);
							add_post_meta($post_id, 'wsko_c_preview', $d->preview);
							add_post_meta($post_id, 'wsko_c_link', $d->link);
							add_post_meta($post_id, 'wsko_c_img', $d->img);
							foreach ($d->tags as $tag)
							{
								add_post_meta($post_id, 'wsko_c_tags', $tag);
							}
							foreach ($d->categories as $cat)
							{
								add_post_meta($post_id, 'wsko_c_cats', $cat);
							}
						}
					}
					$wsko_data['knowledge_base_categories'] = $data_ref['categories'];
					WSKO_Class_Core::save_data($wsko_data);
					WSKO_Class_Core::save_option('last_knowledge_base_update', WSKO_Class_Helper::get_current_time());
					WSKO_Class_Core::save_option('knowledge_base_lang', $lang);
				}
			}
		}
	}

	public static function get_knowledge_base_article($id)
	{
		//WSKO_Class_Knowledge::get_knowledge_base_articles();
		$post = get_post($id);
		if ($post)
		{
			$obj = new stdClass;
			$obj->id = $post->ID;
			$obj->title = $post->post_title;
			$obj->content = $post->post_content;
			$obj->real_id = get_post_meta($post->ID, 'wsko_c_id', true);
			$obj->preview = get_post_meta($post->ID, 'wsko_c_preview', true);
			$obj->tags = get_post_meta($post->ID, 'wsko_c_tags', false);
			$obj->categories = get_post_meta($post->ID, 'wsko_c_cats', false);
			$obj->link = get_post_meta($post->ID, 'wsko_c_link', true);
			$obj->img = get_post_meta($post->ID, 'wsko_c_img', true);
			return $obj;
		}
		return false;
	}
	
	public static function get_knowledge_base_categories()
	{
		$wsko_data = WSKO_Class_Core::get_data();
		if (isset($wsko_data['knowledge_base_categories']) && $wsko_data['knowledge_base_categories'])
		{
			return $wsko_data['knowledge_base_categories'];
		}
		return false;
	}
	
	public static function get_knowledge_base_article_info($type, $id)
	{
		switch ($type)
		{
			case 'kb': return WSKO_Class_Knowledge::get_knowledge_base_article($id);
			case 'q_and_a': return WSKO_Class_Knowledge::get_q_and_a_article($id);
		}
		return false;
	}
	
	public static function clear_question_and_answer()
	{
		if (!static::$post_type_qanda || !WSKO_Class_Helper::starts_with(static::$post_type_qanda, 'wsko_')) //security
			return;
			
		$args = array('post_type' => static::$post_type_qanda, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids');
		$msgs = get_posts($args);
		if ($msgs)
		{
			foreach ($msgs as $p)
			{
				wp_delete_post($p, true);
			}
		}
	}

	public static function get_q_and_a_articles()
	{
		$wsko_data = WSKO_Class_Core::get_data();
		$lang = WSKO_Class_Core::get_plugin_language();
		if (WSKO_Class_Core::get_option('q_and_a_lang') != $lang || WSKO_Class_Core::get_option('last_q_and_a_update') < (WSKO_Class_Helper::get_current_time()-(60*60*24)))// || !isset($wsko_data['q_and_a_categories']) || !$wsko_data['q_and_a_categories'])
		{
			$data = WSKO_Class_Helper::post_to_url(static::$kb_endpoint, array('action' => 'list_q_and_a_articles', 'lang' => $lang), array("charset=utf-8"));
			if ($data)
			{
				WSKO_Class_Knowledge::clear_question_and_answer();
				$data_ref = (array)WSKO_Class_Helper::safe_json_decode($data);
				if ($data_ref && is_array($data_ref) && isset($data_ref['success']) && $data_ref['success'] && isset($data_ref['articles']) && $data_ref['articles'] && isset($data_ref['categories']) && $data_ref['categories'])
				{
					$res = array();
					foreach ($data_ref['articles'] as $d)
					{
						$d->title = htmlspecialchars_decode($d->title);
						$d->content = htmlspecialchars_decode($d->content);
						$d->preview = htmlspecialchars_decode($d->preview);
						$post_id = wp_insert_post(array(
							'post_type' => static::$post_type_qanda,
							'post_status' => 'private',
							'post_title' => $d->title,
							'post_content' => $d->content,
						));
						if ($post_id)
						{
							add_post_meta($post_id, 'wsko_c_id', $d->id);
							add_post_meta($post_id, 'wsko_c_preview', $d->preview);
							add_post_meta($post_id, 'wsko_c_link', $d->link);
							add_post_meta($post_id, 'wsko_c_img', $d->img);
							foreach ($d->tags as $tag)
							{
								add_post_meta($post_id, 'wsko_c_tags', $tag);
							}
							foreach ($d->categories as $cat)
							{
								add_post_meta($post_id, 'wsko_c_cats', $cat);
							}
						}
					}
					$wsko_data['q_and_a_categories'] = $data_ref['categories'];
					WSKO_Class_Core::save_data($wsko_data);
					WSKO_Class_Core::save_option('last_q_and_a_update', WSKO_Class_Helper::get_current_time());
					WSKO_Class_Core::save_option('q_and_a_lang', $lang);
				}
			}
		}
	}
	
	public static function get_q_and_a_categories()
	{
		$wsko_data = WSKO_Class_Core::get_data();
		if (isset($wsko_data['q_and_a_categories']) && $wsko_data['q_and_a_categories'])
		{
			return $wsko_data['q_and_a_categories'];
		}
		return false;
	}

	public static function get_q_and_a_article($id)
	{
		//WSKO_Class_Knowledge::get_q_and_a_articles();
		$post = get_post($id);
		if ($post)
		{
			$obj = new stdClass;
			$obj->id = $post->ID;
			$obj->title = $post->post_title;
			$obj->content = $post->post_content;
			$obj->real_id = get_post_meta($post->ID, 'wsko_c_id', true);
			$obj->preview = get_post_meta($post->ID, 'wsko_c_preview', true);
			$obj->tags = get_post_meta($post->ID, 'wsko_c_tags', false);
			$obj->categories = get_post_meta($post->ID, 'wsko_c_cats', false);
			$obj->link = get_post_meta($post->ID, 'wsko_c_link', true);
			$obj->img = get_post_meta($post->ID, 'wsko_c_img', true);
			return $obj;
		}
		return false;
	}
}
?>