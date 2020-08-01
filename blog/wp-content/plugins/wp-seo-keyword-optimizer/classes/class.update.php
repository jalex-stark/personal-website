<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Update
{
	public static function check_version($force = false)
	{
		$version_pre = get_option('wsko_init');
		if ($version_pre && is_array($version_pre) && isset($version_pre['version']))
			$version_pre = $version_pre['version'];
		else
			$version_pre = false;
		
		$wsko_data = WSKO_Class_Core::get_data();
		if ($force || ($version_pre && version_compare($version_pre, WSKO_VERSION, '<')))
		{
			$update_needed = true;
			
			if ($version_pre)
			{
				$update_needed = false;
				if (version_compare($version_pre, '2.0', '<')) //Reinit below 2.0
				{
					$update_needed = true;
				}
				
				if (version_compare($version_pre, '2.0.4', '<'))
				{
					delete_option('wsko_cache_snapshot');
					wp_clear_scheduled_hook('wsko_cache_snapshot'); //remove old cache snapshot
					
					if (WSKO_Class_Core::is_configured())
						WSKO_Class_Crons::bind_daily_maintenance();
					
					WSKO_Class_Core::save_setting('auto_social_snippet', true);
					WSKO_Class_Core::save_setting('auto_social_thumbnail', true);
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}
				
				if (version_compare($version_pre, '2.0.5', '<'))
				{
					wp_clear_scheduled_hook('wsko_cache_expire');
					$onpage_data = WSKO_Class_Onpage::get_onpage_data();
					if (isset($onpage_data['priority_kewords']))
					{
						$onpage_data['priority_keywords'] = $onpage_data['priority_kewords'];
						unset($onpage_data['priority_kewords']);
						WSKO_Class_Onpage::set_onpage_data($onpage_data);
					}
					
					wp_clear_scheduled_hook('wsko_check_timeout');
					WSKO_Class_Core::save_setting('auto_post_slug_redirects', true);
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}
				
				if (version_compare($version_pre, '2.0.6', '<'))
				{
					$gen_params = WSKO_Class_Onpage::get_sitemap_params();
					if (isset($gen_params['excluded_posts']))
						unset($gen_params['excluded_posts']);
					WSKO_Class_Onpage::set_sitemap_params($gen_params);
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}

				/*if (version_compare($version_pre, '2.0.8', '<')) //install time moved in 2.1.9
				{
					if (!isset($wsko_data['install_time']))
					{
						$wsko_data['install_time'] = time();
						WSKO_Class_Core::save_data($wsko_data);
						$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
					}
				}*/

				if (version_compare($version_pre, '2.1', '<'))
				{
					wsko_fs()->connect_again();
					//WSKO_Class_Crons::unbind_social_cache(); //removed in 2.1.9
					$onpage_data = WSKO_Class_Onpage::get_onpage_data();
					$has_u = false;
					if (isset($onpage_data['priority_keywords']) && $onpage_data['priority_keywords'])
					{
						foreach ($onpage_data['priority_keywords'] as $k => $pks)
						{
							foreach($pks as $k2 => $pk)
							{
								if (is_scalar($pk))
								{
									$onpage_data['priority_keywords'][$k][$k2] = array('prio' => $pk, 'similar' => array());
								}
							}
						}
						foreach ($onpage_data['priority_keywords'] as $post_id => $pks)
						{
							$data = WSKO_Class_Core::get_post_data($post_id);
							$data['priority_keywords'] = $pks;
							WSKO_Class_Core::set_post_data($post_id, $data);
						}
						unset($onpage_data['priority_keywords']);
						$has_u = true;
					}
					if (isset($onpage_data['post_metas']) && $onpage_data['post_metas'])
					{
						foreach ($onpage_data['post_metas'] as $post_id => $meta)
						{
							WSKO_Class_Onpage::set_meta_object($post_id, $meta, 'post_id');
						}
						unset($onpage_data['post_metas']);
						$has_u = true;
					}
					if (isset($onpage_data['post_term_metas']) && $onpage_data['post_term_metas'])
					{
						foreach ($onpage_data['post_term_metas'] as $tax_term_id => $meta)
						{
							WSKO_Class_Onpage::set_meta_object($tax_term_id, $meta, 'post_term');
						}
						unset($onpage_data['post_term_metas']);
						$has_u = true;
					}
					//case 'post_term': $from_m = ''; break;
					if (isset($onpage_data['global_analysis']))
					{
						unset($onpage_data['global_analysis']);
						if (wp_next_scheduled('wsko_onpage_analysis')) //rebind if set
							WSKO_Class_Crons::bind_onpage_analysis(true);
						$has_u = true;
					}
					if ($has_u)
						WSKO_Class_Onpage::set_onpage_data($onpage_data);

					WSKO_Class_Cache::clear_session_cache(false);
					WSKO_Class_Cache::delete_cache();
					WSKO_Class_Cache::check_database();
					WSKO_Class_Core::save_option('search_query_first_run', false);
					if (WSKO_Class_Core::is_configured())
						WSKO_Class_Crons::bind_keyword_update(true);
					//if (WSKO_Class_Core::get_setting('use_leightweight_cache')) //not needed after 2.1.9.9
						//WSKO_Class_Core::save_setting('use_lightweight_cache', true);
					if ($has_u)
						$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}

				if (version_compare($version_pre, '2.1.7', '<'))
				{
                    $redirect_data = WSKO_Class_Onpage::get_all_redirects();
                    if (!$redirect_data)
                    {
                        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
                        $redirect_data = array();
                        if (isset($onpage_data['redirects']) && $onpage_data['redirects'])
                        {
                            $redirect_data['redirects'] = $onpage_data['redirects'];
                            unset($onpage_data['redirects']);
                        }
                        if (isset($onpage_data['auto_redirects']) && $onpage_data['auto_redirects'])
                        {
                            $redirect_data['auto_redirects'] = $onpage_data['auto_redirects'];
                            unset($onpage_data['auto_redirects']);
                        }
                        if (isset($onpage_data['redirect_404']) && $onpage_data['redirect_404'])
                        {
                            $redirect_data['redirect_404'] = $onpage_data['redirect_404'];
                            unset($onpage_data['redirect_404']);
                        }
                        if (isset($onpage_data['technical_seo']) && $onpage_data['technical_seo'])
                        {
                            $redirect_data['post_redirects'] = array();
                            foreach ($onpage_data['technical_seo'] as $k => $data)
                            {
                                if (isset($data['redirect']) && $data['redirect'])
                                    $redirect_data['post_redirects'][$k] = $data['redirect'];
                            }
                            unset($onpage_data['technical_seo']);
                        }
                        WSKO_Class_Cache::save_wp_option('wsko_redirects', $redirect_data);
                    }

                    $backups = get_option('wsko_backups');
                    if ($backups)
                    {
						$path = WSKO_Class_Helper::get_temp_dir('backups');
						if (is_writable($path))
						{
							foreach ($backups as $k => $b)
							{
								if (!isset($backups[$k]['backup_name']))
								{
									$backup_name = $path.'backup_'.date('d.m.Y-H:i', $b['time']).'-'.time().'.bak';
									file_put_contents($backup_name, WSKO_Class_Helper::safe_json_encode($b));
									$backups[$k]['backup_name'] = $backup_name;
								}
							}
							update_option('wsko_backups', $backups); 
						}
                    }
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}

				if (version_compare($version_pre, '2.1.8', '<'))
				{
					$post_types_ex = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('onpage_exclude_post_types'));
					$co_post_types_ex = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_optimizer_post_types_exclude'));
					$post_types = WSKO_Class_Helper::get_public_post_types('names');
					$post_types_in = array();
					$co_post_types_in = array();
					foreach($post_types as $pt)
					{
						if (!$post_types_ex || !in_array($pt, $post_types_ex))
							$post_types_in[] = $pt;
						if (!$co_post_types_ex || !in_array($pt, $co_post_types_ex))
							$co_post_types_in[] = $pt;
					}
					WSKO_Class_Core::save_setting('onpage_include_post_types', implode(',', $post_types_in));
					WSKO_Class_Core::save_setting('content_optimizer_post_types_include', implode(',', $co_post_types_in));
					
					if (wp_next_scheduled('wsko_cache_keywords'))
						WSKO_Class_Crons::bind_keyword_update(true);
					
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}

				if (version_compare($version_pre, '2.1.9', '<'))
				{
					$options = WSKO_Class_Cache::get_wp_option('wsko_options');
					if ($options)
						WSKO_Class_Cache::save_wsko_option('wsko_options', $options);

					if (WSKO_Class_Search::get_se_token())
						WSKO_Class_Core::save_option('se_has_v1', true); //has old v1 creds
					WSKO_Class_Search::set_se_token(false);
					WSKO_Class_Crons::unbind_keyword_update();
					
					wp_clear_scheduled_hook('wsko_cache_social');

					WSKO_Class_Core::get_install_time(); //regenerate install time

					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}
				
				if (version_compare($version_pre, '2.1.9.5', '<'))
				{
					if (WSKO_Class_Search::get_se_token())
					{
						$profiles = WSKO_Class_Search::get_se_properties(false, true);
						if (is_array($profiles))
						{
							foreach ($profiles as $profile)
							{
								if (rtrim($profile['url'], '/') == WSKO_Class_Search::get_search_base(false))
								{
									WSKO_Class_Search::set_se_property($profile['url']);
								}
							}
						}
					}

					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}
				if (version_compare($version_pre, '2.1.9.6', '<'))
				{
					WSKO_Class_Onpage::clean_auto_redirects();
					
					$wsko_data = WSKO_Class_Core::get_data(true); //update fields for further updates
				}
			}
			
			WSKO_Class_Cache::update_database($version_pre);

			if ($update_needed)
			{
				$wsko_data = WSKO_Class_Core::get_data_default();
			}
			else
			{
				$wsko_data['version'] = WSKO_VERSION;
			}
			if (version_compare($version_pre, WSKO_VERSION, '<'))
				$wsko_data['version_pre'] = $version_pre;
			
			WSKO_Class_Core::save_data($wsko_data);
		}
	}
}
?>