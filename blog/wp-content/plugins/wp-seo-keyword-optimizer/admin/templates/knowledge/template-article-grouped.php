<?php
if (!defined('ABSPATH')) exit;

$categories = isset($template_args['categories']) ? $template_args['categories'] : false;
$type = isset($template_args['type']) ? $template_args['type'] : false;
$uniq_id = WSKO_Class_Helper::get_unique_id('wsko_articles_');
foreach ($categories as $cat => $cat_data)
{
    ?><div class="kb-category-wrapper">
        <h2 class="panel-heading"><?=$cat_data->title?></h2>
        <div class="row">
            <?php
            $max = 5; //pagination setting

            $index = 0;
            $count_a = 0;
            $page = 1;
            ?><div class="tab-content">
                <div id="<?=$uniq_id?>_<?=$cat?>_tab_1" class="tab-pane fade in active"><?php
                foreach ($cat_data->articles as $a)
                {
                    $index++;
                    $count_a++;
                    if ($index > $max)
                    {
                        $index = 1;
                        $page++;
                        ?></div><div id="<?=$uniq_id?>_<?=$cat?>_tab_<?=$page?>" class="tab-pane fade <?=$count_a == 1 ? 'in active' : '' ?>"><?php
                    }
                    ?><div class="col-sm-6 col-xs-12">
                        <div class="kb_posts_item">
                                <a href="#" class="wsko-open-knowledge-base-article dark" data-article="<?=$a->id?>" data-type="<?=$type?>">
                                    <?php /* foreach ($a->categories as $cat) { ?><p class="small text-off wsko-uppercase" style="margin-bottom:10px;"><?=$cat?></p><?php } */ ?>			
                                    <?php if ($a->img) { ?>
                                        <div style="overflow:hidden;">
                                            <img style="width:100%" src="<?=$a->img?>">
                                        </div>    
                                    <?php } ?>
                                    <div class="panel-body">
                                        <h4><?=$a->title?></h4> <?php /* <a href="<?=$a->link?>" target="_blank">Open Page</a><br/> */ ?>			
                                        <p class="wsko-text-off"><?=$a->preview?></p>
                                    </div>    
                                </a>	
                        </div>
                    </div><?php
                }
                ?></div>
            </div><?php
            if ($page > 1)
            {
                ?><div class="clearfix"></div>
                <ul class="nav pagination" style="padding-left: 15px; padding-right: 15px;"><?php
                for($i = 1; $i <= $page; $i++) {
                    ?><li class="<?=$i == 1 ? 'active' : ''?>"><a data-toggle="tab" href="#<?=$uniq_id?>_<?=$cat?>_tab_<?=$i?>"><?=$i?></a></li><?php
                }
                ?></ul><?php
            }
            ?>
        </div> 
    </div><?php
}  
?>
