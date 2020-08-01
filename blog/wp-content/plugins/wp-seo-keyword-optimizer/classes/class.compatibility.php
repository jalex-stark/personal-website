<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Compatibility
{
	static $active_plugins_cache;
	static $seo_plugins;

	public static function get_seo_plugins()
	{
		if (static::$seo_plugins)
			return static::$seo_plugins;
		return static::$seo_plugins = $seo_plugins = array(
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => array('title' => 'All in One SEO', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'))),
			'autodescription/autodescription.php' => array('title' => 'The SEO Framework', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'))),
			'headspace2/headspace.php' => array('title' => 'Headspace2', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'))),
			'platinum-seo-pack/platinum_seo_pack.php' => array('title' => 'Platinum SEO', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'))),
			'seo-ultimate/seo-ultimate.php' => array('title' => 'SEO Ultimate', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'))),
			'wordpress-seo/wp-seo.php' => array('title' => 'Yoast SEO', 'options' => array('hide_category_slug' => __('General - Hide "category" base', 'wsko'), 'post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'), 'post_focus_kw' => __('Post - Focus Keywords', 'wsko'), 'post_type_meta_title' => __('Post Types - Meta Title', 'wsko'), 'post_type_meta_desc' => __('Post Types - Meta Description', 'wsko'), 'post_type_meta_robots' => __('Post Types - Meta Noindex', 'wsko'), 'tax_meta_title' => __('Taxonomy - Meta Title', 'wsko'), 'tax_meta_desc' => __('Taxonomy - Meta Description', 'wsko'))),
			'wordpress-seo-premium/wp-seo-premium.php' => array('title' => 'Yoast SEO (Premium)', 'options' => array('hide_category_slug' => __('General - Hide "category" base', 'wsko'), 'post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'), 'post_meta_ni_nf' => __('Post - Meta Noindex/Nofollow', 'wsko'), 'post_focus_kw' => __('Post - Focus Keywords', 'wsko'), 'post_type_meta_title' => __('Post Types - Meta Title', 'wsko'), 'post_type_meta_desc' => __('Post Types - Meta Description', 'wsko'),  'post_type_meta_robots' => __('Post Types - Meta Noindex', 'wsko'), 'tax_meta_title' => __('Taxonomy - Meta Title', 'wsko'), 'tax_meta_desc' => __('Taxonomy - Meta Description', 'wsko'), 'redirects' => __('Redirects (regex currently not supported)', 'wsko'))),
			'wp-meta-seo/wp-meta-seo.php' => array('title' => 'WP Meta SEO', 'options' => array('post_meta_title' => __('Post - Meta Titles', 'wsko'), 'post_meta_desc' => __('Post - Meta Descriptions', 'wsko'))),
	
			'add-meta-tags/add-meta-tags.php' => array('title' => 'Add Meta Tags', 'options' => false),
			'squirrly-seo/squirrly.php' => array('title' => 'SEO Squirrly', 'options' => false),
		);
	}

	public static function get_active_seo_plugins()
	{
		if (static::$active_plugins_cache)
			return static::$active_plugins_cache;
		
		$res = array();
		$seo_plugins = WSKO_Class_Compatibility::get_seo_plugins();
		foreach($seo_plugins as $k => $pl)
		{
			if (is_plugin_active($k))
				$res[$pl['title']] = '<a href="'.admin_url('plugins.php').'" target="_blank">'.$pl['title'].'</a>';
		}
		static::$active_plugins_cache = $res;
		return $res;
	}
	
	public static function is_seo_plugin_active($key = false)
	{
		if ($key)
		{
			switch ($key)
			{
				case 'yoast': return is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php');
			}
			return false;
		}
		else
			return WSKO_Class_Compatibility::get_active_seo_plugins() ? true : false;
	}
	
	public static function get_importable_plugins($full = false)
	{
		$int = new static();
		$res = array();
		$res_f = array();
		$plugins_path = WP_PLUGIN_DIR.'/';
		$seo_plugins = WSKO_Class_Compatibility::get_seo_plugins(); //merge plugins
		foreach ($seo_plugins as $k => $pl)
		{
			if ($pl['options'])
			{
				if (file_exists($plugins_path.$k))
				{
					$res[$k] = array('title' => $pl['title'], 'options' => $pl['options'], 'active' => is_plugin_active($k));
				}
				else if ($full)
				{
					$res_f[$k] = array('title' => $pl['title'], 'options' => $pl['options'], 'active' => false);
				}
			}
		}
		if ($full)
			return array('installed' => $res, 'other' => $res_f);
		return $res;
	}
	
	public static function import_plugin($plugin, $options)
	{
		$post_types = WSKO_Class_Helper::get_public_post_types('names');
		$taxs = WSKO_Class_Helper::get_public_taxonomies('names');

		$stats = array('title' => 0, 'desc' => 0, 'robots' => 0, 'kw' => 0, 'redirects' => 0, 'pt_title' => 0, 'pt_desc' => 0, 'pt_robots' => 0, 'tax_title' => 0, 'tax_desc' => 0);
		$meta_title = false;
		$meta_desc = false;
		$meta_noindex = false;
		$meta_nofollow = false;
		$focus_kw = false;
		switch ($plugin)
		{
			case 'all-in-one-seo-pack/all_in_one_seo_pack.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "_aioseop_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_aioseop_description";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "_aioseop_noindex";
					$meta_nofollow = "_aioseop_nofollow";
				}
			break;
			case 'headspace2/headspace.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "_headspace_page_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_headspace_description";
			break;
			case 'platinum-seo-pack/platinum_seo_pack.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "description";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "robotsmeta";
					$meta_nofollow = "robotsmeta";
				}
			break;
			case 'autodescription/autodescription.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "_genesis_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_genesis_description";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "_genesis_noindex";
					$meta_nofollow = "_genesis_nofollow";
				}
			break;
			case 'seo-ultimate/seo-ultimate.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "_su_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_su_description";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "_su_meta_robots_noindex";
					$meta_nofollow = "_su_meta_robots_nofollow";
				}
			break;
			case 'wordpress-seo/wp-seo.php':
				if (in_array('hide_category_slug', $options))
				{
					$meta_info = get_option('wpseo_titles');
					if ($meta_info && is_array($meta_info) && isset($meta_info['stripcategorybase']) && $meta_info['stripcategorybase'])
						WSKO_Class_Core::save_setting('hide_category_slug', true);
				}
				if (in_array('post_meta_title', $options))
					$meta_title = "_yoast_wpseo_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_yoast_wpseo_metadesc";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "_yoast_wpseo_meta-robots-noindex";
					$meta_nofollow = "_yoast_wpseo_meta-robots-nofollow";
				}
				if (in_array('post_focus_kw', $options))
					$focus_kw = "_yoast_wpseo_focuskw";

				$data = get_option('wpseo_titles');
				if ($data)
				{
					$yoast_seps = array(
						'sc-dash'   => '-',
						'sc-ndash'  => '&ndash;',
						'sc-mdash'  => '&mdash;',
						'sc-colon'  => ':',
						'sc-middot' => '&middot;',
						'sc-bull'   => '&bull;',
						'sc-star'   => '*',
						'sc-smstar' => '&#8902;',
						'sc-pipe'   => '|',
						'sc-tilde'  => '~',
						'sc-laquo'  => '&laquo;',
						'sc-raquo'  => '&raquo;',
						'sc-lt'     => '&lt;',
						'sc-gt'     => '&gt;'
					);
					if (isset($data['separator']) && isset($yoast_seps[$data['separator']]))
						$yoast_sep = $yoast_seps[$data['separator']];
					else
						$yoast_sep = '|';
					foreach ($post_types as $pt)
					{
						$data_pt = array();
						if (in_array('post_type_meta_title', $options) && isset($data['title-'.$pt]))
						{
							$stats['pt_title']++;
							$data_pt['title'] = WSKO_Class_Compatibility::map_markups($data['title-'.$pt], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('post_type_meta_desc', $options) && isset($data['metadesc-'.$pt]))
						{
							$stats['pt_desc']++;
							$data_pt['desc'] = WSKO_Class_Compatibility::map_markups($data['metadesc-'.$pt], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('post_type_meta_robots', $options) && isset($data['noindex-'.$pt]))
						{
							$stats['pt_robots']++;
							$data_pt['robots'] = $data['noindex-'.$pt] ? 2 : 0;
						}
						if ($data_pt)
							WSKO_Class_Onpage::set_meta_object($pt, $data_pt, 'post_type');
					}
					foreach ($taxs as $tax)
					{
						$data_pt = array();
						if (in_array('tax_meta_title', $options) && isset($data['title-tax-'.$tax]))
						{
							$stats['tax_title']++;
							$data_pt['title'] = WSKO_Class_Compatibility::map_markups($data['title-tax-'.$tax], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('tax_meta_desc', $options) && isset($data['metadesc-tax-'.$tax]))
						{
							$stats['tax_desc']++;
							$data_pt['desc'] = WSKO_Class_Compatibility::map_markups($data['metadesc-tax-'.$tax], 'yoast', array('sep' => $yoast_sep));
						}
						if ($data_pt)
							WSKO_Class_Onpage::set_meta_object($tax, $data_pt, 'post_tax');
					}
				}
			break;
			case 'wordpress-seo-premium/wp-seo-premium.php':
				if (in_array('hide_category_slug', $options))
				{
					$meta_info = get_option('wpseo_titles');
					if ($meta_info && is_array($meta_info) && isset($meta_info['stripcategorybase']) && $meta_info['stripcategorybase'])
						WSKO_Class_Core::save_setting('hide_category_slug', true);
				}
				if (in_array('post_meta_title', $options))
					$meta_title = "_yoast_wpseo_title";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_yoast_wpseo_metadesc";
				if (in_array('post_meta_ni_nf', $options))
				{
					$meta_noindex = "_yoast_wpseo_meta-robots-noindex";
					$meta_nofollow = "_yoast_wpseo_meta-robots-nofollow";
				}
				if (in_array('post_focus_kw', $options))
					$focus_kw = "_yoast_wpseo_focuskw";
				if (in_array('redirects', $options))
				{
					$plain_redirects = get_option('wpseo-premium-redirects-export-plain');
					//$regex_redirects = get_option('wpseo-premium-redirects-export-regex');
					if ($plain_redirects)
					{
						foreach ($plain_redirects as $from => $to)
						{
							WSKO_Class_Onpage::add_redirect(WSKO_Class_Helper::format_url($from), 'exact', WSKO_Class_Helper::format_url($to['url']), 'exact', $to['type']);
							$stats['redirects']++;
						}
					}
				}
				
				$data = get_option('wpseo_titles');
				if ($data)
				{
					$yoast_seps = array(
						'sc-dash'   => '-',
						'sc-ndash'  => '&ndash;',
						'sc-mdash'  => '&mdash;',
						'sc-colon'  => ':',
						'sc-middot' => '&middot;',
						'sc-bull'   => '&bull;',
						'sc-star'   => '*',
						'sc-smstar' => '&#8902;',
						'sc-pipe'   => '|',
						'sc-tilde'  => '~',
						'sc-laquo'  => '&laquo;',
						'sc-raquo'  => '&raquo;',
						'sc-lt'     => '&lt;',
						'sc-gt'     => '&gt;'
					);
					if (isset($data['separator']) && isset($yoast_seps[$data['separator']]))
						$yoast_sep = $yoast_seps[$data['separator']];
					else
						$yoast_sep = '|';
					foreach ($post_types as $pt)
					{
						$data_pt = array();
						if (in_array('post_type_meta_title', $options) && isset($data['title-'.$pt]))
						{
							$stats['pt_title']++;
							$data_pt['title'] = WSKO_Class_Compatibility::map_markups($data['title-'.$pt], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('post_type_meta_desc', $options) && isset($data['metadesc-'.$pt]))
						{
							$stats['pt_desc']++;
							$data_pt['desc'] = WSKO_Class_Compatibility::map_markups($data['metadesc-'.$pt], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('post_type_meta_robots', $options) && isset($data['noindex-'.$pt]))
						{
							$stats['pt_robots']++;
							$data_pt['robots'] = $data['noindex-'.$pt] ? 2 : 0;
						}
						if ($data_pt)
							WSKO_Class_Onpage::set_meta_object($pt, $data_pt, 'post_type');
					}
					foreach ($taxs as $tax)
					{
						$data_pt = array();
						if (in_array('tax_meta_title', $options) && isset($data['title-tax-'.$tax]))
						{
							$stats['tax_title']++;
							$data_pt['title'] = WSKO_Class_Compatibility::map_markups($data['title-tax-'.$tax], 'yoast', array('sep' => $yoast_sep));
						}
						if (in_array('tax_meta_desc', $options) && isset($data['metadesc-tax-'.$tax]))
						{
							$stats['tax_desc']++;
							$data_pt['desc'] = WSKO_Class_Compatibility::map_markups($data['metadesc-tax-'.$tax], 'yoast', array('sep' => $yoast_sep));
						}
						if ($data_pt)
							WSKO_Class_Onpage::set_meta_object($tax, $data_pt, 'post_tax');
					}
				}
			break;
			case 'wp-meta-seo/wp-meta-seo.php':
				if (in_array('post_meta_title', $options))
					$meta_title = "_metaseo_metatitle";
				if (in_array('post_meta_desc', $options))
					$meta_desc = "_metaseo_metadesc";
			break;
		}
		
		$offset = 0;
		$step = 500; //batch queries
		do
		{
			$query = new WP_Query(array(
				'posts_per_page' => $step,
				'offset' => $offset * $step,
				'post_type' => 'any',
				'fields' => 'ids'
			));
			$results = $query->posts;
			foreach ($results as $p)
			{
				$res = array();
				if ($meta_title)
				{
					$t = get_post_meta($p, $meta_title, true);
					if ($t)
					{
						$res['title'] =  $t;
						$stats['title']++;
					}
				}
				if ($meta_desc)
				{
					$t = get_post_meta($p, $meta_desc, true);
					if ($t)
					{
						$res['desc'] =  $t;
						$stats['desc']++;
					}
				}
				if ($meta_noindex || $meta_nofollow)
				{
					$t = false;
					if ($meta_noindex)
					{
						$t2 = get_post_meta($p, $meta_noindex, true);
						if ($t2  && is_string($t2))
							$t2 = $t2 == "true" || $t2 == "1" || $t2 == "on" || strpos($t2, 'noindex') !== false;
					}
					if ($meta_nofollow)
					{
						$t3 = get_post_meta($p, $meta_nofollow, true);
						if ($t3 && is_string($t3))
							$t3 = $t3 == "true" || $t2 == "1" || $t2 == "on" || strpos($t3, 'nofollow') !== false;
					}
					
					if ($meta_noindex && $meta_nofollow)
						$t = $t2 ? ($t3 ? 3 : 2) : ($t3 ? 1 : 0);
					else if ($meta_noindex)
						$t = ($t2 ? 2 : 0);
					else if ($meta_nofollow)
						$t = ($t3 ? 1 : 0);
						
					if ($t !== false)
					{
						$res['robots'] = $t;
						$stats['robots']++;
					}
				}
				if ($focus_kw)
				{
					$t = get_post_meta($p, $focus_kw, true);
					if ($t)
					{
						WSKO_Class_Onpage::add_priority_keyword($p, $t, 1);
						$stats['kw']++;
					}
				}
				
				if ($res)
				{
					$res_o = WSKO_Class_Onpage::get_meta_object($p, 'post_id'); 
					if ($res_o)
						$res = $res + $res_o;
					WSKO_Class_Onpage::set_meta_object($p, $res, 'post_id'); 
				}
			}
			$offset++;
		}
		while ($query->post_count >= $step);
		return $stats;
	}

	public static function has_post_builder($post)
	{
		try
		{
			global $wp_version;
			if (version_compare($wp_version, '5.0', '>='))
			{
				if (use_block_editor_for_post($post->ID))
				{
					return 'gutenberg';
				}
			}
			else if (function_exists('is_gutenberg_page') && is_gutenberg_page()) //
			{
				return 'gutenberg';
			}
			if (is_plugin_active('visualcomposer/plugin-wordpress.php') && function_exists('vchelper'))  //Visual Composer
			{
				$editorPostTypeHelper = vchelper('AccessEditorPostType');
				if ($editorPostTypeHelper && $editorPostTypeHelper->isEditorEnabled($post->post_type))
					return 'visual_composer';
			}
			if (is_plugin_active('js_composer/js_composer.php') && get_post_meta($post->ID, '_wpb_vc_js_status', true))  //Visual Composer
			{
				return 'wpvb_visual_composer';
			}
			if (class_exists('AviaBuilder') && 'active' == AviaBuilder::instance()->get_alb_builder_status($post->ID)) //Avia Builder (Enfold Theme)
			{
				return 'avia';
			}
			if (function_exists('et_builder_enabled_for_post') && et_builder_enabled_for_post($post->ID)) //Divi Builder
			{
				return 'divi';
			}
			if (is_plugin_active('fusion-builder/fusion-builder.php') && class_exists('FusionBuilder')) //Fusion Builder
			{
				/*START*/
				//copied from fusion-builder/fusion-builder.php, because the method "allowed_post_types()" is private
				//TODO: keep updated
				$options = get_option('fusion_builder_settings', array());

				if (!empty($options) && isset($options['post_types']))
				{
					$post_types = (' ' === $options['post_types']) ? array() : $options['post_types'];
					$post_types = apply_filters('fusion_builder_allowed_post_types', $post_types);
				}
				else 
					$post_types = FusionBuilder::default_post_types();
				/*END*/

				if (in_array($post->post_type, $post_types))// && 'active' === get_post_meta($post->ID, 'fusion_builder_status', true))
					return 'fusion';
			}
			if ((is_plugin_active('beaver-builder-lite-version/fl-builder.php') || is_plugin_active('bb-plugin/fl-builder.php')) &&
				class_exists('FLBuilderModel') && FLBuilderModel::is_builder_enabled($post->ID)) //Beaver Builder (Free/Pro)
			{
				return 'beaver';
			}
			if (class_exists('\Elementor\User') && \Elementor\User::is_current_user_can_edit($post->ID)) //Elementor Builder
			{
				return 'elementor';
			}
		}
		catch(\Exception $ex)
		{
			
		}
		return false;
	}

	public static function map_markups($string, $type, $args = array())
	{
		$mappings = false;
		$regex_mappings = false;
		switch ($type)
		{
			case 'yoast': 
			$mappings = array(
				'%%title%%' => '%post:post_title%',
				//'%%title%%' => '%post:post_content%',
				'%%excerpt%%' => '%post:post_excerpt%',
				//'%%title%%' => '%post:post_author%',

				'%%term_title%%' => '%term:term_title%',
				'%%term_description%%' => '%term:term_desc%',

				'%%sitename%%' => '%site:blog_name%',
				'%%sitedesc%%' => '%site:blog_tagline%',
				'%%sep%%' => $args['sep']
			);
			break;
		}
		if ($mappings)
			$string = str_ireplace(array_keys($mappings), $mappings, $string);
		if ($regex_mappings)
		{

		}
		return $string;
	}

	public static function get_meta_object_ext($identifier, $from, $source)
	{
		$metas = false;
		switch ($source)
		{
			case 'yoast':
			if ($from == 'post_id')
			{
				$title = get_post_meta($identifier, "_yoast_wpseo_title", true);
				$desc = get_post_meta($identifier, "_yoast_wpseo_metadesc", true);
				if ($title || $desc)
				{
					$metas = array('title' => WSKO_Class_Compatibility::map_markups($title, 'yoast'), 'desc' => WSKO_Class_Compatibility::map_markups($desc, 'yoast'));
				}
			}
			else if ($from == 'post_term')
			{
				/*$title = get_term_meta($identifier, "_yoast_wpseo_title", true);
				$desc = get_term_meta($identifier, "_yoast_wpseo_metadesc", true);
				if ($title || $desc)
				{
					$metas = array('title' => $title, 'desc' => $desc);
				}*/
			}
			break;
		}
		if ($metas)
		{
			foreach ($metas as $k => $m)
			{
				if (!$m)
					unset($metas[$k]);
			}
			return $metas;
		}
		return false;
	}
	
	public static function get_keywords_ext($post_id, $source)
	{
		switch ($source)
		{
			case 'yoast':
			$kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
			if ($kw)
				return array($kw => $kw);
			break;
		}
		return array();
	}

	public static function get_seo_plugin_preview($type)
	{
		$source = false;
		switch ($type)
		{
			case 'metas':
			if (WSKO_Class_Compatibility::is_seo_plugin_active('yoast'))
			{
				$source = 'yoast';
			}
			break;
		}
		return $source;
	}

	public static function register_shortcodes_comp()
	{
		global $wsko_comp_shortcodes_flag;
		if ($wsko_comp_shortcodes_flag)
			return;
		$wsko_comp_shortcodes_flag = true;
		if (is_admin())
		{
			 //visual composer shortcodes for admin
			if (class_exists('WPBMap'))
				WPBMap::addAllMappedShortcodes();
		}
	}

	public static function render_compatibility_notices()
	{
		if (function_exists('w3_instance'))
		{
			$config = w3_instance('W3_Config');
			if ($config->get_integer('dbcache.enabled')) //W3 Total Database Cache active
			{
				$settings = array_map('trim', $config->get_array('dbcache.reject.sql'));
				$tables = array('wsko_');
				$diff = array_diff($tables, $settings);
				if ($diff)
				{
					WSKO_Class_Template::render_notification('error', array(
						'msg' => wsko_loc('notif', 'comp_w3tc_db', array('w3tc_link' => '<a href="'.admin_url('admin.php?page=w3tc_dbcache').'">W3TC Options</a>')), 'subnote' => wsko_loc('notif', 'comp_w3tc_db_sub'),
						'list' => $diff
					));
				}
			}
			if ($config->get_integer('objectcache.enabled_for_wp_admin'))
			{
				WSKO_Class_Template::render_notification('error', array(
					'msg' => wsko_loc('notif', 'comp_w3tc_obj', array('w3tc_link' => '<a href="'.admin_url('admin.php?page=w3tc_objectcache').'">W3TC Options</a>')), 'subnote' => wsko_loc('notif', 'comp_w3tc_obj_sub')
				));
			}
		}
	}
}
?>