<?php
if (!defined('ABSPATH')) exit;

class WSKO_Controller_Optimizer extends WSKO_Controller
{
	public $is_real_page = false;
	public $has_main_nav_link = false;
	public $link = "optimizer";
	public $title = "";
	
	public $ajax_actions = array('co_remove_linking_suggestion', 'co_set_link', 'co_save_content', 'co_sort_priority_keyword', 'co_add_priority_keyword', 'co_delete_priority_keyword', 'co_add_similar_priority_keyword', 'co_delete_similar_priority_keyword', 'co_get_keyword_suggests', 'co_table_data', 'co_save_technicals', 'co_change_height');

	public function load_lazy_onpage_issues($args)
	{
		$post_id = isset($args['post_id']) ? $args['post_id'] : false;
		if ($post_id)
		{
			$op_report = WSKO_Class_Onpage::get_onpage_report($post_id, false);
			$crawl_data = WSKO_Class_Onpage::get_onpage_page_crawl_data(get_permalink($post_id));
			$issues_view = WSKO_Class_Template::render_template('content-optimizer/template-co-issues.php', array('op_report' => $op_report), true);
			$score_view = WSKO_Class_Template::render_radial_progress('success', '', array('val' => $op_report ? $op_report['onpage_score'] : 0, 'class' => 'hidden-xs' ), true);
			return array('success' => true, 'data' => array('score' => $score_view, 'onpage_issues' => $issues_view));
		}
	}
	public function load_lazy_keywords($args)
	{
		$post_id = isset($args['post_id']) ? intval($args['post_id']) : false;
		if ($post_id)
		{
			$data = array();
			$null = null;
			$keyword_data = WSKO_Controller_Optimizer::get_instance()->get_keywords($post_id, $null);
			$data['tab_keywords'] = WSKO_Class_Template::render_template('content-optimizer/template-co-keywords.php', array('post_id' => $post_id, 'keyword_data' => $keyword_data), true);
			return array('success' => true, 'data' => $data);
		}
	}
	public function load_lazy_linking($args)
	{
		$post_id = isset($args['post_id']) ? intval($args['post_id']) : false;
		if ($post_id)
		{
			$data = array();
			$data['tab_linking'] = WSKO_Class_Template::render_template('content-optimizer/template-co-linking.php', array('post_id' => $post_id), true);
			return array('success' => true, 'data' => $data);
		}
	}
	public function load_lazy_backlinks($args)
	{
		$post_id = isset($args['post_id']) ? intval($args['post_id']) : false;
		if ($post_id)
		{
			$data = array();
			$data['tab_backlinks'] = WSKO_Class_Template::render_template('content-optimizer/template-co-backlinks.php', array('post_id' => $post_id), true);
			return array('success' => true, 'data' => $data);
		}
	}
	public function load_lazy_performance($args)
	{
		$post_id = isset($args['post_id']) ? intval($args['post_id']) : false;
		if ($post_id)
		{
			$data = array();
			$data['tab_performance'] = WSKO_Class_Template::render_template('content-optimizer/template-co-performance.php', array('post_id' => $post_id), true);
			return array('success' => true, 'data' => $data);
		}
	}
	public function load_lazy_content($args)
	{
		$post_id = isset($args['post_id']) ? intval($args['post_id']) : false;
		if ($post_id)
		{
			$data = array();
			$data['tab_content'] = WSKO_Class_Template::render_template('content-optimizer/template-co-content.php', array('post_id' => $post_id), true);
			return array('success' => true, 'data' => $data);
		}
	}
	public function load_lazy_page_data()
	{
	}
	
	public static function render($post_id, $widget = false, $preview_args = false, $open_tab = false, $frontend_widget = false)
	{
		$inst = static::get_instance();
		$stats = array(
			'kw_count' => 0,
			'kw_count_ref' => 0
		);
		if (WSKO_Class_Core::get_option('search_query_first_run'))
		{
			$keywords = $inst->get_keywords($post_id, $stats);
			$page_data = $inst->get_page_data($post_id);
		}
		else
		{
			$keywords = array();
			$page_data = array();
		}
		$view = WSKO_Class_Template::render_template('content-optimizer/frame-content-optimizer.php', array('post_id' => $post_id, 'widget' => $widget, 'frontend_widget' => $frontend_widget, 'keywords' => $keywords, 'kw_count' => $stats['kw_count'], 'kw_count_ref' => $stats['kw_count_ref'], 'page_data' => $page_data, 'preview' => $preview_args, 'open_tab' => $open_tab ), true);
		return $view;
	}
	
