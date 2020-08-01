<?php
if (!defined('ABSPATH')) exit;

$post_types = WSKO_Class_Helper::get_public_post_types('objects');
$post_types_san = array();
foreach ($post_types as $pt)
{
	$post_types_san[$pt->name] = $pt->label;
}
$taxonomies = WSKO_Class_Helper::get_public_taxonomies('objects');
?>
<div class="row">
	<div class="col-sm-12">
		<p class="hidden-xs pull-right" style="margin:10px 5px;"><a class="dark text-off wsko-small" target="_blank" href="https://www.bavoko.tools/knowledge_base/meta-descriptions-titles-wordpress/#Dynamic_Meta_Titles_038_Descriptions_in_WordPress"><i class="fa fa-info fa-fw"></i> <?=__('Knowledge Base: Generate Dynamic Meta Titles & Descriptions', 'wsko')?></a></p>
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="active"><a data-toggle="tab" href="#wsko_meta_post_type"><?=__( 'Post Types', 'wsko' )?></a></li>
			<li><a data-toggle="tab" href="#wsko_meta_tax"><?=__( 'Taxonomies', 'wsko' )?></a></li>
			<li><a data-toggle="tab" href="#wsko_meta_other"><?=__( 'Other', 'wsko' )?></a></li>
			<li><a data-toggle="tab" href="#wsko_meta_bulk"><?=__( 'Bulk Tools', 'wsko' )?></a></li>
		</ul>

		<div class="tab-content">
			<div id="wsko_meta_post_type" class="tab-pane fade in active">
				<div class="panel-group" id="wsko_meta_post_type_wrapper">
					<?php
					$f = true;
					foreach ($post_types as $type)
					{
						WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => $type->label." <span class='panel-info'>".sprintf(__("Post Type '%s'", 'wsko'), $type->name)."<span>", 'parent' => 'wsko_meta_post_type_wrapper', 'active' => $f, 'lazyVar' => 'post_type_'.$type->name, 'class' => 'wsko-collapse-page'));
						$f = false;
					} ?>
				</div>
			</div>
			<div id="wsko_meta_tax" class="tab-pane fade">
				<div class="panel-group" id="wsko_meta_tax_wrapper">
					<?php
					$f = true;
					foreach ($taxonomies as $type)
					{
						WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => $type->label." <span class='panel-info'>".sprintf(__("Taxonomy '%s'", 'wsko'), $type->name)."<span>", 'parent' => 'wsko_meta_tax_wrapper', 'active' => $f, 'lazyVar' => 'post_tax_'.$type->name, 'class' => 'wsko-collapse-page'));
						$f = false;
					} ?>
				</div>
			</div>
			<div id="wsko_meta_other" class="tab-pane fade">
				<div class="panel-group" id="wsko_meta_other_wrapper">
					<?php
						WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => __('Home', 'wsko')." <span class='panel-info'>".home_url()."<span>", 'parent' => 'wsko_meta_other_wrapper', 'active' => true, 'lazyVar' => 'other_home', 'class' => 'wsko-collapse-page'));
						WSKO_Class_Template::render_panel(array('type' => 'collapse', 'title' => __('Blog Page', 'wsko')." <span class='panel-info'>".home_url()."<span>", 'parent' => 'wsko_meta_other_wrapper', 'lazyVar' => 'other_blog', 'class' => 'wsko-collapse-page'));
						?>
				</div>
			</div>
			<div id="wsko_meta_bulk" class="tab-pane fade">
				<div class="bsu-panel bsu-panel-chart">
					<div class="panel panel-default wsko-panel-bulk-tools">
						<p class="panel-heading m0"><?=__('Bulk Tools', 'wsko')?></p>
						<?php WSKO_Class_Template::render_table(array(__('Post', 'wsko'), __('Metas', 'wsko')), array(), array('ajax' => array('action' => 'wsko_table_tools', 'arg' => 'bulk_metas'), 'filter' => array('post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => $post_types_san)))); ?>
					</div>
				</div>	
			</div>
		</div>
	</div>	
</div>