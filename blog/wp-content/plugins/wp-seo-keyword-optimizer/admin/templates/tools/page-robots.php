<?php
if (!defined('ABSPATH')) exit;

$htaccess_path = get_home_path().'.htaccess';
$htaccess_exists = file_exists($htaccess_path);
$htaccess_unlocked = WSKO_Class_Core::get_setting('activate_editor_htaccess');
$robots_path = get_home_path().'robots.txt';
$robots_exists = file_exists($robots_path);
$robots_unlocked = WSKO_Class_Core::get_setting('activate_editor_robots');
$backup_path = WSKO_Class_Helper::get_temp_dir('backups');

WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'editors_main'),
	 'list' => wsko_loc('notif_tools', 'editors_main_list', array('backup_dir' => WSKO_Class_Helper::get_temp_dir('backups')))
));

?>
<div class="row">
<?php if (current_user_can('manage_options')) { ?>
	<div class="col-sm-12 col-xs-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="active"><a href="#wsko_file_htaccess_tab" data-toggle="tab"><?=__( '.htaccess', 'wsko' ) ?></a></li>
			<li><a href="#wsko_file_robots_tab" data-toggle="tab"><?=__( 'robots.txt', 'wsko' ) ?></a></li>
		</ul>
		<div class="tab-content">
			<div id="wsko_file_htaccess_tab" class="tab-pane fade in active">
				<div class="panel panel-default bsu-panel">
					<p class="panel-heading m0"><?=__('.htaccess')?></p>		
					<div class="panel-body">
						<?php 
						if (is_writable(get_home_path()) || $htaccess_exists)
						{
							if (is_writable($htaccess_path) || !$htaccess_exists)
							{
								if (is_readable($htaccess_path) || !$htaccess_exists)
								{
									if (!is_writable($backup_path))
									{
										WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'backup_unwritable', array('backup_dir' => $backup_path))));
									}
									/* <textarea id="wsko_htaccess_field"><?=$htaccess_exists ? file_get_contents($htaccess_path) : ''?></textarea> */
									WSKO_Class_Template::render_form(array('type' => 'textarea', /*'highlights' => WSKO_Class_Helper::get_code_highlighting_register('htaccess'),*/ 'title' => '.htaccess', 'subtitle' => '<p class="font-unimportant">' . $htaccess_path . '</p>', 'disabled' => $htaccess_unlocked ? false : true, 'value' => $htaccess_exists ? file_get_contents($htaccess_path) : '', 'id' => 'wsko_htaccess_field', 'class' => 'form-code textarea-big', 'rows' => '25'));  
									if ($htaccess_unlocked)
									{
										echo WSKO_Class_Template::render_form(array('type' => 'submit', 'title' => '', 'id' => 'wsko_save_htaccess', 'nonce' => 'wsko_save_htaccess'), true);
									}
									else 
									{
										_e('Saving .htaccess is disabled. Go to Settings to unlock the editor. Important: Errors in this file will lead to your whole site going down!', 'wsko');
										
										WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#advanced', __('Settings', 'wsko'), array('button' => false));
									}
								}
								else
								{
									WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'htaccess_unreadable')));
								}
							}
							else
							{
								if (is_readable($htaccess_path))
								{
									WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_tools', 'htaccess_unwritable')));
									WSKO_Class_Template::render_form(array('type' => 'textarea', /*'highlights' => WSKO_Class_Helper::get_code_highlighting_register('htaccess'),*/ 'title' => '.htaccess', 'subtitle' => '<p class="font-unimportant">' . $htaccess_path . '</p>', 'disabled' => true, 'value' => $htaccess_exists ? file_get_contents($htaccess_path) : '', 'class' => 'form-code textarea-big', 'rows' => '25')); 
								}
								else
								{
									WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'htaccess_unreadable')));
								}
							}
						}
						else
						{
							WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'htaccess_uncreatable')));
						}
						?>
					</div>
				</div>		
			</div>
			<div id="wsko_file_robots_tab" class="tab-pane fade">
				<div class="panel panel-default bsu-panel">
					<p class="panel-heading m0"><?=__('robots.txt')?></p>		
					<div class="panel-body">
							<?php 
							if (is_writable(get_home_path()) || $robots_exists)
							{
								if (is_writable($robots_path) || !$robots_exists)
								{
									if (is_readable($robots_path) || !$robots_exists)
									{
										if (!is_writable($backup_path))
										{
											WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'backup_unwritable', array('backup_dir' => $backup_path))));
										}
										/* <textarea id="wsko_robots_field"><?=$robots_exists ? file_get_contents($robots_path) : ''?></textarea> */
										WSKO_Class_Template::render_form(array('type' => 'textarea', /*'highlights' => WSKO_Class_Helper::get_code_highlighting_register('robots'),*/ 'title' => 'robots.txt', 'disabled' => $robots_unlocked ? false : true, 'subtitle' => '<p class="font-unimportant">' . $robots_path . '</p>', 'value' => $robots_exists ? file_get_contents($robots_path) : '', 'id' => 'wsko_robots_field', 'class' => 'form-code textarea-big', 'rows' => '25'));
										if ($robots_unlocked)
										{
											WSKO_Class_Template::render_form(array('type' => 'submit', 'title' => '', 'id' => 'wsko_save_robots', 'nonce' => 'wsko_save_robots'));
										}
										else 
										{
											_e('Saving robots.txt is disabled. Go to Settings to unlock the editor. Important: Errors in this file can seriously impact your rankings!', 'wsko');
											
											WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#advanced', __('Settings', 'wsko'), array('button' => false));
										}
									}
									else
									{
										WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'robots_unreadable')));
									}
								}
								else
								{
									if (is_readable($robots_path))
									{
										WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_tools', 'robots_unwritable')));
										WSKO_Class_Template::render_form(array('type' => 'textarea', /*'highlights' => WSKO_Class_Helper::get_code_highlighting_register('robots'),*/ 'title' => 'robots.txt', 'disabled' => true, 'subtitle' => '<p class="font-unimportant">' . $robots_path . '</p>', 'value' => $robots_exists ? file_get_contents($robots_path) : '', 'id' => 'wsko_robots_field', 'class' => 'form-code textarea-big', 'rows' => '25'));
									}
									else
									{
										WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'robots_unreadable')));
									}
								}
							}
							else
							{
							WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'robots_uncreatable')));
							}
						?>
					</div>
				</div>	
			</div>
		</div>
	</div><?php
	}
	else
	{
		?><div class="col-md-12"><?php
		WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'editors_no_admin')));
		?></div><?php
	} ?>
</div>