	public function action_co_remove_linking_suggestion()
	{
		if (!$this->can_execute_action(WSKO_Class_Core::is_demo()))
			return false;
		$source = isset($_POST['source']) ? intval($_POST['source']) : false;
		$post_id = isset($_POST['post']) ? intval($_POST['post']) : false;
		$keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : false;
		if ($source && $post_id && $keyword)
		{
			$wsko_data = WSKO_Class_Core::get_data();
			if (!isset($wsko_data['hide_linking']))
				$wsko_data['hide_linking'] = array();
			if (!isset($wsko_data['hide_linking'][$source]))
				$wsko_data['hide_linking'][$source] = array();
			if (!isset($wsko_data['hide_linking'][$source][$keyword]))
				$wsko_data['hide_linking'][$source][$keyword] = array();
			if (!isset($wsko_data['hide_linking'][$source][$keyword][$post_id]))
				$wsko_data['hide_linking'][$source][$keyword][$post_id] = true;
			WSKO_Class_Core::save_data($wsko_data);
			return true;
		}
	}

	public function action_co_set_link()
	{
		if (!$this->can_execute_action(WSKO_Class_Core::is_demo()))
			return false;

		$post_id = isset($_POST['post']) ? intval($_POST['post']) : false;
		$index = isset($_POST['index']) ? intval($_POST['index']) : false;
		$length = isset($_POST['length']) ? intval($_POST['length']) : false;
		$target = isset($_POST['target']) ? intval($_POST['target']) : false;
		if ($post_id && $target && $index !== false && $length)
		{
			$post = get_post($post_id);
			$target = get_permalink($target);
			if ($post && $target)
			{
				$str1 = substr($post->post_content, 0, $index);
				$str2 = substr($post->post_content, $index, $length);
				$str3 = substr($post->post_content, $index+$length);
				$new_content = $str1.'<a href="'.$target.'">'.$str2.'</a>'.$str3;
				wp_update_post(array(
					'ID' => $post->ID,
					'post_content' => $new_content
				));
			}
			return true;
		}
	}
	public function action_co_save_content()
	{
		if (!$this->can_execute_action(WSKO_Class_Core::is_demo()))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		if ($post_id)
		{
			$title = isset($_POST['title']) ? sanitize_post_field('post_title', $_POST['title'], $post_id) : false;
			$slug = isset($_POST['slug']) ? sanitize_title_with_dashes(WSKO_Class_Helper::map_special_chars($_POST['slug'])) : false;
			$content = isset($_POST['content']) ? sanitize_post_field('post_content', $_POST['content'], $post_id) : false;
			
			if ($title && $content && $slug)
			{
				wp_update_post(array(
					'ID'           => $post_id,
					'post_title'   => $title,
					'post_content' => $content,
					'post_name'    => $slug,
				));
				return true;
			}
		}
	}
	
