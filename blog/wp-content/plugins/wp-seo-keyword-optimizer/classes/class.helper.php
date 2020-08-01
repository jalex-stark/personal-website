<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Helper
{
	static $is_wsko_page = null;
	static $is_wsko_ajax = null;
	static $is_wsko_cron = null;
	static $curl_agent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36 OPR/54.0.2952.71";
	public static function get_asset_url($path)
	{
		$path = realpath($path);
		if ($path && WSKO_Class_Helper::starts_with($path, realpath(WP_CONTENT_DIR))) // security
		{
			$rel_path = substr($path, strlen(get_home_path()));
			return WSKO_Class_Helper::get_host_base(false, true).str_replace("\\","/",$rel_path);
		}
		return false;
	}
	public static function get_asset_path($url, $validate = true)
	{
		$url_data = parse_url($url);
		if (isset($url_data['host']) && $url_data['host'] && ($url_data['host'].(isset($url_data['port'])?':'.$url_data['port']:'')) == WSKO_Class_Helper::get_host(true))
		{
			$path = realpath(get_home_path().$url_data['path']);
			if ($path && WSKO_Class_Helper::starts_with($path, realpath(WP_CONTENT_DIR))) // security
			{
				if (!$validate || file_exists($path))
					return $path;
			}
		}
		return false;
	}
	public static function prepare_heavy_operation($key, $callback = false, $db_operation = true, $override_timeout = false)
	{
		$timeout = $override_timeout ? $override_timeout : WSKO_LRS_TIMEOUT;
		ignore_user_abort(true);
		WSKO_Class_Helper::safe_set_time_limit($timeout);
		if ($db_operation)
			WSKO_Class_Cache::check_database();
		if ($key)
		{
			global $wsko_shutdown_keys;
			if (!is_array($wsko_shutdown_keys))
				$wsko_shutdown_keys = array();
			
			if (!isset($wsko_shutdown_keys[$key]))
			{
				$wsko_shutdown_keys[$key] = array('s' => time(), 'c' => $callback);
				register_shutdown_function(function($params) {
					$error = error_get_last();
					global $wsko_shutdown_keys;
					if ($error && isset($error['type']) && in_array($error['type'], array(E_ERROR, E_USER_ERROR)) && isset($params['key']) && isset($wsko_shutdown_keys[$params['key']]))
					{
						$r_timeout = time() - $wsko_shutdown_keys[$params['key']]['s'];
						$error = wp_slash('"'.(isset($error['message']) ? $error['message'] : '-').'" in "'.(isset($error['file']) ? $error['file'] : '-').'" on line '.(isset($error['line']) ? $error['line'] : '-'));
						WSKO_Class_Helper::report_error('error', 'E1: '.__('CronJob Timeout', 'wsko'), 'The cronjob function "'.$params['key'].'" was not completed due to an uncought error after '.date('H:i:s', $r_timeout).' ('.$error.')');
						if ($wsko_shutdown_keys[$params['key']]['c'] && is_callable($wsko_shutdown_keys[$params['key']]['c']))
							$wsko_shutdown_keys[$params['key']]['c']();
					}
				}, array('key' => $key));
			}
		}
	}
	public static function finish_heavy_operation($key)
	{
		global $wsko_shutdown_keys;
		if (isset($wsko_shutdown_keys[$key]))
		{
			unset($wsko_shutdown_keys[$key]);
		}
	}

	public static function safe_set_time_limit($limit)
	{
		if (WSKO_Class_Helper::is_php_func_enabled('set_time_limit'))
			set_time_limit($limit);
	}
	public static function get_ref_value($curr, $old)
	{
		if (!$old && $curr)
			return 100;
		if (!$curr && $old)
			return -100;
		return $old && $curr != $old ? round(($curr - $old) / $old * 100, 2) : 0;
	}
	public static function get_relative_url($url)
	{
		$url_res = str_replace(array(rtrim('/', home_url()), WSKO_Class_Helper::get_host_base(false), WSKO_Class_Helper::get_host_base(false, true), defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH ? WSKO_HOST_BASE_PATH : ''), array('','','',''), $url);
		if (!$url_res && $url)
			return '/';
		return $url_res;
	}
	public static function get_asset_icon($asset)
	{
		switch ($asset)
		{
			case 'js': return 'fa-code fa-fw mr5';
			case 'css': return 'fa-css3 fa-fw mr5';
			case 'img': return 'fa-image fa-fw mr5';
		}
		return 'fa-times';
	}
	public static function get_path_source($path, $type)
	{
		switch ($type)
		{
			case 'asset': 
				if (WSKO_Class_Helper::starts_with($path, includes_url()))
					return 'wp';
				if (WSKO_Class_Helper::starts_with($path, get_theme_root_uri()))
					return 'theme';
				if (WSKO_Class_Helper::starts_with($path, plugins_url()))
					return 'plugin';
			return 'other';
			case 'asset_plugin':
			$plugin_url = plugins_url();
			if (substr($path, 0, strlen($plugin_url)) == $plugin_url) {
				$path = WSKO_Class_Helper::safe_explode('/', substr($path, strlen($plugin_url)));
				if ($path && is_array($path) && count($path) >= 2)
				{
					return $path[0].'/'.$path[1];
				}
			} 
			break;
		}
		return false;
	}
	public static function get_red_green_transition($perc, $a = 1.0)
	{
		$min = 60;
		$max = 240;

		if ($perc > 0.5)
		{
			$r = ((1-$perc)*2)*($max-$min);
			$g = $max;	
		}
		else
		{
			$g = ($perc*2)*($max-$min);
			$r = $max;
		}

		$b = $min;
		return 'rgba('.round($r, 0).','.round($g, 0).','.round($b, 0).','.$a.')';
	}
	public static function get_temp_dir($dir)
	{
		if (is_writable(WP_CONTENT_DIR) && is_readable(WP_CONTENT_DIR))
		{
			$temp_path = realpath(WP_CONTENT_DIR).'/bst/';
			if (!file_exists($temp_path) || !is_dir($temp_path))
			{
				mkdir($temp_path, 0755, true);
			}
			if (!WSKO_Class_Helper::starts_with($temp_path, realpath(WP_CONTENT_DIR))) // security
				return false;
			if (!file_exists($temp_path.'.htaccess'))
			{
				file_put_contents($temp_path.'.htaccess', 'deny from all');
			}
			if ($dir)
			{
				$dir = basename($dir);
				if ($dir == '..' || $dir == '.')
					return false;
				$temp_path = $temp_path.$dir.'/';
				if (!file_exists($temp_path) || !is_dir($temp_path))
				{
					mkdir($temp_path, 0755, true);
				}
			}
			if (!WSKO_Class_Helper::starts_with($temp_path, realpath(WP_CONTENT_DIR))) // security
				return false;
			return $temp_path;
		}
		return false;
	}
	public static function is_wsko_cron()
	{
		if (static::$is_wsko_cron !== null)
			return static::$is_wsko_cron;
		if (defined('DOING_CRON') && DOING_CRON)
		{
			return static::$is_wsko_cron = true;
		}
		return static::$is_wsko_cron = false;
	}
	public static function is_wsko_ajax()
	{
		if (static::$is_wsko_ajax !== null)
			return static::$is_wsko_ajax;
		if (is_admin())
		{
			if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && WSKO_Class_Helper::starts_with($_REQUEST['action'], 'wsko_'))
			{
				return static::$is_wsko_ajax = true;
			}
		}
		return static::$is_wsko_ajax = false;
	}
	public static function is_wsko_page()
	{
		if (static::$is_wsko_page !== null)
			return static::$is_wsko_page;
		if (is_admin())
		{
			global $pagenow;
			if ($pagenow === 'admin.php' && isset($_GET['page']) && WSKO_Class_Helper::starts_with($_GET['page'], 'wsko_'))
				return static::$is_wsko_page = true;
			//else if ($pagenow === 'edit.php' || $pagenow === 'post.php')
				//return static::$is_wsko_page = true;
		}
		return static::$is_wsko_page = false;
	}
	public static function is_dev()
	{
		return defined('WSKO_DEV_SWITCH') && WSKO_DEV_SWITCH ? true : false;
	}
	public static function format_number($number, $precision = 0)
	{
		return number_format($number, $precision, ",", ".");
	}
	public static function format_time($time)
	{
		if ($time > 60)
		{
			$time = $time/60;
			if ($time > 60)
			{
				$time = $time/60;
				return WSKO_Class_Helper::format_number($time, 2).' h';
			}
			return WSKO_Class_Helper::format_number($time, 2).' min';
		}
		return WSKO_Class_Helper::format_number($time, 2).' s';
	}
	public static function format_byte($byte)
	{
		if ($byte > 1000)
		{
			$byte = $byte/1000;
			if ($byte > 1000)
			{
				$byte = $byte/1000;
				return WSKO_Class_Helper::format_number($byte, 2).' mb';
			}
			return WSKO_Class_Helper::format_number($byte, 2).' kb';
		}
		return WSKO_Class_Helper::format_number($byte, 2).' b';
	}
	public static function map_special_chars($str)
	{
		if ($str && is_scalar($str))
		{
			return str_replace(array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´"),
							   array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", ""), $str);
		}
		return "";
	}
	public static function is_term_url($url, $tax)
	{
		$cats = get_terms(array('taxonomy' => $tax, 'hide_empty' => false));
		foreach ($cats as $ct)
		{
			if ($url === WSKO_Class_Helper::get_term_url($ct, $tax))
			{
				return $ct;
			}
		}
		return false;
	}
	public static function get_term_url($term_obj, $tax)
	{
		//if (is_scalar($term_obj) && $term_obj)
			//$term_obj = get_term_by('slug', $term_obj, $tax, OBJECT);
		if ($term_obj && is_object($term_obj))
		{
			return ($term_obj->parent?WSKO_Class_Helper::get_term_url(get_term($term_obj->parent, $tax), $tax).'/':'').$term_obj->slug;
		}
		return false;
	}
	public static function merge_array_deep($arr1, $arr2)
	{
		if (!is_array($arr1) || !is_array($arr2))
			return $arr1;

		foreach ($arr2 as $k => $v)
		{
			if (array_key_exists($k, $arr1))
			{
				if (is_array($arr1[$k]) && is_array($arr2[$k]))
				{
					$arr1[$k] = WSKO_Class_Helper::merge_array_deep($arr1[$k], $arr2[$k]);
				}
				else
				{
				}
			}
			else
			{
				$arr1[$k] = $arr2[$k];
			}
		}
		return $arr1;
	}
	public static function get_obj_rewrite_base($type, $arg)
	{
		$obj = false;
		if ($type === 'post_type')
			$obj = get_post_type_object($arg);
		else if ($type == 'post_tax')
			$obj = get_taxonomy($arg);
		if ($obj)
		{
			$meta_obj = WSKO_Class_Onpage::get_meta_object($arg, $type);
			$auto_redirects = WSKO_Class_Onpage::get_auto_redirects(false);
			if (isset($auto_redirects[$type][$arg]['first_slug']) && $auto_redirects[$type][$arg]['first_slug'])
				$first_slug = $curr_slug = $auto_redirects[$type][$arg]['first_slug'];
			else
				$first_slug = $curr_slug = $obj && $obj->rewrite && $obj->rewrite['slug'] ? $obj->rewrite['slug'] : ($arg === 'page' || $arg === 'post' ? '/' : $arg);
			if ($meta_obj && isset($meta_obj['slug']) && $meta_obj['slug'])
				$curr_slug = $meta_obj['slug'];
			if (!$first_slug)
				$first_slug = '/';
			if (!$curr_slug)
				$curr_slug = '/';
			return array('original' => $first_slug, 'current' => $curr_slug);
		}
		return false;
	}
	public static function set_file_download_headers($filename, $size = false)
	{
		$size = intval($size);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		if ($size)
			header('Content-Length: '.$size);
	}
	
	public static function get_http_status_codes($url, $urls, $follow = true)
	{
		$res = array();
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);

		$html = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$next_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
		curl_close($ch);
		$res[]= array('url' => $url, 'code' => $http_code);
		
		if ($follow && $next_url && ($http_code == 301 || $http_code == 302))
		{
			foreach ($urls as $p_url)
			{
				if ($p_url['url'] == $next_url)
				{
					$res[] = array('url' => $url, 'code' => 'loop');
					return $res;
				}
			}
				
			$codes = WSKO_Class_Helper::get_http_status_codes($next_url, $res, $follow);
			if ($codes)
			{
				foreach ($codes as $c)
				{
					$res[] = $c;
				}
			}
		}
		return $res;
	}
	public static function refresh_permalinks()
	{
		//flush_rewrite_rules();//update permalinks
		//global $wp_rewrite;
		//flush_rules();
		WSKO_Class_Core::save_option('refresh_permalinks', true, true);
	}
	public static function refresh_wp_cache($option, $all = true)
	{
		if ($option)
			wp_cache_get($option, 'options', true);
		if ($all)
		{
			wp_cache_get('alloptions', 'options', true);
			wp_cache_get('notoptions', 'options', true);
		}
		WSKO_Class_Core::get_data(true);
	}
	public static function get_public_post_types($output)
	{
		$names = false;
		if ($output == 'names')
			$names = true;
		$pts = get_post_types(array('public' => true), $output);
		foreach ($pts as $k => $pt)
		{
			$c_v = $k;
			if ($names)
				$c_v = $pt;
			if ($c_v == 'attachment')
				unset($pts[$k]);
		}
		return $pts;
	}
	public static function get_public_taxonomies($output)
	{
		$names = false;
		if ($output == 'names')
			$names = true;
		$txs = get_taxonomies(array('public' => true), $output);
		foreach ($txs as $k => $pt)
		{
			$c_v = $k;
			if ($names)
				$c_v = $pt;
			if ($c_v == 'post_format')
				unset($txs[$k]);
		}
		return $txs;
	}
	public static function get_empty_page_title()
	{
		return '<i class="wsko-post-title-empty">'.__('Untitled', 'wsko').'</i>';
	}
	public static function get_default_page_title($title)
	{
		return $title.' - '.get_bloginfo('name');
	}
	public static function sanitize_meta($value, $filter = false)
	{
		if ($filter)
			$value = apply_filters($filter, $value);
		$preg_find = array('/\[[^|\/]vc_.*?\]/');
		$preg_rep = array('');
		$value = preg_replace($preg_find, $preg_rep, $value);
		return WSKO_Class_Helper::get_plain_string(WSKO_Class_Helper::handle_shortcodes($value));
	}
	public static function handle_shortcodes($value)
	{
		if (!is_scalar($value))
			return "";
		WSKO_Class_Compatibility::register_shortcodes_comp();
		return strip_shortcodes(do_shortcode($value));
	}
	public static function get_code_highlighting_register($type)
	{
		switch ($type)
		{
			case 'html':
			return array(
				array('color' => 'orange', 'type' => 1, 'regex' => '(&lt;[^&gt;]*&gt;|&lt;|&gt;)'), //html tags
				array('color' => 'red', 'type' => 1, 'regex' => '(&lt;\?|&lt;\?=|&lt;\?php|\?&gt;)'), //php
				//array('color' => 'red', 'type' => 2, 'regex' => '(&lt;\?)(.*?)(\?&gt;)'), //php
				//array('color' => 'orange', 'type' => 3, 'regex' => '(&lt;.*&gt;)(.*)(&lt;\/.*&gt;)|(&lt;.*[\/]&gt;)'), //html tags
			);
			case 'robots':
			return array(
				array('color' => 'green', 'type' => 1, 'regex' => '(?<=[^s]|^)(allow:)'),
				array('color' => 'red', 'type' => 1, 'regex' => '(disallow:)'),
			);
			case 'htaccess':
			return array(
				array('color' => 'orange', 'type' => 1, 'regex' => '(RewriteBase)'),
			);
		}
		return array();
	}
	
	public static function get_post_type_labels($post_types)
	{
		$res = array();
		foreach ($post_types as $pt)
		{
			$pt_o = get_post_type_object($pt);
			$res[$pt] = $pt_o->label;
		}
		return $res;
	}
	public static function densify_string($string, $keep_whitespace = false)
	{
		if ($keep_whitespace)
			return strtolower(str_replace(array('-', '_', '.', ',', '\r\n', '\r', '\n', '\t'), array('', '', ' ', ' ', ' ', ' ', ' ', ' '), $string));
		else
			return strtolower(str_replace(array(' ', '-', '.', '_', ',', '\r\n', '\r', '\n', '\t'), '', $string));
	}
	public static function get_post_titles($posts)
	{
		$res = array();
		foreach ($posts as $p)
		{
			$title = esc_html(get_the_title($p));
			if (!$title)
				$title = WSKO_Class_Helper::get_empty_page_title();
			$res[$p] = array('title'=>$title,'url'=>get_permalink($p));
		}
		return $res;
	}
	public static function get_real_post_content($post_id, $format = false)
	{
		$post = get_post($post_id);
		$c = $post->post_content;
		$c = apply_filters('the_content', $c);
		if ($format)
			$c = str_replace(']]>', ']]&gt;', $c);
		return $c;
	}
	public static function post_to_url($url, $data, $headers = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public static function get_from_url($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("charset=utf-8"));
		curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public static function get_url_head($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	public static function sanitize_table_filter($filter, $for_sql = false, $args = array())
	{
		if (!$filter)
			return array();
		$keys = isset($args['keys']) ? $args['keys'] : false;
		$filter_s = array();
		foreach ($filter as $k => $f)
		{
			//types: set, co, eq, ra
			if ($for_sql)
			{
				if ($keys !== false)
				{
					$k = is_array($keys[$f['key']]) ? $keys[$f['key']][0] : $keys[$f['key']];
				}
				else
				{
					$k = $f['key'];
				}
				if ($k !== null)
				{
					if ($f['comp'] == 'set')
						$filter_s[$k] = " IS ".((!$f['val'] || $f['val'] == 'false' || $f['val'] == 'off') ? 'NOT ':'')."NULL";
					else if ($f['comp'] == 'co')
						$filter_s[$k] = " LIKE '%".esc_sql(strtolower($f['val']))."%'";
					else if ($f['comp'] == 'eq')
						$filter_s[$k] = " = '".esc_sql($f['val'])."'";
					else if ($f['comp'] == 'ra')
					{
						$vals = WSKO_Class_Helper::safe_explode(':', $f['val']);
						$filter_s[$k] = " BETWEEN ".round(floatval(str_replace('.','.', $vals[0])), 2)." AND ".round(floatval(str_replace('.','.', $vals[1])), 2);
					}
				}
			}
			else
			{
				$filter_s[$k] = $f;
				if ($f['comp'] == 'set')
					$filter_s[$k]['val']= (!$f['val'] || $f['val'] == 'false' || $f['val'] == 'off') ? false : true;
				else if ($f['comp'] == 'co')
					$filter_s[$k]['val']= strtolower($f['val']);
				else if ($f['comp'] == 'ra' && !is_array($f['val']))
					$filter_s[$k]['val'] = WSKO_Class_Helper::safe_explode(':', $f['val']);
			}
		}
		return $filter_s;
	}

	public static function prepare_data_table($data, $pagination = false, $filter = false, $sort = false, $params = array(), $is_formated_data = false)
	{
		if (empty($data))
			return $data;
		$filtered = 0;
		$total = 0;
		$result = array();
		$from_cache = isset($params['from_cache']) ? $params['from_cache'] : false;
		$spec = isset($params['specific_keys']) ? $params['specific_keys'] : false;
		$format = isset($params['format']) ? $params['format'] : false;
		$search = isset($params['search']) && $params['search'] ? strtolower($params['search']) : false;
		if (!$from_cache)
		{
			if ($filter)
			{
				$filter = WSKO_Class_Helper::sanitize_table_filter($filter);
			}
		}
		$ti = 0;
		foreach ($data as $d)
		{
			$ti++;
			if (is_object($d))
				$d = (array)$d;
			$passed_filter = true;
			if (!$from_cache)
			{
				$total++;
				if ($search)
				{
					$has_search = false;
					foreach ($d as $v)
					{
						$val = null;
						if ($is_formated_data)
						{
							if (isset($v['order']))
							{
								$val = $v['order'];
							}
							else
							{
								$val = $v['value'];
							}
						}
						else
						{
							$val = $v;
						}
						if ((is_string($val) && strpos(strtolower($val), $search) !== false))// || (is_scalar($val) && $val == $search))
						{
							$has_search = true;
							break;
						}
					}
					if (!$has_search)
						$passed_filter = false;
				}
				if ($passed_filter && $filter)
				{
					foreach ($filter as $f)
					{
						$val = null;
						if ($is_formated_data)
						{
							if (isset($d[$f['key']]['order']))
							{
								$val = $d[$f['key']]['order'];
							}
							else
							{
								$val = $d[$f['key']]['value'];
							}
						}
						else
						{
							$val = $d[$f['key']];
						}
						if ($val !== null)
						{
							switch ($f['comp'])
							{
								case 'eq':
								if ($val != $f['val'])
									$passed_filter = false;
								break;
								case 'co':
								if (strpos(strtolower($val), $f['val']) === false)
									$passed_filter = false;
								break;
								case 'ra':
								if ($val < $f['val'][0] || $val > $f['val'][1])
									$passed_filter = false;
								break;
								case 'gt':
								if ($val <= $f['val'])
									$passed_filter = false;
								break;
								case 'lt':
								if ($val >= $f['val'])
									$passed_filter = false;
								break;
								case 'set':
								if (($f['val'] && !$val) || (!$f['val'] && $val))
									$passed_filter = false;
								break;
							}
						}
						else
						{
							$passed_filter = false;
						}
					}
				}
				if (!$passed_filter)
					continue;
				$filtered++;
			}
			$r = array();
			if ($spec)
			{
				$r_k = 0;
				foreach ($spec as $s)
				{
					if (!is_array($s))
						$s_a = array($s);
					else
						$s_a = $s;
					foreach($s_a as $sp)
					{
						$val = null;
						if (isset($d[$sp]))
						{
							if ($is_formated_data)
							{
								if ($sort && isset($d[$sp]['order']) && $sp == $sort['key'])
									$val = $d[$sp];
								else
									$val = $d[$sp]['value'];
							}
							else
								$val = $d[$sp];
						}
						if (array_key_exists($r_k, $r))
						{
							if (!is_array($r[$r_k]))
								$r[$r_k] = array($r[$r_k]);
							$r[$r_k][] = $val;
						}
						else
							$r[$r_k] = $val;
					}
					$r_k++;
				}
			}
			else
			{
				foreach ($d as $k => $v)
				{
					if ($is_formated_data)
					{
						if ($sort && isset($d[$k]['order']) && $k == $sort['key'])
							$r[] = $v;
						else
							$r[] = $v['value'];
					}
					else
						$r[] = $v;
				}
			}
			$result[] = $r;
		}
		if (!$from_cache)
		{
			if ($sort)
			{
				usort($result, function($a, $b) use ($sort) {
					$a_v = isset($a[$sort['key']]['order']) ? $a[$sort['key']]['order'] : (is_array($a[$sort['key']]) ? $a[$sort['key']][0] : $a[$sort['key']]);
					$b_v = isset($b[$sort['key']]['order']) ? $b[$sort['key']]['order'] : (is_array($b[$sort['key']]) ? $b[$sort['key']][0] : $b[$sort['key']]);
					
					if ($a_v == $b_v)
						return 0;
					if ($sort['dir'] == 1)
						return $a_v < $b_v ? -1 : 1;
					else
						return $a_v > $b_v ? -1 : 1;
				});
			}
			if ($pagination)
			{
				$result = array_slice($result, $pagination['offset'], $pagination['count']);
			}
		}
		foreach($result as $k => $r)
		{
			foreach($r as $k2 => $a)
			{
				if (is_array($a) && isset($a['value'])) $result[$k][$k2] = $a = $a['value'];
				if ($format && isset($format[$k2]))
				{
					if (is_scalar($format[$k2]))
					{
						switch ($format[$k2])
						{
							case 'url_resolve': $result[$k][$k2] = WSKO_Class_Template::render_url_resolve_field($a, true); break;
							case 'url': $result[$k][$k2] = WSKO_Class_Template::render_url_post_field_s($a, array(), true); break;
							case 'url_co': $result[$k][$k2] = WSKO_Class_Template::render_url_post_field_s($a, array('with_co' => true), true); break;
							case 'prog_rad': $result[$k][$k2] = $a === null || $a === false ? '-' : WSKO_Class_Template::render_radial_progress('success', false, array('val' => $a), true); break;
							case 'date': $result[$k][$k2] = date('d.m.Y', $a); break;
							case 'datetime': $result[$k][$k2] = date('d.m.Y H:i', $a); break;
							case 'keyword': $result[$k][$k2] = WSKO_Class_Template::render_keyword_field($a, true); break;
							case 'http_code': $result[$k][$k2] = WSKO_Class_Template::render_http_code_cell($a, array(), true); break;
						}
					}
					else
					{
						$result[$k][$k2] = $format[$k2]($a,$r);
					}
				}
			}
		}
		return array('filtered' => $filtered, 'total' => $total, 'data' => $result);
	}
	public static function get_plain_string($string)
	{
		if (!is_scalar($string))
			return "";
		$preg_find = array('/\[.*?\]/', '#<script(.*?)>(.*?)</script>#is', '#<style(.*?)>(.*?)</style>#is');
		$preg_rep = array('', '', '');
		$string = preg_replace($preg_find, $preg_rep, $string);
		return wp_strip_all_tags($string);
	}
	public static function get_word_count($string)
	{
		if (!$string)
			return 0;
		$parts = mb_split('[\s_"]', $string);
		$c = 0;
		foreach($parts as $p)
		{
			if ($p && !ctype_space($p))
				$c++;
		}
		return $c;
		//if (WSKO_Class_Core::get_setting('non_latin_mode'))
			//return count(mb_split('[\s_"]', $string));
		//else
			//return str_word_count($string, 0, '1234567890,.?!_-#');
	}
	public static function get_parent_url($url, $level = 1)
	{
		$url = WSKO_Class_Helper::get_current_dir($url);
		$len = strlen($url);
		$url = WSKO_Class_Helper::get_current_dir(substr($url, 0, $len && $url[$len - 1] == '/' ? -1 : $len));
		if ($level <= 1)
			return $url;
		else
			return WSKO_Class_Helper::get_parent_url($url, $level-1);
	}
	public static function get_current_dir($url)
	{
		if ($first_query = strpos($url, '?')) $url = substr($url, 0, $first_query);
		if ($first_fragment = strpos($url, '#')) $url = substr($url, 0, $first_fragment);
		$last_slash = strrpos($url, '/');
		if (!$last_slash)
			return '/';
		if (($first_colon = strpos($url, '://')) !== false && $first_colon + 2 == $last_slash)
			return $url . '/';
		return substr($url, 0, $last_slash + 1);
	}
	public static function get_unique_words($str)
	{
		return array_unique(array_map(function($arg){ return WSKO_Class_Helper::densify_string($arg); }, str_word_count($str, 1)));
	}
	public static function get_unique_id($prefix = false)
	{
		return str_replace('.', '_', uniqid($prefix, true));
	}
	public static function get_all_meta_keys($type)
	{
		$res = array();
		if ($type == 'post')
		{
			global $wpdb;
			$keys =  $wpdb->get_results("SELECT pm.meta_key FROM {$wpdb->postmeta} pm");
			foreach($keys as $key)
			{
				if (!isset($res[$key->meta_key]))
					$res[$key->meta_key] = 1;
				else
					$res[$key->meta_key]++;
			}
		}
		else if ($type == 'tax')
		{
			$taxs = get_taxonomies();
			foreach($taxs as $tax)
			{
				if (!isset($res[$tax]))
					$res[$tax] = 1;
				else
					$res[$tax]++;
			}
		}
		return $res;
	}
	public static function get_random_post($post_type = 'any', $count = 1, $fields = 'all')
	{
		$query = new WP_Query(array( 
			'post_type' => $post_type ? $post_type : 'any', 
			'posts_per_page' => $count,
			'orderby' => 'rand',
			'fields' => $fields
		));
		if ($query && !empty($query->posts))
		{
			if ($count > 1)
				return $query->posts;
			else
				return $query->posts[0];
		}
		return false;
	}
	public static function get_random_term($post_tax, $count = 1)
	{
		$query = get_terms(array('taxonomy' => $post_tax, 'hide_empty' => false, 'orderby' => 'rand', 'number' => $count));
		if ($query && !empty($query))
		{
			if ($count > 1)
				return $query;
			else
				return $query[0];
		}
		return false;
	}
	public static function get_first_post($post_type)
	{
		$query = new WP_Query(array( 
			'post_type' => $post_type, 
			'posts_per_page' => 1,
		));
		if ($query && !empty($query->posts))
		{
			return $query->posts[0];
		}
		return false;
	}
	public static function url_get_info($urls)
	{
		$codes = array();
		$curls = array();
		$mh = curl_multi_init();
		foreach ($urls as $url)
		{
			if (!isset($curls[$url]))
			{
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_NOBODY, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);
				curl_multi_add_handle($mh, $ch);
				$curls[$url] = $ch;
			}
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($active > 0);
		foreach ($curls as $url => $curl)
		{
			$codes[$url] = array('code' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'url' => curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
			curl_multi_remove_handle($mh, $curl);
		}
		return $codes;
	}
	
	public static function url_get_tags($url, $element)
	{
		$html = new DOMDocument();
		$html->recover = TRUE;
		@$html->loadHtmlFile($url);
		$xpath = new DOMXPath($html);
		$res = array();
		switch ($element)
		{
			case 'a-out':
			$nodelist = $xpath->query("//a");// "//a/@href"
			foreach ($nodelist as $n)
			{
				$href = $n->getAttribute('href');
				if ($href)
				{
					$first_char = substr($href, 0, 1);
					if ($first_char != '#' && $first_char != '/' && $first_char != '?')
						$res[]=array('href' => $href, 'anchor' => $n->nodeValue, 'nofollow' => $n->getAttribute('rel') == 'nofollow');
				}
			}
			break;
		}
		return $res;
	}
	
	static $url_title_cache;

	public static function url_get_title($url)
	{
		if (!static::$url_title_cache)
			static::$url_title_cache = array(
					'post_types' => array(),
					'terms' => array(),
				);
			
		$res = new stdClass;
		$res->title = '';
		$res->type = 'unknown';
		$res->resolved = false;
		//$res->post_id = $postid;
		
		$postid = WSKO_Class_Helper::url_to_postid($url);
		
		if ($postid)
		{
			$t = get_the_title($postid);
			$res->title = $t ? $t : WSKO_Class_Helper::get_empty_page_title();//.' '. WSKO_Class_Template::render_content_optimizer_link($postid, array(), true);
			$res->title_empty = $t ? false : true;
			$res->type = 'post';
			$res->post_id = $postid;
			$res->resolved = true;
		}
		else 
		{
			$isType = false;
			$post_types = get_post_types(array(), 'names');
			foreach ($post_types as $type)
			{
				if (!isset(static::$url_title_cache['post_types'][$type]))
					$url_c = static::$url_title_cache['post_types'][$type] = get_post_type_archive_link($type);
				else
					$url_c = static::$url_title_cache['post_types'][$type];
				
				if ($url_c == $url)
				{
					$type = get_post_type_object($type);
					$res->title = 'Archive - ' . $type->label;
					$res->type = 'archive';
					$res->resolved = true;
					$isType = true;
					break;
				}
			}
			
			if (!$isType)
			{
				$isTerm = false;
				$terms = get_terms();
				foreach ($terms as $term)
				{
					if (!isset(static::$url_title_cache['terms'][(string)$term->term_id]))
						$url_c = static::$url_title_cache['terms'][(string)$term->term_id] = get_term_link($term->term_id, $term->taxonomy);
					else
						$url_c = static::$url_title_cache['terms'][(string)$term->term_id];
					
					if ($url_c == $url)
					{
						$res->title = 'Term Archive - ' . $term->name;
						$res->type = 'term';
						$res->resolved = true;
						$isTerm = true;
						break;
					}
				}
			
				if (!$isTerm)
				{
					$res->title = '<span class="wsko-post-not-found text-off">'.__('No Wordpress-Post found.', 'wsko').'</span>';
				}
			}
		}
		
		return $res;
	}

	public static function url_to_postid($url)
	{
		global $wp_rewrite;

		$pid = url_to_postid($url);
		if ($pid)
			return $pid;
		
		$url = apply_filters('url_to_postid', $url);

		// First, check to see if there is a 'p=N' or 'page_id=N' to match against
		if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
			$id = absint($values[2]);
			if ( $id )
				return $id;
		}

		// Check to see if we are using rewrite rules
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
		if ( empty($rewrite) )
			return 0;

		// Get rid of the #anchor
		$url_split = WSKO_Class_Helper::safe_explode('#', $url);
		$url = $url_split[0];

		// Get rid of URL ?query=string
		$url_split = WSKO_Class_Helper::safe_explode('?', $url);
		$url = $url_split[0];

		// Add 'www.' if it is absent and should be there
		if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
			$url = str_replace('://', '://www.', $url);

		// Strip 'www.' if it is present and shouldn't be
		if ( false === strpos(home_url(), '://www.') )
			$url = str_replace('://www.', '://', $url);

		// Strip 'index.php/' if we're not using path info permalinks
		if ( !$wp_rewrite->using_index_permalinks() )
			$url = str_replace('index.php/', '', $url);

		if ( false !== strpos($url, home_url()) ) {
			// Chop off http://domain.com
			$url = str_replace(home_url(), '', $url);
		} else {
			// Chop off /path/to/blog
			$home_path = parse_url(home_url());
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
			$url = str_replace($home_path, '', $url);
		}
		// Trim leading and lagging slashes
		$url = trim($url, '/');

		$request = $url;
		// Look for matches.
		$request_match = $request;
		foreach ( (array)$rewrite as $match => $query) {
			// If the requesting file is the anchor of the match, prepend it
			// to the path info.
			if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
				$request_match = $url . '/' . $request;

			if ( preg_match("!^$match!", $request_match, $matches) ) {
				// Got a match.
				// Trim the query of everything up to the '?'.
				$query = preg_replace("!^.+\?!", '', $query);

				// Substitute the substring matches into the query.
				$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

				// Filter out non-public query vars
				global $wp;
				parse_str($query, $query_vars);
				$query = array();
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array($key, $wp->public_query_vars) )
						$query[$key] = $value;
				}

			// Taken from class-wp.php
			foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
				if ( $t->query_var )
					$post_type_query_vars[$t->query_var] = $post_type;

			foreach ( $wp->public_query_vars as $wpvar ) {
				if ( isset( $wp->extra_query_vars[$wpvar] ) )
					$query[$wpvar] = $wp->extra_query_vars[$wpvar];
				elseif ( isset( $_POST[$wpvar] ) && !is_array( $_POST[$wpvar] ) )
					$query[$wpvar] = sanitize_text_field($_POST[$wpvar]);
				elseif ( isset( $_GET[$wpvar] ) && !is_array( $_GET[$wpvar] ) )
					$query[$wpvar] = sanitize_text_field($_GET[$wpvar]);
				elseif ( isset( $query_vars[$wpvar] ) )
					$query[$wpvar] = $query_vars[$wpvar];

				if ( !empty( $query[$wpvar] ) ) {
					if ( ! is_array( $query[$wpvar] ) ) {
						$query[$wpvar] = (string) $query[$wpvar];
					} else {
						foreach ( $query[$wpvar] as $vkey => $v ) {
							if ( !is_object( $v ) ) {
								$query[$wpvar][$vkey] = (string) $v;
							}
						}
					}

					if ( isset($post_type_query_vars[$wpvar] ) ) {
						$query['post_type'] = $post_type_query_vars[$wpvar];
						$query['name'] = $query[$wpvar];
					}
				}
			}
				// Do the query
				$query = new WP_Query($query);
				if ( !empty($query->posts) && $query->is_singular )
					return $query->post->ID;
				else
					return 0;
			}
		}
		return 0;
	}
	
	public static function format_url($link, $append_slash = false, $use_real_base = false)
	{
		if (WSKO_Class_Helper::starts_with($link, 'http://') || WSKO_Class_Helper::starts_with($link, 'https://'))
			$link = $link;
		else if ($use_real_base)
		{
			if (WSKO_Class_Helper::starts_with($link, WSKO_Class_Helper::get_host()))
			   $link = WSKO_Class_Helper::home_url(substr($link, strlen(WSKO_Class_Helper::get_host())), true);
		   	else
			   $link = WSKO_Class_Helper::home_url($link, true);
		}
		else
		{
			if (WSKO_Class_Helper::starts_with($link, WSKO_Class_Helper::get_domain()))
			   $link = WSKO_Class_Helper::home_url(substr($link, strlen(WSKO_Class_Helper::get_domain())));
		   	else
			   $link = WSKO_Class_Helper::home_url($link);
		}
		if ($append_slash)
		{
			if (strpos($link, '?') !== false || strpos($link, '?') !== false)
			{
				$parts = parse_url($link);
				$link = (isset($parts['scheme']) ? $parts['scheme'].'://' : '').(isset($parts['host']) ? $parts['host'] : '').(isset($parts['path']) ? $parts['path'] : '');
				$link = rtrim($link, '/').'/'.(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
			}
			else
			{
				$link = rtrim($link, '/').'/';
			}
		}
		return $link;
	}
	public static function home_url($path = '/', $use_real_base = false)
	{
		if ($use_real_base)
			return WSKO_Class_Helper::get_host_base(false, true).(!WSKO_Class_Helper::starts_with($path,'/')?'/':'').$path;
		else
			return WSKO_Class_Helper::get_domain(false, true).(!WSKO_Class_Helper::starts_with($path,'/')?'/':'').$path;
	}
	public static function starts_with($string, $query)
	{
		return substr($string, 0, strlen($query)) === $query;
	}
	
	public static function ends_with($string, $query)
	{
		return substr($string, strlen($string)-strlen($query)) === $query;
	}
	
	public static function is_local_host()
	{
		$domain = WSKO_Class_Helper::get_host();
		return (substr($domain, 0, strlen('http://localhost')) === 'http://localhost' ||
		substr($domain, 0, strlen('https://localhost')) === 'https://localhost' ||
		substr($domain, 0, strlen('localhost')) === 'localhost');
	}	
	public static function get_host($real = false)
	{
		$parts = parse_url(home_url());
		$real_host = isset($parts['host'])? $parts['host'] : false;
		return !$real && defined('WSKO_HOST_BASE') && WSKO_HOST_BASE ? WSKO_HOST_BASE : $real_host;
	}
	public static function get_host_base($append_slash = true, $real = false)
	{
		return defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH && !$real ? (rtrim(WSKO_HOST_BASE_PATH, '/').($append_slash?'/':'')) : ((is_ssl()?'https':'http') . "://" . WSKO_Class_Helper::get_host(true).($append_slash?'/':''));
	}
	public static function get_domain($append_slash = true, $real = false)
	{
		return defined('WSKO_HOST_BASE_PATH') && WSKO_HOST_BASE_PATH && !$real ? (rtrim(WSKO_HOST_BASE_PATH, '/').($append_slash?'/':'')) : (rtrim(home_url(), '/').($append_slash?'/':''));
	}
	
	public static function get_current_url($with_host = true)
	{
		$url = esc_url((is_ssl()?'https':'http') . "://" . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		if (!$with_host)
			$url = ltrim(str_replace(WSKO_Class_Helper::home_url(), '', $url), '/');
		return $url;
	}
	
	public static function get_effective_url($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);
		curl_exec($ch);
		$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_close($ch);
		return $effective_url;
	}
	
	public static function get_effective_urls($urls)
	{
		$eff_urls = array();
		$curls = array();
		$mh = curl_multi_init();
		foreach ($urls as $url)
		{
			if (!isset($curls[$url]))
			{
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_NOBODY, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_USERAGENT, static::$curl_agent);
				curl_multi_add_handle($mh, $ch);
				$curls[$url] = $ch;
			}
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($active > 0);
		foreach ($curls as $url => $curl)
		{
			$eff_urls[$url] = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			curl_multi_remove_handle($mh, $curl);
		}
		return $eff_urls;
	}
	
	public static function get_current_time()
	{
		return current_time('timestamp');
	}
	public static function get_midnight($time = false)
	{
		if (!$time)
			$time = WSKO_Class_Helper::get_current_time();
		if (is_numeric($time) || is_integer($time))
			$time = date('Y-m-d H:i:s', $time);
		$date = new DateTime($time, new DateTimeZone("UTC"));
		$date->setTime(0,0,0);
		return $date->getTimestamp();
	}
	
	public static function report_error($type, $title, $var, $additional = false)
	{
		switch ($type)
		{
			case 'exception':
				$type = 'error';
				$c = 'An exception occurred:<br/><br/><pre>' . $var->getMessage() . '</pre>';
				if ($additional)
					$c .= $additional;
				break;
				
			case 'error_dump':
				$type = 'error';
				ob_start();
				var_dump($var);
				$add = ob_get_clean();
				$c = 'An exception occurred:<br/><br/><pre>' . $add . '</pre>';
				if ($additional)
					$c .= $additional;
				break;
				
			case 'warning':
			case 'info':
			case 'error':
				$c = $var;
				if ($additional)
					$c .= '<br/><br/>' . $additional;
				break;
				
			default:
				return;
		}
		
		
		//if (WSKO_Class_Core::get_setting('activate_log'))
		//{
			ob_start();
			debug_print_backtrace();
			$backtrace = ob_get_clean();
			$args = array(
				'post_type' => WSKO_POST_TYPE_ERROR,
				'post_status' => $type,
				'post_title' => $title ? sanitize_text_field($title) : '-title missing-',
				'post_content' => $c ? sanitize_text_field(substr($c, 0, 5000).(strlen($c) > 5000 ? '...' : '')) : '-no details-',
				'post_author' => 0,
				'meta_input' => array('_wsko_backtrace' => sanitize_text_field($backtrace))
			);
			return wp_insert_post($args);
		//}
	}
	
	public static function add_error_post_type()
	{
		register_post_type(WSKO_POST_TYPE_ERROR,
			array(
			'labels' => array( //just for completeness
				'name' => 'WSKO Log Report',
				'singular_name' => 'WSKO Log Report',
				/*'add_new' => 'Add a New WSKO Log Report',
				'add_new_item' => 'Add a New WSKO Log Report',
				'edit_item' => 'Edit WSKO Log Report',
				'new_item' => 'New WSKO Log Report',
				'view_item' => 'View WSKO Log Report',
				'search_items' => 'Search WSKO Log Reports',
				'not_found' => 'Nothing Found',
				'not_found_in_trash' => 'Nothing found in Trash',
				'parent_item_colon' => ''*/
			),
			'description' => 'WSKO Log Reports',
			'public' => false,
			'exclude_from_search' => false,
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
	
	public static function clear_logs($all = false)
	{
		if (!WSKO_POST_TYPE_ERROR || !WSKO_Class_Helper::starts_with(WSKO_POST_TYPE_ERROR, 'wsko_')) //security
			return;
			
		$args = array('post_type' => WSKO_POST_TYPE_ERROR, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids');
		if (!$all)
		{
			$args['date_query'] = array(
				array(
					'column' => 'post_date_gmt',
					'before' => '1 week ago',
				)
			);
		}
		$msgs = get_posts($args);
		if ($msgs)
		{
			foreach ($msgs as $p)
			{
				wp_delete_post($p, true);
			}
		}
	}
	
	public static function format_reports()
	{
		$res = "";
		$msgs = get_posts(array('post_type' => WSKO_POST_TYPE_ERROR, 'post_status' => 'any', 'numberposts' => 20));
		if ($msgs)
		{
			foreach ($msgs as $p)
			{
				$res .= "Status: ".$p->post_status."<br/>".
						"Type:   ".$p->post_title."<br/>".
						"From:   ".get_the_date('d.m.Y H:i:s', $p->ID)."<br/>".
						"<br/><br/>".
						$p->post_content."<br/>".
						"<br/>---------------------------<br/><br/>";
			}
		}
		else
		{
			$res = "No Reports found.";
		}
		return $res;
	}
	
	public static function check_user_permissions($high_value = true)
	{
		$wsko_data = WSKO_Class_Core::get_data();
		
		$user = wp_get_current_user();
		if ($user && $user->ID)
		{
			if (current_user_can('manage_options'))
			{
				return true;
			}
			if (!$high_value)
			{
				$add_roles = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('additional_permissions'));
				foreach($user->roles as $role)
				{
					if ($add_roles && in_array($role, $add_roles))
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	public static function remove_caps()
	{
		global $wp_roles;
		$roles = $wp_roles->get_names(); 
		foreach ($roles as $k => $role)
		{
			$r = get_role($k);
			if ($r)
				$r->remove_cap(WSKO_VIEW_CAP);
		}
	}
	
	public static function get_country_name($countryCode)
	{
		try
		{
			WSKO_Class_Core::include_lib('iso_lang');
			$data = (new League\ISO3166\ISO3166)->alpha3(strtoupper($countryCode));
			return $data ? $data['name'] : false;
		}
		catch (\Exception $ex)
		{
		}
		return false;
	}

	public static function convert_country_2_to_3($two_letter_code)
	{
		try
		{
			WSKO_Class_Core::include_lib('iso_lang');
			$data = (new League\ISO3166\ISO3166)->alpha2($two_letter_code);
			return $data ? $data['alpha3'] : false;
		}
		catch (\Exception $ex)
		{
		}
		return false;
	}

	public static function convert_country_3_to_2($three_letter_code)
	{
		try
		{
			WSKO_Class_Core::include_lib('iso_lang');
			$data = (new League\ISO3166\ISO3166)->alpha3($three_letter_code);
			return $data ? $data['alpha2'] : false;
		}
		catch (\Exception $ex)
		{
		}
		return false;
	}
	
	public static function is_php_func_enabled($func)
	{
		$disabled = WSKO_Class_Helper::safe_explode(',', ini_get('disable_functions'));
		return !in_array($func, $disabled);
	}

	public static function safe_json_decode($str, $assoc = false)
	{
		if ($str)
		{
			try
			{
				return json_decode($str, $assoc);
			}
			catch (\Exception $ex)
			{

			}
		}
		return false;
	}

	public static function safe_json_encode($str)
	{
		if ($str)
		{
			try
			{
				return json_encode($str);
			}
			catch (\Exception $ex)
			{

			}
		}
		return "";
	}

	public static function convert_utf8($mixed_str)
	{
		WSKO_Class_Core::include_lib('forceutf8');
		return \ForceUTF8\Encoding::fixUTF8($mixed_str);
	}

	public static function get_root_domain($path)
	{
		if (strpos($path, '/') !== false)
		{
			$url_data = parse_url($path);
			$path = isset($url_data['host']) ? $url_data['host'] : false;
		}
		if ($path)
		{
			$root_domain = false;
			$domain_parts = WSKO_Class_Helper::safe_explode('.', $path);
			$c_parts = count($domain_parts);
			if ($c_parts >= 2)
			{
				if ($c_parts >= 3)
				{
					if (strlen($domain_parts[$c_parts-2]) <= 2 && !preg_match('/[^A-Za-z0-9]/', $domain_parts[$c_parts-2])) //is language domain
					{
						$root_domain = $domain_parts[$c_parts-3].'.'.$domain_parts[$c_parts-2].'.'.$domain_parts[$c_parts-1];
					}
					else
					{
						$root_domain = $domain_parts[$c_parts-2].'.'.$domain_parts[$c_parts-1];
					}
				}
				else
					$root_domain = $path;
				return $root_domain;
			}
		}
		return false;
	}

	public static function get_preg_indices($preg, $string, $options = array())
	{
		$custom_data = isset($options['custom_data']) ? $options['custom_data'] : false;
		$matches_r = array();
		preg_match_all($preg, $string, $matches, PREG_OFFSET_CAPTURE);
		if ($matches && $matches[0])
		{
			foreach ($matches[0] as $k => $d)
			{
				if ($d && isset($d[1]) && isset($d[0]) && $d[0])
				{
					$matches_r[$k] = $d;
					$matches_r[$k][2] = $d[1] + strlen($d[0]);
					if ($custom_data)
					{
						$matches_r[$k] = $custom_data($matches_r[$k]);
					}
				}
			}
		}
		return $matches_r;
	}

	public static function get_time_elapsed_string($datetime, $full = false, $t_now = false) {
		$now = new DateTime;
		if ($t_now)
			$now = new DateTime($t_now);
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	public static function safe_explode($delimiter, $string, $limit = PHP_INT_MAX)
	{
		if ($string)
		{
			return explode($delimiter, $string, $limit);
		}
		return array();
	}

	public static function is_enqueued($handle, $type)
	{
		switch ($type)
		{
			case 0: //css
			global $wp_styles;
			foreach ($wp_styles->queue as $hl)
			{
				if ($handle === $hl)
					return true;
			}
			break;
			case 1: //js
			global $wp_scripts;
			foreach ($wp_scripts->queue as $hl)
			{
				if ($handle === $hl)
					return true;
			}
			break;
		}
		return false;
	}

	public static function sanitize_array($array, $san_func, $san_key)
	{
		if (!is_callable($san_func))
			return array(); //TODO: add error
		$san = array();
		if ($array && is_array($array))
		{
			foreach ($array as $key => $val)
			{
				if ($san_key)
					$key = $san_func($key);
				if (is_array($val))
					$val = WSKO_Class_Helper::sanitize_array($val, $san_func, $san_key);
				else
					$val = $san_func($val);
				$san[$key] = $val;
			}
		}
		return $san;
	}

	public static function get_home_id()
	{
		$frontpage_id = get_option('page_on_front');
		//$blog_id = get_option('page_for_posts');
		return $frontpage_id;
	}

	public static function map_url($url, $new_base = false)
	{
		if (!$new_base)
			$new_base = home_url();

		$url_parts = parse_url($url);
		$new_parts = parse_url($new_base);
		$base = false;
		if ($new_parts)
		{
			if (isset($new_parts['path']))
			{
				$base = true;
			}
		}
		if ($url_parts)
		{
			$new_url = "";
			if (isset($new_parts['scheme']))
			{
				$new_url .= $new_parts['scheme'].'://';
			}
			if (isset($new_parts['host']))
			{
				$new_url .= $new_parts['host'];
			}
			if ($base)
			{
				$new_url .= $new_parts['path'];
			}
			if (isset($url_parts['path']))
			{
				if (!WSKO_Class_Helper::starts_with($new_url, '/'))
					$new_url .= '/';
				$new_url .= ltrim($base?WSKO_Class_Helper::trim_start($url_parts['path'], $new_parts['path']) : $url_parts['path'], '/');
			}
			if (isset($url_parts['query']))
			{
				$new_url .= '?'.$url_parts['query'];
			}
			if (isset($url_parts['fragment']))
			{
				$new_url .= '#'.$url_parts['fragment'];
			}
			return $new_url;
		}
		return $url;
	}
	public static function trim_start($string, $trim)
	{
		if (WSKO_Class_Helper::starts_with($string, $trim))
			return substr($string, strlen($trim));
		return $string;
	}
}
?>