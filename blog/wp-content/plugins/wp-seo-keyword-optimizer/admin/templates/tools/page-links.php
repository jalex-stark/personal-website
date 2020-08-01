<?php
if (!defined('ABSPATH')) exit;

$post_types = WSKO_Class_Helper::get_public_post_types('objects');
$post_types_san = array();
foreach ($post_types as $pt)
{
	$post_types_san[$pt->name] = $pt->label;
}
unset($post_types['post']);
unset($post_types['page']);
$taxonomies = WSKO_Class_Helper::get_public_taxonomies('objects');

$redirect_data = WSKO_Class_Onpage::get_all_redirects();
?>
<div class="row">
	<div class="bsu-panel bsu-panel-custom col-md-12 col-xs-12">
		<div class="panel panel-default">
			<p class="panel-heading m0"><?=__('Canonical Settings', 'wsko')?></p>
			<div id="canonical" class="panel-body">
				<div class="row form-group">
					<div class="col-sm-3">
						<p class="m0"><?=__('Automatic Canonicals', 'wsko')?></p>
						<small class="text-off"><?=__('If a page has no specific canonical tag set, BST will add a tag linking to the same resource.', 'wsko')?></small>
					</div>
					<div class="col-sm-9">
						<label>
							<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('auto_canonical') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="auto_canonical">
							<?=__('Activate automatic Canonicals', 'wsko')?>
						</label>
					</div>
				</div>
			</div>	
		</div>
	</div>
	<div class="col-md-12 col-sm-12">
		<div class="bsu-panel bsu-panel-custom">
			<div class="panel panel-default">
				<p class="panel-heading m0"><?=__('Permalink settings', 'wsko')?></p>
				<div class="panel-body">
					<!--div class="row form-group">
						<div class="col-md-3">
							<p>Hide post type slug</p>
							<small class="text-off">Every post is rewritten to cut of the slug added by the corresponding post type. Beware of possible duplicates!</small>
						</div>
						<div class="col-md-9">
							<label>
								<input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('hide_post_types') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="hide_post_types">
								Hide slugs
							</label>
						</div>
					</div-->
					<div class="row form-group" style="padding-bottom:15px;">
						<div class="col-sm-6 col-xs-12">
							<div class="row">
								<div class="col-md-6">
									<p><?=__('Hide category base', 'wsko')?></p>
									<small class="text-off"><?=sprintf(__('Category slug is hidden from the URL, so <i>%s</i> becomes <i>%s</i>', 'wsko'), home_url('/category/term-name/'), home_url('/term-name/'))?></small>
								</div>
								<div class="col-md-6">
									<label>
										<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('hide_category_slug') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="hide_category_slug" data-reload="true" data-alert="<?=__('Do you want to activate redirects from your old category structure?', 'wsko')?>" data-alert-send="true">
										<?=__('Hide category', 'wsko')?>
									</label>
								</div>
							</div>	
						</div>
						<div class="col-sm-6 col-xs-12">
							<div class="row">
								<div class="col-md-6">
									<p><?=__('Redirect old category links', 'wsko')?></p>
									<small class="text-off"><?=__('If checked, BST will redirect the old category URLs (with/without "category") to the new structure. E.G: If you have hidden the category base, activating this option will redirect <i>"/category/general"</i> to <i>"/general"</i>', 'wsko')?></small>
								</div>
								<div class="col-md-6">
									<label>
										<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('hide_category_slug_redirect') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="hide_category_slug_redirect">
										<?=__('Activate Category Redirect', 'wsko')?>
									</label>
								</div>
							</div>	
						</div>	
					</div>
				</div>	
			</div>
		</div>	
	</div>
	<div class="col-sm-12">
		<!--p class="hidden-xs pull-right" style="margin:10px 5px;"><a class="dark text-off wsko-small" target="_blank" href="https://www.bavoko.tools/en/knowledge_base/generate-dynamic-meta-descriptions-titles-in-wordpress/"><i class="fa fa-info fa-fw"></i> Knowledge Base: Generate Dynamic Meta Titles & Descriptions</a></p-->
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="active"><a data-toggle="tab" href="#wsko_links_post_type"><?=__( 'Post Types', 'wsko' )?></a></li>
			<li><a data-toggle="tab" href="#wsko_links_tax"><?=__( 'Taxonomies', 'wsko' )?></a></li>
			<li><a data-toggle="tab" href="#wsko_links_bulk"><?=__('Bulk Tools', 'wsko' )?></a></li>
		</ul>

		<div class="tab-content">
			<div id="wsko_links_post_type" class="tab-pane fade in active">
				<p class="wsko-text-off wsko-small"><i class="fa fa-info fa-fw"></i> <?=__('Change post type slugs', 'wsko')?></p>
				<div class="panel-group" id="wsko_meta_post_type_wrapper">
					<?php
					$f = true;
					if ($post_types) {
						foreach ($post_types as $type)
						{
							$data = WSKO_Class_Helper::get_obj_rewrite_base('post_type', $type->name);
							if ($data)
							{
								$first_slug = $data['original'];
								$curr_slug = $data['current'];
							}
							else
							{
								$first_slug = '<i>'.__('not provided', 'wsko').'</i>';
								$curr_slug =  '<i>'.__('not provided', 'wsko').'</i>';
							}
							$url = get_post_type_archive_link($type->name);
							if (strpos($url, '?') !== false)
							{
								$url .= '&'.$type->name.'=post-name';
							}
							else
							{
								$url .= (WSKO_Class_Helper::ends_with($url, '/')?'':'/').'post-name/';
								$url = str_replace('/'.$curr_slug.'/', '<b>/'.$curr_slug.'/</b>', $url);
							}
							WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => $type->label." <span class='panel-info'>".sprintf(__("Post Type '%s' • %s"), $type->name, $url)."<span>", 'parent' => 'wsko_meta_post_type_wrapper', 'active' => $f, 'lazyVar' => 'post_type_'.$type->name, 'class' => 'wsko-collapse-page'));
							$f = false;
						} 
					} else {
						echo "<p class='wsko-text-off'>".__('No post types available', 'wsko')."</p>";
					}	?>
				</div>
			</div>
			<div id="wsko_links_tax" class="tab-pane fade">
				<p class="wsko-text-off wsko-small"><i class="fa fa-info fa-fw"></i> <?=__('Change taxonomy slugs', 'wsko')?></p>
				<div class="panel-group" id="wsko_meta_tax_wrapper">
					<?php
					$f = true;
					foreach ($taxonomies as $type)
					{
						$data = WSKO_Class_Helper::get_obj_rewrite_base('post_tax', $type->name);
						if ($data)
						{
							$first_slug = $data['original'];
							$curr_slug = $data['current'];
						}
						else
						{
							$first_slug = '<i>'.__('not provided', 'wsko').'</i>';
							$curr_slug =  '<i>'.__('not provided', 'wsko').'</i>';
						}
						$url = home_url($curr_slug.'/a-term/');
						$url = str_replace('/'.$curr_slug.'/', '<b>/'.$curr_slug.'/</b>', $url);
						WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => $type->label." <span class='panel-info'>".sprintf(__("Taxonomy '%s' • %s"), $type->name, $url)."<span>", 'parent' => 'wsko_meta_tax_wrapper', 'active' => $f, 'lazyVar' => 'post_tax_'.$type->name, 'class' => 'wsko-collapse-page'));
						$f = false;
					} ?>
				</div>
			</div>
			<div id="wsko_links_bulk" class="tab-pane fade">
				<p class="wsko-text-off wsko-small"><i class="fa fa-info fa-fw"></i> <?=__('Bulk tools for post slugs', 'wsko')?></p>
				<div class="bsu-panel bsu-panel-chart">
					<div class="panel panel-default wsko-panel-bulk-tools">
						<p class="panel-heading m0"><?=__('Bulk Tools', 'wsko')?></p>
						<?php WSKO_Class_Template::render_table(array(__('Post', 'wsko'), __('Links', 'wsko')), array(), array('ajax' => array('action' => 'wsko_table_tools', 'arg' => 'bulk_links'), 'filter' => array('post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => $post_types_san)))); ?>
					</div>
				</div>	
			</div>
		</div>
	</div>	
</div>