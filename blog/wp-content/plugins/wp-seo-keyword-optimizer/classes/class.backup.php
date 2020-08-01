<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Backup
{
    public static function get_backups()
    {
        return get_option('wsko_backups');
    }

	public static function load_existing_configuration_backup($key)
	{
        $backup = WSKO_Class_Backup::get_configuration_backup($key);
		if ($backup)
		{
			WSKO_Class_Backup::load_configuration_backup($backup);
			return true;
		}
        return false;
    }

	public static function load_configuration_backup($backup)
	{
		if ($backup && isset($backup['version']) && version_compare($backup['version'], '2.1', '>='))
		{
            if (isset($backup['data'])) //general
            {
                $data = $backup['data'];
                $old_data = get_option('wsko_init');
                if ($old_data)
                    $data = WSKO_Class_Helper::merge_array_deep($data, $old_data); //$data = $data + $old_data;
                update_option('wsko_init', $data);
            }

            if (isset($backup['data_me'])) //metas
            {
				$data = $backup['data_me'];
				foreach ($data as $post => $p_data)
				{
					$old_data = WSKO_Class_Core::get_post_data($post);
					if ($old_data)
						$p_data = WSKO_Class_Helper::merge_array_deep($p_data, $old_data); //$data = $data + $old_data;
					WSKO_Class_Core::set_post_data($post, $p_data);
				}
            }

            if (isset($backup['data_re'])) //redirects
            {
                $data = $backup['data_re'];
                update_option('wsko_redirects', $data);
            }
		}
    }

	public static function backup_configuration($auto = false, $targets = array())
	{
		if (!is_array($targets))
			$targets = array();
			
		$backups = get_option('wsko_backups');
		if (!$backups)
			$backups = array();
		$backup_data_r = get_option('wsko_init');
		if ($backup_data_r && is_array($backup_data_r) && isset($backup_data_r['version']))
		{
			$backup_data = $backup_data_s = array('time' => WSKO_Class_Helper::get_current_time(), 'auto' => $auto, 'version' => WSKO_VERSION);
			if (!$targets || in_array('general', $targets))
			{
				//dont save these
				if (isset($backup_data_r['search']['se_token']))
					unset($backup_data_r['search']['se_token']);
				if (isset($backup_data_r['search']['an_token']))
					unset($backup_data_r['search']['an_token']);
				if (isset($backup_data_r['search']['an_profile']))
					unset($backup_data_r['search']['an_profile']);
	
				if (isset($backup_data_r['knowledge_base_categories']))
					unset($backup_data_r['knowledge_base_categories']);
				if (isset($backup_data_r['q_and_a_categories']))
					unset($backup_data_r['q_and_a_categories']);
	
				if (isset($backup_data_r['timeout_check']))
					unset($backup_data_r['timeout_check']);
				if (isset($backup_data_r['version']))
					unset($backup_data_r['version']);
				if (isset($backup_data_r['configured']))
					unset($backup_data_r['configured']);
				if (isset($backup_data_r['configured_prem']))
					unset($backup_data_r['configured_prem']);
				//--

                $backup_data['data'] = $backup_data_r;
                $backup_data_s['data'] = array();
			}
			
			if (!$targets || in_array('metas', $targets))
			{
				$backup_data_r = array();
				
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
						$data = WSKO_Class_Core::get_post_data($p);
						if ($data)
						{
							//dont save these
							if (isset($data['history']))
								unset($data['history']);
							//--

							$backup_data_r[$p] = $data;
						}
					}
					$offset++;
				}
				while ($query->post_count >= $step);

				$backup_data['data_me'] = $backup_data_r;
                $backup_data_s['data_me'] = array();
			}
			
			if (!$targets || in_array('redirects', $targets))
			{
				$backup_data_r = get_option('wsko_redirects');
				$backup_data['data_re'] = $backup_data_r;
                $backup_data_s['data_re'] = array();
            }
            $path = WSKO_Class_Helper::get_temp_dir('backups');
			if ($auto)
				$time = WSKO_Class_Helper::get_midnight();
			else
				$time = time();
            $backup_name = $path.'backup_'.date('d.m.Y-H:i', $time).'-'.time().'.bak';
            $backup_data['backup_name'] = $backup_name;
            $backup_data_s['backup_name'] = $backup_name;
            file_put_contents($backup_name, WSKO_Class_Helper::safe_json_encode($backup_data));
			$backups[$time] = $backup_data_s;
			update_option('wsko_backups', $backups);
		}
    }
    
    public static function update_backups()
    {
        $backups = get_option('wsko_backups');
        $backup_freq = intval(WSKO_Class_Core::get_setting('conf_backup_interval'));
        if (!$backup_freq || $backup_freq <= 0)
            $backup_freq = 1;
        $has_backup = false;
        if ($backups)
        {
            ksort($backups);
            foreach ($backups as $b)
            {
                if ($b['time'] < time()-(60*60*24*$backup_freq))
                    $has_backup = true;
            }
        }
        if (!$has_backup)
            WSKO_Class_Backup::backup_configuration(true);
        
        //expired backups
        $backups = get_option('wsko_backups');
        if ($backups)
        {
            ksort($backups);
            $c = 0;
            $max_b = intval(WSKO_Class_Core::get_setting('conf_backup_limit'));
            if (!$max_b || $max_b <= 0)
                $max_b = 7;
            foreach($backups as $k => $b)
            {
                if ($b['auto'])
                {
                    $c++;
                    if ($c > $max_b)
                    {
                        WSKO_Class_Backup::delete_configuration_backup($k);
                    }
                }
            }
            update_option('wsko_backups', $backups);
        }
    }
	
	public static function import_configuration_backup($data_str, $load = true)
	{
		if ($data_str)
		{
			$data = WSKO_Class_Helper::safe_json_decode($data_str, true);
			if ($data && is_array($data) && isset($data['time']) && isset($data['auto']) && isset($data['data']) && isset($data['key']) && isset($data['version']) && version_compare($data['version'], '2.1', '>='))
			{
				$key = $data['key'];
				unset($data['key']);
				if ($load)
				{
                    WSKO_Class_Backup::load_configuration_backup($data);
				}
				else //add
				{
                    $data_s = $data;
                    if (isset($data_s['data']))
                        $data_s['data'] = array();
                    if (isset($data_s['data_me']))
                        $data_s['data_me'] = array();
                    if (isset($data_s['data_re']))
                        $data_s['data_re'] = array();
                    $path = WSKO_Class_Helper::get_temp_dir('backups');
                    $backup_name = $path.'backup_'.date('d.m.Y-H:i', $data['time']).'-'.time().'.bak';
                    $data['backup_name'] = $backup_name;
                    $data_s['backup_name'] = $backup_name;
                    file_put_contents($backup_name, WSKO_Class_Helper::safe_json_encode($data));
					$backups = get_option('wsko_backups');
					if (!$backups)
						$backups = array();
                    $backups[$key] = $data_s;
					update_option('wsko_backups', $backups);
				}
				return true;
			}
		}
		return false;
	}
	
	public static function get_configuration_backup($key)
	{
		$backups = get_option('wsko_backups');
		if (isset($backups[$key]))
		{
			//return $backups[$key];
            return WSKO_Class_Helper::safe_json_decode(file_get_contents($backups[$key]['backup_name']), true);
		}
		return false;
	}
	
	public static function delete_configuration_backup($key)
	{
		$backups = get_option('wsko_backups');
		if (!$backups)
			$backups = array();
		if (isset($backups[$key]))
		{
            unlink($backups[$key]['backup_name']);
            unset($backups[$key]);
			update_option('wsko_backups', $backups);
		}
	}
    
    public static function clear_backups()
    {
        $backups = get_option('wsko_backups');
        foreach ($backups as $k => $b)
        {
            WSKO_Class_Backup::delete_configuration_backup($k);
        }
    }
}
?>