	public function action_co_sort_priority_keyword()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$sort = isset($_POST['sort']) && is_array($_POST['sort']) ? array_map('sanitize_text_field', $_POST['sort']) : false;
		$similar = isset($_POST['similar']) && $_POST['similar'] ? sanitize_text_field($_POST['similar']) : false;
		if ($sort)
		{
			WSKO_Class_Onpage::sort_priority_keywords($post_id, $sort, $similar);
			return true;
		}
	}

	public function action_co_add_priority_keyword()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$keywords = isset($_POST['keyword']) && $_POST['keyword'] ? WSKO_Class_Helper::safe_explode(',', sanitize_text_field($_POST['keyword'])) : false;
		$prio = isset($_POST['prio']) ? intval($_POST['prio']) : false;
		$sort = isset($_POST['sort']) && is_array($_POST['sort']) ? array_map('sanitize_text_field', $_POST['sort']) : false;
		if ($post_id && $keywords)
		{
			$duplicate = false;
			$hit_limit = false;
			$view = "";
			$null = null;
			$keyword_data = WSKO_Controller_Optimizer::get_instance()->get_keywords($post_id, $null);
			foreach ($keywords as $keyword)
			{
				$kws = WSKO_Class_Onpage::get_priority_keywords($post_id);
				$keyword = trim(strtolower($keyword));
				if ($keyword)
				{
					$count_pr1 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 1; })) : 0;
					$count_pr2 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 2; })) : 0;

					if ($count_pr1 > 25)
						$count_pr1 = 25;

					if (!$prio)
						$prio = 1;

					if (WSKO_Class_Core::is_premium())
					{
						if ($count_pr1 >= 3)
						{
							$prio = 2;
							if ($count_pr2 >= (25-$count_pr1))
								$hit_limit = true;
						}
					}
					else
					{
						if ($prio != 1)
							$prio = 1;

						if ($count_pr1 >= 2)
							$hit_limit = true;
					}

					if ($kws && isset($kws[$keyword]) && $kws[$keyword]['prio'] == $prio)
					{
						$duplicate = true;
					}

					if (!$hit_limit && !$duplicate)
					{
						$has_kw = isset($kws[$keyword]);
						
						if ($has_kw)
							WSKO_Class_Onpage::remove_priority_keyword($post_id, $keyword);
						WSKO_Class_Onpage::add_priority_keyword($post_id, $keyword, $prio);
						if ($has_kw)
						{
							foreach ($kws[$keyword]['similar'] as $similar => $f)
							{
								WSKO_Class_Onpage::add_similar_priority_keyword($post_id, $keyword, $similar);
							}
						}
						$kw_data = array('prio' => $prio, 'similar' => $kws[$keyword]['similar']);
						$op_report = WSKO_Class_Onpage::get_onpage_report($post_id);
						$view .= WSKO_Class_Template::render_priority_keyword_item($post_id, $keyword, $kw_data, $keyword_data, $op_report, true);
						
						if ($sort)
							WSKO_Class_Onpage::sort_priority_keywords($post_id, $sort);
					}
				}
			}
			if (!$duplicate && !$hit_limit)
			{
				$kws = WSKO_Class_Onpage::get_priority_keywords($post_id); //refetch
				$count_pr1 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 1; })) : 0;
				$count_pr2 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 2; })) : 0;
				return array('success' => true, 'view' => $view, 'prio' => $prio, 'count_pr1' => $count_pr1, 'count_pr2' => $count_pr2);
			}
			else if ($duplicate)
			{
				return array('success' => false, 'view' => $view, 'prio' => $prio, 'limit' => false, 'msg' => __('You have allready added this keyword.', 'wsko'));
			}
			else if ($hit_limit)
			{
				if (WSKO_Class_Core::is_premium())
					return array('success' => false, 'view' => $view, 'prio' => $prio, 'limit' => true, 'msg' => __('You can only have 25 SEO Keywords per post.', 'wsko'));
				else
					return array('success' => false, 'view' => $view, 'prio' => $prio, 'limit' => true, 'msg' => __('You can only have 2 SEO Keywords per post in the free version.', 'wsko'));
			}
		}
	}
	
	public function action_co_delete_priority_keyword()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$keyword = isset($_POST['keyword']) ? trim(strtolower(sanitize_text_field($_POST['keyword']))) : false;
		if ($post_id && $keyword)
		{
			WSKO_Class_Onpage::remove_priority_keyword($post_id, $keyword);
			$kws = WSKO_Class_Onpage::get_priority_keywords($post_id); //refetch
			$count_pr1 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 1; })) : 0;
			$count_pr2 = $kws ? count(array_filter($kws, function($a){ return $a['prio'] == 2; })) : 0;
			return array('success' => true, 'count_pr1' => $count_pr1, 'count_pr2' => $count_pr2);
		}
	}
	
	public function action_co_add_similar_priority_keyword()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$keyword_key = isset($_POST['keyword_key']) && $_POST['keyword_key'] ? sanitize_text_field($_POST['keyword_key']) : false;
		$keywords = isset($_POST['keyword']) && $_POST['keyword'] ? WSKO_Class_Helper::safe_explode(',', sanitize_text_field($_POST['keyword'])) : false;
		if ($post_id && $keyword_key && $keywords)
		{
			$duplicate = false;
			$hit_limit = false;
			$view = "";
			$kws = WSKO_Class_Onpage::get_priority_keywords($post_id);	
			if (isset($kws[$keyword_key]))
			{
				$keyword_data = WSKO_Controller_Optimizer::get_instance()->get_keywords($post_id);
				$kw_data = $kws[$keyword_key];
				$i = 0;
				foreach ($keywords as $keyword)
				{
					$keyword = trim(strtolower($keyword));
					if ($keyword && $keyword != $keyword_key)
					{
						if (count($kws[$keyword_key]['similar'])+$i <= 5)
						{
							if (isset($kws[$keyword_key]['similar'][$keyword]))
								$duplicate = true;
							else
							{
								$i++;
								$kw_data['similar'][$keyword] = true; //mirror for template
								WSKO_Class_Onpage::add_similar_priority_keyword($post_id, $keyword_key, $keyword);
							}
						}
						else
							$hit_limit = true;
					}
				}

				$op_report = WSKO_Class_Onpage::get_onpage_report($post_id);
				$view .= WSKO_Class_Template::render_priority_keyword_item($post_id, $keyword_key, $kw_data, $keyword_data, $op_report, true);
				if (!$duplicate && !$hit_limit)
				{
					return array('success' => true, 'view' => $view);
				}
				else if ($duplicate)
				{
					return array('success' => false, 'view' => $view, 'msg' => __('You have allready added this keyword.', 'wsko'));
				}
				else if ($hit_limit)
				{
					return array('success' => false, 'view' => $view, 'msg' => __('You can only have 5 sub-keywords.', 'wsko'));
				}
			}
		}
	}

	public function action_co_delete_similar_priority_keyword()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$keyword = isset($_POST['keyword']) ? trim(strtolower(sanitize_text_field($_POST['keyword']))) : false;
		$keyword_key = isset($_POST['keyword_key']) ? trim(strtolower(sanitize_text_field($_POST['keyword_key']))) : false;
		if ($post_id && $keyword)
		{
			WSKO_Class_Onpage::remove_similar_priority_keyword($post_id, $keyword_key, $keyword);
			return true;
		}
	}

	public function action_co_get_keyword_suggests()
	{
		if (!$this->can_execute_action(false))
			return false;
		
		$keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : false;
		if ($keyword)
		{
			$keyword = utf8_encode(urlencode(trim($keyword)));

			$loc = WSKO_Class_Core::get_plugin_language(false, true);
			$loc = "&hl=" . $loc;
			
			if (ini_get('allow_url_fopen'))
			{
				$file = WSKO_Class_Helper::get_from_url("http://google.de/complete/search?output=firefox" . $loc . "&q=".$keyword);
				$suggestions = json_decode($file); 
				if ($suggestions && is_array($suggestions))
				{
					ob_start();
					foreach($suggestions[1] as $value)
					{
						echo '<li><a href="#" class="wsko-co-keyword-suggestion dark" data-val="'.$value.'">'.$value.'</a> <span class="wsko-badge badge-default"><a href="#" class="wsko-keyword-suggestion-add dark" data-keyword="'.$value.'" data-prio=""><i class="fa fa-plus fa-fw"></i></a></span></li>';
					}
					$c = ob_get_clean();
					return array('success' => true, 'view' => $c);
				}
			}
		}
		else
		{
			return array('success' => true, 'view' => "");
		}
	}
	
	public function action_co_table_data()
	{
		if (!$this->can_execute_action(false))
			return false;
		
	}
	
	public function action_co_save_technicals()
	{
		if (!$this->can_execute_action(WSKO_Class_Core::is_demo()))
			return false;
		
		$post = isset($_POST['post_id']) ? intval($_POST['post_id']) : false;
		$data_t = isset($_POST['data']) ? $_POST['data'] : false;
		if ($post)
		{
			if (isset($data_t['activate_redirect']) && $data_t['activate_redirect'] && $data_t['activate_redirect'] !== 'false' && isset($data_t['redirect_type']) && in_array($data_t['redirect_type'], array('1','2')) && isset($data_t['redirect_to']))
				WSKO_Class_Onpage::add_page_redirect($post, sanitize_text_field($data_t['redirect_to']), $data_t['redirect_type']);
			else
				WSKO_Class_Onpage::remove_page_redirects($post);
			return true;
		}
	}
	
	public function action_co_change_height()
	{
		if (!$this->can_execute_action(false))
			return false;
		$height = isset($_POST['height']) ? intval($_POST['height']) : false;
		if ($height)
		{
			WSKO_Class_Core::save_option('co_box_height_'.get_current_user_id(), $height);
			return true;
		}
	}
	
	public function get_page_data($post_id)
	{
		$end = time();
		$co_time = intval(WSKO_Class_Core::get_setting('content_optimizer_time'));
		$start = $end - (60*60*24*($co_time?$co_time:28));
		if ($post_id && $start && $end)
		{
			if (WSKO_Class_Core::get_option('search_query_first_run'))
			{
				$search_connected = WSKO_Class_Search::check_se_connected();
				if ($search_connected)
				{
					$page_obj = WSKO_Class_Cache::get_session_cache('se_post_data_sum_'.$post_id);
					if (is_object($page_obj))
						return $page_obj;
					else
					{
						$obj = false;
						$url = defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH ? WSKO_HOST_BASE_PATH : get_permalink($post_id);
						$page_data = WSKO_Class_Search::get_se_data_for_url($start, $end, 'page', $url);
						if ($page_data && isset($page_data[$url]))
						{
							$obj = $page_data[$url];
							$obj->clicks_ref = null;
							$obj->impressions_ref = null;
							$obj->position_ref = null;
							$obj->ctr_ref = null;
							$page_data_ref = WSKO_Class_Search::get_se_data_for_url($start-($end-$start), $start, 'page', $url);
							if ($page_data_ref && isset($page_data_ref[$url]))
							{
								$obj->clicks_ref = $page_data_ref[$url]->clicks;
								$obj->impressions_ref = $page_data_ref[$url]->impressions;
								$obj->position_ref = $page_data_ref[$url]->position;
								$obj->ctr_ref = $page_data_ref[$url]->ctr;
							}
						}
						WSKO_Class_Cache::set_session_cache('se_post_data_sum_'.$post_id, $obj, 60*60*24);
						return $obj;
					}
				}
			}
		}
		return false;
	}
	public function get_keywords($post_id, &$stats_out = false)
	{
		$stats_out = array('kw_count' => 0, 'kw_count_ref' => 0);
		$end = time();
		$co_time = intval(WSKO_Class_Core::get_setting('content_optimizer_time'));
		$start = $end - (60*60*24*($co_time?$co_time:28));
		if ($post_id && $start && $end)
		{
			if (WSKO_Class_Core::get_option('search_query_first_run'))
			{
				$search_connected = WSKO_Class_Search::check_se_connected();
				if ($search_connected)
				{
					$keywords_data = false;
					$keywords = WSKO_Class_Cache::get_session_cache('se_post_data_'.$post_id);
					if (is_array($keywords))
					{
						$keywords = WSKO_Class_Cache::get_session_cache('se_post_data_'.$post_id);
						$stats_out = WSKO_Class_Cache::get_session_cache('se_post_data_stats_'.$post_id);
						return $keywords;
					}
					else
					{
						$keywords_data = WSKO_Class_Search::get_se_data_for_url($start, $end, 'query', defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH ? WSKO_HOST_BASE_PATH : get_permalink($post_id));
						if ($keywords_data && $keywords_data !== -1)
						{
							foreach ($keywords_data as $k => $data)
							{
								$stats_out['kw_count']++;
								$keywords_data[$k]->clicks_ref = null;
								$keywords_data[$k]->position_ref = null;
								$keywords_data[$k]->impressions_ref = null;
								$keywords_data[$k]->ctr_ref = null;
							}
							$keywords_data_ref = WSKO_Class_Search::get_se_data_for_url($start-($end-$start), $start, 'query', defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH ? WSKO_HOST_BASE_PATH : get_permalink($post_id));
							if ($keywords_data_ref && $keywords_data_ref !== -1)
							{
								foreach ($keywords_data_ref as $data_ref)
								{
									$stats_out['kw_count_ref']++;
									if (isset($keywords_data[$data_ref->keyval]))
									{
										$keywords_data[$data_ref->keyval]->clicks_ref = $data_ref->clicks;
										$keywords_data[$data_ref->keyval]->position_ref = $data_ref->position;
										$keywords_data[$data_ref->keyval]->impressions_ref = $data_ref->impressions;
										$keywords_data[$data_ref->keyval]->ctr_ref = $data_ref->clicks && $data_ref->impressions ? round($data_ref->clicks/$data_ref->impressions, 2) : 0;
									}
								}
							}
						}
						else
							$keywords_data = false;
						WSKO_Class_Cache::set_session_cache('se_post_data_'.$post_id, $keywords_data, 60*60*24);
						WSKO_Class_Cache::set_session_cache('se_post_data_stats_'.$post_id, $stats_out, 60*60*24);
						return $keywords_data;
					}
				}
			}
		}
		return false;
		//return $this->get_cached_var('keywords', false, array('post_id' => $post_id, 'start' => $start, 'end' => $end));
	}
	//Singleton
	static $instance;
}
WSKO_Controller_Optimizer::init_controller();
?>