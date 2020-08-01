<?php
if (!defined('ABSPATH')) exit;

$article = isset($template_args['article']) ? $template_args['article'] : false;
$type = isset($template_args['type']) ? $template_args['type'] : false;

if ($article)
{
	?>
	<div class="col-sm-12 col-xs-12">
		<?php /* if ($article->img) { ?>
			<img style="width:100%" src="<?=$article->img?>">
		<?php } */ ?>
		<h2 style="margin-bottom: 5px;"><?=$article->title?></h2>
		<p style="margin-bottom: 20px;" class="wsko-text-off wsko-small"><a class="dark" href="<?=$article->link?>" target="_blank"><?=$article->link?></a></p>

		<div class="wsko-misc-content-table">
			<?=$article->content?>
		</div>
		<?php if ($type == 'kb') { ?>
			<div class="kb-helpful">
				<p><?=__('Was this article helpful?', 'wsko')?></p>
				<a class="btn btn-primary btn-sm wsko-kb-rate-article" data-good="true" data-post="<?=$article->id?>" style="color:#fff; line-height: 1.5;" data-nonce="<?=wp_create_nonce('wsko_rate_knowledge_base_article')?>"><i class="fa fa-thumbs-up fa-fw"></i> <?=__('Yes', 'wsko')?></a>
				<a class="btn btn-secondary btn-sm wsko-kb-rate-article" data-good="false" data-post="<?=$article->id?>" style="color:#fff; line-height: 1.5;" data-nonce="<?=wp_create_nonce('wsko_rate_knowledge_base_article')?>"><i class="fa fa-thumbs-down fa-fw"></i> <?=__('No', 'wsko')?></a>
			</div>
		<?php } ?>
	</div>	
	<?php
}
else
{
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'help_article_fail')));
}
?>