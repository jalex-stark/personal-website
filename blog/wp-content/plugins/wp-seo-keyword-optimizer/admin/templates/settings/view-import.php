<?php
if (!defined('ABSPATH')) exit;

$pluginRef = 0;
$importable_plugins = WSKO_Class_Compatibility::get_importable_plugins(true);
?><div class="wsko-import-plugins-wrapper panel-group" id="wsko_import_plugins_wrapper"><?php
	if ($importable_plugins['installed'])
	{
		foreach ($importable_plugins['installed'] as $k => $pl)
		{
			?>
			<div class="bsu-panel panel panel-default wsko-import-plugin-wrapper z-depth-1" data-nonce="<?=wp_create_nonce('wsko_import_plugin')?>" data-plugin="<?=$k?>">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#wsko_import_plugins_wrapper" href="#wsko_import_plugin<?=$pluginRef?>"><i class="fa fa-plus fa-fw text-off"></i><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span> <?=$pl['title']?> (<?=$pl['active'] ? __('active', 'wsko') : __('inactive', 'wsko')?>)</a>
					</h4>
				</div>
				<div id="wsko_import_plugin<?=$pluginRef?>" class="panel-collapse collapse <?=($pluginRef == 0) ? 'in' : '';?>">
					<div class="panel-body">
						<?php 
						foreach ($pl['options'] as $opt_k => $opt)
						{
							?><p class="mb5"><input class="wsko-import-plugin-option form-control" type="checkbox" value="<?=$opt_k?>"> <?=$opt?></p><?php
						}
						?>
						<a style="margin-top: 5px; " class="wsko-import-plugin button button button-primary" href=""><?=__('Import', 'wsko')?></a>
						<p class="text-off" style="margin-top: 5px; margin-bottom: 0px;"><?=__('This may take a while', 'wsko')?></p>
					</div>
				</div>
			</div>	
			
			<?php
			$pluginRef++;	
		}
	}
	else
	{
		_e('No installed SEO plugins found.', 'wsko');
	}
	if ($importable_plugins['other'])
	{
		?>
		<div id="wsko_import_uninstalled_list" style="margin-top:5px;" class="collapse"><?php
			foreach ($importable_plugins['other'] as $k => $pl)
			{
				?>
				<div class="bsu-panel panel panel-default wsko-import-plugin-wrapper z-depth-1" data-nonce="<?=wp_create_nonce('wsko_import_plugin')?>" data-plugin="<?=$k?>">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#wsko_import_plugins_wrapper" href="#wsko_import_plugin<?=$pluginRef?>"><i class="fa fa-plus fa-fw text-off"></i><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span> <?=$pl['title']?> (<?=__('not installed', 'wsko')?>)</a>
						</h4>
					</div>
					<div id="wsko_import_plugin<?=$pluginRef?>" class="panel-collapse collapse <?=($pluginRef == 0) ? 'in' : '';?>">
						<div class="panel-body">
							<?php 
							foreach ($pl['options'] as $opt_k => $opt)
							{
								?><p class="mb5"><input class="wsko-import-plugin-option form-control" type="checkbox" value="<?=$opt_k?>"> <?=$opt?></p><?php
							}
							?>
							<a style="margin-top: 5px; " class="wsko-import-plugin button button button-primary" href=""><?=__('Import', 'wsko')?></a>
							<p class="text-off" style="margin-top: 5px; margin-bottom: 0px;"><?=__('This may take a while', 'wsko')?></p>
						</div>
					</div>
				</div>	
				
				<?php
				$pluginRef++;	
			}
		?></div>
		<a href="#wsko_import_uninstalled_list" style="margin-top:15px;" class="button" data-toggle="collapse"><?=__('Show not found plugins', 'wsko')?></a>
		<?php
	}
?></div>