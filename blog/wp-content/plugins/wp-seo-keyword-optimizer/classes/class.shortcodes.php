<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Shortcodes
{
    public static function register_shortcodes()
    {
		if (!WSKO_Class_Core::is_beta())
			return;
		$inst = WSKO_Class_Shortcodes::get_instance();
        add_shortcode('bst_breadcrumbs', array($inst, 'shortcode_breadcrumbs'));
		add_shortcode('bst_content_table', array($inst, 'shortcode_content_table'));
		
		if (!is_admin())
		{
			if (WSKO_Class_Core::get_setting('activate_auto_content_table'))
			{
				add_filter('the_content', function($content){
					global $post;
					if (is_singular() && $post)
					{
						$excluded = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_table_exclude_posts'));
						if (!$excluded || !in_array($post->post_type, $excluded))
						{
							return do_shortcode('[bst_content_table]').$content;
						}
					}
					return $content;
				}, 999999);
			}
			if (WSKO_Class_Core::get_setting('activate_auto_breadcrumbs'))
			{
				$breadcrumb_type = WSKO_Class_Core::get_setting('auto_breadcrumb_target');
				switch ($breadcrumb_type)
				{
					case 'content_start':
					add_filter('the_content', function($content){
						return do_shortcode('[bst_breadcrumbs]').$content;
					}, 999999);
					break;
					case 'content_end':
					add_filter('the_content', function($content){
						return $content.do_shortcode('[bst_breadcrumbs]');
					}, 999999);
					break;
					case 'footer':
					add_action('get_footer', function(){
						echo do_shortcode('[bst_breadcrumbs]');
					});
					case 'custom':
					default:
					add_action('get_header', function(){
						$breadcrumb_type = WSKO_Class_Core::get_setting('auto_breadcrumb_target');
						echo ($breadcrumb_type == 'custom'?'<div id="wsko_initial_breadcrumbs">':'').do_shortcode('[bst_breadcrumbs]').($breadcrumb_type == 'custom'?'</div>':'');
					});
					break;
				}
				if ($breadcrumb_type == 'custom')
				{
					$target = WSKO_Class_Core::get_setting('auto_breadcrumb_target_custom');
					if ($target)
					{
						add_action('wp_head', function(){
							$target = WSKO_Class_Core::get_setting('auto_breadcrumb_target_custom');
							echo '<script type="text/javascript">if (typeof(jQuery) != "undefined") { jQuery(document).ready(function($){
								$("#wsko_initial_breadcrumbs").children().prependTo($(\''.$target.'\').first());
								$("#wsko_initial_breadcrumbs").remove();
							}); }</script>';
						});
					}
				}
			}
			add_action('wp_head', function(){
				$script = WSKO_Class_Core::get_setting('widgets_custom_script_head');
				$style = WSKO_Class_Core::get_setting('widgets_custom_style_head');
				if ($script || $style)
					echo '<!--BST Frontend Custom-->';
				if ($script)
					echo '<script type="text/javascript">'.$script.'</script>';
				if ($style)
					echo '<style>'.$style.'</style>';
			});
			add_action('wp_footer', function(){
				$script = WSKO_Class_Core::get_setting('widgets_custom_script_foot');
				$style = WSKO_Class_Core::get_setting('widgets_custom_style_foot');
				if ($script || $style)
					echo '<!--BST Frontend Custom-->';
				if ($script)
					echo '<script type="text/javascript">'.$script.'</script>';
				if ($style)
					echo '<style>'.$style.'</style>';
			});
		}
    }

    public static function shortcode_breadcrumbs($atts, $content = false)
    {
		$type = "";
		$arg = false;
		$breadcumbs = "";
		if (is_admin()) //preview
		{
			$type = isset($atts['override_type'])?$atts['override_type']:false;
			$arg = isset($atts['override_arg'])?$atts['override_arg']:false;
			if ($type == 'term')
			{
				if ($arg)
				{
					$arg = WSKO_Class_Helper::safe_explode(':', $arg);
					$arg = get_term(intval($arg[1]), $arg[0]);
					if (is_wp_error($arg))
						$arg = false;
				}
				else
					$type = "";
			}
		}
		else
		{
			$activate_all = WSKO_Class_Core::get_setting('activate_auto_breadcrumbs_all');
			if (is_front_page() || is_home())
			{
				if ($activate_all)
				{
					$type = "home";
				}
			}
			else if (is_post_type_archive())
			{
				if ($activate_all)
				{
					$type = "post_type";
					$arg = get_post_type();
				}
			}
			else if (is_tax() || is_category() || is_tag())
			{
				if ($activate_all)
				{
					$queried_object = get_queried_object();
					if ($queried_object && $queried_object->taxonomy)
					{
						$taxonomy = $queried_object->taxonomy;
						$term = $queried_object->term_id;
						if ($term)
						{
							$type = "term";
							$arg = $queried_object;
						}
					}
				}
			}
			else if (is_singular())
			{
				global $post;
				if ($post)
				{
					$arg = $post->ID;
					$type = "post";
				}
			}
		}
		if ($type)
			$breadcumbs = WSKO_Class_Shortcodes::get_breadcrumb($type, $arg, $atts);
		return $breadcumbs;
	}
	
    public static function shortcode_content_table($atts, $content = false)
    {
		$source = isset($atts['source'])?$atts['source']:WSKO_Class_Core::get_setting('content_table_post_source');
		$target = isset($atts['target'])?$atts['target']:WSKO_Class_Core::get_setting('content_table_post_target');
		$list_type = isset($atts['list_type'])?$atts['list_type']:WSKO_Class_Core::get_setting('content_table_list_type');
		$separator = isset($atts['separator'])?$atts['separator']:WSKO_Class_Core::get_setting('content_table_separator');
		$separator_end = isset($atts['separator_end'])?$atts['separator_end']:WSKO_Class_Core::get_setting('content_table_separator_end');
		$num_color = isset($atts['num_color'])?$atts['num_color']:WSKO_Class_Core::get_setting('content_table_num_color');
		$text_color = isset($atts['text_color'])?$atts['text_color']:WSKO_Class_Core::get_setting('content_table_text_color');
		$background_color = isset($atts['background_color'])?$atts['background_color']:WSKO_Class_Core::get_setting('content_table_background_color');
		$border_type = isset($atts['border_type'])?$atts['border_type']:WSKO_Class_Core::get_setting('content_table_border_type');
		$border_width = isset($atts['border_width'])?$atts['border_width']:WSKO_Class_Core::get_setting('content_table_border_width');
		$border_color = isset($atts['border_color'])?$atts['border_color']:WSKO_Class_Core::get_setting('content_table_border_color');
		$box_shadow = isset($atts['box_shadow'])&&$atts['box_shadow']&&$atts['box_shadow']!='false'?true:WSKO_Class_Core::get_setting('content_table_box_shadow');
		$box_shadow_color = isset($atts['box_shadow_color'])?$atts['box_shadow_color']:WSKO_Class_Core::get_setting('content_table_box_shadow_color');
		$heading = isset($atts['heading'])?$atts['heading']:WSKO_Class_Core::get_setting('content_table_heading');
		$append_h1 = isset($atts['append_h1'])?$atts['append_h1']:WSKO_Class_Core::get_setting('content_table_append_h1');
		
		if (!$separator)
			$separator = ".";
		if (!$num_color)
			$num_color = "#000";
		if (!$text_color)
			$text_color = "#000";
		if (!$background_color)
			$background_color = "#FFF";
		if (!$border_type)
			$border_type = "solid";
		if (!$border_width)
			$border_width = "1";
		if (!$border_color)
			$border_color = "#EEE";
		if (!$box_shadow_color)
			$box_shadow_color = "#EEE";
		$id = WSKO_Class_Helper::get_unique_id('bst_content_table_');
		$content_table = '<div id="'.$id.'" class="bst-content-table" '.($append_h1?'data-appendh1="true"':'').' '.($heading?'data-heading="'.$heading.'"':'').' '.($source?'data-source="'.$source.'"':'').' '.($target?'data-target="'.$target.'"':'').' '.($list_type?'data-type="'.$list_type.'"':'').'>
			<style>
				#'.$id.' li:before {content: counters(wsko-table-content-item, "'.$separator.'") "'.($separator_end?$separator:' ').'"!important; }
				#'.$id.' li a.bst-content-table-link {color:'.$text_color.'}
				#'.$id.' li.bst-content-table-row:before {color:'.$num_color.'}
				#'.$id.' {background-color:'.$background_color.';border: '.$border_width.'px '.$border_type.' '.$border_color.';'.($box_shadow?'box-shadow:0 0 15px 4px '.$box_shadow_color.'0d;':'').'}
				
			</style>
		</div>';
		return $content_table;
	}

	public static function get_breadcrumb($type, $arg, $args = array())
	{
		$breadcumbs = array();
		$breadcrumbs_meta = array();

		$prefix = isset($args['prefix'])?$args['prefix']:WSKO_Class_Core::get_setting('breadcrumb_prefix');
		$home_format = isset($args['home_format'])?$args['home_format']:WSKO_Class_Core::get_setting('breadcrumb_home_format');
		$separator = isset($args['separator'])?$args['separator']:WSKO_Class_Core::get_setting('breadcrumb_separator');
		$archive_format = isset($args['archive_format'])?$args['archive_format']:WSKO_Class_Core::get_setting('breadcrumb_archive_format');
		$tax_format = isset($args['tax_format'])?$args['tax_format']:WSKO_Class_Core::get_setting('breadcrumb_tax_format');
		$post_format = isset($args['post_format'])?$args['post_format']:WSKO_Class_Core::get_setting('breadcrumb_post_format');
		$suffix = isset($args['suffix'])?$args['suffix']:WSKO_Class_Core::get_setting('breadcrumb_suffix');
		$link_last = isset($args['link_last'])?$args['link_last']:WSKO_Class_Core::get_setting('breadcrumb_last_mode');

		if (!$separator)
			$separator = " > ";
		if (!$archive_format)
			$archive_format = 'Archive for %title%';
		if (!$tax_format)
			$tax_format = '%tax%: %term%';
		if (!$post_format)
			$post_format = '%title%';
		$text = ($home_format?$home_format:'Homepage');
		$text = WSKO_Class_Onpage::calculate_meta($text, array());
		$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-main" href="'.home_url().'">'.$text.'</a>';
		$breadcrumbs_meta[] = '{
			"@type": "ListItem",
			"position": 1,
			"item":
			{
				"@id": "'.home_url().'",
				"name": "'.$text.'"
			}
		}';
		if ($type && $arg)
		{
			switch ($type)
			{
				case 'term':
				$term = $arg;
				if ($term_link = get_term_link($term) != home_url())
				{
					$taxonomy = get_taxonomy($term->taxonomy);
					$tax_labels = get_taxonomy_labels($taxonomy);

					$text = WSKO_Class_Onpage::calculate_meta($tax_format, array('tax' => $term->taxonomy, 'term' => $term->term_id));
					$text = str_replace(array('%tax%', '%term%'), array($tax_labels->name, $term->name), $text);
					//$text = str_replace(array('%tax%', '%term%'), array($tax_labels->name, $term->name), $tax_format);
					if ($link_last == 'hide')
					{ }
					else if ($link_last == 'show_link')
						$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-tax" href="'.$term_link.'">'.$text.'</a>';
					else
						$breadcumbs[] = $text;
					$breadcrumbs_meta[] = '{
						"@type": "ListItem",
						"position": 2,
						"item":
						{
							"@id": "'.$term_link.'",
							"name": "'.$text.'"
						}
					}';
					break;
				}
				break;
				case 'post_type':
				if (($archive_link = get_post_type_archive_link($arg)) != home_url())
				{
					$post_type = get_post_type_object($arg);
					$pt_labels = get_post_type_labels($post_type);
					$text = WSKO_Class_Onpage::calculate_meta($archive_format, array());
					$text = str_replace(array('%title%'), array($pt_labels->name), $text);
					//$text = str_replace(array('%title%'), array($pt_labels->name), $archive_format);
					if ($link_last == 'hide')
					{ }
					else if ($link_last == 'show_link')
						$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-archive" href="'.$archive_link.'">'.$text.'</a>';
					else
						$breadcumbs[] = $text;
					$breadcrumbs_meta[] = '{
						"@type": "ListItem",
						"position": 2,
						"item":
						{
							"@id": "'.$archive_link.'",
							"name": "'.$text.'"
						}
					}';
					break;
				}
				break;
				case 'post':
				$post_type = get_post_type($arg);
				if (($archive_link = get_post_type_archive_link($post_type)) != home_url())
				{
					$post_type = get_post_type_object($post_type);
					$pt_labels = get_post_type_labels($post_type);
					$text = WSKO_Class_Onpage::calculate_meta($archive_format, array('post' => $arg));
					$text = str_replace(array('%title%'), array($pt_labels->name), $text);
					//$text = str_replace(array('%title%'), array($pt_labels->name), $archive_format);
					$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-archive" href="'.$archive_link.'">'.$text.'</a>';
					$breadcrumbs_meta[] = '{
						"@type": "ListItem",
						"position": 2,
						"item":
						{
							"@id": "'.$archive_link.'",
							"name": "'.$text.'"
						}
					}';
				}
					
				$taxonomy = false;
				$post_tax_rel = WSKO_Class_Core::get_setting('breadcrumb_post_tax_relations');
				if ($post_tax_rel && isset($post_tax_rel[$post_type->name]))
				{
					$tax = $post_tax_rel[$post_type->name];
					$terms = wp_get_post_terms($arg, $tax);
					if ($terms)
					{
						$terms = array_reverse($terms);
						$first_term = array_pop($terms);
						if ($first_term && ($term_link = get_term_link($first_term)) != home_url())
						{
							$taxonomy = get_taxonomy($tax);
							$tax_labels = get_taxonomy_labels($taxonomy);
							$text = WSKO_Class_Onpage::calculate_meta($tax_format, array('tax' => $first_term->taxonomy, 'term' => $first_term->term_id, 'post' => $arg));
							//$text = str_replace(array('%tax%', '%term%'), array($tax_labels->name, $first_term->name), $tax_format);
							$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-tax" href="'.$term_link.'">'.$text.'</a>';
							$breadcrumbs_meta[] = '{
								"@type": "ListItem",
								"position": 3,
								"item":
								{
									"@id": "'.$term_link.'",
									"name": "'.$text.'"
								}
							}';
						}
					}
				}
				
				$post_link = get_permalink($arg);
				$text = WSKO_Class_Onpage::calculate_meta($post_format, array('tax' => $first_term->taxonomy, 'term' => $first_term->term_id, 'post' => $arg));
				$text = str_replace(array('%title%'), array(get_the_title($arg)), $text);
				//$text = str_replace(array('%title%'), array(get_the_title($arg)), $post_format);
				if ($link_last == 'hide')
				{ }
				else if ($link_last == 'show_link')
					$breadcumbs[] = '<a class="bst-breadcrumb-link bst-breadcrumb-link-post" href="'.$post_link.'">'.$text.'</a>';
				else
					$breadcumbs[] = $text;
				$breadcrumbs_meta[] = '{
					"@type": "ListItem",
					"position": '.($taxonomy?'4':'3').',
					"item":
					{
						"@id": "'.$post_link.'",
						"name": "'.$text.'"
					}
				}';
				break;
			}
		}
		return '<div class="bst-breadcrumb">
		<script type="application/ld+json">
		{
		 	"@context": "http://schema.org",
		 	"@type": "BreadcrumbList",
		 	"itemListElement": ['.implode(',', $breadcrumbs_meta).']
		}
		</script>
		'.($prefix?$prefix:'').implode('<p class="bst-breadcrumb-separator">'.htmlentities($separator).'</p>',$breadcumbs).($suffix?$suffix:'').'</div>';
	}

	//Singleton
	static $instance;
	
	public static function get_instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}
}
?>