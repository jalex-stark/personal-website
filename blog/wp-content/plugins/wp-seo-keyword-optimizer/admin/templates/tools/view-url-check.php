<?php
if (!defined('ABSPATH')) exit;

$url = isset($template_args['url']) ? $template_args['url'] : false;
$suppress_success = isset($template_args['suppress_success']) && $template_args['suppress_success'] ? true : false;
$status_check = isset($template_args['status_check']) && $template_args['status_check'] ? true : false;
if ($url)
{
	$redirects = WSKO_Class_Onpage::get_redirect_for_url($url, true);
	if ($redirects)
	{
		$is_admin = current_user_can('manage_options');
		$list = array();
		$count = 0;
		foreach($redirects as $re)
		{
			$count++;
			$str = "";
			switch ($re['type'])
			{
				//case '404': $str .= '404 to 301 - '; break;
				case 'custom': 
				$str .= sprintf(__('Custom Rule (%s) <span>%s to <i>%s</i></span>', 'wsko'), $re['data']['re']['comp'], $re['data']['re']['page'], $re['to']);
				if ($is_admin)
					$str .= WSKO_Class_Template::render_ajax_button('<i class="fa fa-times"></i>', 'remove_redirect', array('redirects' => $re['data']['key']), array(), true);
				break;
				case 'post': 
				$str .= sprintf(__('Post Redirect <span>To <i>%s</i></span>', 'wsko'), $re['to']);
				if ($is_admin)
					$str .= WSKO_Class_Template::render_ajax_button('<i class="fa fa-times"></i>', 'remove_page_redirect', array('post' => $re['data']['post']), array(), true);
				break;
				case 'auto_post': 
				$str .= sprintf(__('Auto Post Redirect <span><i>%s</i> to <i>%s</i></span>', 'wsko'), $re['from'], $re['to']);
				$str .= '';
				if ($is_admin)
					$str .= WSKO_Class_Template::render_ajax_button('<i class="fa fa-times"></i>', 'remove_auto_redirect', array('type' => $re['data']['type'], 'arg' => $re['data']['arg'], 'key' => $re['data']['key']), array(), true);
				break;
				case 'auto_post_type': 
				$str .= sprintf(__('Auto Post Type (%s) <span>%s to <i>%s</i></span>', 'wsko'), $re['data']['arg'], $re['data']['slug'], $re['to']);
				if ($is_admin)
					$str .= WSKO_Class_Template::render_ajax_button('<i class="fa fa-times"></i>', 'remove_auto_redirect', array('type' => $re['data']['type'], 'arg' => $re['data']['arg'], 'key' => $re['data']['slug']), array(), true);
				break;
			}
			$list[] = $str;
		}
		WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_co', 'post_redirects_data', array('count' => $count, 'url' => $url)), 'list' => $list, 'subnote' => wsko_loc('notif_co', 'post_redirects_data_sub'), 'notif-class' => 'wsko-post-redirects'));
	}
	else if (!$suppress_success)
	{
		WSKO_Class_Template::render_notification('success', array('msg' => wsko_loc('notif_co', 'post_redirects_data_empty', array('url' => $url))));
	}
	if ($status_check)
	{
		?><label><?=__('HTTP Code Trace', 'wsko')?></label><?php
		$codes = WSKO_Class_Helper::get_http_status_codes($url);
		if ($codes)
		{
			$last_url = false;
			?>
			<div class="wsko-http-trace">
				<?=__('Call', 'wsko')?> <i class="fa fa-angle-right fa-fw"></i> <?php
				$f = true;
				foreach ($codes as $code)
				{
					if ($code['code'] == 200)
						$color = 'green';
					else if ($code['code'] < 400)
						$color = 'yellow';
					else if ($code['code'] == 'loop')
						$color = 'blue';
					else
						$color = 'red';
					if (!$f)
						echo ' <i class="fa fa-angle-right fa-fw"></i> ';
					?>
					
					<p class=""><span class="badge wsko-badge wsko-<?=$color?>"><?=$code['code']?></span> <span><?=$code['url']?></span></p><?php
					$f = false;
					$last_url = $code;
				}
				if ($last_url)
				{
					?><br/><?=__('Effective URL:', 'wsko')?> <?=$last_url['url']?> (<?=$last_url['code']?>)<?php
				}
				?>
			</div>
			<?php
		}
		else
		{
			WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_co', 'status_code_error')));
		}
	}
}
?>