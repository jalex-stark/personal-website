<?php
if (!defined('ABSPATH')) exit;

$articles = isset($template_args['articles']) ? $template_args['articles'] : false;
$qa_articles = isset($template_args['qa_articles']) ? $template_args['qa_articles'] : false;
$search = isset($template_args['search']) ? $template_args['search'] : false;
$categories = WSKO_Class_Knowledge::get_knowledge_base_categories();
$qa_categories = WSKO_Class_Knowledge::get_q_and_a_categories();

if ($articles !== false || $qa_articles != false)
{
	if ($categories)
	{
		$categories = (array)$categories;
		uasort($categories, function($a, $b){
			if ($a->prio == $b->prio)
				return 0;
			return $a->prio > $b->prio ? 1 : -1;
		});
		foreach ($categories as $cat => $cat_data)
		{
			$has_article = false;
			foreach ($articles as $a)
			{
				if ($a->categories && in_array($cat, $a->categories))
				{
					$has_article = true;
					if (!isset($categories[$cat]->articles))
						$categories[$cat]->articles = array();
					$categories[$cat]->articles[] = $a;
				}
			}
			if (!$has_article)
				unset($categories[$cat]);
		}
	}
	if ($qa_categories)
	{
		$qa_categories = (array)$qa_categories;
		uasort($qa_categories, function($a, $b){
			if ($a->prio == $b->prio)
				return 0;
			return $a->prio > $b->prio ? 1 : -1;
		});
		foreach ($qa_categories as $cat => $cat_data)
		{
			$has_article = false;
			foreach ($qa_articles as $a)
			{
				if ($a->categories && in_array($cat, $a->categories))
				{
					$has_article = true;
					if (!isset($qa_categories[$cat]->articles))
						$qa_categories[$cat]->articles = array();
					$qa_categories[$cat]->articles[] = $a;
				}
			}
			if (!$has_article)
				unset($qa_categories[$cat]);
		}
	}
	$last_fetch = WSKO_Class_Core::get_option('last_knowledge_base_update');
	?><div class="wsko-search-knowledge-base-wrapper kb-main" style="position:relative;">
		<div class="wsko-kb-header-wrapper">
			<div class="row">
				<div class="col-sm-2 align-center">
					<i class="fa fa-quote-right icon-kb-head" style="margin: 0 auto;"></i>
				</div>
				<div class="col-sm-10">	
					<div class="wsko-kb-search-wrapper">
						<small class="pull-right text-off"><?=__('Last fetch:', 'wsko')?> <?=$last_fetch ? date('d.m.Y H:i', $last_fetch) : __('never', 'wsko') ?></small>
						<h2><?=__('BAVOKO Knowledge Base', 'wsko')?></h2>
						<input style="margin: 0px;" class="form-control wsko-search-knowledge-base mb10" data-nonce="<?=wp_create_nonce('wsko_search_knowledge_base')?>" placeholder="<?=__('Search Articles', 'wsko')?>" value="<?=$search?>">
					</div>
				</div>
			</div>		
		</div>	
			
		<ul class="wsko-nav bsu-tabs bsu-tabs-sm border-dark wsko-mt15">
			<li><a class="wsko-nav-link wsko-nav-link-active" href="#wsko_kb_documentation"><?=__('Documentation', 'wsko')?></a></li>
			<li><a class="wsko-nav-link" href="#wsko_kb_question"><?=__('Question & Answer', 'wsko')?></a></li>
		</ul>
		<div class="wsko-tab-content">
			<div id="wsko_kb_documentation" class="wsko-tab wsko-tab-active">	
				<ul class="wsko-search-knowledge-base-list kb_posts_wrapper"><?php
					if ($categories)
						WSKO_Class_Template::render_template('knowledge/template-article-grouped.php', array('categories' => $categories, 'type' => 'kb'));
					else
						echo '<p class="wsko-text-off">'.sprintf(__('Articles not available yet. Please try again later or visit <a href="%s" target="_blank">bavoko.tools</a>.', 'wsko'), 'https://www.bavoko.tools/knowledge_base/').'</p>';
					?>
				</ul>
			</div>
			<div id="wsko_kb_question" class="wsko-tab">
				<ul class="wsko-search-knowledge-base-list kb_posts_wrapper"><?php
					if ($qa_categories)
						WSKO_Class_Template::render_template('knowledge/template-article-grouped.php', array('categories' => $qa_categories, 'type' => 'q_and_a'));
					else
						echo '<p class="wsko-text-off">'.sprintf(__('Articles not available yet. Please try again later or visit <a href="%s" target="_blank">bavoko.tools</a>.', 'wsko'), 'https://www.bavoko.tools/knowledge_base/').'</p>';
					?>
				</ul>
			<div>
		</div>
	</div><?php
}
else
{
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'kb_empty')));
}
?>